# Changed in v0.7.3

- `includes/bootstrap.php`: private bcrypt seed, migration repair, version and login state cleanup.
- `assets/app.css`: centered login and bottom-positioned responsive DigitalPulse promo.
- `includes/ui_extras.php`: version label in footer for deploy verification.
- `service-worker.js`: fresh static/PWA cache version.
- `VERSION`, `README.md`, `CHANGELOG.md`: release version and instructions.
- `INSTALL-FIRST-0.7.3.txt`, `UPGRADE-0.7.3.md`, `CHANGED-FILES-0.7.3.md`, `TEST-RESULTS-0.7.3.md`: new release documentation.
- Removed old shipped `storage/.owner-login-once` marker and outdated `INSTALL-FIRST-0.7.2.txt`.

This is the **full application**, not just changed files. No user runtime
settings, chats, uploads, API keys or unencrypted passwords are included.
