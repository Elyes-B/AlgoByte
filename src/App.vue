<script setup>
import HeaderBar from './components/HeaderBar.vue'
import FooterBar from './components/FooterBar.vue'
import editorComponent from './components/editorComponent.vue';
</script>

<template>
  <HeaderBar />
  <router-view />
  <editorComponent></editorComponent>
  <FooterBar />
</template>