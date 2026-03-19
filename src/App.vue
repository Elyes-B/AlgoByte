<script setup>
import HeaderBar from './components/HeaderBar.vue'
import FooterBar from './components/FooterBar.vue';
</script>

<template>
  <HeaderBar />
  <router-view />
  <FooterBar />
</template>