# Free AI Hub 63 v0.7.1 — cPanel login repair

This is a **full application ZIP**. Use it to repair the `HTTP 503` response
that v0.7.0 intentionally returned while the owner password was unconfigured.
It also removes the duplicated theme icon, improves the login layout, and
makes the DigitalPulse panel visible next to the form on desktop.

## Safe replacement

1. Back up the live `storage/` directory and any `config.local.php` privately.
2. Upload the **contents of `ai-app/`** to the `ai.63.lv` document root. Replace
   existing **application code** (including `.htaccess`), but **do not delete or
   overwrite your current `storage/` or `config.local.php`**.
3. If you already have a **unique personal password** and your login works,
   you are done. Do not run owner setup again.
4. If the login shows **Owner password is not configured**, follow the next section.

## Activate a unique owner password using cPanel File Manager (no SSH)

This is a one-time action; a complete ZIP cannot safely ship or guess your
private login password.

1. In cPanel, open **File Manager** and navigate to your live `ai.63.lv`
   document root, then into **`storage/`**.
2. Create a new text file called **`owner-setup.key`**. Generate a **random
   secret of at least 48 URL-safe characters** in your password manager,
   using letters/numbers and optionally `-` and `_`. Paste only that secret
   into the file. Where cPanel allows it, set file permissions to **600**.
   **This is not your new login password.**
3. **Before entering any secrets**, visit
   `https://ai.63.lv/storage/owner-setup.key` in an incognito window.
   It must be denied with **403/404**, not expose any text. If it is
   accessible, **delete the file immediately** and fix `storage/.htaccess`
   (or Nginx security rules) first.
4. Open **`https://ai.63.lv/owner-setup.php`**. Paste the same private
   one-time code and choose a *different* unique login password:
   **15 or more characters, no more than 72 UTF-8 bytes**. Confirm it and
   click **Activate login**.
5. The setup tool stores only a salted password hash in the existing
   `storage/settings.json`, keeps all other settings/API keys, clears old
   remembered logins, and deletes `storage/owner-setup.key`. If it remains
   after success, delete that temporary file in File Manager.
6. Sign in on `https://ai.63.lv/`. Clear the browser/PWA cache if needed.

**Security:** `owner-setup.php` cannot reset an already-configured, valid
password. It requires the temporary code file created by someone with hosting
file access, CSRF protection, attempt limiting and matching code/password
validation. Never share the code or password, commit them to Git, or send
screenshots of their contents. Do not leave the setup file in place.

### Alternative with SSH/cPanel Terminal

From the live app root run:

```sh
php tools/set-password.php
```

The password is read with hidden prompts. You don't need the web setup page
or private token file with this method. For additional instructions on resetting
an existing, configured password, see `SECURITY-UPDATE.md`.

## Verify

- Public login page returns HTTP **200** even when a password has not yet
  been configured; sign-in remains disabled until secure setup completes.
- Wrong owner setup codes fail; a correct one-time code cannot be reused.
- Login and authenticated Smart Search work using the new password.
- Light/dark button shows **one** moon/sun icon; language flags still work.
- DigitalPulse appears to the right of login on desktop and below on mobile.
- `hCaptcha: recaptchacompat disabled` in the console is normal and does **not**
  indicate an error.

This release does not change your existing provider settings, hCaptcha keys,
search connections, uploads or chats. Existing browser sessions are invalidated
on password activation.
