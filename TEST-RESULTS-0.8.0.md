# v0.8.0 validation

- All PHP files: syntax checked with PHP WebAssembly CLI (no native PHP installed in the build environment).
- Existing authentication suite: 164 checks passed.
- Existing UI/search suite: 147 checks passed.
- Free cPanel suite: passed allowlist, direct provider guards, preserved input settings/password, complete provider migration, generation-only Auto, explicit source search, long queues, transient status retry and terminal failed/expired job checks.
- All 9 JavaScript/CJS files: Node syntax checks passed.
- All 30 bundled plugin manifests: valid JSON.
- Music unit test: deterministic seeds, variation, finite/bounded amplitudes, fades, duration cap and PCM WAV header passed.
- Headless Chromium: rendered actual PHP studio markup with application CSS/JS. Generated/downloaded WAV, validated missing-image feedback, exported an actual 8-second WebM with music, decoded and played the result, cancelled another export, checked 390px viewport for horizontal overflow and no JavaScript page errors. Desktop/mobile screenshots inspected during QA.
- Corrected hidden-button styling and cancellation-status repaint issues found by browser tests.
- Removed inherited shared owner seed and historical private recovery documentation; existing owner-password preservation/auth tests passed again.
- Git whitespace check passed with the repository's existing CRLF convention.

Limitations: the new studio was tested in isolation, not the complete authenticated application in production. No live FirstHost deployment, real AI Horde generation, external local-model server, Safari/iOS or Android device test was performed. The community queue remains dependent on volunteer capacity. Paid API requests were tested with offline guards, never with billable calls.
