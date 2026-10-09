# Free AI Hub 63 add-ons

v0.5 add-ons are intentionally manifest-only. They can add AI system context and reusable quick actions, but installed ZIPs cannot execute PHP or JavaScript.

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
