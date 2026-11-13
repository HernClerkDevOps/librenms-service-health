<?php

declare(strict_types=1);

namespace HernandoTech\ServiceHealth\Support;

final class ServiceHealthPresenter
{
    /**
     * @param array<string, mixed> $service
     * @return array<string, mixed>
     */
    public function present(
        array $service,
        ?\DateTimeInterface $lastPolled,
        \DateTimeInterface $now,
        int $staleAfterMinutes,
    ): array {
        $status = $this->status($service, $lastPolled, $now, $staleAfterMinutes);

        return [
            'id' => (int) ($service['service_id'] ?? 0),
            'name' => $this->name($service),
            'type' => (string) ($service['service_type'] ?? ''),
            'message' => trim((string) ($service['service_message'] ?? '')),
            'status' => $status['label'],
            'statusClass' => $status['class'],
            'responseTime' => $this->responseTime((string) ($service['service_message'] ?? '')),
            'lastPoll' => $lastPolled?->format('Y-m-d H:i:s T'),
            'lastPollAge' => $this->age($lastPolled, $now),
        ];
    }

    /** @param array<string, mixed> $service */
    private function name(array $service): string
    {
        foreach (['service_name', 'service_desc', 'service_type'] as $field) {
            $value = trim((string) ($service[$field] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return 'Service #' . (int) ($service['service_id'] ?? 0);
    }

    /**
     * @param array<string, mixed> $service
     * @return array{label: string, class: string}
     */
    private function status(
        array $service,
        ?\DateTimeInterface $lastPolled,
        \DateTimeInterface $now,
        int $staleAfterMinutes,
    ): array {
        if (! empty($service['service_disabled'])) {
            return ['label' => 'Disabled', 'class' => 'default'];
        }

        if (! empty($service['service_ignore'])) {
            return ['label' => 'Ignored', 'class' => 'default'];
        }

        if ($lastPolled === null) {
            return ['label' => 'Unknown', 'class' => 'info'];
        }

        if (($now->getTimestamp() - $lastPolled->getTimestamp()) > ($staleAfterMinutes * 60)) {
            return ['label' => 'Stale', 'class' => 'warning'];
        }

        return match ((int) ($service['service_status'] ?? 3)) {
            0 => ['label' => 'OK', 'class' => 'success'],
            1 => ['label' => 'Warning', 'class' => 'warning'],
            2 => ['label' => 'Critical', 'class' => 'danger'],
            default => ['label' => 'Unknown', 'class' => 'info'],
        };
    }

    private function age(?\DateTimeInterface $lastPolled, \DateTimeInterface $now): string
    {
        if ($lastPolled === null) {
            return 'Never';
        }

        $seconds = max(0, $now->getTimestamp() - $lastPolled->getTimestamp());
        if ($seconds < 60) {
            return $seconds . 's ago';
        }
        if ($seconds < 3600) {
            return intdiv($seconds, 60) . 'm ago';
        }
        if ($seconds < 86400) {
            return intdiv($seconds, 3600) . 'h ago';
        }

        return intdiv($seconds, 86400) . 'd ago';
    }

    private function responseTime(string $message): ?string
    {
        $patterns = [
            '/(?:time|latency|response(?:\s+time)?)\s*[=:]\s*([0-9]+(?:\.[0-9]+)?)\s*(us|ms|s(?:ec(?:ond)?s?)?)/i',
            '/([0-9]+(?:\.[0-9]+)?)\s*(us|ms|s(?:ec(?:ond)?s?)?)\s+(?:response\s+time|latency)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message, $matches) === 1) {
                $value = (float) $matches[1];
                $unit = strtolower($matches[2]);
                $milliseconds = match (true) {
                    $unit === 'us' => $value / 1000,
                    $unit === 'ms' => $value,
                    default => $value * 1000,
                };

                return $milliseconds >= 1000
                    ? rtrim(rtrim(number_format($milliseconds / 1000, 2, '.', ''), '0'), '.') . ' s'
                    : rtrim(rtrim(number_format($milliseconds, 2, '.', ''), '0'), '.') . ' ms';
            }
        }

        return null;
    }
}
