<script setup lang="ts">
import { bookNumberRequest } from '@/service/book/book'
import bookCard from './cpns/bookCard.vue'
import bookFilter from './cpns/bookFilter.vue'
import { ref, onMounted } from 'vue'
import eventBus from '@/eventbus/index'

const params = ref({})

eventBus.on('updateFilter', async (nparams) => {
  params.value = nparams as Object
  await fetchBooks(currentPage.value)
})

const showFilterModal = ref(false)
const pageSize = 12
const totalBooks = ref(0)
const currentPage = ref(1)
const dataLoaded = ref(false)
const bookIds = ref<number[]>([])
const fetchBooks = async (page: number) => {
  dataLoaded.value = false
  try {
    const response = await bookNumberRequest(params.value)
    console.log(response)
    totalBooks.value = response.data
    const startIndex = (page - 1) * pageSize + 1
    bookIds.value = Array.from(
      { length: Math.min(pageSize, totalBooks.value - startIndex + 1) },
      (_, index) => startIndex + index
    )
    dataLoaded.value = true
  } catch (error) {
    console.error('获取图书信息失败:', error)
  }
}

const handlePageChange = async (page: number) => {
  currentPage.value = page
  await fetchBooks(page)
}

onMounted(async () => {
  await fetchBooks(currentPage.value)
})
</script>

<style scoped>
.container {
  display: flex;
  justify-content: center;
  align-items: center;
}
</style>

<template>
  <CommonPage :show-footer="true">
    <n-grid :x-gap="12" :y-gap="8" :cols="4">
      <n-grid-item v-for="id in bookIds" :key="id">
        <book-card :id="id" />
      </n-grid-item>
    </n-grid>
    <n-button type="info" @click="showFilterModal = true">筛选</n-button>
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
    <book-filter
      :showFilterModal="showFilterModal"
      @update-filter-visible="showFilterModal = false"
    />
  </CommonPage>
</template>
