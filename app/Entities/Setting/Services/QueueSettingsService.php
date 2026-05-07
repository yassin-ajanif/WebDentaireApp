<?php

namespace App\Entities\Setting\Services;

use App\Entities\Setting\Contracts\QueueSettingsServiceInterface;
use App\Entities\Setting\Models\Setting;

class QueueSettingsService implements QueueSettingsServiceInterface
{
    private const KEY = 'queue_prediction';

    public function getQueuePredictionConfig(): array
    {
        $row = Setting::query()->where('key', self::KEY)->first();

        if ($row === null) {
            return [
                'average_consultation_minutes' => 20,
            ];
        }

        $value = $row->value;

        return [
            'average_consultation_minutes' => (int) ($value['average_consultation_minutes'] ?? 20),
        ];
    }

    public function updateQueuePredictionConfig(array $config): void
    {
        $minutes = max(1, min(480, (int) ($config['average_consultation_minutes'] ?? 20)));

        Setting::query()->updateOrCreate(
            ['key' => self::KEY],
            ['value' => ['average_consultation_minutes' => $minutes]],
        );
    }
}
