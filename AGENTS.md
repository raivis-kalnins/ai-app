# Free AI Hub 63 agent guardrails

This repository uses a small "harness engineering" approach so coding agents have explicit quality rules instead of relying on implicit project history.

1. **Free/local first.** Automatic routing starts with self-hosted/local inference. User-configured cloud providers may be tried next, followed by no-key web/community fallbacks; never pretend a quota-backed cloud service is unlimited.
2. **Never turn an image failure into a Puter charge/upgrade flow.** Puter is not an automatic image fallback. It may still be used for video when the user explicitly chooses it and allowance is available.
3. **Free web image search/composition must preserve provenance.** Search results and composed designs must retain source/creator context; never present existing web media as AI-generated or imply that composition changes the original licence. Final exports may include a compact credit line, but the UI must keep the original source/licence navigation available.
4. **No unsupported browser scraping.** Consumer web apps such as Microsoft Copilot or Claude Web may be linked as manual resources, but must not be automated as if they were public inference APIs.
5. **Fail over, do not loop.** 429, quota/auth errors, connection failures and real 5xx responses must produce cooldown state and continue to the next provider. A provider that accepted an asynchronous job is pending, not failed.
6. **No-key routes must remain usable without secrets.** AI Horde image/text anonymous mode and the Openverse/Wikimedia/NASA/Met image search must work without user API keys. Developer/research grounding should keep no-key public sources usable even when optional Brave/Ollama credentials are absent.
7. **Local image generation is the only practical no-cloud-allowance generation path.** Keep both Stable Diffusion WebUI/Forge and ComfyUI routes operational. Existing web images are a separate search/reuse path, not generation.
8. **Show provider truth.** Errors/status must distinguish not configured, cooldown, accepted queue jobs, temporary status retries, web-search results and quota exhaustion. Never convert a long but valid queue into a false provider failure.
9. **Keep language UI accessible.** Language names remain native-language labels; flags are decorative prefixes, not replacements for text labels.
10. **Task-aware local routing.** If Ollama is set to Auto, inspect installed models and select by task; do not download models silently or assume hardware can run an uninstalled model. Developer tasks should prefer root-cause diagnosis and complete production-ready output; current/research tasks should use retrieved evidence when grounding is enabled.
11. **Remote image fetches are restricted.** The composer cache may fetch only public HTTP(S) addresses and must reject loopback/private/link-local targets before and after redirects; do not turn it into an arbitrary fetch/proxy endpoint.
12. **Before release:** run PHP lint on every PHP file, JavaScript syntax checks, verify plugin manifests, and verify the migrated provider/image order still contains all known providers exactly once.
