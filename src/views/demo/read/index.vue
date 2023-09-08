<template>
  <CommonPage :show-footer="true">
    <n-tabs type="line" animated>
      <n-tab-pane name="calendar" tab="阅读日历">
        <status-calendar />
      </n-tab-pane>
      <n-tab-pane name="overview" tab="阅读总览">
        <status-panel />
      </n-tab-pane>
    </n-tabs>
  </CommonPage>
</template>

<script setup lang="ts">
import statusCalendar from './cpns/statusCalendar.vue'
import statusPanel from './cpns/statusPanel.vue'
</script>

<style scope></style>
