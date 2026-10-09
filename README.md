# Free AI Hub 63 v0.6.2


## v0.6.2 free web images + stronger image quality

- **Real free web images in Chat and Media Studio:** added a no-key `Free web images` provider that searches **Openverse** and **Wikimedia Commons** and displays a responsive gallery with original, source and licence links.
- **Auto now avoids weak anonymous generations when a real image is a better fallback:** after local/configured generators are unavailable, ordinary photo/reference prompts try Openverse/Wikimedia before the AI Horde anonymous queue. Clearly stylised/original design prompts (for example anime, illustration, logo, 3D render or fantasy art) can still try generation first. Existing/custom v0.6.1 provider orders are migrated so the new web-image provider is inserted immediately before AI Horde as the normal fallback.
- **Better AI Horde photo output:** ordinary prompts prefer photographic/SDXL/realistic community models, anime-heavy model names are strongly penalised unless the prompt asks for an illustrated style, and the prompt receives a photographic quality/negative-prompt guardrail.
- **Censored black-card fix:** AI Horde responses explicitly marked as censored are rejected and reported as such instead of being displayed as if they were a valid generated image.
- **More useful web-image search:** filler such as `image`, `please`, `nice`, `quality` and `colours` is removed before searching so prompts such as `Sigulda Latvia nicer colours and quality` search for the actual subject/place.
- **Chat spacing:** user-side message bubbles keep the Copy action but remove the unnecessary bottom padding shown in the mobile/desktop screenshots.
- **PWA refresh:** static cache bumped to v0.6.2 so the new JavaScript/CSS replaces the old cached image UI after one reload/reopen.

Free web-image results are existing third-party media, not newly generated AI images. Keep the displayed source/licence links with your workflow and verify the licence on the source page before publishing.


## v0.6.1 asynchronous images + stronger own/local AI

- **Image queue fix:** AI Horde jobs are no longer killed when the anonymous queue estimate is longer than 180 seconds. Chat and Media Studio now submit once and poll a lightweight status endpoint for up to 30 minutes.
- **Smarter AI Horde fallback:** when no model is pinned, the app checks active community workers and chooses a model using queue ETA, worker count and performance signals.
- **Own/local AI Auto:** Ollama model `auto` inspects installed models and chooses a stronger coding, research/reasoning or general-chat model for the current task.
- **New local presets:** GPT-OSS 20B, Qwen3-Coder 30B, Qwen3 8B, Gemma 3 12B/4B, DeepSeek R1 8B, Mistral 7B and Llama 3.2 3B.
- **Free means accurate routing, not fake unlimited cloud:** local Forge/Stable Diffusion, ComfyUI and local Ollama use compute you control. Community/free-tier services can still queue, rate-limit or change quotas.

## v0.6.0 free/local harness + language flags

- Added **Local ComfyUI** to the image router. Together with Stable Diffusion WebUI/Forge this is the practical no-cloud-allowance path: generation is limited by your own hardware rather than a vendor allowance.
- Added **AI Horde Text** as a free community server-side chat fallback. It works anonymously without a developer key; an optional AI Horde key only improves queue priority.
- AI Horde remains the built-in no-key image fallback. Anonymous community generation is free, but it can queue or be restricted during high load, so it is not described as a guaranteed unlimited cloud API.
- Puter is still excluded from automatic image generation, preventing exhausted Puter allowance from masking the free image routes.
- Language switchers now show a clear country flag plus the native language name.
- Added repository-level harness guardrails in `AGENTS.md` and `docs/FREE_AI_HARNESS.md`: explicit provider order, cooldown/failover behavior, no consumer-web scraping and release checks.

**About Microsoft Copilot image creation:** it is useful as a manual consumer tool, but it is not an unlimited free public image inference API that this PHP router can safely automate.


## v0.5.9 image fallback fix

Image generation no longer depends on Puter or on having a paid/free-tier API key configured. The Auto router now includes **AI Horde anonymous image generation** as a built-in no-key server-side fallback and Puter is not used automatically for images. AI Horde is community powered and officially supports anonymous requests with the `0000000000` key at the lowest queue priority, so it can be slower during busy periods. Existing configured providers are still preferred first.

The image credential resolver also checks saved connection-style records and common server environment variables for Cloudflare, Hugging Face, Pollinations, Alibaba and SiliconFlow, preventing a provider from being shown as connected elsewhere but skipped by the image router.



## v0.5.8 image-router failover

- Fixed the image-generation path that could still end on **Puter free allowance exhausted** even though the text chat router had other providers. In v0.5.7 the image branch only tried local Stable Diffusion/Forge and Pollinations before Puter; image generation now has its own quota-aware multi-provider router.
- **Auto image order** on a fresh install: Local Stable Diffusion/Forge -> Cloudflare Workers AI / ByteDance SDXL-Lightning -> Alibaba Model Studio / Qwen Image -> Hugging Face image inference -> SiliconFlow image API -> Pollinations -> Puter only as the final browser fallback.
- Missing credentials are skipped immediately. HTTP 429 is treated as rate-limited, 401/402/403 as auth/quota exhaustion, unavailable endpoints and 5xx errors receive a temporary cooldown, and the next image provider is tried automatically.
- Added **Cloudflare image generation** using the existing Cloudflare Account ID + Workers AI token. The default is `@cf/bytedance/stable-diffusion-xl-lightning`.
- Added **Alibaba Model Studio / Qwen Image** with Workspace ID, region, API key and model controls. The default is `qwen-image-2.0`. For new-user free image quota, select the Singapore region and enable **Free Quota Only** in Alibaba so generation stops instead of switching to paid overage.
- Added **Hugging Face image inference** reusing the existing Hugging Face token, plus a configurable image model.
- Added **SiliconFlow image generation** with a dedicated API key/model. SiliconFlow currently starts new accounts with a small free credit, after which normal quota failure causes Free AI Hub to move on.
- Media Studio now exposes the same provider choices and image priority order in Settings.
- The error shown after all routes fail includes the provider chain/statuses so it is clear which resource was missing, rate-limited, exhausted or unreachable.
- Browser WebLLM remains text-only; it is not falsely presented as an image generator.
- Service-worker cache bumped to v0.5.8.

**Important:** Free cloud image tiers are quota-limited, not unlimited. To generate images after Puter is exhausted, configure at least one image-capable free-tier account (Cloudflare is the simplest recurring free allocation) or run local Stable Diffusion/Forge.


## v0.5.7 Claude/free-provider corrections and code export fixes

- Removed the incorrect **AICloud.ai / cloud.aicloud.ai** connector. It was not a verified developer AI service and should not have been presented as one.
- Added **Cloudflare Workers AI** as a real free-tier developer connector. It needs a Cloudflare Account ID plus Workers AI API token. Free accounts currently receive a daily Workers AI allocation; when quota/capacity is exhausted the router automatically falls through.
- Added **Vercel AI Gateway** as an OpenAI-compatible connector. It can route to Claude and many other models. The default model is `anthropic/claude-haiku-5.5`; Vercel currently gives free users recurring AI Gateway credits, so exhausted credits are handled like any other quota failure and the router continues.
- Added **NVIDIA NIM Developer API** using the OpenAI-compatible `integrate.api.nvidia.com/v1/chat/completions` endpoint. NVIDIA Developer Program access is intended for prototyping/development; production licensing/terms differ.
- Added a **Claude Web** resource shortcut to `https://claude.ai/new`. Claude's consumer web session is a manual fallback only; it is not silently automated or scraped. For automatic routing, use Vercel AI Gateway or another supported API connector.
- Reworked chat code-fence parsing. Triple/longer backtick fences, tilde fences, and unclosed final code fences are now rendered as source code instead of leaking raw Markdown such as `````html`` into the message body.
- **Copy code** now has a clipboard fallback for browsers where `navigator.clipboard` is unavailable or denied.
- **Download** now appends the Blob anchor to the DOM before clicking and uses the correct MIME type/extension. HTML code downloads as `index.html` with `text/html`, and assistant responses containing a primary HTML block expose **Download HTML** instead of incorrectly downloading Markdown.
- Service-worker cache bumped to v0.5.7 so the repaired JavaScript replaces cached v0.5.6 assets.


## v0.5.6 free-first failover and Puter quota protection

- Automatic chat routing is now **server/local/free-key first**, then Browser WebLLM, with Puter used only as the final browser fallback. An explicitly selected Puter model is still honoured when possible.
- Before any Puter chat/image/video/compare request, Free AI Hub checks `puter.auth.getMonthlyUsage()`. When the remaining monthly allowance is zero, Puter is skipped before the paid call, avoiding the common Low Balance upgrade dialog and immediately continuing to another configured resource.
- Puter quota errors are temporarily remembered in the browser so repeated requests do not keep reopening the upgrade prompt.
- Browser WebLLM can auto-load as a no-account fallback after server providers fail.
- Image requests now prefer configured local Stable Diffusion/Forge and authenticated Pollinations before Puter.
- Compare, Smart Search summaries and App Builder also prefer server/local/browser resources before Puter.
- The old AICloud custom connector from v0.5.6 is removed in v0.5.7 because it was not a verified developer AI service.


## v0.5.4 UI and login improvements

- Added a clear mobile drawer close button in the sidebar header.
- Replaced the small desktop chevron with a clearer panel/collapse icon and matching expand state/tooltip.
- Added password visibility eye toggle on the login screen.
- Added a WordPress-style **Remember me for 30 days** option using a rotating secure HttpOnly token, not a stored plaintext password.
- Remember-me tokens are invalidated on sign out and automatically become invalid after a password change.
- Added translations for the new login/navigation controls across all supported interface languages.

## v0.5.2 design, music and rapid-prototype modules

The bundled plugin catalog now also includes Figma Prototype Studio, Rapid App Prototyper, Music Studio, Web Audio Lab, Brand & Visual Design Studio, SVG & Illustration Studio, and Mobile & PWA Prototyper. Enable only the modules you want; enabled modules add focused context and quick actions to AI Chat.

App & WP Builder also contains three preview-first project types: Instant website/app prototype, Figma-style responsive prototype, and Music/audio web app prototype. These produce a downloadable ZIP and a sandboxed live HTML preview for fast iteration before production development.


A private, password-protected PHP 8+ AI workspace for `ai.63.lv`. It combines free/local AI routing, multilingual chat, universal smart search, image/video generation, project/WordPress ZIP generation and temporary file storage without forcing a monthly AI subscription.


## v0.5.x workspace highlights

- Redesigned the workspace shell with a more familiar **ChatGPT/Copilot-style information architecture** while keeping Free AI Hub branding: New chat, Smart Search, Library, Plugins, More tools and a 30-day Recent chats list in the left sidebar.
- Added collapsible desktop sidebar and improved responsive navigation.
- Chat is cleaner and more document-like: assistant answers use a wide reading column, user messages remain compact, the composer stays close to the bottom, and existing Copy / Download / Copy code tools remain available.
- Added real **multi-thread chat history**. Each conversation has its own thread ID/title and remains available for up to 30 days. Existing pre-v0.5 history is migrated into a legacy thread automatically.
- Added a **Plugins / Add-ons** page with search, category cards, enable/disable controls, permissions display and install/remove support.
- Bundled safe AI-skill add-ons include WordPress Expert, Code Reviewer, Research & Compare, UI/UX Designer, SEO & Content, E-commerce Growth, Web Performance and Security Checklist.
- Enabled add-ons can add AI system context and quick-action chips to Chat. Fresh installs enable WordPress Expert, Code Reviewer and Research & Compare by default.
- Added safe custom add-on ZIP installation. A plugin ZIP contains one `plugin.json`; uploaded add-ons are manifest-based and **cannot execute arbitrary PHP or JavaScript**.
- File retention is unchanged from v0.4.2: uploads, generated files/media and search reports auto-delete after **48 hours**; chat history remains **30 days**.

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
- **Cloudflare Workers AI** - daily free Workers AI allocation on the Free plan; requires Account ID + API token.
- **Vercel AI Gateway** - OpenAI-compatible gateway that can route to Claude and many other models; free accounts currently receive recurring AI Gateway credits.
- **NVIDIA NIM Developer API** - hosted OpenAI-compatible endpoints for prototyping/development through the NVIDIA Developer Program.

### Optional free/free-credit account keys

- **Groq** - free/developer account limits; default `openai/gpt-oss-20b`.
- **Google Gemini** - free tier where available; default `gemini-3.8-flash`.
- **OpenRouter** - `openrouter/free` routes to currently available free models; account key required.
- **Hugging Face Inference Providers** - free account credits; OpenAI-compatible router endpoint.
- **Mistral API** - Free mode with limited usage/rate limits; default `mistral-small-latest`.
- **Ollama Cloud** - optional account key.
- **Pollinations** - optional authenticated/credit provider; not labelled unlimited/no-key.

The server router skips missing optional keys, handles HTTP 429 and quota/auth cooldowns, and moves to the next provider. Browser WebLLM is the first client-side no-account fallback; Puter is last and is pre-checked for remaining monthly allowance before a paid AI call.

## Open model presets

Settings includes one-click local presets:

```text
Auto task router          -> auto
GPT-OSS 20B               -> gpt-oss:20b
Qwen3-Coder 30B           -> qwen3-coder:30b
Qwen3 8B                  -> qwen3:8b
Gemma 3 12B / 4B          -> gemma3:12b / gemma3:4b
DeepSeek R1 8B            -> deepseek-r1:8b
Mistral 7B                -> mistral:7b
Llama 3.2 3B              -> llama3.2:3b
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

## Plugins / add-ons

Open **Plugins** from the sidebar to enable built-in AI skills or install a custom add-on ZIP. The v0.5 plugin format is intentionally safe and simple: it changes AI instructions and adds reusable quick actions, but it does not load arbitrary executable server/client code.

Minimal `plugin.json`:

```json
{
  "id": "my-addon",
  "name": "My Add-on",
  "version": "1.0.0",
  "category": "Custom",
  "description": "Adds reusable AI guidance",
  "permissions": ["AI prompt context"],
  "ai_context": "Extra instructions for the assistant...",
  "quick_actions": [
    {"label": "My action", "prompt": "Do this..."}
  ]
}
```

Package that file inside a ZIP and upload it from **Plugins > Install plugin**. The ZIP is validated against path traversal and only non-executable manifest/document/image file types are accepted. Bundled add-ons cannot be removed, but they can be disabled.

## 48-hour temporary storage

Uploads, generated project/media files and Smart Search reports are automatically removed **48 hours after their file modification/creation time**. The 48-hour rule is separate from chat history, which is kept for up to **30 days**. Settings, provider state, logs and chat history are excluded from the 48-hour file deletion. Cache uses its own shorter retention setting.

The app performs opportunistic cleanup during requests. For reliable cleanup even when the site is idle, run hourly:

```cron
17 * * * * /usr/bin/php /path/to/ai-free-hub/cleanup.php >/dev/null 2>&1
```

Files & Storage also has a manual **Delete expired files now** button and shows each file's planned auto-delete time.

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

### Upgrade from an earlier version

Back up `storage/settings.json`, replace the application files, then keep the existing `storage/` directory. Settings migration adds new providers/UI defaults without deleting saved keys. Existing pre-v0.5 chat rows are automatically grouped into a legacy chat thread. The new plugin state file is created automatically.

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
  plugins.php
plugins/
  README.md
  wordpress-expert/
  code-reviewer/
  research-compare/
  ...
storage/
  uploads/
  generated/
  reports/
  cache/
  logs/
  chats/
```

## Security notes

- Change the initial password after deployment.
- Keep HTTPS enabled.
- Never commit `storage/settings.json`; it can contain provider tokens.
- Apache protection is included for `storage/` and `plugins/`; on Nginx explicitly deny direct web access to `/storage/` and `/plugins/`.
- The app intentionally does not scrape logged-in ChatGPT, Claude, Grok, Kimi, Copilot or other consumer web sessions as unofficial APIs.
- Free tiers and model catalogues change, so model IDs, keys and provider order remain editable in Settings.


## v0.4.2 storage and chat
- Chat history is retained for a maximum of 30 days.
- User uploads, generated projects/media and search reports auto-delete after 48 hours.
- Settings, provider state, logs and chat history are excluded from the 48-hour file cleanup.
- Chat assistant messages include Copy/Download actions and fenced code blocks include Copy code.
- Smart Search renders sector-specific structured filters based on selected sectors.


## Responsive UI fix (v0.5.2)

Phones and tablets use an overlay navigation drawer and a full-width workspace. Chat controls, the composer, Smart Search controls, sector filters and primary action buttons adapt down to 320-380px viewports. The service-worker cache version was also bumped so an installed PWA does not keep the broken v0.5.0 CSS.

## Figma OAuth connection (v0.5.3)

Settings now contains a **Figma OAuth** block with Client ID, Client Secret, Redirect URI, scopes, Connect, Test and Disconnect controls. The Client Secret is stored only in the protected `storage/settings.json` file and is never printed back into the page. OAuth access/refresh tokens are stored separately in protected `storage/figma_oauth.json`.

For `https://ai.63.lv/`:

1. In Figma Developer → My Apps → your OAuth app → **OAuth credentials**, add the exact Redirect URL shown in Free AI Hub Settings. If the app shows `https://ai.63.lv/`, keep that exact value in Figma.
2. Under **OAuth scopes**, enable at least `current_user:read`, `file_content:read`, and `file_metadata:read`.
3. Copy the Figma **Client ID** and **Client Secret** into Free AI Hub → Settings → Figma OAuth.
4. Click **Connect Figma**. Free AI Hub saves the credentials, starts the OAuth flow, validates the `state` value and uses PKCE.
5. After Figma redirects back, use **Test connection** to verify the current Figma account.

The app automatically refreshes long-lived OAuth access using the refresh token when possible.


## v0.5.6 Figma project builder

Settings now stores a Figma Team ID and optional projects/folders scopes. App & WP Builder can load accessible Figma projects/folders and files when the OAuth app is eligible, or use a direct Figma design URL as a universal fallback. The new **Figma -> Bootstrap 5 Gutenberg Builder Theme** target creates an installable block theme scaffold and, when AI is available, uses Figma page/frame metadata as implementation context.

Settings also has a **Clear cache & refresh** maintenance action. It clears temporary search/application cache and provider cooldown state only; it does not delete uploads, generated files, reports, chat history, settings or Figma OAuth tokens.
