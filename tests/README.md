# Local checks

`php tests/auth-unit.php` tests pure authentication helpers, all 15 language packs
and bundled plugin manifest syntax without loading or modifying runtime storage.

Run PHP lint over all PHP files and `node --check` for each JavaScript file.

HTTP login/change/reset/cookie tests require an isolated copy with randomly generated
test credentials. Never run destructive regression tests against a production site.
The release's isolated Apache/Nginx test results are in TEST-RESULTS.md.
