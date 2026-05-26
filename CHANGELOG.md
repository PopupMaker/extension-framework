# Changelog

## 1.0.1 - 2026-05-26

- Extend `Plugin\Core` from `PopupMaker\Plugin\Extension` for core service delegation.
- Remove duplicate beta registration; `PUM_Extension_License` owns `item_shortname` betas.

## 1.0.0 - 2026-05-25

- Initial release.
- `Plugin\Core` base for standalone extensions.
- `Services\License` — `PUM_Extension_License` registration.
- `Controllers\Assets` — webpack/DEWP asset registration from config.
- `Controllers\Admin\ProUpsell` — Pro migration upsell surfaces.
- `Plugin\Controller` base class.
