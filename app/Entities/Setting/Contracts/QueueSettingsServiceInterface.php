<?php

namespace App\Entities\Setting\Contracts;

interface QueueSettingsServiceInterface
{
    /**
     * @return array{average_consultation_minutes: int}
     */
    public function getQueuePredictionConfig(): array;

    /**
     * @param  array{average_consultation_minutes: int}  $config
     */
    public function updateQueuePredictionConfig(array $config): void;
}
