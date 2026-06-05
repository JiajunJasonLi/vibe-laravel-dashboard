<?php

namespace App\Services;

class LogStatsService
{
    /**
     * @param  array<string, array<int, string>>  $keywordGroups
     * @return array{
     *     total_matches: int,
     *     groups: array<string, array{total: int, keywords: array<string, int>}>
     * }
     */
    public function analyze(string $logText, array $keywordGroups): array
    {
        $normalizedLogText = strtolower($logText);
        $groups = [];
        $totalMatches = 0;

        foreach ($keywordGroups as $groupName => $keywords) {
            $groupTotal = 0;
            $keywordMatches = [];

            foreach ($keywords as $keyword) {
                $matchCount = substr_count($normalizedLogText, strtolower($keyword));

                $keywordMatches[$keyword] = $matchCount;
                $groupTotal += $matchCount;
            }

            $groups[$groupName] = [
                'total' => $groupTotal,
                'keywords' => $keywordMatches,
            ];

            $totalMatches += $groupTotal;
        }

        return [
            'total_matches' => $totalMatches,
            'groups' => $groups,
        ];
    }
}
