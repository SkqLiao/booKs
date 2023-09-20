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

const fetchFinishedBookNumber = async () => {
  const data = (await getInfo(getRequest, {
    table: 'reading_record',
    fields: ['book_id'],
    conditions: ['finished=1'],
    order_by: 'start_time DESC'
  })) as [{ book_id: number }]
  return data.map((item) => item.book_id)
}

const fetchReadingBookNumber = async () => {
  const data = (await getInfo(getRequest, {
    table: 'reading_record',
    fields: ['DISTINCT(book_id)'],
    conditions: [
      'book_id NOT IN (\
  SELECT book_id\
  FROM reading_record\
  WHERE finished = 1\
);'
    ]
  })) as [{ book_id: number }]
  return data.map((item) => item.book_id)
}

onMounted(async () => {
  book_ids0.value = await fetchReadingBookNumber()
  book_ids1.value = await fetchFinishedBookNumber()
})
</script>

<style scope>
.status-panel {
  width: 50%;
  flex-wrap: wrap;
}
</style>
