let engine = null;
let loading = null;
const modelId = document.body?.dataset?.webllmModel || 'Llama-3.2-1B-Instruct-q4f16_1-MLC';

function emit(text) {
  window.dispatchEvent(new CustomEvent('aih-webllm-status', {detail: String(text || '')}));
}

window.AIHWebLLM = {
  model: modelId,
  isSupported() { return !!navigator.gpu; },
  isLoaded() { return !!engine; },
  async load() {
    if (engine) return engine;
    if (loading) return loading;
    if (!navigator.gpu) throw new Error('WebGPU is not available in this browser.');
    loading = (async () => {
      emit('Loading WebLLM library…');
      const webllm = await import('https://esm.run/@mlc-ai/web-llm');
      emit('Preparing browser AI model. First download can be several hundred MB…');
      engine = await webllm.CreateMLCEngine(modelId, {
        initProgressCallback: (p) => emit(p?.text || 'Loading browser AI…'),
        logLevel: 'WARN',
      }, {context_window_size: 2048});
      emit('Browser AI ready: ' + modelId);
      return engine;
    })();
    try { return await loading; }
    finally { loading = null; }
  },
  async chat(messages) {
    const e = engine || await this.load();
    const r = await e.chat.completions.create({messages, temperature: 0.3, max_tokens: 700});
    const text = r?.choices?.[0]?.message?.content || '';
    if (!String(text).trim()) throw new Error('Browser AI returned an empty response.');
    return String(text).trim();
  }
};
