# Free AI Hub 63 v0.7.3 — Direct login repair and promotional placement

This package is made for the private `ai.63.lv` installation. It must never be
used as a general public template or committed to a public GitHub repository.

## Upgrade

1. Back up `storage/` and `config.local.php` and the previous application files.
2. Upload the ZIP to the **existing `ai.63.lv` document root** where `index.php`
   already resides; extract **there**. The ZIP uses **root-level files**, so no
   extra `ai-app/` folder is created.
3. Accept overwriting old PHP/CSS/JS, including `includes/bootstrap.php` and `assets/app.css`.
4. **Keep** the existing `storage/settings.json`, chat history, uploads and saved
   provider settings, and keep `config.local.php` unchanged.
5. Reload the login page and verify **v0.7.3** in the footer.
6. Sign in with your previously requested password, completing hCaptcha if your
   current settings enable it. No extra activation key/file or SSH is needed.

## What changed

- The private deployment contains a salted bcrypt **hash** (not the plaintext
  password) in `includes/bootstrap.php`. On the first request where the saved
  password is absent or retired, `aih_migrate_settings()` seeds that hash. An
  already-valid unique owner password is **not** overridden.
- On this repair only, old login-attempt counters and remember tokens are
  cleared; the remaining `settings.json`, provider keys and chat data are kept.
- The previous v0.7.2 hidden marker is *not shipped* and need not be uploaded.
- The desktop DigitalPulse promo moved from beside the login form to below it;
  mobile and tablet show the same top-to-bottom order.
- The CSS and service-worker cache were versioned again to avoid stale designs.

## Troubleshooting

- Still see “Owner password is not configured”? The old `includes/bootstrap.php`
  is probably still running, or PHP cannot write the saved settings file.
  Check the document root and permissions; keep your backups.
- Still see the promo on the right? Hard-refresh, and check that `assets/app.css`
  was overwritten and the service worker updated.
- hCaptcha is separate from the owner setup key. It remains optional; a site
  with the existing hCaptcha option enabled must complete its verification.
- This private ZIP has a known owner password in this conversation, so rotate
  to a new unique password immediately after successful login.

## Release verification

See `TEST-RESULTS-0.7.3.md` for the local PHP/auth/HTTP/UI checks. Real hosting
and third-party AI provider availability cannot be confirmed without deployment.
