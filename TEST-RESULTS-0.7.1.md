# Free AI Hub 63 v0.7.1 — local test results

Tested 18 October 2026 against an **isolated temporary deployment**. The live
`ai.63.lv` site was **not modified** and no live hosting credentials were read.

- PHP `php -l`: **25** application scripts passed.
- JavaScript `node --check`: **7** scripts passed.
- JSON, plugin manifests and PWA manifest: **31** files parsed.
- Existing offline security checks: **164** passed.
- Existing offline UI/search checks: **132** passed.
- Local HTTP/security integration: **21** assertions passed, covering:
  - Unconfigured owner login returns **HTTP 200**, not intentional HTTP 503.
  - Sign-in disabled until password is configured; setup link visible.
  - One-time owner setup unavailable until a private code file is created.
  - Incorrect code and mismatched password do not change the login.
  - Successful activation uses a salted bcrypt hash and removes the token file.
  - Existing unrelated stored settings are preserved.
  - Owner setup is unavailable once the login is configured.
  - New password can sign in; Smart Search still loads behind authentication.
  - CSS, JS, flag files load locally.
- Headless Chromium static layout rendering: **1200px, 962px and 400px**;
  the SVG theme icon showed once (not duplicated), desktop two-column layout
  and mobile stacked layout rendered with the expected dimensions.

## Limitations

- The local PHP development server ignores `.htaccess`, so production file-access
  protections MUST be checked on cPanel before creating the private token file.
  `https://ai.63.lv/storage/owner-setup.key` must be HTTP 403/404, never its content.
- We did not verify the production web host, real hCaptcha verification or
  external AI/search providers. The latest full ZIP was used as the base;
  unrelated provider logic was not rewritten.
- The screenshot test used the rendered local HTML/CSS without external network
  access (fonts may differ slightly on the real website).
