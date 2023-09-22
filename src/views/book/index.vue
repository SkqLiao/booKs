<script setup lang="ts">
import { getRequest, getInfo } from '@/service/book/book'
import bookCard from './cpns/bookCard.vue'
import bookFilter from './cpns/bookFilter.vue'
import { ref, Ref, onMounted } from 'vue'
import eventBus from '@/eventbus/index'
import { useBookStore } from '@/store'
import bookSearch from './cpns/bookSearch.vue'

const bookStore = useBookStore()
const filters: Ref<string[]> = ref([])

eventBus.on('deleteBook', async () => {
  await fetchBookNumber(currentPage.value)
})

eventBus.on('updateFilter', async () => {
  currentPage.value = 1
  await fetchBookNumber(currentPage.value)
  filters.value = getFilterInfo()
})

const showFilterModal = ref(false)
const pageSize = 12
const totalBooks = ref(0)
const currentPage = ref(1)
const bookIds = ref<number[]>([])

const fetchBookNumber = async (page: number) => {
  const data = (await getInfo(getRequest, {
    table: 'basic_info',
    fields: ['COUNT(*) as count'],
    conditions: bookStore.buildQueryConditions()
  })) as { count: number }[]
  totalBooks.value = data[0].count
  const startIndex = (page - 1) * pageSize
  const data2 = (await getInfo(getRequest, {
    table: 'basic_info',
    fields: ['id'],
    conditions: bookStore.buildQueryConditions(),
    limit: Math.min(pageSize, totalBooks.value - startIndex),
    offset: startIndex,
    order_by: 'buy_date DESC, id DESC'
  })) as [{ id: number }]
  bookIds.value = data2.map((item) => item.id)
}

const getFilterInfo = () => {
  const params = bookStore.getParams
  const info: string[] = []
  const translate: Record<string, string> = {
    series: '丛书',
    publish: '出版社',
    producer: '出品方',
    buy_pos: '来源',
    author: '作者',
    title: '标题'
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

  if ('author' in params) {
    info.push(`作者: ${params.author}`)
  }

  return info
}

const handlePageChange = async (page: number) => {
  currentPage.value = page
  await fetchBookNumber(page)
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
    作者: ['author'],
    标题: ['title']
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
.left-wrapper {
  display: inline-block;
}
.right-wrapper {
  width: 25%;
  display: inline-block;
}
</style>

<template>
  <CommonPage :show-footer="true">
    <div style="display: flex; justify-content: space-between">
      <div class="left-wrapper">
        <n-button type="info" @click="showFilterModal = true">筛选</n-button>
        <span v-if="filters.length > 0">
          <n-tag
            v-for="(key, index) in filters"
            :key="index"
            size="large"
            type="info"
            closable
            @close="removeFilter(key)"
            >{{ key }}</n-tag
          >
        </span>
      </div>
      <div class="right-wrapper">
        <book-search></book-search>
      </div>
    </div>

    <n-grid :x-gap="8" :y-gap="5" :cols="4">
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
    <book-filter
      :showFilterModal="showFilterModal"
      @update-filter-visible="showFilterModal = false"
    />
  </CommonPage>
</template>
