# Service Health for LibreNMS

A native LibreNMS dashboard widget for selected monitored services.

Version 0.2.0 is designed against LibreNMS 26.11 source interfaces. It reads the existing `App\Models\Service` records through LibreNMS's device access scope and does not modify LibreNMS core, monitoring configuration, or the database schema.

## Features

- Per-widget selection of one or more accessible LibreNMS services
- Native widget title and automatic refresh settings
- Configurable stale threshold
- OK, warning, critical, unknown, stale, ignored, and disabled states
- Response time when LibreNMS's stored service message contains a recognizable time value
- Last poll time, based on the monitored device's `last_polled` value
- Links to each service's device Services page
- Manual refresh button

## Behavior and limits

LibreNMS stores the latest service status and message, but it does not store a dedicated last-poll timestamp or current performance value in the `services` table. The widget therefore:

- uses the parent device's `last_polled` time for last poll and stale detection;
- extracts response time only from recognizable text in `service_message` (for example, `time=24ms` or `0.123 second response time`); and
- displays `N/A` when a response time is not present in that message.

The widget only returns services allowed by LibreNMS's `Service::hasAccess()` scope. A configured service that was removed or is no longer accessible is counted as unavailable without revealing its details.

Dashboard discovery through the `ajax/dash` route prefix is an internal LibreNMS interface. Re-test this package with every LibreNMS upgrade.

## Requirements

- LibreNMS 26.11
- PHP 8.2 or newer
- `librenms/plugin-interfaces` 1.x
- Existing LibreNMS service checks

## Test

From the package checkout:

```bash
composer validate --strict
composer test
```

The test suite covers status mapping, stale and unknown behavior, disabled and ignored states, last-poll handling, and response-time extraction. A staging LibreNMS acceptance test is still required because this repository does not bundle LibreNMS itself.

## Upgrade from 0.1.0

Do this on staging first. Do not edit `/opt/librenms` core files.

1. Record the currently installed version and save the existing widget settings or a dashboard screenshot.
2. Confirm the 0.2.0 tag is available to the Composer repository used by LibreNMS.
3. Check the LibreNMS tree before changing dependencies:

   ```bash
   cd /opt/librenms
   sudo -u librenms git status --short
   ```

4. Use LibreNMS's plugin package manager:

   ```bash
   sudo -u librenms ./lnms plugin:add hernandotech/librenms-service-health '0.2.0'
   sudo -u librenms php artisan optimize:clear --except=cache
   ```

5. Open the dashboard, edit the Service Health widget, select the services, set the stale threshold and refresh interval, then save.
6. Verify each selected service's state, service link, last poll, automatic refresh, manual refresh, and access behavior with a restricted LibreNMS user.
7. Run LibreNMS validation and inspect the working tree:

   ```bash
   sudo -u librenms ./validate.php
   sudo -u librenms git status --short
   ```

8. Exercise the normal LibreNMS daily update on staging and repeat the checks before requesting production approval.

No production deployment is part of this repository change.

## Rollback to 0.1.0

If 0.2.0 fails staging acceptance:

1. Remove Service Health widgets from affected dashboards, or record their placement before removal.
2. Reinstall the last known good release through the plugin package manager:

   ```bash
   cd /opt/librenms
   sudo -u librenms ./lnms plugin:remove hernandotech/librenms-service-health
   sudo -u librenms ./lnms plugin:add hernandotech/librenms-service-health '0.1.0'
   sudo -u librenms php artisan optimize:clear --except=cache
   ```

3. Re-add the 0.1.0 widget if its dashboard placement was removed.
4. Run `sudo -u librenms ./validate.php` and confirm `git status --short` contains no unexpected LibreNMS core changes.

Confirm the exact `plugin:remove` syntax with `./lnms plugin:remove --help` on the target LibreNMS version before executing rollback.

## Troubleshooting

- Widget missing from Add Widget: check Composer package discovery and `bootstrap/cache/packages.php`, then clear the route and view caches. Do not edit LibreNMS's tracked routes.
- HTTP 419: verify the request is made from the authenticated LibreNMS dashboard so the normal CSRF context is included.
- HTTP 500: inspect `storage/logs/laravel.log` or the configured application log, then roll back through the plugin manager if needed.
- No services in settings: verify service checks exist and the signed-in user can access their devices.
- Stale despite recent service results: compare the device's `last_polled` time with the configured stale threshold and confirm the device poller is running.
- Response shows N/A: the latest stored service message does not contain a supported response-time format. The status remains valid.

## Package layout

- `src/Http/Controllers/ServiceHealthWidgetController.php`: access-controlled service query and widget settings
- `src/Support/ServiceHealthPresenter.php`: status, age, and response-time presentation logic
- `resources/views/widgets/service-health.blade.php`: widget display
- `resources/views/widgets/settings/service-health.blade.php`: service selection and stale configuration
- `routes/web.php`: authenticated native widget route
- `tests/run.php`: dependency-free presentation tests
- `docs/COMPATIBILITY.md`: inspected LibreNMS 26.11 source contracts and assumptions

## License

MIT
