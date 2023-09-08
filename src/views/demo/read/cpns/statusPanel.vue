<template>
  <div style="display: flex">
    <div style="width: 50%">
      <n-grid :x-gap="8" :y-gap="5" :cols="2">
        <n-grid-item v-for="id in bookIds" :key="id">
          <book-card :id="id" />
        </n-grid-item>
      </n-grid>
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
    </div>
    <div style="width: 50%">
      <n-grid :x-gap="8" :y-gap="5" :cols="2">
        <n-grid-item v-for="id in bookIds" :key="id">
          <book-card :id="id" />
        </n-grid-item>
      </n-grid>
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
    </div>
  </div>
</template>

<script setup lang="ts">
import { getRequest } from '@/service/book/book'
import { getInfo } from '@/service/book/book'
import bookCard from '@/views/demo/book/cpns/bookCard.vue'

const totalBooks = ref(0)
const pageSize = 6
const currentPage = ref(1)
const bookIds = ref<number[]>([])

const fetchBookNumber = async (page: number) => {
  const data = (await getInfo(getRequest, {
    table: 'reading_status',
    fields: ['COUNT(*) as count'],
    conditions: ['finished = true']
  })) as { count: number }[]
  totalBooks.value = data[0].count
  const startIndex = (page - 1) * pageSize
  const data2 = (await getInfo(getRequest, {
    table: 'reading_status',
    fields: ['book_id'],
    limit: Math.min(pageSize, totalBooks.value - startIndex),
    offset: startIndex
  })) as [{ book_id: number }]
  bookIds.value = data2.map((item) => item.book_id)
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
  display: flex;
  justify-content: center;
  align-items: center;
}
</style>
