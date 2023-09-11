<template>
  <CommonPage :show-footer="true">
    <n-tabs type="line" animated>
      <n-tab-pane name="calendar" tab="阅读日历">
        <status-calendar />
      </n-tab-pane>
      <n-tab-pane name="overview" tab="阅读总览">
        <div style="display: flex">
          <div class="status-panel">
            <status-panel :status="true" />
          </div>
          <div class="status-panel">
            <status-panel :status="false" />
          </div>
        </div>
      </n-tab-pane>
    </n-tabs>
  </CommonPage>
</template>

<script setup lang="ts">
import statusCalendar from './cpns/statusCalendar.vue'
import statusPanel from './cpns/statusPanel.vue'
</script>

<style scope>
.status-panel {
  width: 50%;
  flex-wrap: wrap;
}
</style>
