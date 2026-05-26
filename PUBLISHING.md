## Publishing

This package is published independently of extension plugins.

- **GitHub:** https://github.com/PopupMaker/extension-framework
- **Packagist:** https://packagist.org/packages/popupmaker/extension-framework

Extensions require it via Composer only:

```json
"require": {
  "popupmaker/extension-framework": "^1.0"
}
```

Do not use `path` repositories in extension `composer.json`.

Release workflow:

1. Commit and push to `PopupMaker/extension-framework`
2. Tag semver release (`git tag v1.0.1 && git push origin v1.0.1`)
3. Packagist auto-updates (or trigger manual update on packagist.org)
