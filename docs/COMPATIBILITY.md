# LibreNMS 26.11 compatibility notes

The v0.2.0 implementation was checked against LibreNMS source commit `54add18697eba99d43048d5e82690db6ae867951`, the 26.11 development source available during implementation.

Interfaces used:

- `app/Models/Service.php`: service fields, `hasAccess()` inheritance, and the `device` relationship
- `app/Models/Device.php`: `last_polled`, `display`, and service relationship behavior
- `app/Http/Controllers/Widgets/WidgetController.php`: widget settings, refresh value, invocation, and JSON response contract
- `resources/views/widgets/settings/base.blade.php`: native settings form and refresh control
- `resources/views/overview/default.blade.php`: array setting serialization, refresh scheduling, manual reload behavior, and `ajax/dash/{widget}` requests
- `includes/services.inc.php`: Nagios status values and service performance-data handling
- `app/Http/Parsers/AlertLogDetailParser.php`: canonical device Services page link shape

Important compatibility assumptions:

1. Dashboard widget discovery continues to include authenticated POST routes whose prefix is exactly `ajax/dash`.
2. `App\Models\Service` continues to inherit `hasAccess()` through `DeviceRelatedModel`.
3. Nagios-compatible service status values remain 0 OK, 1 warning, 2 critical, and 3 unknown.
4. The parent device's `last_polled` value remains the closest supported timestamp for a service poll.
5. The dashboard continues to serialize `name[]` settings fields as arrays.

These are internal LibreNMS interfaces, not a stable public widget API. Recheck them before declaring compatibility with a later LibreNMS release.
