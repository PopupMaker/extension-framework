# Changelog

## 1.0.5 - 2026-05-27

- Rank Pro bundled features by value and dynamically highlight the top 3–4 (excluding the active extension) in upsell copy.

## 1.0.4 - 2026-05-27

- Pro panel upsell now mentions the current bundled extension once (e.g. "Scheduling plus Analytics…") instead of omitting it.

## 1.0.3 - 2026-05-27

- Make Pro upsell copy context-aware: exclude the current extension from bundled feature lists so Scheduling no longer reads "Scheduling + Scheduling…".

## 1.0.2 - 2026-05-26

- Add Pro upsell to the core notifications panel via `pum_alert_list` (`category: offer`).
- Persist admin notice dismissal through core `_pum_dismissed_alerts` user meta.
- Use separate dismissal codes for the admin notice and notifications panel.

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
