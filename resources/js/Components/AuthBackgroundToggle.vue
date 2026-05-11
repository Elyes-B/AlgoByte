<script setup>
import { computed, onMounted, ref } from 'vue';

const modes = ['light', 'dark', 'blue'];
const mode = ref('dark');
const buttonLabel = computed(() => {
    if (mode.value === 'light') return 'Light Mode';
    if (mode.value === 'blue') return 'Blue Mode';
    return 'Dark Mode';
});

const applyMode = (value) => {
    mode.value = value;
    const root = document.documentElement;

    // We set the colors based on the mode
    if (value === 'light') {
        root.style.setProperty('--site-bg', '#f8fafc');
        root.style.setProperty('--site-text', '#0f172a');
        root.style.setProperty('--grid-opacity', '0.05');
    } else if (value === 'blue') {
        root.style.setProperty('--site-bg', '#001f3f');
        root.style.setProperty('--site-text', '#e0f2fe');
        root.style.setProperty('--grid-opacity', '0.03');
    } else {
        // Default Dark
        root.style.setProperty('--site-bg', '#05080d');
        root.style.setProperty('--site-text', '#ffffff');
        root.style.setProperty('--grid-opacity', '0.035');
    }

    localStorage.setItem('authBackgroundMode', value);
};

const toggleMode = () => {
    const currentIndex = modes.indexOf(mode.value);
    const nextMode = modes[(currentIndex + 1) % modes.length];
    applyMode(nextMode);
};

onMounted(() => {
    const savedMode = localStorage.getItem('authBackgroundMode');
    applyMode(savedMode || 'dark');
});
</script>

<template>
    <button class="auth-bg-toggle" type="button" @click="toggleMode">
        {{ buttonLabel }}
    </button>
</template>

<style scoped>
.auth-bg-toggle {
    /* Remove position: fixed, top, and right */
    min-height: 32px;
    padding: 0 10px;
    border: 1px solid var(--ab-border);
    border-radius: 6px;
    background: rgba(56, 217, 255, 0.1);
    color: var(--ab-cyan);
    cursor: pointer;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    transition: all 0.2s ease;
}

.auth-bg-toggle:hover {
    transform: translateY(-2px);
    background: rgba(255, 255, 255, 0.92);
    box-shadow: 0 16px 34px rgba(6, 55, 65, 0.18);
}

@media (max-width: 560px) {
    .auth-bg-toggle {
        top: 18px;
        right: 18px;
    }
}
</style>
