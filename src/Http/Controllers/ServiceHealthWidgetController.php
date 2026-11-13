<?php

declare(strict_types=1);

namespace HernandoTech\ServiceHealth\Http\Controllers;

use App\Http\Controllers\Widgets\WidgetController;
use App\Models\Service;
use HernandoTech\ServiceHealth\Support\ServiceHealthPresenter;
use Illuminate\Http\Request;
use Illuminate\View\View;
use LibreNMS\Util\Url;

final class ServiceHealthWidgetController extends WidgetController
{
    protected string $name = 'hernando-service-health';
    protected $defaults = [
        'title' => null,
        'service_ids' => [],
        'stale_after_minutes' => 15,
        'refresh' => 60,
    ];

    public function getTitle(): string
    {
        return 'Service Health';
    }

    public function getView(Request $request): View|string
    {
        $settings = $this->getSettings();
        $serviceIds = $this->normalizeServiceIds($settings['service_ids'] ?? []);

        if ($serviceIds === []) {
            return $this->getSettingsView($request);
        }

        $staleAfterMinutes = max(1, min(1440, (int) ($settings['stale_after_minutes'] ?? 15)));
        $services = Service::query()
            ->hasAccess($request->user())
            ->with('device')
            ->whereIntegerInRaw('service_id', $serviceIds)
            ->orderBy('device_id')
            ->orderBy('service_name')
            ->get();

        $presenter = new ServiceHealthPresenter();
        $rows = $services->map(function (Service $service) use ($presenter, $staleAfterMinutes): array {
            $device = $service->device;
            $row = $presenter->present(
                $service->getAttributes(),
                $device?->last_polled,
                new \DateTimeImmutable('now'),
                $staleAfterMinutes,
            );
            $row['device'] = $device?->display ?? $device?->hostname ?? 'Unknown device';
            $row['url'] = Url::deviceUrl($service->device_id, ['tab' => 'services', 'view' => 'detail']);

            return $row;
        });

        return view('hernandoservicehealth::widgets.service-health', [
            'id' => $settings['id'],
            'rows' => $rows,
            'missingCount' => count($serviceIds) - $rows->count(),
            'staleAfterMinutes' => $staleAfterMinutes,
        ]);
    }

    public function getSettingsView(Request $request): View
    {
        $settings = $this->getSettings(true);
        $settings['service_ids'] = $this->normalizeServiceIds($settings['service_ids'] ?? []);
        $settings['services'] = Service::query()
            ->hasAccess($request->user())
            ->with('device')
            ->orderBy('device_id')
            ->orderBy('service_name')
            ->get();

        return view('hernandoservicehealth::widgets.settings.service-health', $settings);
    }

    /** @return list<int> */
    private function normalizeServiceIds(mixed $value): array
    {
        $values = is_array($value) ? $value : explode(',', (string) $value);

        return array_values(array_unique(array_filter(
            array_map(static fn (mixed $id): int => (int) $id, $values),
            static fn (int $id): bool => $id > 0,
        )));
    }
}
