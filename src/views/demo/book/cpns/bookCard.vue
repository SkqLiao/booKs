<template>
  <div v-if="bookInfo">
    <n-card class="card" :hoverable="true">
      <div class="card-content">
        <n-row>
          <n-col :span="6">
            <div class="cover-container">
              <img :src="decodedCover" alt="封面图像" class="cover-image" />
            </div>
          </n-col>
          <n-col :span="18">
            <div class="content">
              <n-button text @click="showDetailModal = true"
                ><h3>{{ bookInfo.title }}</h3></n-button
              >
              <div class="info">
                <p>
                  作者：<span>
                    <n-button
                      v-for="author in formattedAuthors"
                      :key="author"
                      text
                      quaterary
                      round
                      @click="selectFilter('author', author)"
                    >
                      {{ author }}
                    </n-button>
                  </span>
                </p>
                <p>
                  出版社：<n-button
                    text
                    quaterary
                    round
                    type="info"
                    @click="selectFilter('publish', bookInfo.publish)"
                    >{{ bookInfo.publish }}</n-button
                  >
                </p>
                <p v-if="bookInfo.producer">
                  出版方：<n-button
                    text
                    quaterary
                    round
                    type="primary"
                    @click="selectFilter('producer', bookInfo.producer)"
                    >{{ bookInfo.producer }}</n-button
                  >
                </p>
                <p v-if="bookInfo.series">
                  丛书：<n-button
                    text
                    quaterary
                    round
                    type="primary"
                    @click="selectFilter('series', bookInfo.series)"
                    >{{ formattedSeries }}</n-button
                  >
                </p>
              </div>
            </div>
          </n-col>
        </n-row>
      </div>
      <template #action>
        <span class="time"> 购买于{{ bookInfo.buy_date }}</span>
      </template>
    </n-card>
    <book-detail
      :bookInfo="bookInfo"
      :showDetailModal="showDetailModal"
      :decodedCover="decodedCover"
      :id="id"
      @update-detail-visible="updateDetailVisible"
    />
  </div>
  <div v-else>
    <n-card class="card" :hoverable="true">
      <div class="card-content" />
      <template #action>
        <span class="time">购买于</span>
      </template>
    </n-card>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { Ibook } from '@/service/book/types'
import { bookInfoRequest } from '@/service/book/book'
import bookDetail from './bookDetail.vue'
import eventBus from '@/eventbus/index'
import { useBookStore } from '@/store'

const bookStore = useBookStore()

const props = defineProps({
  id: {
    type: Number,
    required: true
  }
})

const selectFilter = (key: string, value: string) => {
  bookStore.setParams({
    [key]: [value]
  })
  eventBus.emit('updateFilter')
}

const showDetailModal = ref(false)

function updateDetailVisible(id: number) {
  console.log('update-detail-visible', id)
  if (props.id === id) {
    showDetailModal.value = false
  }
}

eventBus.on('updateFilter', async () => {
  await fetchBookInfo()
})

const bookInfo = ref<Ibook | null>(null)
const decodedCover = ref<string | undefined>(undefined)

const formattedAuthors = computed(() => {
  const MAX_LENGTH = 12
  const authors = bookInfo.value?.author || []

  let displayAuthors = []
  let currentLength = 0

  for (const author of authors) {
    if (currentLength + author.length <= MAX_LENGTH) {
      displayAuthors.push(author)
      currentLength += author.length + 1
    } else {
      break
    }
  }

  return displayAuthors
})

const formattedSeries = computed(() => {
  const MAX_LENGTH = 10
  const series = bookInfo.value?.series || ''
  let displaySeries = series
  if (displaySeries.length > MAX_LENGTH) {
    displaySeries = displaySeries.slice(0, MAX_LENGTH) + '...'
  }
  return displaySeries
})

// 获取图书信息函数
const fetchBookInfo = async () => {
  try {
    const response = await bookInfoRequest({
      n: props.id,
      ...bookStore.getParams
    })
    if (response.code != 200) return
    if (response['data'].length === 0) {
      bookInfo.value = null
      return
    }
    bookInfo.value = response['data'][0]
    if (bookInfo.value.cover_base64) {
      decodedCover.value = base64ToUrl(bookInfo.value.cover_base64)
    }
  } catch (error) {
    console.error('获取图书信息失败:', error)
  }
}

// base64 转 URL 函数
const base64ToUrl = (base64Data: string): string => {
  const byteCharacters = atob(base64Data)
  const byteNumbers = new Uint8Array(byteCharacters.length)

  for (let i = 0; i < byteCharacters.length; i++) {
    byteNumbers[i] = byteCharacters.charCodeAt(i)
  }

  const blob = new Blob([byteNumbers], { type: 'image/jpeg' })
  return URL.createObjectURL(blob)
}

onMounted(async () => {
  await fetchBookInfo()
})
</script>

<style scoped>
/* 样式定义 */
.card {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}
.card-content {
  height: 130px;
}
.cover-container {
  width: 100%;
  text-align: center;
}
.cover-image {
  width: 100%;
  height: auto;
}
.content {
  padding-left: 20px;
}
.cover-container {
  max-width: 100%;
  max-height: 400px;
  overflow: hidden;
  display: flex;
  justify-content: center;
  align-items: center;
}
.cover-image {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}
.time {
  font-size: 12px;
  color: #999;
}
</style>
