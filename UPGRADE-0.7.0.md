# Free AI Hub 63 v0.7.0 — Upgrade instructions

**The ZIP is complete**: upload the contents of `ai-app/` into the cPanel
web root for `https://ai.63.lv/`.

## Keep these live files/directories unchanged

- `storage/` including current `settings.json`, uploads, caches, reports, chat
  history and other runtime data.
- `config.local.php` if it exists.
- Any server-level environment variables and hosting provider API keys.

Never replace an existing `storage/` with empty placeholders from a ZIP extraction.
Extract to a temporary folder and copy/merge code files rather than deleting your
live installation. Keep the protected `.htaccess` files present.

## Verify after copying

1. Visit the login page; real flags should appear on the language selector.
2. Switch between Latvian and English, and check both light and dark modes.
3. Verify that the DigitalPulse panel and footer credit link to
   https://digitalpulse.click/ and display correctly on mobile.
4. Log in and check existing chats, plugins, providers and stored settings.
5. Open Smart Search, run a query; results should appear before optional AI
   enrichment, and search filter/sorting should work without a new network call.
6. If previous assets remain: force reload or clear the website's service worker /
   PWA cache. The new cache key is `aih63-static-v070-ui-search`.

## Login/security

`v0.6.9` retired the shared default password. No login password is shipped. Read
`SECURITY-UPDATE.md` for how to set a unique password with
`php tools/set-password.php` or on shared hosting without terminal access.
Do not publish `storage/settings.json` or credentials to GitHub.

## Testing

Use `php tests/auth-unit.php` and `php tests/ui-search-unit.php` for offline
checks, `php -l` for PHP and `node --check` for JavaScript. External providers
and the production server require separate live testing.
