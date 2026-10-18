# 0.7.0 — Complete release

This release ZIP contains the FULL application, not only the paths below.
Runtime files, passwords and API secrets are intentionally excluded.

- `index.php` — improved login/workspace UI, localized flags, promo, search controls.
- `includes/ui_extras.php` — new language dropdown, theme button, DigitalPulse CTA/credit.
- `includes/search.php` — search concurrency, async AI analysis, result cache update.
- `api.php` — authenticated follow-up search analysis with session-lock release.
- `includes/bootstrap.php` and `VERSION` — v0.7.0.
- `assets/app.js` — progressive result updates and browser-side filtering/sorting.
- `assets/login.js`, `assets/locale.js` — smaller login and accessible language handling.
- `assets/app.css` — responsive UI and promotional styles.
- `assets/flags/*.svg` — 15 locally served country flags.
- `service-worker.js` — new versioned static asset cache.
- `README.md`, `CHANGELOG.md`, `UPGRADE-0.7.0.md` — release notes.
- `tests/ui-search-unit.php` — offline UI/search tests.

---

# Changed files - v0.6.9 security update

Upload the listed new/changed files over the existing application, preserving paths.
Include dotfiles. Keep your LIVE storage/settings.json, uploads and local config.
The shared default is rejected: read SECURITY-UPDATE.md before updating.

- `.gitignore`
- `.htaccess`
- `CHANGELOG.md`
- `README.md`
- `SECURITY-UPDATE.md`
- `TEST-RESULTS.md`
- `VERSION`
- `api.php`
- `assets/app.js`
- `config.example.php`
- `docs/.htaccess`
- `docs/nginx-security.conf.example`
- `includes/.htaccess`
- `includes/auth.php`
- `includes/bootstrap.php`
- `includes/i18n.php`
- `includes/i18n_extra.php`
- `index.php`
- `service-worker.js`
- `storage/.htaccess`
- `storage/index.php`
- `tests/.htaccess`
- `tests/README.md`
- `tests/auth-unit.php`
- `tools/.htaccess`
- `tools/set-password.php`

Removed from the DISTRIBUTION only (do not delete these from a live installation):
- storage/settings.json
- storage/provider_state.json
- storage/last_cleanup.txt

Untrack runtime data in Git using the --cached instructions in SECURITY-UPDATE.md.
Applying an overlay ZIP cannot remove files already tracked in your Git repository.
