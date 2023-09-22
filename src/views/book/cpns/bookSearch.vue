<template>
  <n-select
    v-model:value="selectedValue"
    filterable
    placeholder="搜索书籍"
    :options="options"
    :loading="loading"
    clearable
    remote
    :render-label="renderLabel"
    :show-arrow="false"
    @search="handleSearch"
    :on-update:value="handleUpdate"
    :style="props.style"
  />
  <book-detail
    :bookInfo="bookInfo"
    :showDetailModal="showDetailModal"
    :decodedCover="decodedCover"
    :id="selectedBookId"
    @update-detail-visible="updateDetailVisible"
    v-if="selectedBookId && bookInfo"
  />
</template>

<script setup lang="ts">
import { ref, h } from 'vue'
import { SelectOption, NText, SelectRenderLabel } from 'naive-ui'
import { getInfo, getRequest, bookCoverRequest } from '@/service/book/book'
import bookDetail from './bookDetail.vue'
import { Ibook } from '@/service/book/types'

const selectedBookId = ref<number>()
const bookInfo = ref<Ibook>()
const decodedCover = ref('')
const showDetailModal = ref(false)

const props = defineProps({
  style: {
    type: Object,
    default: () => ({ width: '100%' })
  }
})

const handleUpdate = async (book_id: number) => {
  selectedBookId.value = book_id
  const data = (await getInfo(getRequest, {
    table: 'basic_info',
    fields: ['*'],
    conditions: ['id=' + book_id]
  })) as Ibook[]
  bookInfo.value = data[0]
  const response2 = await bookCoverRequest({
    isbn: bookInfo.value?.isbn
  })
  if (response2.data?.length > 0) {
    decodedCover.value = base64ToUrl(response2.data)
    bookInfo.value.cover_base64 = response2.data
  }
  showDetailModal.value = true
}

function updateDetailVisible(id: number) {
  if (selectedBookId.value === id) {
    showDetailModal.value = false
  }
}

const loading = ref(false)
const options = ref<SelectOption[]>([])
const selectedValue = ref(null)

const base64ToUrl = (base64Data: string): string => {
  const byteCharacters = atob(base64Data)
  const byteNumbers = new Uint8Array(byteCharacters.length)

  for (let i = 0; i < byteCharacters.length; i++) {
    byteNumbers[i] = byteCharacters.charCodeAt(i)
  }

  const blob = new Blob([byteNumbers], { type: 'image/jpeg' })
  return URL.createObjectURL(blob)
}

const handleSearch = async (query: string) => {
  if (!query.length) {
    options.value = []
    return
  }
  loading.value = true
  window.setTimeout(async () => {
    options.value = (await getInfo(getRequest, {
      table: 'basic_info',
      fields: ['id as value', 'title', 'publish', 'isbn'],
      conditions: ['title like ' + `'%${query}%'`],
      order_by: 'id DESC'
    })) as { book_id: number; title: string; publish: string; isbn: string }[]
    loading.value = false
  }, 500)
}

const renderLabel: SelectRenderLabel = (option) => {
  return h(
    'div',
    {
      style: {
        display: 'flex',
        alignItems: 'center'
      }
    },
    [
      h(
        'div',
        {
          style: {
            marginLeft: '10px',
            padding: '4px 0'
          }
        },
        [
          h('div', null, [option.title as string]),
          h(
            NText,
            { depth: 3, tag: 'div' },
            {
              default: () => option.publish as string
            }
          )
        ]
      )
    ]
  )
}
</script>

<style scoped></style>
