<template>
  <CommonPage :show-footer="true">
    <div style="display: flex">
      <div class="status-panel">
        <h2>已读完：</h2>
        <status-panel :book_ids="book_ids1" />
      </div>
      <div class="status-panel">
        <h2>阅读中：</h2>
        <status-panel :book_ids="book_ids0" />
      </div>
    </div>
  </CommonPage>
</template>

<script setup lang="ts">
import statusPanel from '@/views/common/panel.vue'
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
