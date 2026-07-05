<?php

namespace App\Support\Incident;

/**
 * Module 3A.2 — deterministic, on-premise rules-based advisory (the spec
 * explicitly allows this instead of an external/LLM model, provided no SPI
 * leaves the LAN — this keeps everything local and auditable).
 *
 * CORE SAFEGUARD: this produces an ADVISORY DRAFT ONLY. It is never a
 * finding, never auto-applied, and always requires human review before
 * anything is finalized (Module 3A.3).
 */
class RulesAdvisoryEngine
{
    public const ADVISORY_LABEL = 'ADVISORY DRAFT — AI/RULES-ASSISTED. NOT A FINDING OR PENALTY. For review and determination by the authorized officer under 2025 RACCS due process.';

    /** @var array<string, array{keywords: string[], classification: string, penalty_range: string, suggested_track: string}> */
    private const PROVISION_MAP = [
        'Habitual Tardiness (2025 RACCS Rule 10 §63(C)(10))' => [
            'keywords' => ['late', 'tardy', 'tardiness'],
            'classification' => 'light',
            'penalty_range' => 'Reprimand (1st offense) → 1-30 days suspension (2nd) → Dismissal (3rd)',
            'suggested_track' => 'counseling',
        ],
        'Habitual Absenteeism (2025 RACCS, grave offense)' => [
            'keywords' => ['absent', 'absence', 'awol', 'no call no show'],
            'classification' => 'grave',
            'penalty_range' => 'Suspension 6 months 1 day to 1 year (1st) → Dismissal (2nd)',
            'suggested_track' => 'escalated',
        ],
        'Discourtesy in the Course of Official Duties (light offense)' => [
            'keywords' => ['rude', 'disrespect', 'discourteous', 'shouted', 'argument'],
            'classification' => 'light',
            'penalty_range' => 'Reprimand (1st) → Suspension 1-30 days (2nd) → Dismissal (3rd)',
            'suggested_track' => 'counseling',
        ],
        'Simple Neglect of Duty (less grave offense)' => [
            'keywords' => ['negligence', 'neglect', 'failed to submit', 'failed to complete', 'missed deadline'],
            'classification' => 'less_grave',
            'penalty_range' => 'Suspension 1 month 1 day to 6 months (1st) → Dismissal (2nd)',
            'suggested_track' => 'counseling',
        ],
        'Grave Misconduct / Dishonesty (grave offense)' => [
            'keywords' => ['theft', 'stole', 'falsif', 'fraud', 'dishonesty', 'harassment', 'assault', 'threat'],
            'classification' => 'grave',
            'penalty_range' => 'Dismissal (1st offense)',
            'suggested_track' => 'escalated',
        ],
        'Safety/Property Concern (facts-only — office policy review)' => [
            'keywords' => ['injury', 'accident', 'damage', 'broken', 'safety hazard'],
            'classification' => 'unclassified',
            'penalty_range' => 'Not a disciplinary matter by default — refer to office safety policy',
            'suggested_track' => 'counseling',
        ],
    ];

    /**
     * @return array{citations: array, penalty_range: string, recommendation: string, suggested_track: string}
     */
    public function draft(string $narrative, string $category): array
    {
        $haystack = strtolower($narrative . ' ' . $category);
        $matches = [];

        foreach (self::PROVISION_MAP as $provision => $rule) {
            foreach ($rule['keywords'] as $keyword) {
                if (str_contains($haystack, $keyword)) {
                    $matches[] = [
                        'provision' => $provision,
                        'classification' => $rule['classification'],
                        'penalty_range' => $rule['penalty_range'],
                        'confidence' => 'keyword-match (deterministic, not probabilistic)',
                    ];
                    break;
                }
            }
        }

        if ($matches === []) {
            $matches[] = [
                'provision' => 'No specific provision matched — manual classification needed',
                'classification' => 'unclassified',
                'penalty_range' => 'N/A',
                'confidence' => 'no keyword match',
            ];
        }

        $mostSevere = $this->mostSevereMatch($matches);
        $suggestedTrack = $mostSevere ? (self::PROVISION_MAP[$mostSevere['provision']]['suggested_track'] ?? 'counseling') : 'counseling';

        $recommendation = $suggestedTrack === 'escalated'
            ? 'Facts described may constitute a grave or less-grave offense. Recommend escalation to the formal disciplinary process for review; do not proceed with informal counseling alone.'
            : 'Recommend a coaching/counseling session addressing the described conduct, with a documented improvement plan and follow-up date. Consider referral to employee wellness or relevant training if applicable.';

        return [
            'citations' => $matches,
            'penalty_range' => implode('; ', array_column($matches, 'penalty_range')),
            'recommendation' => $recommendation,
            'suggested_track' => $suggestedTrack,
        ];
    }

    private function mostSevereMatch(array $matches): ?array
    {
        $severity = ['grave' => 3, 'less_grave' => 2, 'light' => 1, 'unclassified' => 0];
        usort($matches, fn ($a, $b) => ($severity[$b['classification']] ?? 0) <=> ($severity[$a['classification']] ?? 0));

        return $matches[0] ?? null;
    }
}
