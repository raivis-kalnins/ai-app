# Free AI Hub 63 v0.7.3 - Local validation (18 October 2026)

## Passed

- `php -l` on **25** PHP files; `node --check` on **7** JavaScript files.
- **30** bundled add-on `plugin.json` manifests parsed successfully.
- Existing offline tests: **164** auth/i18n/plugin checks and **132** UI/search checks.
- Password migration integration fixtures (separate temporary sites): fresh
  install, empty saved hash, original retired saved hash, existing unique valid
  hash, and an installation with an overriding `config.local.php`. All passed:
  missing/retired hashes were repaired; valid unique hash was not overwritten.
- Existing provider key, search URL, hCaptcha setting and language preference
  survived the password repair untouched in the fixture.
- HTTP integration: locked/unconfigured login GET returns **200** with enabled
  Sign in control; no owner-setup link; hCaptcha still rejects an unverified
  request when enabled; correct password redirects **302** to chat when
  hCaptcha is disabled in that test fixture; authenticated chat and Smart
  Search pages both return **200**.
- Locally rendered desktop (1280 px) and mobile (390 px) snapshots: login card
  centered; DigitalPulse banner below login and above footer; no horizontal
  overflow at either breakpoint.
- Included SVG flag assets remain available in the supported 15 languages.
- ZIP contents checked for absence of runtime `storage/settings.json`, provider
  tokens, chats, the old hidden activation marker and plaintext owner password.

## Important limitations

- This is a **private, site-specific** ZIP containing a salted bcrypt hash of
  the requested password (not plaintext) in the PHP bootstrap. Keep it private
  and change the password after login.
- No access to the live cPanel filesystem was available. Actual deploy,
  file/directory permissions, PWA caching, hCaptcha delivery and live external
  AI/search-provider performance cannot be guaranteed until checked there.
