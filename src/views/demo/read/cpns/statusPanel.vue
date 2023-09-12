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
      :item-count="totalBookIds.length"
      show-quick-jumper
      :on-update:page="handlePageChange"
    >
      <template #goto> 跳转至 </template>
    </n-pagination>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import bookCard from '@/views/demo/book/cpns/bookCard.vue'

const props = defineProps({
  book_ids: {
    type: Array,
    required: true
  }
})

const pageSize = 6
const currentPage = ref(1)
const totalBookIds = ref<number[]>([])
const bookIds = ref<number[]>([])

function fetchBookNumber(page: number) {
  const startIndex = (page - 1) * pageSize
  const len = Math.min(pageSize, totalBookIds.value.length - startIndex)
  bookIds.value = totalBookIds.value.slice(startIndex, startIndex + len)
}

watch(
  () => props.book_ids,
  (newVal) => {
    totalBookIds.value = newVal as number[]
    handlePageChange(1)
  }
)

const handlePageChange = (page: number) => {
  currentPage.value = page
  fetchBookNumber(page)
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
