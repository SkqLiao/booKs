<script setup lang="ts">
function handleLinkClick() {
  window.open('https://github.com/SkqLiao/booKs')
}
</script>

<template>
  <n-icon mr-20 size="18" style="cursor: pointer" @click="handleLinkClick">
    <icon-mdi:github />
  </n-icon>
</template>
