<?php

namespace App\Livewire\Forms\Scrim\Concerns;

trait SanitizesScrimGameScores
{
    public function updated(string $path, mixed $newValue): void
    {
        if (! preg_match('/\.(kills|deaths|assists)$/', $path)) {
            return;
        }

        data_set($this, $path, $this->sanitizeScoreValue($newValue));
    }

    protected function sanitizeScoreValue(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value)) {
            return $value >= 0 ? $value : null;
        }

        if (is_numeric($value)) {
            return max(0, (int) $value);
        }

        return null;
    }
}
