<template>
  <div class="editor-wrapper">
    <div class="controls d-flex align-items-center gap-2 p-2 border-bottom">
      
      <div class="d-flex align-items-center gap-1">
        <label class="small m-0">Language:</label>
        <select v-model="selectedLanguage" class="form-select form-select-sm custom-select">
          <option value="javascript">JavaScript</option>
          <option value="python">Python</option>
          <option value="java">Java</option>
          <option value="c">C</option>
        </select>
      </div>

      <div class="d-flex align-items-center gap-1">
        <label class="small m-0">Theme:</label>
        <select v-model="selectedTheme" class="form-select form-select-sm custom-select">
          <option value="dracula">Dracula</option>
          <option value="material">Material</option>
          <option value="eclipse">Eclipse</option>
          <option value="monokai">Monokai</option>
        </select>
      </div>

      <div class="ms-auto d-flex align-items-center gap-1">
        <button type="button" class="btn btn-sm btn-light border d-flex align-items-center gap-1">
          <img src="../assets/play.svg" width="14" alt="Run"> Run
        </button>
        <button type="button" class="btn btn-sm btn-light border" @click="increaseFont">+</button>
        <button type="button" class="btn btn-sm btn-light border" @click="decreaseFont">-</button>
        <span class="small text-secondary ms-1">{{ fontSize }}px</span>
      </div>
    </div>

    <div class="editor-container" :style="{ '--editor-font-size': fontSize + 'px' }">
      <textarea ref="editorTextarea"></textarea>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, watch } from "vue";
import CodeMirror from "codemirror";

// CSS IMPORTS (Crucial for the editor to actually appear)
import "codemirror/lib/codemirror.css";
import "codemirror/theme/dracula.css";
import "codemirror/theme/material.css";
import "codemirror/theme/eclipse.css";
import "codemirror/theme/monokai.css";

// Modes
import "codemirror/mode/javascript/javascript.js";
import "codemirror/mode/python/python.js";
import "codemirror/mode/clike/clike.js";

// Addons
import "codemirror/addon/edit/matchbrackets.js";
import "codemirror/addon/edit/closebrackets.js";

export default {
  setup() {
    const editorTextarea = ref(null);
    let cmInstance = null;

    const selectedLanguage = ref("javascript");
    const selectedTheme = ref(localStorage.getItem("theme") || "dracula");
    const fontSize = ref(parseInt(localStorage.getItem("fontSize")) || 14);

    const languageMap = {
      javascript: "javascript",
      python: "python",
      java: "text/x-java",
      c: "text/x-csrc",
    };

    const createEditor = () => {
      if (!editorTextarea.value) return;
      
      cmInstance = CodeMirror.fromTextArea(editorTextarea.value, {
        lineNumbers: true,
        mode: languageMap[selectedLanguage.value],
        theme: selectedTheme.value,
        matchBrackets: true,
        autoCloseBrackets: true,
        indentUnit: 4,
      });
    };

    const increaseFont = () => {
      if (fontSize.value < 30) {
        fontSize.value += 2;
        localStorage.setItem("fontSize", fontSize.value);
        if (cmInstance) cmInstance.refresh(); // Tells CM to recalculate lines
      }
    };

    const decreaseFont = () => {
      if (fontSize.value > 10) {
        fontSize.value -= 2;
        localStorage.setItem("fontSize", fontSize.value);
        if (cmInstance) cmInstance.refresh();
      }
    };

    watch(selectedLanguage, (lang) => {
      if (cmInstance) cmInstance.setOption("mode", languageMap[lang]);
    });

    watch(selectedTheme, (theme) => {
      if (cmInstance) cmInstance.setOption("theme", theme);
      localStorage.setItem("theme", theme);
    });

    onMounted(() => {
      createEditor();
    });

    return {
      editorTextarea,
      selectedLanguage,
      selectedTheme,
      fontSize,
      increaseFont,
      decreaseFont,
    };
  },
};
</script>

<style>
.editor-container .CodeMirror {
  height: calc(100vh - 150px);
  text-align: left !important; 
  font-family: 'Fira Code', monospace;
  font-size: var(--editor-font-size);
  line-height: 1.5;
}

.custom-select {
  width: auto !important;
  font-size: 0.8rem !important;
  padding: 0.1rem 1.5rem 0.1rem 0.5rem !important;
  cursor: pointer;
}

.controls {
  background-color: #bcbcbc !important;
  color: #333 !important;
}

.controls label {
  font-weight: 600;
  font-size: 0.7rem !important;
}

.editor-wrapper {
  border: 1px solid #ccc;
  border-radius: 4px;
}
</style>
