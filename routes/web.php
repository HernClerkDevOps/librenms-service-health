<?php

declare(strict_types=1);

use HernandoTech\ServiceHealth\Http\Controllers\ServiceHealthWidgetController;
use Illuminate\Support\Facades\Route;

// Nested prefixes are intentional: DashboardController::listWidgets()
// checks $route->getPrefix() === 'ajax/dash'.
Route::middleware(['web', 'auth'])->group(function (): void {
    Route::prefix('ajax')->group(function (): void {
        Route::prefix('dash')->group(function (): void {
            Route::post('hernando-service-health', ServiceHealthWidgetController::class)
                ->name('hernandotech.service-health.widget');
        });
    });
});
