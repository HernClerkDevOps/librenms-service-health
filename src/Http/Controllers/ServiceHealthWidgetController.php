<?php

declare(strict_types=1);

namespace HernandoTech\ServiceHealth\Http\Controllers;

use App\Http\Controllers\Widgets\WidgetController;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ServiceHealthWidgetController extends WidgetController
{
    protected string $name = 'hernando-service-health';

    public function getTitle(): string
    {
        return 'Hernando Service Health';
    }

    public function getView(Request $request): View|string
    {
        return view('hernandoservicehealth::widgets.service-health');
    }
}
