# Free AI Hub 63 v0.4.0

A private, password-protected PHP 8+ AI workspace for `ai.63.lv`. It combines free/local AI routing, multilingual chat, universal smart search, image/video generation, project/WordPress ZIP generation and temporary file storage without forcing a monthly AI subscription.

## v0.3.0 highlights

- **AI Chat is the first page after login.**
- New clean **light UI is the default**; dark mode is optional from the top bar and Settings.
- Full interface + chat language support for:
  - English
  - Latvian, Lithuanian, Estonian
  - Swedish, Norwegian, Danish, Finnish, Icelandic
  - Polish, Russian, Ukrainian
  - Spanish, French, German
- hCaptcha remains **optional**. If enabled, its `hl` language always follows the active interface language; English is the default on a fresh install.
- Settings now show **Connect / Setup / Get key / Test** actions for providers instead of expecting users to know where credentials come from.
- Added **Hugging Face Inference Providers** and **Mistral API Free mode**.
- Added visible local model presets for **Llama 3.x (Meta), Gemma (Google), Mistral 7B, DeepSeek and Qwen** through Ollama/local OpenAI-compatible runtimes.
- Existing **Groq**, **Gemini**, **OpenRouter**, **Ollama Cloud**, **Puter**, local engines and custom endpoints remain available.
- **React / Next.js + TypeScript + Node.js route handlers + Supabase** is now a first-class app-generator stack.
- Media Studio supports **Puter text-to-image and text-to-video** in the browser with no developer API key, plus local Stable Diffusion/Forge and optional Pollinations.
- Smart Search no longer asks for a search “type”. It uses **country multiselect with All countries selected by default** and combines all configured search resources.
- Search country set now includes Latvia, Lithuania, Estonia, United Kingdom, Germany, Sweden, Norway, Denmark, Finland, Iceland, Poland, France, Spain, Ukraine and Russia.
- Smart Search can combine:
  - DuckDuckGo HTML fallback - no key
  - SearXNG - optional/self-hosted
  - Ollama Web Search - optional account key
  - Google Gemini grounding with Google Search when Gemini is configured
  - Groq Browser Search research notes when a supported GPT-OSS model/key is configured
  - AI structuring/summarisation through the normal failover router
  - Puter browser-side summary fallback when no server AI provider answers
- Search reports include sources plus AI/research notes and export as HTML, JSON and PDF.

## AI providers

### No developer cloud API key required

- **Puter AI** - runs through Puter.js in the browser. The user signs in to Puter and uses their own allowance/account quota. Puter exposes many model vendors and also image/video AI.
- **Browser AI / WebLLM** - local model in a WebGPU-capable browser.
- **Ollama Local** - local/self-hosted open models.
- **LM Studio Local** - OpenAI-compatible local server.
- **llama.cpp Local** - OpenAI-compatible local server.
- **LocalAI Local** - self-hosted OpenAI-compatible server.
- **Custom OpenAI-compatible** - key is optional, so another local/free endpoint can be connected.

### Optional free/free-credit account keys

- **Groq** - free/developer account limits; default `openai/gpt-oss-20b`.
- **Google Gemini** - free tier where available; default `gemini-3.8-flash`.
- **OpenRouter** - `openrouter/free` routes to currently available free models; account key required.
- **Hugging Face Inference Providers** - free account credits; OpenAI-compatible router endpoint.
- **Mistral API** - Free mode with limited usage/rate limits; default `mistral-small-latest`.
- **Ollama Cloud** - optional account key.
- **Pollinations** - optional authenticated/credit provider; not labelled unlimited/no-key.

The server router skips missing optional keys, handles HTTP 429 cooldowns, and moves to the next provider. Client-side Puter/WebLLM are then available as no-developer-key fallbacks.

## Open model presets

Settings includes one-click local presets:

```text
Llama 3.x (Meta)  -> llama3.2:3b
Gemma (Google)    -> gemma3:4b
Mistral 7B        -> mistral:7b
DeepSeek          -> deepseek-r1:8b
Qwen              -> qwen3:8b
```

The UI also shows the matching `ollama pull ...` command. Model IDs are editable because local model catalogues change frequently.

## Connect buttons / external resources

Provider rows have the appropriate action:

- Puter: real browser sign-in/connect button
- Browser WebLLM: load/test locally
- Ollama / LM Studio / llama.cpp / LocalAI: setup link + endpoint test
- Groq / Gemini / OpenRouter / Hugging Face / Mistral: direct link to create a free/account token + server-side test
- Other configured providers: connect/setup link when available

Settings also links to Microsoft Copilot Studio, GitHub Copilot, Supabase and Hugging Face resources. **Microsoft/GitHub Copilot consumer/IDE sessions are not treated as hidden free inference APIs.** Microsoft Copilot Studio may have trials/licensing, so it is offered as a setup resource rather than a fake “free unlimited” router provider.

## Multilingual behaviour

The active language is stored in a cookie and can be switched from login or any logged-in page. A fresh install defaults to English.

The selected language controls:

- navigation and page labels
- settings/actions/status messages
- login and installation instructions
- hCaptcha widget language when hCaptcha is enabled
- AI Chat response language
- Smart Search AI summary language
- country names in modern browsers through `Intl.DisplayNames`

Supported interface codes: `en`, `lv`, `lt`, `et`, `sv`, `no`, `da`, `fi`, `is`, `pl`, `ru`, `uk`, `es`, `fr`, `de`.

## Optional hCaptcha

hCaptcha is **disabled by default**. Password-only login works with no hCaptcha configuration.

To enable it:

1. Open **Settings > Security**.
2. Enable hCaptcha.
3. Add Site Key and Secret.
4. Save.

The login page loads hCaptcha as `...?hl=<active-language>`. There is no separate hCaptcha language setting that can get out of sync with the interface.


## v0.4 task-aware AI routing

Puter is used as a **multi-model gateway**, not as one fixed `gpt-5-nano` chatbot. After **Connect Puter**, the app loads the current Puter model catalog and lets you select a model manually or keep **Auto - best for task**. Auto routing prefers different current models for normal chat, code/WordPress, research and comparison. Compare mode can ask multiple independent models/providers and synthesize the result.

Image and video requests are routed to media generation (`puter.ai.txt2img` / `puter.ai.txt2vid`) instead of a text model. This fixes the old behaviour where a text model could reply “I can’t render an image here”. Media Studio can also use local Stable Diffusion / Forge, with optional Pollinations where configured.

Puter uses the signed-in Puter user's own current allowance/quota; no developer API key is required by this app. Model/provider availability can change, so the UI discovers live models where possible.

## Universal sector Smart Search

Smart Search is not limited to cars. **All sectors** is selected by default, or choose one/more sectors such as vehicles, property, jobs, services, web/digital agencies, electronics, IT, phones, home/garden, construction, agriculture, transport, animals, fashion, business, leisure, education, or health/beauty.

The query builder combines broad web search with optional country/sector marketplace hints. For Latvia this includes `ss.lv` / `ss.com` for broad classifieds, while still searching other indexed sites. Results are deduplicated and AI may only structure facts that are explicitly present in the returned source title/snippet.

Search reports export to a redesigned HTML/PDF layout with query, selected sectors/countries, search resources, AI summary/research notes, readable result cards and source links.

## Smart Search

There is one universal search flow instead of separate “cars / agency / general / Google AI” modes.

1. Enter a natural-language request.
2. Leave **All countries** selected, or select specific countries.
3. Optionally use structured fields such as make/model/price/year.
4. The server queries every configured search resource that is available.
5. URLs are deduplicated.
6. Source titles/snippets are parsed for structured values.
7. AI may organise/summarise those sourced results but is instructed not to invent missing values.
8. Download the result as HTML, PDF or JSON.

For vehicle-like searches the app adds useful marketplace-domain hints per country, but it does not bypass marketplace logins/CAPTCHAs or scrape private pages directly.

### Search providers

- **DuckDuckGo HTML**: no key fallback. Some hosts/networks can block it.
- **SearXNG**: recommended self-hosted/open metasearch connection. Public instances can disable JSON output.
- **Ollama Web Search**: optional key.
- **Gemini Google Search grounding**: automatically participates when Gemini is enabled and has a key.
- **Groq Browser Search**: automatically adds a cited research note when Groq is enabled with a supported GPT-OSS model/key.

Search is cached for 10 minutes to avoid unnecessary repeated requests.

## Media Studio

### Puter

Puter is the main no-developer-key browser option:

- text → image
- text → video
- broad chat model catalogue

The browser may ask the user to sign in to Puter. Usage follows that user account’s current allowance/quota.

### Local image generation

Set a Stable Diffusion WebUI/Forge URL in Settings and select the local provider. The PHP server calls `/sdapi/v1/txt2img`.

### Pollinations

Available as an optional authenticated provider when you supply a current valid key/credits.

## App & WordPress Builder

Available project types:

- **React / Next.js + TypeScript + Node.js + Supabase**
- React + TypeScript + Vite
- Svelte + Vite
- Alpine.js + AJAX/fetch
- WordPress plugin
- WordPress theme
- PHP app

If a server AI provider is available it can generate project files. If server AI is unavailable and Puter is connected, the browser can ask Puter to create the files and then send the generated file manifest back to PHP for safe ZIP packaging. A deterministic clean starter ZIP remains the final fallback.

## 1 GB upload/download

Application limit: **1 GB per file**.

The package includes `.user.ini`:

```ini
upload_max_filesize = 1024M
post_max_size = 1050M
max_execution_time = 600
max_input_time = 600
```

Your web server/hosting panel can still impose a lower limit. Nginx may additionally require:

```nginx
client_max_body_size 1024m;
```

Downloads are streamed instead of loading the whole file into PHP memory.

## 48-hour temporary storage

Temporary uploads, generated files, reports and cache are automatically removed after **48 hours**. Settings/provider state are excluded.

The app performs opportunistic cleanup during requests. For reliable cleanup even when the site is idle, run hourly:

```cron
17 * * * * /usr/bin/php /path/to/ai-free-hub/cleanup.php >/dev/null 2>&1
```

Files & Storage also has a manual cleanup button.

## PWA / favicon / Android / Apple

Included:

- favicon SVG + PNG
- Apple Touch icon
- Android 192/512 icons
- `manifest.webmanifest`
- `service-worker.js`
- install controls below login

Android uses the browser install prompt when available. On iPhone/iPad use Safari → Share → Add to Home Screen.

## Installation

Requirements:

- PHP 8.0+ with JSON
- Apache or Nginx
- writable `storage/`
- HTTPS strongly recommended
- cURL/DOM/mbstring recommended, but the app includes fallbacks where practical
- ZIP creation uses `ZipArchive` when available and `PharData` fallback otherwise

Upload the folder contents to the domain web root.

The requested initial password is stored only as a bcrypt hash in `config.example.php`. Sign in using the password chosen for this project, then change it in Settings. It is never saved as plaintext in `storage/settings.json`.

### Upgrade from v0.1/v0.2

Back up `storage/settings.json`, replace application files, then keep the existing `storage/` directory. Settings migration adds new providers/UI defaults without deleting saved keys.

## Folder structure

```text
index.php                 login + multilingual UI
api.php                   authenticated AJAX API
download.php              streamed downloads up to 1 GB
report.php                HTML / JSON / PDF reports
cleanup.php               48-hour cleanup
config.example.php        application defaults + password hash
.htaccess                 Apache hardening
.user.ini                 1 GB PHP upload settings
manifest.webmanifest
service-worker.js
assets/
  app.css
  app.js
  browser-ai.js
  pwa.js
  icons/
includes/
  bootstrap.php
  http.php
  providers.php
  search.php
  generators.php
  i18n.php
  i18n_extra.php
  pdf.php
storage/
  uploads/
  generated/
  reports/
  cache/
  logs/
```

## Security notes

- Change the initial password after deployment.
- Keep HTTPS enabled.
- Never commit `storage/settings.json`; it can contain provider tokens.
- Apache protection is included for `storage/`; on Nginx explicitly deny web access to `/storage/`.
- The app intentionally does not scrape logged-in ChatGPT, Claude, Grok, Kimi, Copilot or other consumer web sessions as unofficial APIs.
- Free tiers and model catalogues change, so model IDs, keys and provider order remain editable in Settings.
