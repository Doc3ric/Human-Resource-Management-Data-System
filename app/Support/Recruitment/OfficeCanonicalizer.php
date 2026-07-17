<?php

namespace App\Support\Recruitment;

use App\Models\OrganizationalUnit;

/**
 * applicants.office should represent the office of the VACANCY being applied for, and
 * (as of 2026-07-17) is populated either from a manually-typed office name or derived
 * from the item number's office-code prefix via fromItemNo() — either way it ends up
 * spelled multiple ways (abbreviations, whitespace/dash variants, typos). This resolves
 * a raw value to the matching organizational_units.name when known, so office
 * filters/dropdowns/groupings across the Appointment module don't fragment into
 * near-duplicate entries for what is really the same office.
 *
 * Historical note: earlier rows (pre-2026-07-17) were populated from a CSV "PGB OFFICE"
 * column, which is actually the applicant's own current PGB employer (only meaningful
 * for internal candidates) — a different concept that was mistakenly used as a stand-in
 * for the vacancy's office. Those older rows may still be blank/wrong for external
 * applicants as a result.
 *
 * Known abbreviation/typo groups below were inferred from actual data (2026-07-16) and
 * should be verified against PHRMO records before trusting an unfamiliar merge.
 */
class OfficeCanonicalizer
{
    private static ?array $keyMap = null;

    public static function canonicalize(?string $raw): string
    {
        if ($raw === null || trim($raw) === '') {
            return 'Unassigned Office';
        }

        $clean = trim(preg_replace('/\s+/', ' ', $raw));
        $key = self::normalizeKey($clean);

        return self::keyMap()[$key] ?? $clean;
    }

    /**
     * Derives the office of the VACANCY (not the applicant) from an item number's
     * office-code prefix — e.g. "BPMC-43" -> "BPMC" -> Bukidnon Provincial Medical
     * Center, "BPH-KAL-34" -> "BPH-KAL" -> BPH-Kalilangan. This is the field that
     * should populate applicants.office; the "PGB OFFICE" import column is the
     * applicant's own current employer (only meaningful for internal PGB-employee
     * candidates) and is a different concept entirely — see the class docblock.
     *
     * Returns null when the prefix isn't a recognized office code, rather than
     * guessing — an unrecognized prefix is not useful data to store.
     */
    public static function fromItemNo(?string $itemNo): ?string
    {
        if (!$itemNo || !preg_match('/^(.+)-\d+$/', trim($itemNo), $m)) {
            return null;
        }

        $prefix = trim(preg_replace('/\s+/', ' ', $m[1]));
        $resolved = self::canonicalize($prefix);

        // canonicalize() returns the cleaned input verbatim when it has no mapping
        // for it — that's not an actual office name, just an unrecognized code.
        return $resolved !== $prefix ? $resolved : null;
    }

    /**
     * Given a list of raw office values and a list of selected canonical names,
     * returns the subset of raw values whose canonical form is in the selection.
     * Use this to translate a canonicalized filter selection back into a whereIn().
     *
     * @param  iterable<string>  $rawValues
     * @param  array<string>  $selectedCanonical
     * @return array<string>
     */
    public static function matchingRawValues(iterable $rawValues, array $selectedCanonical): array
    {
        $matches = [];
        foreach ($rawValues as $raw) {
            if (in_array(self::canonicalize($raw), $selectedCanonical, true)) {
                $matches[] = $raw;
            }
        }
        return $matches;
    }

    private static function canonicalGroups(): array
    {
        return [
            'BUKIDNON PROVINCIAL HOSPITAL - KALILANGAN' => ['BPH-KAL'],
            'BUKIDNON PROVINCIAL HOSPITAL - SAN FERNANDO' => ['BPH-SF'],
            'BUKIDNON PROVINCIAL HOSPITAL - MARAMAG' => ['BPH-MAR', 'BPH-MARAMAG'],
            'BUKIDNON PROVINCIAL HOSPITAL - KIBAWE' => ['BPH-KIB', 'BPH-KIBAWE'],
            'BUKIDNON PROVINCIAL HOSPITAL - TALAKAG' => ['BPH-TAL', 'BPH-TALAKAG'],
            'BUKIDNON PROVINCIAL HOSPITAL - MANOLO FORTICH' => ['BPH-MANOLO FORTICH', 'BPH- MANOLO FORTICH'],
            'BUKIDNON PROVINCIAL MEDICAL CENTER' => ['BPMC'],
            'PROVINCIAL ECONOMIC ENTERPRISE DEVELOPMENT AND MANAGEMENT OFFICE' => ['PEEDMO'],
            'PROVINCIAL SOCIAL WELFARE AND DEVELOPMENT OFFICE' => ['PSWDO', 'PROVINCIAL SOCIAL WELFARE DEVELOPMENT OFFICE'],
            'PROVINCIAL TOURISM OFFICE' => ['PGO-TOURISM'],
            "PROVINCIAL GOVERNOR'S OFFICE - PROVINCIAL DETENTION AND REHABILITATION CENTER" => [
                'PGO-PROVINCIAL DETENTION & REHABILATATION CENTER',
                'PGO-PROVINCIAL DETENTION REHABILITATION CENTER',
            ],
            "PROVINCIAL GOVERNOR'S OFFICE - PUBLIC AFFAIRS, INFORMATION AND ASSISTANCE" => [
                'PGO-PUBLIC AFFAIRS INFORMATIONS& ASSISTANCE',
            ],
        ];
    }

    private static function keyMap(): array
    {
        if (self::$keyMap !== null) {
            return self::$keyMap;
        }

        $map = [];

        foreach (OrganizationalUnit::pluck('name') as $name) {
            $map[self::normalizeKey($name)] = $name;
        }

        foreach (self::canonicalGroups() as $canonical => $variants) {
            foreach ($variants as $variant) {
                $map[self::normalizeKey($variant)] = $canonical;
            }
        }

        return self::$keyMap = $map;
    }

    private static function normalizeKey(string $value): string
    {
        $clean = trim(preg_replace('/\s+/', ' ', $value));
        $clean = preg_replace('/\s*-\s*/', '-', $clean);
        return strtoupper($clean);
    }
}
