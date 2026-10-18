# Free AI Hub 63 - security update 0.6.9

## Read this before replacing files

Back up the live site privately. Upload the new application files over the existing
installation, INCLUDING hidden .htaccess files. Keep your existing storage directory
and config.local.php. This release contains only storage access-control placeholders,
not settings.json, tokens, uploads or chat history. Do not delete live storage.

All browser sessions from the previous version need to sign in again. Existing
unique passwords remain valid. The specific hash bundled with v0.6.8 is deliberately
REJECTED, even when it remains in old runtime settings or config.local.php. There is
no replacement default password. Set a unique one as below.

## Where the password protection is

- index.php: login form and sign-in handling; Settings > Security changes the login.
- includes/auth.php: hash validation, retired-default rejection, attempt limits.
- includes/bootstrap.php: login guard, session checks, remember-me tokens and storage.
- api.php: current-password verification and password changes.
- storage/settings.json: the server's password_hash and optional provider credentials.
  This is PRIVATE runtime data, not a file to upload to GitHub.
- config.example.php: non-secret defaults. No real initial credential is included.
- includes/i18n_extra.php: translated UI labels, not the login credential store.

## Set/reset the login (hosting terminal / SSH)

From the application's directory, using the same account that owns the runtime files:

    php tools/set-password.php

Enter a NEW unique password twice at the hidden prompts. Use at least 15 characters
and at most 72 UTF-8 bytes. Do not reuse any password that was published or shared.
The tool saves only the hash, retains other settings, and invalidates sessions and
remembered logins. It cannot be run through a browser. No password belongs in a
command-line argument, source file, Git commit or support screenshot.

For normal future changes: sign in > Settings > Security > Current password >
New password > Confirm new password > Save settings. Leaving all three fields blank
keeps the current password. Other sessions are signed out after a change.

## One-time owner activation in cPanel without SSH (v0.7.1)

For installations displaying "Owner password is not configured", the preferred
no-terminal route is `owner-setup.php` plus a temporary private
`storage/owner-setup.key` created through cPanel File Manager. The owner code
must be random, must not be a login password, and must be inaccessible from the
web. See `UPGRADE-0.7.1.md` for the exact protected setup steps. This route
**does not** reset an already-configured unique password.

## Shared hosting without SSH (manual password-hash alternative)

1. Download the release to a private local folder and run PHP locally:

       php tools/set-password.php --hash-only

   The hidden prompts produce only a new hash and do not change application files.
   Linux/macOS use stty; Windows uses PowerShell Read-Host. PHP must be installed.
2. In your HOSTING file manager, edit your EXISTING storage/settings.json. Replace
   only the string value of password_hash with the generated hash. Keep valid JSON
   and leave every other setting intact. Do not replace the whole live file with
   a copy from a fresh installation. Keep any backup OUTSIDE the public web root.
3. Save and sign in using your new password. Old sessions/remembered tokens stop
   working because their fingerprint no longer matches. If a login-attempt lock
   remains, remove only storage/auth_attempts.json or let its 15-minute window expire.
4. On a brand-new installation with no settings.json, create config.local.php from
   config.example.php and set security.initial_password_hash to your generated hash
   BEFORE the first request. On an existing install this initial value is not a reset;
   edit the live settings value or use the reset command instead.

The optional AIH_INITIAL_PASSWORD_HASH server environment variable is likewise only
for initial seeding. It is not a runtime override and must never contain plaintext.
Do not publish any generated hash: it is still sensitive authentication material.

## GitGuardian alert dated 9 October 2026

The referenced commit is 0272287d81d577af16bc2b7488e15ef9c89b3ada and the alert points
to includes/i18n_extra.php:210. That historical file could not be fetched (GitHub
connector returned 404). The supplied ZIP is a different snapshot: its line 210 is
a Lithuanian sector_digital_agency label, not a password. The translation file also
contains human-readable new/show/hide password UI labels. Those are plausible generic
password false positives, but the historical detection has NOT been conclusively
identified. This release gives those UI labels unambiguous ui_* names, without
obfuscating real credentials or disabling secret scanning.

A separate verified problem in the supplied ZIP was the same default bcrypt hash
in both config.example.php and storage/settings.json. No nonempty provider API keys
or captcha secrets were present in that ZIP's settings. This is not a full audit of
the repository's history or of your deployed server.

Review the exact detected value in GitGuardian privately. Only mark that incident
as a false positive if it is a translation label. If it was an actual password,
rotate it everywhere it was used first. This ZIP does NOT resolve the GitGuardian
incident or edit GitHub history, and no GitHub commits/pushes were performed.

## Remove already tracked local/runtime files from Git

.gitignore does not untrack files already committed. In your repository checkout:

    git rm -r --cached --ignore-unmatch -- storage
    git rm --cached --ignore-unmatch -- config.local.php .env
    git add .gitignore .htaccess storage/.htaccess storage/index.php

Review and stage the remaining changed application files, then commit and push.
These --cached commands leave your local files in place; they do not remove historic
copies. Check other branches, archives and backup files too. For a genuine exposed
credential, rotate first. Coordinate any git-filter-repo history cleanup with
collaborators; do not blindly force-push. Never delete translations just to hide an
alert and never exclude an entire source directory from secret detection.

## Web-server protection is mandatory

Apache 2.4: enable the needed AllowOverride permissions so the shipped .htaccess
files are effective. The patch uses standard authorization rules and optional
mod_rewrite. Retain additional host-specific rules from your old .htaccess as needed.

Nginx: .htaccess has NO effect. Merge docs/nginx-security.conf.example into your
server configuration before broader regex locations, adapt for a subdirectory
installation, and run nginx -t before reloading. A hosting provider can apply this.
The PHP development server is not a production server and also ignores .htaccess.

Use HTTPS. While signed out, request these paths and verify the expected responses:

- /storage/settings.json, /storage/remember_tokens.json, /storage/chats/history.json:
  403/404, never JSON or chat content.
- /config.local.php, /config.local.php.bak, /.env, /.git/config, /tools/set-password.php:
  403/404, never file content or a public reset form.
- /api.php?action=files and /download.php: 401 without a valid session.

No PHP login form can protect static JSON served directly by a misconfigured host.
Behind a reverse proxy, the limiter uses REMOTE_ADDR (not attacker-controlled
X-Forwarded-For). Configure trusted real-IP handling in the web server when needed;
otherwise people behind that proxy share a 10-attempt/15-minute allowance. Sessions
use Secure on HTTPS, HttpOnly and SameSite settings. Review proxy trust separately.

## Scope and references

This is a targeted password/repository hygiene update, not a penetration test or a
guarantee that every unrelated app feature is secure. Provider and image workflows
were not redesigned. See TEST-RESULTS.md for the actual tests performed.

Official references:
- GitGuardian on translated-label false positives: https://docs.gitguardian.com/secrets-detection/secrets-detection-engine/machine_learning
- GitGuardian generic password detector: https://docs.gitguardian.com/secrets-detection/secrets-detection-engine/detectors/generics/generic_password
- GitHub credential rotation and history cleanup: https://docs.github.com/en/authentication/keeping-your-account-and-data-secure/removing-sensitive-data-from-a-repository
- PHP password hashing: https://www.php.net/manual/en/function.password-hash.php
- OWASP authentication guidance: https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html
