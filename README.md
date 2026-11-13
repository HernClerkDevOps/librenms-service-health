# Service Health: LibreNMS native widget proof of concept

**Status:** Source package for pilot testing, not installed or tested on your LibreNMS server.

Designed around source code supplied from LibreNMS 26.11.0-dev.215 and Laravel 13.33.0.
The plugin adds one native dashboard widget named **Service Health**.
It does not read databases, change monitoring, create alerts, or modify LibreNMS core files.

## Package contents

- `composer.json`: Composer package metadata and Laravel package auto-discovery.
- `src/Providers/ServiceHealthProvider.php`: loads routes and namespaced Blade views.
- `routes/web.php`: registers authenticated POST widget route under nested `ajax` / `dash` prefixes.
- `src/Http/Controllers/ServiceHealthWidgetController.php`: extends LibreNMS's widget controller.
- `resources/views/widgets/service-health.blade.php`: static proof-of-concept display.

## Recommended production-safe installation path

**Do not** copy these PHP files over LibreNMS core files. **Do not** modify `routes/web.php` or `bootstrap/providers.php` in LibreNMS.

1. Put this package into a private Git repository or package registry (with internal code review). Ensure your Composer repository can resolve `hernandotech/librenms-service-health` version `0.1.0` (a VCS tag or registry publication may be required). A ZIP by itself is not a published Composer package.
2. On a staging copy of LibreNMS, back up the database and configuration. Check `sudo -u librenms git status --short` before modifying dependencies.
3. Use LibreNMS's supported plugin package manager (after the package is discoverable to Composer):

    ```bash
    cd /opt/librenms
    sudo -u librenms ./lnms plugin:add hernandotech/librenms-service-health '0.1.0'
    sudo -u librenms php artisan route:clear
    sudo -u librenms php artisan view:clear
    ```

4. **No `plugin:enable` step is required in this minimal POC**: its Composer provider registers the route when the package is installed. It does not publish legacy plugin hooks or expose an entry in Plugins Admin. If the package isn't discovered, check Laravel's package-discovery output and `bootstrap/cache/packages.php` before attempting changes.
5. Open an existing LibreNMS dashboard and choose **Add Widget > Service Health**. The widget should display a green confirmation banner.
6. Verify the route **in the web UI**, rather than relying solely on `php artisan route:list`, which on your server currently exposes only two API routes. In a staging environment, the widget should be tested while logged in and blocked when logged out.
7. Exercise a normal nightly-update process on staging, confirm the plugin still appears, then inspect `git status --short` and `validate.php`. **Update survival is not proven by this ZIP.**

### Troubleshooting

If Add Widget doesn't show the POC, check the package/provider discovery first, then Laravel web route cache (`route:clear`). Do not modify the tracked LibreNMS routes file.

If you get HTTP 419, the frontend request may be missing CSRF context. The built-in widget AJAX request handles this in ordinary dashboard use.

If the widget returns HTTP 500, inspect `storage/logs/laravel.log` or LibreNMS's configured application log. Disable/remove the package through the documented package-management process if necessary.

If plugin installation cannot resolve the package, it is not yet available in a Composer repository. Do not run `composer require` against the LibreNMS root without first reviewing its update workflow and rollback plan.

## Removing the package

On the staging instance, if installed through `lnms plugin:add`, use `lnms plugin:remove` for the package after confirming its command syntax with `./lnms plugin:remove --help`. Clear route/view caches afterward. Remove any dashboard placement first.

## Important caveat

LibreNMS dashboard discovery via `ajax/dash` routes is an **internal implementation detail**, not a guaranteed stable public widget API. Test each LibreNMS version before production use. This POC does not yet implement plugin enable/disable hooks, service selection, live polling status, alerts, or TDX integration.
