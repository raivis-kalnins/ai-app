# Free AI Hub 63 v0.7.2 - private-login repair verification

Validated against the supplied v0.7.1 full application package.

- PHP syntax checks passed for all **25 PHP files**.
- JavaScript syntax checks passed for the application's bundled JS and service-worker source.
- Existing automated unit tests: **164 authentication/localization/plugin checks** and **132 UI/search checks** (296 total) passed.
- **7 isolated integration cases** passed:
  - New empty login accepts private one-time activation.
  - Retired shared-password hash is replaced for this individual install.
  - Existing unique password is kept and the marker consumed without changing it.
  - Invalid marker is never applied.
  - A symlink marker is never followed.
  - Without marker, no password is installed.
  - Local HTTP GET serves the login with HTTP 200; a proper authenticated POST signs in.
- Provider API key, hCaptcha site key, search endpoint and user interface language were preserved in migration tests.
- The one-time activation file was removed after a successful local activation.
- All **15 local SVG language flags** remain present.
- No copy of the requested plaintext password exists in the distributed source or ZIP.

**Not tested:** cPanel live server permissions, deployed Apache/Nginx access-control rules, live external AI/search provider performance, or the live `ai.63.lv` installation. Only local PHP integration and browser-style HTTP flows have been tested.

**Important:** The ZIP is personalized for one owner site. It contains a salted bcrypt login-activation hash in `storage/.owner-login-once` and MUST NOT be published or committed. After the owner verifies sign-in, rotate to a unique new login password.
