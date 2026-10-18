# Free AI Hub 63 v0.7.2 — private login repair for ai.63.lv

**This ZIP is personalised. It contains a salted one-time password hash for the password that the owner already supplied privately.** No plaintext password is included. Do NOT publish or push this ZIP to GitHub, share it, or reuse it for other projects. It is intended for the one affected `ai.63.lv` installation only.

## Upload on cPanel (no setup code or SSH)

1. Back up the complete website, especially `storage/settings.json` and `config.local.php` in a safe, private location.
2. Upload **all contents of `ai-app/`** from this ZIP into the `ai.63.lv` document root, replacing application code. Use merge/overwrite for regular application files **but do not replace or delete any existing storage data or `config.local.php`**.
3. Inside the archive is `ai-app/storage/.owner-login-once` (hidden file). **Make sure this one new file is copied to the EXISTING `storage/` directory** alongside `settings.json`. Enable 'Show Hidden Files' in cPanel if you cannot see it. The existing `storage/settings.json`, chats, provider configuration and hCaptcha keys must not be overwritten.
4. Load `https://ai.63.lv/` once. If the current stored owner login is unconfigured or still uses the intentionally retired hash, the app will **save a fresh hash for your requested login password**, keep all other settings, and remove `storage/.owner-login-once` automatically. You do not need `owner-setup.php` or a setup code.
5. Log in using the password you supplied in chat. Do not copy it from this ZIP—it is not stored as plaintext. If you see the old page, clear the browser/PWA cache or use a private browsing window.
6. Confirm that `storage/.owner-login-once` no longer exists. If it remains, remove it manually through cPanel once login works.

If a valid unique password was *already* configured, the one-time file will be deleted without changing that password. This is intentional: the repair never silently resets an already-working account. For a true reset of a configured password, use `php tools/set-password.php` through cPanel Terminal or the separately protected owner-controlled reset method.

## Security notes

- `storage/` must return **403 or 404** for direct web requests, including `storage/settings.json` and `storage/.owner-login-once`. If either is readable, fix the hosting security rules before using the site.
- The requested password has been provided in a chat and is potentially known to others; replace it with a new, **unique 15+ character** password in Settings after confirming that login works.
- Do not commit this private recovery archive or copy its hash file to a public repository.
- A copied folder without the private hidden activation file will still show the owner setup warning. Confirm that the one-time file was extracted into the existing storage folder.
- This update does not modify AI engines, provider order, plugins or search and image data.
