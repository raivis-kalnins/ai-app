# Free AI Hub 63 v0.1.0

A private, password-protected PHP 8.2+ dashboard built from the strongest ideas in the two supplied AI dashboard examples and the modular structure of the supplied `remote-app` project.

## What is included

- Multi-provider AI chat with automatic failover and provider cooldowns after rate-limit errors.
- Providers: **Ollama Local**, **Groq**, **Google Gemini**, **OpenRouter free models**, optional **Ollama Cloud**, optional **Pollinations**, plus one custom OpenAI-compatible endpoint.
- Provider status shows Ready / Needs key / Cooldown / Disabled.
- Smart web search with three routes: optional Ollama Web Search free tier, optional SearXNG instance, and a no-key DuckDuckGo HTML fallback.
- Marketplace/car search presets for **Latvia, Germany, United Kingdom, Lithuania and Estonia**.
- Search results are deduplicated and displayed in a table with source URL, detected price/currency/year/mileage and country.
- Optional AI normalization is constrained to source titles/snippets; missing facts stay blank.
- Search report download as **HTML, JSON and PDF**.
- Image generation through Pollinations (API key/credits) or a local Stable Diffusion / Forge WebUI API.
- App/website starter ZIPs for **React + TypeScript + Vite**, **Svelte + Vite**, **Alpine.js + AJAX/fetch**, PHP, WordPress plugins and WordPress themes.
- AI-generated project ZIPs when a text provider is available; clean starter ZIP fallback when it is not.
- File manager with **1 GB maximum upload/download**.
- Automatic deletion of uploads, generated files, reports and cache after **48 hours**.
- Manual "Clean expired now" button plus CLI cleanup script.
- Protected server-side settings, CSRF protection, private session cookie, noindex headers and protected `storage/`.

## Important 2026 provider notes

This build deliberately does **not** pretend that consumer ChatGPT, Claude, Kimi or Grok web pages are hidden free APIs. Browser subscriptions/web chat sessions should not be scraped or reused as an unofficial API. The dashboard uses local models or official provider endpoints/free tiers instead.

`GitHub Models` is not included because GitHub retired its playground, model catalog and inference API on **30 July 2026**. If you have older code that points to GitHub Models, remove it or replace it with another provider.

Pollinations is included only as an optional authenticated provider. Current Pollinations generation endpoints use API keys/credits, so it is **not** labelled "unlimited/no-key" here.

Free tiers change frequently. The app therefore treats provider order/models/URLs as settings instead of hard-coding promises about unlimited usage.

## Installation

Requirements:

- Apache or Nginx
- PHP **8.2+**
- PHP 8.2+ with JSON. `curl`, `dom` and `mbstring` are recommended; HTTP falls back to PHP streams when cURL is unavailable. ZIP generation uses `ZipArchive` when available and falls back to `PharData`.
- HTTPS strongly recommended
- `storage/` writable by PHP

Upload the complete folder to the web root, for example `https://ai.63.lv/`.

The requested initial password is already stored as a bcrypt hash in `config.example.php`. Sign in with the password you specified for this build, then change it in **Settings**. The password itself is never stored in `storage/settings.json` as plaintext.

### 1 GB uploads

The package includes `.user.ini`:

```ini
upload_max_filesize = 1024M
post_max_size = 1050M
max_execution_time = 600
max_input_time = 600
```

Your hosting layer can still impose a smaller limit.

For Nginx, the host configuration may also need:

```nginx
client_max_body_size 1024m;
```

For managed hosting, increase the PHP upload/post limits in the hosting control panel if `.user.ini` is ignored.

Downloads are streamed in chunks and are not loaded into PHP memory all at once.

## 48-hour cleanup

The application opportunistically checks cleanup every 15 minutes during normal requests. It removes temporary files older than 48 hours from:

- `storage/uploads/`
- `storage/generated/`
- `storage/reports/`
- `storage/cache/`

Settings and provider state are never part of automatic cleanup.

For reliable cleanup even when nobody visits the site, run this cron job hourly:

```cron
17 * * * * /usr/bin/php /path/to/ai-free-hub/cleanup.php >/dev/null 2>&1
```

## AI provider setup

Open **Settings** and add only the providers you want.

### Ollama Local - best zero-subscription fallback

Install Ollama on a machine reachable by the PHP server and run a model. Default endpoint:

`http://127.0.0.1:11434/v1/chat/completions`

Default model in this package: `qwen3:8b`. Change it to any locally installed model.

If PHP and Ollama are on different machines, secure the connection; do not expose an unauthenticated Ollama port directly to the public internet.

### Groq

Add a free Groq API key. When Groq returns HTTP 429, the dashboard reads `Retry-After`/rate-limit headers when available, marks Groq in cooldown and automatically tries the next provider.

### Gemini

Add a Gemini Developer API key from Google AI Studio. The default is `gemini-3.8-flash`, which currently has a free tier; quotas still depend on account/region and can change.

### OpenRouter

Add an OpenRouter key. The default model is `openrouter/free`, which lets OpenRouter route to a currently available free model. Free-plan daily/rate limits still apply.

### Ollama Cloud

Optional. Add an Ollama account API key and enable the provider.

### Pollinations

Optional. Add a Pollinations server-side key/credit balance. The key is stored server-side only.

### Custom OpenAI-compatible

Use this for another local/free service that accepts OpenAI Chat Completions format. Set its URL, model and optional key.

## Search setup

The search engine priority is:

1. Ollama Web Search if its key is configured.
2. SearXNG if you provide a SearXNG base URL that allows JSON search responses.
3. DuckDuckGo HTML fallback with no API key.

SearXNG is recommended for a reliable long-term deployment because you can self-host it, enable the engines you want and control the search API. Public SearXNG instances may disable JSON output.

### Car marketplace presets

The included domain packs are deliberately query-based instead of brittle direct scrapers:

- LV: `ss.com`, `auto24.lv`
- DE: `mobile.de`, `autoscout24.de`, `kleinanzeigen.de`
- UK: `autotrader.co.uk`, `motors.co.uk`, `pistonheads.com`
- LT: `autoplius.lt`, `autogidas.lt`
- EE: `auto24.ee`, `soov.ee`

The search engine gathers public indexed result titles/snippets/URLs. The app does not bypass logins, CAPTCHAs or access controls on marketplaces.

## PDF reports

`report.php` creates a dependency-free PDF using standard PDF fonts, so it works on ordinary shared PHP hosting. Because standard PDF fonts are used, non-ASCII characters are transliterated in the PDF fallback. The HTML report keeps full UTF-8 text and is the best format for Latvian/German names if exact diacritics matter.

## Development technology choices

The dashboard itself stays light: server-rendered PHP + AJAX/fetch. This keeps first load and hosting requirements small.

The generator offers separate stacks rather than loading several frameworks into one runtime:

- **React + TypeScript + Vite**: larger app/admin surfaces, mature component ecosystem, complex state.
- **Svelte + Vite**: small/medium interactive applications where runtime size and simple reactivity matter.
- **Alpine.js + AJAX/fetch**: WordPress/PHP sites, server-rendered pages and lightweight widgets.
- **PHP + AJAX**: simplest shared-hosting option.

For search performance, the important work is server-side: parallel requests, deduplication, a 10-minute result cache, domain/country filters and source-aware normalization. The browser framework does not make external search engines faster.

## Security notes

- Change the initial password immediately after deployment.
- Do not commit `storage/settings.json` because it can contain provider keys.
- Keep HTTPS enabled.
- Keep `storage/` outside direct web access (the included `.htaccess` denies it on Apache).
- For Nginx, explicitly deny `/storage/` at the server configuration level.
- The app intentionally does not support unofficial scraping of logged-in ChatGPT/Claude/Grok/Kimi sessions.

## Folder structure

```text
index.php                 UI + login
api.php                   authenticated AJAX API
download.php              streamed downloads up to 1 GB
report.php                HTML / JSON / PDF reports
cleanup.php               48-hour cleanup (web/CLI)
config.example.php        application defaults + password hash
.user.ini                 PHP 1 GB upload settings
.htaccess                 Apache hardening
assets/
  app.css
  app.js
includes/
  bootstrap.php           sessions, settings, storage, cleanup
  http.php                HTTP + parallel GET helpers
  providers.php           AI adapters + failover/cooldowns
  search.php              search engines + marketplace presets
  generators.php          React/Svelte/Alpine/WP ZIP builders
  pdf.php                 dependency-free PDF fallback
storage/
  uploads/
  generated/
  reports/
  cache/
  logs/
```

## Upgrade strategy

Back up `storage/settings.json` before replacing application files. When installing a newer release, keep the existing `storage/` directory if you want to preserve API keys and settings.
