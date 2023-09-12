<template>
  <CommonPage :show-footer="true">
    <n-tabs type="line" animated>
      <n-tab-pane name="calendar" tab="阅读日历">
        <status-calendar />
      </n-tab-pane>
      <n-tab-pane name="overview" tab="阅读总览">
        <div style="display: flex">
          <div class="status-panel">
            <status-panel :book_ids="book_ids1" />
          </div>
          <div class="status-panel">
            <status-panel :book_ids="book_ids0" />
          </div>
        </div>
      </n-tab-pane>
      <n-tab-pane name="log" tab="数据总览">
        <status-overview />
      </n-tab-pane>
    </n-tabs>
  </CommonPage>
</template>

<script setup lang="ts">
import statusCalendar from './cpns/statusCalendar.vue'
import statusPanel from './cpns/statusPanel.vue'
import statusOverview from './cpns/statusOverview.vue'
import { onMounted, ref, Ref } from 'vue'
import { getRequest, getInfo } from '@/service/book/book'

const book_ids1: Ref<number[]> = ref([])
const book_ids0: Ref<number[]> = ref([])

const fetchBookNumber = async (finished: boolean) => {
  const data = (await getInfo(getRequest, {
    table: 'reading_status',
    fields: ['book_id'],
    conditions: ['finished=' + finished]
  })) as [{ book_id: number }]
  return data.map((item) => item.book_id)
}

onMounted(async () => {
  book_ids0.value = await fetchBookNumber(false)
  book_ids1.value = await fetchBookNumber(true)
})
</script>

<style scope>
.status-panel {
  width: 50%;
  flex-wrap: wrap;
}
</style>
