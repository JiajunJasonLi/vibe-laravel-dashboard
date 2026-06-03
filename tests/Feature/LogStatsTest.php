<?php

namespace Tests\Feature;

use Tests\TestCase;

class LogStatsTest extends TestCase
{
    public function test_it_counts_keyword_matches_by_group(): void
    {
        $response = $this->postJson('/api/log-stats', [
            'log_text' => implode("\n", [
                'INFO Service started',
                'ERROR Timeout while connecting to database',
                'warning: database latency exceeded threshold',
                'error retry failed after timeout',
            ]),
            'keyword_groups' => [
                'severity' => ['error', 'warning'],
                'systems' => ['database', 'timeout'],
            ],
        ]);

        $response
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    'total_matches' => 7,
                    'groups' => [
                        'severity' => [
                            'total' => 3,
                            'keywords' => [
                                'error' => 2,
                                'warning' => 1,
                            ],
                        ],
                        'systems' => [
                            'total' => 4,
                            'keywords' => [
                                'database' => 2,
                                'timeout' => 2,
                            ],
                        ],
                    ],
                ],
            ]);
    }

    public function test_it_validates_required_payload_fields(): void
    {
        $response = $this->postJson('/api/log-stats', [
            'log_text' => '',
            'keyword_groups' => [
                'severity' => [],
            ],
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'log_text',
                'keyword_groups.severity',
            ]);
    }
}
