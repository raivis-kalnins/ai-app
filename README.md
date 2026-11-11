# Free AI Hub 63 — v0.8.0 cPanel free edition

This edition is designed for shared cPanel hosting without a GPU. See
[UPGRADE-0.8.0.md](UPGRADE-0.8.0.md) before replacing files.

- Server-side allowlist blocks metered inference, including saved cloud keys and explicit requests. Puter is disabled. No credit card or new paid subscription is needed for the included community/browser features.
- Auto images use original generation (configured external self-hosted SD/ComfyUI or AI Horde). Existing web media remains a separate, explicit source-search choice with attribution.
- Accepted image jobs stay asynchronous. Cancel them explicitly, or repeat the same prompt in the same tab to resume the stored job after navigation/reload. Browser polling pauses after 30 minutes; community jobs can expire upstream.
- Music and clips: local procedural instrumental WAV generation and real-time image/title/music video export. These are **not** AI audio/video inference. No external media library, CDN or API is used by this studio. Download exports to keep them.
- Shared-host loopback model endpoints are skipped. Existing credentials and provider catalogs are retained, but metered routes cannot be enabled through Settings in this edition.
- Free-tier cloud services are intentionally not treated as guaranteed zero-cost APIs: account billing modes cannot be verified from a saved key. Community availability and browser hardware still limit generation.

