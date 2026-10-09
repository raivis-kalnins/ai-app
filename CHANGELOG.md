# Changelog

## 0.6.2
- Added `Free web images` as a no-key image provider using Openverse and Wikimedia Commons. Results render as a responsive gallery in Chat and Media Studio with original/source/licence links.
- Auto image routing now tries free web-image search before AI Horde for ordinary photo/reference prompts when local/configured generators are unavailable. Clearly stylised/original design prompts keep generation first. Older custom image orders are migrated by inserting `free_web` immediately before `aihorde` as the normal fallback.
- Added prompt cleanup and quality/relevance scoring for web-image search so real places and concrete subjects are not diluted by UI words such as image, quality, nice or colours.
- Improved AI Horde quality routing: photo prompts prefer photographic/SDXL/realistic model names, anime-heavy models are avoided unless requested, and prompts get a quality-focused negative prompt.
- AI Horde results flagged as censored are no longer displayed as successful black images; the UI receives a clear retry/web-image fallback error instead.
- Tightened the bottom spacing on user chat bubbles while retaining the Copy action.
- Bumped the service-worker/static cache to v0.6.2.

## 0.6.1
- Fixed image generation on slow AI Horde queues: accepted jobs now return immediately as asynchronous jobs and the browser polls status instead of cancelling them after the old 180-second PHP timeout.
- Added live AI Horde worker/model selection when no image model is pinned, plus a low-work 10-step default for the anonymous community queue.
- Moved AI Horde to the final Auto image fallback after local and configured providers; local Stable Diffusion/Forge and ComfyUI remain the true no-cloud-quota paths.
- Added task-aware Ollama Auto routing. The app inspects installed Ollama models and prefers coding, reasoning/research or general-chat models according to the current task.
- Added stronger open/local presets: GPT-OSS 20B, Qwen3-Coder 30B, Qwen3 8B, Gemma 3 12B/4B, DeepSeek R1 8B, Mistral 7B and Llama 3.2 3B.
- Updated the harness guardrails so a long external queue is represented as pending state rather than a provider failure.
- Bumped the PWA/static cache to v0.6.1.

## 0.6.0
- Added **Local ComfyUI** as a second self-hosted image backend alongside Stable Diffusion WebUI/Forge. Local generation has no third-party cloud allowance; the configured ComfyUI checkpoint is executed through its workflow API.
- Added **AI Horde Text** as a no-account server-side chat fallback. Anonymous requests use the documented `0000000000` key; an optional free account key can improve community queue priority.
- Kept **AI Horde image generation** as the built-in no-key image fallback and kept Puter out of automatic image routing.
- Added country flags to every language option while retaining native language names for accessibility.
- Added `AGENTS.md` and `docs/FREE_AI_HARNESS.md` with explicit free-first routing, quota/cooldown, no-scraping and release guardrails inspired by harness-engineering practice.
- Bumped the PWA/static cache to v0.6.0.

## 0.5.9
- Removed Puter from automatic **image** generation, so an exhausted Puter allowance can no longer turn a free image request into a Puter error. Puter remains available for text/video where configured.
- Added AI Horde as a real no-account/no-developer-key image fallback. Anonymous image requests use the official `0000000000` anonymous key and the community queue.
- Auto image routing now prioritizes local Stable Diffusion/Forge, then AI Horde anonymous mode, then any configured cloud image connectors; there is no automatic Puter image fallback.
- Added optional AI Horde account key, model and queue timeout settings; a key is not required.
- Image credentials now also resolve from saved connection-style settings and common server environment variables, so existing Cloudflare/Hugging Face/etc. connections are not missed by the image router.
- Bumped the service-worker cache to v0.5.9 so the repaired image routing JavaScript refreshes immediately after upgrade.

## 0.5.8
- Added a dedicated image-generation failover router instead of allowing image requests to fall from local/Pollinations directly to Puter.
- Added Cloudflare Workers AI / ByteDance SDXL-Lightning image generation.
- Added Alibaba Model Studio / Qwen Image generation with region, Workspace ID, API key and model settings.
- Added Hugging Face image inference and SiliconFlow image generation.
- Media Studio Auto now walks all configured image providers, records rate/quota/unavailable status, applies cooldowns and uses Puter only as the final browser fallback.
- Chat image tasks now use the same server image router before considering Puter.
- Improved provider-chain error reporting so exhausted Puter does not hide the preceding image-provider results.
- Added Settings fields for image provider priority and the new image connectors.
- Service-worker cache bumped to v0.5.8.

## 0.5.7
- Removed the unverified AICloud.ai/cloud.aicloud.ai connector.
- Added Cloudflare Workers AI free-tier connector with Account ID + API token support.
- Added Vercel AI Gateway connector with Claude-capable default model and normal quota failover.
- Added NVIDIA NIM Developer API connector for prototyping/development.
- Added Claude Web as a manual external resource, not an automated router provider.
- Repaired fenced-code parsing, including unclosed HTML fences.
- Repaired Copy code with a clipboard fallback.
- Repaired HTML/code download with DOM-backed Blob links, correct MIME types and filenames; HTML downloads as index.html.
- Service worker cache bumped to v0.5.7.

# Free AI Hub 63 changelog

## 0.5.6
- Added Figma Team ID, connected project/folder browser and file selector in App & WP Builder, with a direct Figma file URL fallback.
- Added Figma -> Bootstrap 5 Gutenberg Builder Theme output with theme.json, native Gutenberg templates/parts/patterns and Bootstrap 5 integration.
- Connected Figma file page/frame metadata is supplied to AI generation for implementation-ready WordPress theme builds.
- Added Settings-only Clear cache & refresh, clearing Smart Search/app cache and temporary provider cooldowns without deleting uploads, generated files, reports, chat history, settings or Figma OAuth tokens.
- Added optional Figma projects:read/folders:read scope support; direct-file import remains available when Figma restricts project-list endpoints.
- Media Studio Auto mode is now free/local-first for images: local Stable Diffusion/Forge, configured Pollinations, then Puter only as a last fallback when allowance remains.
- Existing Puter-only media configurations migrate to Auto so exhausted Puter allowance no longer remains the default image route.
- Service worker cache bumped to v0.5.6.

# Changelog

## 0.5.5
- Changed automatic chat routing to server/local/free-key first, Browser WebLLM second and Puter last.
- Added Puter monthly allowance preflight via `puter.auth.getMonthlyUsage()` so an exhausted free allowance is skipped before AI calls.
- Added short browser-side Puter quota cooldown after insufficient-funds/subscription errors to avoid repeated upgrade prompts.
- Reordered image, compare, Smart Search summary and App Builder fallbacks so Puter is no longer the primary default path.
- Added optional AICloud.ai optional custom connector provider with configurable endpoint/model/key and the normal 429/402/403 failover/cooldown behavior.
- Fixed optional API-key inputs for Custom OpenAI-compatible style connectors.
- Bumped service-worker cache to v0.5.5.

## 0.5.4
- Added a dedicated mobile sidebar close button with a larger touch target.
- Replaced the desktop text chevron with a clearer sidebar panel/collapse SVG icon and proper expand/collapse labels.
- Added login password visibility eye toggle.
- Added secure 30-day Remember me login using rotating HttpOnly persistent tokens; plaintext passwords are never stored.
- Remember tokens are revoked on sign out and invalidated on password change.
- Added translations for the new login/navigation controls across all 15 supported languages.
- Bumped the service worker cache so the navigation/login UI refreshes immediately after upgrade.

## 0.5.3
- Added complete Figma OAuth admin setup: Client ID, Client Secret, redirect URI, scopes, Connect, Test, Disconnect and connection status.
- Added OAuth state validation and PKCE for Figma browser authorization.
- Figma OAuth access/refresh tokens are stored separately in protected storage and secrets are never rendered back into Settings.
- Added automatic Figma token refresh and `/v1/me` connection testing.


## 0.5.2
- Smart Search primary action is now a larger icon-only search button with an accessible label, keeping the desktop and mobile UI cleaner.
- Added bundled design/music modules: Figma Prototype Studio, Rapid App Prototyper, Music Studio, Web Audio Lab, Brand & Visual Design Studio, SVG & Illustration Studio, and Mobile & PWA Prototyper.
- App & WP Builder now includes Instant Prototype, Figma-style Prototype, and Music App Prototype project types.
- Prototype project types create a self-contained HTML preview plus the downloadable ZIP.
- Added sandboxed `preview.php` so generated prototypes can be viewed quickly without executing them in the main application origin.
- Existing 30-day chat history and 48-hour generated/upload/report retention remain unchanged.

# 0.5.1 - 2026-10-08

- Fixed mobile/tablet shell regression where the v0.5 desktop sidebar margin overrode the earlier responsive rule and squeezed content into a narrow column.
- Sidebar is now an overlay drawer below 980px with backdrop, Escape/outside close, and auto-close after navigation.
- Main content now stays 100% wide on phones/tablets.
- Reworked chat controls for responsive two-column/one-column layouts.
- Chat composer remains horizontal on phones with a compact icon send button.
- Search & compare button is smaller and better proportioned on desktop, full-width and touch-friendly on mobile.
- Search countries/sectors/structured filters collapse to a clean single-column layout on small screens.
- Added extra 380px breakpoint for very narrow phones.
- Bumped service-worker cache to force the repaired CSS/JS to replace v0.5.0 cached assets.

## 0.5.0
- Reworked the left navigation into a cleaner ChatGPT/Copilot-inspired workspace: New chat, Smart Search, Library, Plugins, More tools and recent chats.
- Added collapsible desktop sidebar and responsive mobile behavior.
- Added multi-thread chat history with per-thread titles while keeping the 30-day retention policy; legacy history is preserved as a migrated thread.
- Added a Plugins / Add-ons manager with search, category filters, enable/disable, remove and ZIP installation.
- Added safe manifest-only plugin packages (`plugin.json`); uploaded add-ons cannot execute arbitrary PHP or JavaScript.
- Added bundled WordPress, code review, research, UI/UX, SEO, e-commerce, web performance and security skills.
- Enabled plugins can append AI system context and expose quick-action prompt chips in Chat.
- Kept 48-hour automatic deletion for uploads, generated media/projects and Smart Search reports.
- Added plugin/navigation translations across all 15 supported interface languages.

## 0.4.2
- Restored automatic 48-hour deletion for uploaded files, generated projects/media and Smart Search reports.
- Chat history remains separate and is retained for up to 30 days.
- Settings, provider state, logs and chat history are not removed by the 48-hour file cleanup.
- Files & Storage now shows the exact auto-delete time for each file.
- Manual cleanup and `cleanup.php` delete already-expired files immediately; opportunistic cleanup continues during normal requests.

## 0.4.1
- Chat history persists server-side for up to 30 days and is automatically pruned after that.
- Uploaded, generated and report files no longer expire after 48 hours; they remain until manually deleted.
- Chat replies now support formatted Markdown-style code blocks, Copy, Download and per-block Copy code actions.
- Smart Search structured filters are now sector-specific for vehicles, property, jobs, services, agencies, electronics, computers, phones, home/garden, construction, agriculture, transport, animals, fashion, business, leisure, education, health/beauty and other listings.
- Smart Search reports include selected structured filters.

## 0.4.0 - 2026-10-08

- Puter is no longer tied to the legacy `gpt-5-nano` default. Existing installs using that old default migrate to task-aware **Auto** routing.
- Added live Puter model discovery and task-specific model selection for chat, code/WordPress, research and multi-AI comparison.
- AI Chat now treats image/video requests as real generation tasks instead of asking a text model to describe a prompt. Puter image/video is tried first, with configured fallbacks.
- Added Puter research web-search tooling when the selected compatible chat model supports it.
- Media Studio now exposes Puter image models and includes safe current model fallbacks if model discovery is unavailable.
- Smart Search now supports **sector/category multi-select** with **All sectors** selected by default.
- Added sectors for vehicles, real estate, jobs, services, digital agencies, electronics, computers/IT, phones, home/garden, construction, agriculture, transport/logistics, animals, fashion, business/industrial, leisure, education, health/beauty and other classifieds.
- Added country/sector marketplace hints, including broad Latvian classifieds coverage through `ss.lv` / `ss.com`, without restricting search only to those sites.
- Replaced car-only Smart Search filters with generic brand/company, model, keywords, location, condition, price and year filters.
- Search result structuring is now sector-neutral and can capture explicit company, salary, area, condition and location fields without inventing missing facts.
- Redesigned Smart Search HTML and PDF exports with a cleaner report header, search overview, sector/country metadata, AI summary cards, research notes, result cards, source URLs and page numbers.
- Added translations for the new task router and sector search UI across all 15 supported interface languages.

## 0.3.0 - 2026-10-08

- Made AI Chat the first page after login.
- Reworked the application to a clean light-first UI with optional dark mode.
- Added complete interface language packs for English, Latvian, Lithuanian, Estonian, Swedish, Norwegian, Danish, Finnish, Icelandic, Polish, Russian, Ukrainian, Spanish, French and German.
- hCaptcha remains optional and now always follows the active interface language; fresh installs default to English.
- Added provider Connect / Setup / Get key / Test controls.
- Added Hugging Face Inference Providers and Mistral API Free-mode adapters.
- Added Llama 3.x, Gemma, Mistral 7B, DeepSeek and Qwen local Ollama model presets.
- Added Next.js + React + TypeScript + Node.js route handlers + Supabase project generator.
- Added Puter browser-side text-to-image and text-to-video Media Studio flows.
- Replaced Smart Search “type” with country multiselect; All countries is selected by default.
- Expanded countries to Baltic, Nordic, UK, Germany, Poland, France, Spain, Ukraine and Russia.
- Smart Search now combines available SearXNG, Ollama Web Search, DuckDuckGo, Gemini grounding and Groq Browser Search research resources.
- Added Puter browser-side AI summary fallback and persisted it into downloadable reports.
- Search PDF/HTML reports now include AI summary/research notes and no longer contain obsolete “mode” metadata.
- Updated PWA light theme metadata and multilingual install instructions.

## 0.2.0 - 2026-10-08

- Added Latvian login UI and optional hCaptcha.
- Added no-developer-key Puter AI browser fallback.
- Added WebLLM browser-local AI fallback with WebGPU.
- Added LM Studio, llama.cpp and LocalAI local providers.
- Added favicon/PWA/mobile install support.
- Existing settings are migrated without deleting saved keys.

## 0.1.0 - 2026-10-08

- Initial Free AI Hub 63 package.
- Password-protected private dashboard.
- Multi-provider AI failover with cooldowns.
- Smart search with SearXNG, Ollama Web Search and DuckDuckGo fallback.
- HTML/JSON/PDF search reports.
- React + TypeScript, Svelte, Alpine.js + AJAX, WordPress and PHP starter ZIPs.
- 1 GB upload/download and 48-hour automatic storage cleanup.
