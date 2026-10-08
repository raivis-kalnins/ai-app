# Changelog

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
