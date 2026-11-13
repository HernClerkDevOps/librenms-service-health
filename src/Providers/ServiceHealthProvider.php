<?php

declare(strict_types=1);

namespace HernandoTech\ServiceHealth\Providers;

use Illuminate\Support\ServiceProvider;

final class ServiceHealthProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'hernandoservicehealth');
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
    }
}
