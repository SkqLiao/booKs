<script setup lang="ts">
import { bookNumberRequest } from '@/service/book/book'
import bookCard from './cpns/bookCard.vue'
import bookFilter from './cpns/bookFilter.vue'
import { ref, onMounted } from 'vue'
import eventBus from '@/eventbus/index'
import { useBookStore } from '@/store'

const bookStore = useBookStore()
const filters: Ref<string[]> = ref([])

eventBus.on('updateFilter', async () => {
  currentPage.value = 1
  await fetchBooks(currentPage.value)
  filters.value = getFilterInfo()
})

const showFilterModal = ref(false)
const pageSize = 12
const totalBooks = ref(0)
const currentPage = ref(1)
const bookIds = ref<number[]>([])
const fetchBooks = async (page: number) => {
  try {
    const response = await bookNumberRequest(bookStore.params)
    totalBooks.value = response.data
    const startIndex = (page - 1) * pageSize + 1
    bookIds.value = Array.from(
      { length: Math.min(pageSize, totalBooks.value - startIndex + 1) },
      (_, index) => startIndex + index
    )
  } catch (error) {
    console.error('获取图书信息失败:', error)
  }
}

const getFilterInfo = () => {
  const params = bookStore.getParams
  const info: string[] = []
  const translate: Record<string, string> = {
    series: '丛书',
    publish: '出版社',
    producer: '出品方',
    buy_pos: '来源',
    author: '作者'
  }
  for (const key in params) {
    if (Array.isArray(params[key]) && (params[key] as string[]).length > 0) {
      info.push(`${translate[key]}: ${(params[key] as string[]).join('、')}`)
    }
  }

  if ('buy_date_to' in params && 'buy_date_from' in params) {
    info.push(`购买日期: ${params.buy_date_from}~${params.buy_date_to}`)
  }

  if ('buy_price_to' in params && 'buy_price_from' in params) {
    info.push(`价格: [${params.buy_price_from},${params.buy_price_to}]`)
  }

  return info
}

const handlePageChange = async (page: number) => {
  currentPage.value = page
  await fetchBooks(page)
}

onMounted(async () => {
  eventBus.emit('updateFilter')
})

const removeFilter = (info: string) => {
  const type = info.split(':')[0].trim()
  const bookStore = useBookStore()
  const translate: Record<string, string[]> = {
    丛书: ['series'],
    出版社: ['publish'],
    出品方: ['producer'],
    购买日期: ['buy_date_from', 'buy_date_to'],
    价格: ['buy_price_from', 'buy_price_to'],
    来源: ['buy_pos'],
    作者: ['author']
  }
  for (const key of translate[type]) {
    bookStore.removeParams(key)
  }
  eventBus.emit('updateFilter')
}
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
    <span v-if="filters.length > 0">
      <n-tag
        v-for="(key, index) in filters"
        :key="index"
        type="info"
        closable
        @close="removeFilter(key)"
        >{{ key }}</n-tag
      >
    </span>
    <n-grid :x-gap="8" :y-gap="5" :cols="4">
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
