<?php

namespace App\Support\Recruitment;

/**
 * applicants.position_applied is free text — the same real vacancy item often ends up with
 * several slightly different strings (trailing comma/period, embedded "SG-16, Monthly Salary
 * 43,560" vs "...43,560.00", comma placement). applicants.item_no is the authoritative Plantilla
 * item identifier for a vacancy, so it should be preferred as the grouping/filter key over the
 * free-text title wherever one is available — mirroring what OfficeCanonicalizer does for office.
 *
 * Records without an item_no (older/manually-entered rows) fall back to a lightly cleaned
 * position_applied string, same as office falls back to "Unassigned Office".
 */
class VacancyIdentifier
{
    /**
     * The key that identifies "this specific vacancy" for grouping/filtering purposes.
     * Prefer item_no (authoritative); fall back to cleaned position_applied text when
     * item_no is blank.
     */
    public static function key(?string $itemNo, ?string $positionApplied): string
    {
        $itemNo = trim((string) $itemNo);
        if ($itemNo !== '') {
            return $itemNo;
        }

        $clean = self::cleanPositionText($positionApplied);
        return $clean !== '' ? $clean : 'Unassigned Position';
    }

    /** Trims stray trailing punctuation/whitespace from a free-text position title. */
    public static function cleanPositionText(?string $raw): string
    {
        if ($raw === null) {
            return '';
        }
        $clean = trim(preg_replace('/\s+/', ' ', $raw));
        return rtrim($clean, " ,.");
    }

    /**
     * Picks a representative display label for a group of applicants sharing the same
     * vacancy key — the most common cleaned position_applied text among them.
     *
     * @param  iterable<string|null>  $positionTexts
     */
    public static function labelFor(iterable $positionTexts): string
    {
        $counts = [];
        foreach ($positionTexts as $text) {
            $clean = self::cleanPositionText($text);
            if ($clean === '') {
                continue;
            }
            $counts[$clean] = ($counts[$clean] ?? 0) + 1;
        }

        if (empty($counts)) {
            return 'Unassigned Position';
        }

        arsort($counts);
        return array_key_first($counts);
    }

    /**
     * Splits a selected list of vacancy keys (from a filter dropdown built with key()/labelFor())
     * back into the raw values needed for a precise SQL WHERE clause: item_no values to match
     * exactly, and (only for rows with a blank item_no) position_applied text values to match
     * exactly. Mirrors OfficeCanonicalizer::matchingRawValues().
     *
     * @param  iterable<object{item_no:?string,position_applied:?string}>  $rows
     * @param  array<string>  $selectedKeys
     * @return array{itemNos: array<string>, fallbackTexts: array<string>}
     */
    public static function matchingConditions(iterable $rows, array $selectedKeys): array
    {
        $itemNos = [];
        $fallbackTexts = [];

        foreach ($rows as $row) {
            if (!in_array(self::key($row->item_no, $row->position_applied), $selectedKeys, true)) {
                continue;
            }
            if (trim((string) $row->item_no) !== '') {
                $itemNos[] = $row->item_no;
            } else {
                $fallbackTexts[] = $row->position_applied;
            }
        }

        return [
            'itemNos' => array_values(array_unique($itemNos)),
            'fallbackTexts' => array_values(array_unique($fallbackTexts)),
        ];
    }

    /** Applies the split from matchingConditions() onto a query builder as a WHERE clause. */
    public static function applyMatch($query, array $conditions): void
    {
        $itemNos = $conditions['itemNos'];
        $fallbackTexts = $conditions['fallbackTexts'];

        $query->where(function ($q) use ($itemNos, $fallbackTexts) {
            $matchedAnything = false;
            if (!empty($itemNos)) {
                $q->orWhere(function ($subQ) use ($itemNos) {
                    $subQ->whereIn('item_no', $itemNos);
                    foreach ($itemNos as $itemNo) {
                        $subQ->orWhere('position_applied', 'like', "%{$itemNo}%");
                    }
                });
                $matchedAnything = true;
            }
            if (!empty($fallbackTexts)) {
                $q->orWhere(function ($q2) use ($fallbackTexts) {
                    $q2->where(function ($q3) {
                        $q3->whereNull('item_no')->orWhere('item_no', '');
                    })->whereIn('position_applied', $fallbackTexts);
                });
                $matchedAnything = true;
            }
            if (!$matchedAnything) {
                // Nothing matched the selection — return no rows rather than an unconstrained query.
                $q->whereRaw('1 = 0');
            }
        });
    }
}
