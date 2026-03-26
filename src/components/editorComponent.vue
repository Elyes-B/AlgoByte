<script setup>
import { computed, ref, watch } from 'vue'
import { VueMonacoEditor } from '@guolao/vue-monaco-editor'

const eclipseTheme = {
  base: 'vs',
  inherit: true,
  rules: [
    { token: 'keyword', foreground: '7F0055', fontStyle: 'bold' },
    { token: 'comment', foreground: '3F7F5F' },
    { token: 'string', foreground: '2A00FF' },
    { token: 'number', foreground: '000000' }
  ],
  colors: {
    'editor.background': '#ffffff',
    'editor.foreground': '#000000'
  }
}

const handleBeforeMount = (monaco) => {
  monaco.editor.defineTheme('eclipse', eclipseTheme)
}

const apiBaseUrl = (import.meta.env.VITE_API_URL ?? 'http://127.0.0.1:8000').replace(/\/$/, '')
const codeTemplates = {
  javascript: `console.log("Hello AlgoByte");`,
  python: `print("Hello AlgoByte")`,
  java: `public class Main {
  public static void main(String[] args) {
    System.out.println("Hello AlgoByte");
  }
}`,
  c: `#include <stdio.h>

int main(void) {
  printf("Hello AlgoByte\\n");
  return 0;
}`
}
const failureStatuses = new Set(['compile_error', 'runtime_error', 'runner_unavailable'])

const code = ref(codeTemplates.javascript);
const language = ref('javascript');
const fontSize = ref(14);
const isSubmitting = ref(false);
const statusMessage = ref('');
const errorMessage = ref('');
const lastSubmission = ref(null);

watch(language, (lang) => {
  code.value = codeTemplates[lang] ?? codeTemplates.javascript
})

const getApiErrorMessage = (payload) => {
  if (payload?.message) {
    return payload.message
  }

  const firstValidationError = Object.values(payload?.errors ?? {})[0]

  if (Array.isArray(firstValidationError) && firstValidationError.length > 0) {
    return firstValidationError[0]
  }

  return 'Unable to send the code to the API.'
}

const hasExecutionFailure = computed(() => {
  return lastSubmission.value ? failureStatuses.has(lastSubmission.value.status) : false
})

const resultPanelClass = computed(() => {
  return errorMessage.value || hasExecutionFailure.value
    ? 'submission-panel-error'
    : 'submission-panel-success'
})

const formatExecutionStatus = (status) => {
  return status ? status.replaceAll('_', ' ') : 'unknown'
}

const formatRuntimeLabel = (submission) => {
  if (!submission?.runtime) {
    return ''
  }

  return submission.runtime_version
    ? ` using ${submission.runtime} ${submission.runtime_version}`
    : ` using ${submission.runtime}`
}

const runCode = async () => {
  errorMessage.value = ''
  statusMessage.value = ''
  lastSubmission.value = null

  if (!code.value.trim()) {
    errorMessage.value = 'Write some code before sending it to the API.'
    return
  }

  isSubmitting.value = true

  try {
    const response = await fetch(`${apiBaseUrl}/api/code-submissions`, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        language: language.value,
        code: code.value
      })
    })

    const payload = await response.json().catch(() => null)

    if (!response.ok) {
      throw new Error(getApiErrorMessage(payload))
    }

    lastSubmission.value = payload?.data ?? null
    statusMessage.value = payload?.message ?? 'Code executed successfully.'
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'Unable to send the code to the API.'
  } finally {
    isSubmitting.value = false
  }
}

const increaseFontSize = () => {
  if (fontSize.value < 36) fontSize.value += 2;
}

const decreaseFontSize = () => {
  if (fontSize.value > 10) fontSize.value -= 2;
}

const editorOptions = computed(() => ({
  fontSize: fontSize.value,
  automaticLayout: true
}))

const languages = [
  { value: 'javascript', label: 'JavaScript' },
  { value: 'python', label: 'Python' },
  { value: 'java', label: 'Java' },
  { value: 'c', label: 'C' },
]

const theme = ref('vs-dark')
const themes = [
  { value: 'vs-dark', label: 'Dark' },
  { value: 'vs', label: 'Light' },
  { value: 'hc-black', label: 'High Contrast' }
]

const isDarkTheme = computed(() => {
  return !['vs', 'eclipse'].includes(theme.value)
})
</script>

<template>
  <div :class="['editor-container border rounded shadow-sm overflow-hidden my-3 mx-2', isDarkTheme ? 'border-secondary dark-theme' : 'border-secondary light-theme']">
    <!-- Toolbar -->
    <div :class="['toolbar d-flex align-items-center justify-content-between p-2', isDarkTheme ? 'bg-dark text-white' : 'bg-light text-dark']"
         :style="{ borderBottom: isDarkTheme ? '2px solid #2d2d2d' : '2px solid #e9ecef' }">
      <div class="d-flex align-items-center gap-3 flex-wrap">
        
        <!-- Theme Selector -->
        <div class="d-flex align-items-center gap-2">
          <label for="theme-select" :class="['mb-0 fw-bold small d-none d-sm-block', isDarkTheme ? 'text-light' : 'text-dark']">Theme:</label>
          <select id="theme-select" v-model="theme" :class="['form-select form-select-sm', isDarkTheme ? 'bg-secondary text-white border-secondary' : 'bg-white text-dark border-secondary']" style="width: auto; cursor: pointer;">
            <option v-for="t in themes" :key="t.value" :value="t.value">
               {{ t.label }}
            </option>
          </select>
        </div>

        <div :class="['vr mx-1 d-none d-sm-block', isDarkTheme ? 'bg-secondary' : 'bg-dark']"></div>

        <!-- Language Selector -->
        <div class="d-flex align-items-center gap-2">
          <label for="language-select" :class="['mb-0 fw-bold small d-none d-sm-block', isDarkTheme ? 'text-light' : 'text-dark']">Language:</label>
          <select id="language-select" v-model="language" :class="['form-select form-select-sm', isDarkTheme ? 'bg-secondary text-white border-secondary' : 'bg-white text-dark border-secondary']" style="width: auto; cursor: pointer;">
            <option v-for="lang in languages" :key="lang.value" :value="lang.value">
               {{ lang.label }}
            </option>
          </select>
        </div>

        <div :class="['vr mx-1 d-none d-sm-block', isDarkTheme ? 'bg-secondary' : 'bg-dark']"></div>

        <!-- Font Size Controls -->
        <div class="d-flex align-items-center gap-2">
          <span :class="['small fw-bold d-none d-sm-block', isDarkTheme ? 'text-light' : 'text-dark']">Font Size:</span>
          <div class="btn-group shadow-sm">
            <button @click="decreaseFontSize" :class="['btn btn-sm d-flex align-items-center justify-content-center', isDarkTheme ? 'btn-outline-light' : 'btn-outline-dark']" title="Decrease Font Size">
              <span style="font-size: 1.2rem; line-height: 0.5;">-</span>
            </button>
            <div :class="['px-2 d-flex align-items-center justify-content-center border-top border-bottom', isDarkTheme ? 'bg-secondary text-white border-light' : 'bg-white text-dark border-dark']" style="min-width: 45px; font-size: 0.875rem;">
              {{ fontSize }}px
            </div>
            <button @click="increaseFontSize" :class="['btn btn-sm d-flex align-items-center justify-content-center', isDarkTheme ? 'btn-outline-light' : 'btn-outline-dark']" title="Increase Font Size">
               <span style="font-size: 1.2rem; line-height: 0.5;">+</span>
            </button>
          </div>
        </div>
      </div>
      
      <!-- Run Button -->
      <button @click="runCode" class="btn btn-success btn-sm px-4 py-1 fw-bold d-flex align-items-center gap-2 shadow run-btn rounded-pill border-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-play-fill" viewBox="0 0 16 16">
          <path d="m11.596 8.697-6.363 3.692c-.54.313-1.233-.066-1.233-.697V4.308c0-.63.692-1.01 1.233-.696l6.363 3.692a.802.802 0 0 1 0 1.393z"/>
        </svg>
        <span class="d-none d-sm-inline">Run Code</span>
        <span class="d-inline d-sm-none">Run</span>
      </button>
    </div>

    <!-- Editor -->
    <div class="editor-wrapper">
      <VueMonacoEditor
        v-model:value="code"
        :language="language"
        :theme="theme"
        :options="editorOptions"
        @before-mount="handleBeforeMount"
      />
    </div>

    <div
      v-if="isSubmitting || statusMessage || errorMessage || lastSubmission"
      :class="[
        'submission-panel px-3 py-2 small border-top',
        resultPanelClass
      ]"
    >
      <div v-if="isSubmitting">Submitting code to the JDoodle API...</div>
      <div v-else-if="errorMessage">{{ errorMessage }}</div>
      <div v-else-if="lastSubmission" class="result-stack">
        <div class="result-summary">
          {{ statusMessage }}
          Submission #{{ lastSubmission.id }} finished with status "{{ formatExecutionStatus(lastSubmission.status) }}"{{ formatRuntimeLabel(lastSubmission) }}.
        </div>
        <div class="result-meta">
          <span>Engine: JDoodle API</span>
          <span>Language: {{ lastSubmission.language }}</span>
          <span>Exit code: {{ lastSubmission.exit_code ?? 'n/a' }}</span>
          <span>Runtime: {{ lastSubmission.runtime ?? 'n/a' }}</span>
          <span>Version: {{ lastSubmission.runtime_version ?? 'n/a' }}</span>
          <span>Signal: {{ lastSubmission.signal ?? 'none' }}</span>
          <span>Received: {{ lastSubmission.received_at }}</span>
        </div>
        <div v-if="lastSubmission.compile_output" class="result-block">
          <div class="result-label">JDoodle Compiler Output</div>
          <pre>{{ lastSubmission.compile_output }}</pre>
        </div>
        <div v-if="lastSubmission.stdout" class="result-block">
          <div class="result-label">Standard Output</div>
          <pre>{{ lastSubmission.stdout }}</pre>
        </div>
        <div v-if="lastSubmission.stderr" class="result-block">
          <div class="result-label">Standard Error</div>
          <pre>{{ lastSubmission.stderr }}</pre>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.editor-container {
  width: calc(100% - 1rem); /* Slight margin for better visual boundaries */
  transition: background-color 0.3s ease;
}

.editor-container.dark-theme {
  background-color: #1e1e1e; /* Monaco dark theme background */
}

.editor-container.light-theme {
  background-color: #ffffff;
}

.toolbar {
  transition: all 0.3s ease;
}

.editor-wrapper {
  width: 100%;
  height: 75vh;
  min-height: 400px;
}

.submission-panel {
  transition: background-color 0.3s ease;
}

.result-stack {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.result-summary {
  font-weight: 600;
}

.result-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  opacity: 0.9;
}

.result-block {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.result-label {
  font-weight: 700;
}

.result-block pre {
  margin: 0;
  padding: 0.75rem;
  border-radius: 0.5rem;
  background: rgba(0, 0, 0, 0.08);
  color: inherit;
  font-family: "Consolas", "Courier New", monospace;
  white-space: pre-wrap;
  word-break: break-word;
}

.submission-panel-success {
  background-color: #e8f7ef;
  color: #0f5132;
}

.submission-panel-error {
  background-color: #f8d7da;
  color: #842029;
}

.btn-outline-light {
  border-color: #6c757d;
}
.btn-outline-light:hover {
  background-color: #6c757d;
  color: white;
  border-color: #6c757d;
}

.run-btn {
  background: linear-gradient(135deg, #198754, #146c43); /* Slightly richer green */
  transition: all 0.2s ease-in-out;
}

.run-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(25, 135, 84, 0.4) !important;
  background: linear-gradient(135deg, #1b965d, #157347); /* Brighter on hover */
}

.run-btn:active {
  transform: translateY(1px);
  box-shadow: 0 2px 4px rgba(25, 135, 84, 0.3) !important;
}

select.form-select-sm:focus {
  box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
}
</style>
