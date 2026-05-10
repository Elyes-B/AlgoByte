<script setup>
import { computed, onMounted, ref } from 'vue';

const modes = ['light', 'dark', 'blue'];
const mode = ref('light');

const labels = {
    light: 'Light',
    dark: 'Dark',
    blue: 'Blue',
};

const buttonLabel = computed(() => `BG: ${labels[mode.value]}`);

const applyMode = (value) => {
    mode.value = value;
    document.documentElement.dataset.authBg = value;
    localStorage.setItem('authBackgroundMode', value);
};

const toggleMode = () => {
    const currentIndex = modes.indexOf(mode.value);
    const nextMode = modes[(currentIndex + 1) % modes.length];
    applyMode(nextMode);
};

onMounted(() => {
    const savedMode = localStorage.getItem('authBackgroundMode');
    applyMode(modes.includes(savedMode) ? savedMode : 'light');
});
</script>

<template>
    <button class="auth-bg-toggle" type="button" @click="toggleMode">
        {{ buttonLabel }}
    </button>
</template>

<style scoped>
.auth-bg-toggle {
    position: fixed;
    top: 24px;
    right: 28px;
    z-index: 50;
    min-height: 36px;
    padding: 0 13px;
    border: 1px solid rgba(8, 127, 149, 0.24);
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.78);
    color: #063741;
    cursor: pointer;
    font-size: 12px;
    font-weight: 800;
    box-shadow: 0 12px 28px rgba(6, 55, 65, 0.12);
    backdrop-filter: blur(12px);
    transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
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
