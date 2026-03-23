<template>
  <div :dir="isRTL ? 'rtl' : 'ltr'">
    <router-view></router-view>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const { locale } = useI18n()
const isRTL = computed(() => locale.value === 'ar')
</script>

<style>
/* Global Transition */
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

[dir='rtl'] {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  text-align: right;
}
</style>
