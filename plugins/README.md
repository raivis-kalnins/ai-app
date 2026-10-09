# Free AI Hub 63 add-ons

Add-ons are intentionally manifest-only. They can add AI system context and reusable quick actions, but installed ZIPs cannot execute PHP or JavaScript. This keeps community add-ons useful without turning plugin installation into arbitrary server-side code execution.

A plugin package must contain exactly one `plugin.json`. Example:

```json
{
  "id": "my-addon",
  "name": "My Add-on",
  "version": "1.0.0",
  "category": "Custom",
  "description": "Reusable AI guidance",
  "icon": "AI",
  "permissions": ["AI prompt context"],
  "ai_context": "Additional instructions for the AI assistant.",
  "quick_actions": [
    {"label": "Run my task", "prompt": "Do the task..."}
  ]
}
```

Allowed uploaded files are non-executable manifest/document/image types. Bundled add-ons are marked with `.bundled` and can be disabled but not removed from the UI.


## Bundled v0.6.4 developer/design toolbox

The bundled set now includes GitHub repository guidance, API/JSON, SQL/database help, debugging, refactoring, test generation, regex/validation, accessibility, landing pages, design systems, image composition, WordPress hooks, WooCommerce, documentation and deployment checklists. Bundled manifests can declare `default_enabled: true`; migration only uses that flag for a plugin that has never had an explicit user state, so an existing disabled choice is not overwritten.
