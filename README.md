# popupmaker/extension-framework

Shared PHP scaffold for Popup Maker **standalone extensions** (Pro-tier core addons).

Extensions require this package and only implement feature-specific controllers, config, and install migrations.

## Included

- `Plugin\Core` — container bootstrap, `$this->core`, extension registration, license init
- `Services\License` — `PUM_Extension_License` registration
- `Controllers\Admin\ProUpsell` — Pro migration upsell surfaces
- `Controllers\Assets` — DEWP/webpack asset registration from config
- `Plugin\Controller` — controller base typed to extension core

## Extension config keys

| Key | Purpose |
|-----|---------|
| `asset_packages` | Webpack package definitions for `Assets` controller |
| `pro_upsell.feature_name` | Feature name in admin notice copy |
| `pro_upsell.utm_medium` | UTM medium passed to `generate_upgrade_url()` |

All standard plugin keys (`slug`, `edd_id`, `name`, `version`, `text_domain`, `basename`, etc.) are required.
