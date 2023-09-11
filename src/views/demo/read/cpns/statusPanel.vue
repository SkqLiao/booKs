<template>
  <div>
    <n-grid :x-gap="8" :y-gap="5" :cols="2">
      <n-grid-item v-for="id in bookIds" :key="id">
        <book-card :id="id" cardType="status" />
      </n-grid-item>
    </n-grid>
  </div>
  <div class="container">
    <n-pagination
      v-model:page="currentPage"
      :page-size="pageSize"
      :item-count="totalBooks"
      show-quick-jumper
      :on-update:page="handlePageChange"
    >
      <template #goto> 跳转至 </template>
    </n-pagination>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { getRequest } from '@/service/book/book'
import { getInfo } from '@/service/book/book'
import bookCard from '@/views/demo/book/cpns/bookCard.vue'

const props = defineProps({
  status: {
    type: Boolean,
    required: true
  }
})

const totalBooks = ref(0)
const pageSize = 6
const currentPage = ref(1)
const bookIds = ref<number[]>([])

const fetchBookNumber = async (page: number) => {
  const data = (await getInfo(getRequest, {
    table: 'reading_status',
    fields: ['COUNT(*) as count'],
    conditions: ['finished = ' + props.status]
  })) as { count: number }[]
  totalBooks.value = data[0].count
  const startIndex = (page - 1) * pageSize
  const data2 = (await getInfo(getRequest, {
    table: 'reading_status',
    fields: ['book_id'],
    limit: Math.min(pageSize, totalBooks.value - startIndex),
    offset: startIndex,
    conditions: ['finished = ' + props.status]
  })) as [{ book_id: number }]
  bookIds.value = data2.map((item) => item.book_id)
  console.log(bookIds.value)
}

onMounted(() => {
  fetchBookNumber(1)
})

const handlePageChange = async (page: number) => {
  currentPage.value = page
  await fetchBookNumber(page)
}
</script>
<style scoped>
.container {
  padding-top: 10px;
  display: flex;
  justify-content: center;
  align-items: center;
}
</style>
