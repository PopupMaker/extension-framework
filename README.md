# popupmaker/extension-framework

Shared PHP framework for Popup Maker standalone Pro-tier extensions.

## Install

```bash
composer require popupmaker/extension-framework
```

Requires [Popup Maker](https://wordpress.org/plugins/popup-maker/) core as the runtime host.

## Packagist

Published as [`popupmaker/extension-framework`](https://packagist.org/packages/popupmaker/extension-framework).

To register or update the package index, submit the GitHub repository URL on [Packagist](https://packagist.org/packages/submit):

```
https://github.com/PopupMaker/extension-framework
```

## Repository

Source: [github.com/PopupMaker/extension-framework](https://github.com/PopupMaker/extension-framework)

Releases are tagged semver (`v1.0.0`, etc.).

## Included

- `Plugin\Core` — container bootstrap, `$this->core`, extension registration, license init
- `Services\License` — wraps `PUM_Extension_License`
- `Controllers\Assets` — DEWP/webpack asset registration from config
- `Controllers\Admin\ProUpsell` — Pro migration upsell when Pro is not active
- `Plugin\Controller` — controller base

## Extension config keys

| Key | Purpose |
|-----|---------|
| `asset_packages` | Webpack package definitions for `Assets` controller |
| `pro_upsell.feature_name` | Feature name in admin notice copy |
| `pro_upsell.utm_medium` | UTM medium for `generate_upgrade_url()` |

Standard plugin keys (`slug`, `edd_id`, `name`, `version`, `text_domain`, `basename`, etc.) are required.

## Development

This directory in the Popup Maker monorepo mirrors the canonical GitHub repository. Changes should be committed and pushed to [PopupMaker/extension-framework](https://github.com/PopupMaker/extension-framework), then tagged for release.
