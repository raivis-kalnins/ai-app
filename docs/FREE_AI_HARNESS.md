# Free AI routing harness

The app intentionally avoids adding a heavy Python MLOps dependency to the PHP runtime. Instead it applies the useful harness-engineering ideas directly to provider orchestration: explicit provider capabilities, ordered context/routing rules, health/cooldown state, structured failure reasons and release-time guardrails.

## Free image path

1. Stable Diffusion WebUI / Forge on your own hardware — no cloud quota.
2. ComfyUI on your own hardware — no cloud quota.
3. Configured cloud free-tier/credit providers — only when credentials are present.
4. Openverse + Wikimedia Commons + NASA Images + The Met — no-key search for existing openly licensed/public-domain web images. Results retain original/source/licence links and are labelled as web images, never as AI-generated media. Selected results can be composed locally in the browser into cards/posters/social graphics/heroes/moodboards without changing their underlying licence.
5. AI Horde anonymous community queue — no account/key required, final no-key generative fallback. Accepted jobs are asynchronous and stay queued instead of being cancelled by a PHP request timeout.

Puter is not an automatic image fallback. For ordinary photo/reference prompts, web-image search is intentionally before AI Horde so a real photograph/reference can be used instead of a weak anonymous generation when no stronger generator is available. Clearly stylised/original design prompts may move AI Horde ahead of web search while retaining web images as a fallback.

## Free text path

1. Local Ollama / LM Studio / llama.cpp / LocalAI. Ollama `auto` inspects installed models and selects by task (coding, research/compare, or general chat).
2. AI Horde text community queue (anonymous works without a key).
3. Configured free-tier APIs such as Groq/Gemini/OpenRouter/Cloudflare etc.
4. Browser WebLLM.
5. Puter only while allowance remains.

## Important limitation

There is no reliable third-party cloud image API that is both unlimited and guaranteed free. "No limits" requires compute you control (for example Forge or ComfyUI on a local GPU/server). Community services can remain free while still using queues, request limits or temporary restrictions under load.

## Queue state is not failure

Long-running community inference is represented as an accepted job ID plus `pending`, `wait_time` and `queue_position`. The browser polls status independently, so web-server execution limits do not incorrectly cancel a legitimate generation. Temporary status 5xx/429 responses remain pending and are retried.


## Image-quality guardrails

- Normal photographic prompts bias the AI Horde model picker toward SDXL/realistic/photo-oriented models and away from anime/cartoon model names.
- Explicit illustration/anime prompts are allowed to select stylised models.
- AI Horde generations marked as censored are rejected rather than displayed as a valid black image.
- Free web-image search removes generic request filler before querying and ranks larger, more relevant results higher.
- Composer image caching is deliberately restricted to public HTTP(S) hosts, re-validates redirects, caps file size and accepts image content only. Do not weaken this into an unrestricted fetch endpoint.

## Search / coding grounding

Normal web grounding can merge DuckDuckGo, Wikipedia, SearXNG, optional Brave Search and optional Ollama Web Search. When the query is clearly developer-oriented, the harness can also add public GitHub repository, Stack Overflow, npm and Packagist results. Research/current queries can add Hacker News and arXiv. These are evidence inputs, not authority by themselves: the answering model should prefer official documentation/repositories when available and state uncertainty rather than inventing missing facts.

## Free-source design composition

The browser composer is intentionally deterministic rather than another paid image-generation dependency. It can use up to four selected search results, crop them into a selected template, add overlay text and export PNG. Clear “create card/poster/social graphic/moodboard from available sources” prompts can auto-pick results and return a saved composed image in Chat. Source/creator metadata remains attached to the result workflow, and composition never overrides the source licence.

## v0.6.6 visual-composition completion rule

For greeting cards, birthday/holiday cards, posters and any request that explicitly asks for text on an image, a background image is only an intermediate result. The router must extract the exact copy, create or select a suitable background, run the compositor, verify that the final PNG contains readable overlay text, save the composed asset, and return that finished file. Image-generation models are treated as background generators; they are not trusted to spell user copy correctly.


## v0.6.7 visual-quality rule

Greeting-card requests are design tasks. Prefer a purpose-built generated background before generic stock search when a free/local generator is available. Strip overlay copy from the background-generation prompt, reserve visual breathing room for the greeting, and always complete the browser-side typography/composition pass before presenting the final card. Reusable web images remain the fallback and must retain source/licence metadata in the UI.
