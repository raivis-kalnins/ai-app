# Test results - Free AI Hub 63 v0.6.9 security update

Tested in isolated local copies, not on the user's live server or GitHub repository.
Environment: PHP 8.4.24, Apache 2.4 with mod_php, Nginx, Node.js 22.
Test credentials were generated randomly at runtime and are not in either release.

## Passed

- PHP lint: 22 files.
- JavaScript syntax: 5 files.
- Pure helper/localization/plugin suite: 164 checks (`php tests/auth-unit.php`).
- Apache HTTP and CLI regression suite: 88 checks (detailed below).
- Nginx configuration validation and access rules: 11 checks.
- 3,090 pre-existing translation values preserved across all 15 languages.
- Provider and image migration orders retain every known provider exactly once.
- Hash-only CLI mode creates a usable new hash without reading/changing runtime files.
- No retired bundled hash, runtime settings, local configuration or test credentials
  in the release. No broad GitGuardian exclusions or suppressed detectors added.

## Not verified

- The exact flagged historical commit: GitHub fetch returned 404. The uploaded ZIP
  was inspected directly; the original alert is not conclusively classified.
- GitGuardian's own cloud rescan/incident status; no scanner account action taken.
- Deployment on your hosting, or whether its .htaccess/Nginx rules are effective.
- Interactive visual browser QA: available Chromium refused localhost navigation
  with ERR_BLOCKED_BY_ADMINISTRATOR. Browser rendering and JavaScript form submission
  were not verified; HTTP form/endpoint behavior and JavaScript syntax were tested.
- The Windows PowerShell hidden-input path; Linux/CLI stdin/hash-only flows tested.
- Live cloud AI providers, paid APIs, image-generation services and unrelated features.
- This is not a comprehensive penetration test or full repository-history audit.

## Apache HTTP and CLI checks

1. Unconfigured install is locked
2. Unconfigured login submit is disabled
3. Blank input cannot bootstrap public account
4. Unauthenticated endpoint blocked: api.php
5. Unauthenticated endpoint blocked: download.php
6. Unauthenticated endpoint blocked: preview.php
7. Unauthenticated endpoint blocked: report.php
8. Unauthenticated endpoint blocked: cleanup.php
9. CLI reset succeeds and reports success
10. CLI does not print plaintext
11. Only bcrypt hash stored
12. Plaintext not saved to settings
13. Apache denies private path: storage/settings.json
14. Apache denies private path: storage/settings.json.tmp.test
15. Apache denies private path: storage/remember_tokens.json
16. Apache denies private path: storage/chats/history.json
17. Apache denies private path: storage/uploads/test.txt
18. Apache denies private path: includes/bootstrap.php
19. Apache denies private path: config.local.php
20. Apache denies private path: config.local.php.bak
21. Apache denies private path: config.example.php
22. Apache denies private path: .env
23. Apache denies private path: .git/config
24. Apache denies private path: backup.zip
25. Apache denies private path: tools/set-password.php
26. Public assets remain accessible
27. Login requires CSRF token
28. Correct credential signs in
29. Login cookies are HttpOnly
30. Settings has current/new/confirm fields
31. Settings does not expose password hash
32. Second independent session signs in
33. Independent remembered login created
34. Remember-me restores a valid session
35. Remember-me token rotates on use
36. Settings changes reject GET
37. Settings requires CSRF
38. Password change requires current credential
39. Wrong current credential is rejected
40. Weak new credential is rejected
41. Mismatched confirmation is rejected
42. Identical credential cannot be reused
43. Rejected changes preserve stored hash
44. Valid password change succeeds
45. Password change rotates session ID
46. Changing tab stays signed in
47. Other session invalidated immediately
48. Additional old session invalidated
49. Remember-authenticated session invalidated
50. Old remember token cannot regain access
51. Password change revokes stored remember tokens
52. Blank credential fields preserve login while saving settings
53. Authenticated file download still works
54. Old password rejected after change
55. New password accepted
56. Localized login renders: en
57. Localized login renders: lv
58. Localized login renders: lt
59. Localized login renders: et
60. Localized login renders: sv
61. Localized login renders: no
62. Localized login renders: da
63. Localized login renders: fi
64. Localized login renders: is
65. Localized login renders: pl
66. Localized login renders: ru
67. Localized login renders: uk
68. Localized login renders: es
69. Localized login renders: fr
70. Localized login renders: de
71. Counted failure with fresh cookies 1
72. Counted failure with fresh cookies 2
73. Counted failure with fresh cookies 3
74. Counted failure with fresh cookies 4
75. Counted failure with fresh cookies 5
76. Counted failure with fresh cookies 6
77. Counted failure with fresh cookies 7
78. Counted failure with fresh cookies 8
79. Counted failure with fresh cookies 9
80. Counted failure with fresh cookies 10
81. Eleventh attempt blocked across fresh sessions
82. Throttling exposes retry interval
83. Expired throttle permits login
84. CLI reset succeeds and reports success
85. CLI does not print plaintext
86. CLI reset invalidates existing session
87. CLI reset preserves unrelated settings
88. Retired bundled default fails closed

## Nginx checks

1. Nginx rule denies storage/settings.json
2. Nginx rule denies storage/uploads/test.txt
3. Nginx rule denies storage/chats/history.json
4. Nginx rule denies includes/bootstrap.php
5. Nginx rule denies tools/set-password.php
6. Nginx rule denies config.local.php
7. Nginx rule denies config.local.php.bak
8. Nginx rule denies .env
9. Nginx rule denies .git/config
10. Nginx rule denies backup.zip
11. Nginx public static asset remains available
