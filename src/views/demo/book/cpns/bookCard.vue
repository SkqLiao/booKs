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
              <n-button
                text
                @click="showDetailModal = true"
                style="white-space: normal"
              >
                <h3>{{ bookInfo.title }}</h3>
              </n-button>
              <div class="info">
                <p>
                  作者：
                  <span class="field-container">
                    <n-button
                      v-for="author in bookInfo.author"
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
                  出版社：
                  <span class="field-container">
                    <n-button
                      text
                      quaterary
                      round
                      type="info"
                      @click="selectFilter('publish', bookInfo.publish)"
                    >
                      {{ bookInfo.publish }}
                    </n-button>
                  </span>
                </p>
                <p v-if="bookInfo.producer">
                  出版方：
                  <span class="field-container">
                    <n-button
                      text
                      quaterary
                      round
                      type="primary"
                      @click="selectFilter('producer', bookInfo.producer)"
                    >
                      {{ bookInfo.producer }}
                    </n-button>
                  </span>
                </p>
                <p v-if="bookInfo.series">
                  丛书：
                  <span class="field-container">
                    <n-button
                      text
                      quaterary
                      round
                      type="primary"
                      @click="selectFilter('series', bookInfo.series)"
                      style="white-space: normal"
                    >
                      {{ bookInfo.series }}
                    </n-button>
                  </span>
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
import { getRequest, getInfo, bookCoverRequest } from '@/service/book/book'
import { ref } from 'vue'
import { Ibook } from '@/service/book/types'
import bookDetail from './bookDetail.vue'
import eventBus from '@/eventbus/index'
import { useBookStore } from '@/store'
import defaultCoverImage from '@/assets/images/default_cover.jpg'
const bookStore = useBookStore()

const props = defineProps({
  id: {
    type: Number,
    required: true
  },
  cardType: {
    type: String,
    default: 'default'
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
  if (props.id === id) {
    showDetailModal.value = false
  }
}

eventBus.on('deleteBook', async (id: number) => {
  updateDetailVisible(id)
  if (props.id >= id) {
    await fetchBookInfo()
  }
})

const bookInfo = ref<Ibook>()
const decodedCover = ref<string>(defaultCoverImage)

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

const fetchBookInfo = async () => {
  const data = (await getInfo(getRequest, {
    table: 'basic_info',
    fields: ['*'],
    conditions: ['id=' + props.id]
  })) as Ibook[]
  bookInfo.value = data[0]
  const response2 = await bookCoverRequest({
    isbn: bookInfo.value?.isbn
  })
  if (response2.data?.length > 0) {
    decodedCover.value = base64ToUrl(response2.data)
    bookInfo.value.cover_base64 = response2.data
  }
}

eventBus.on('updateFilter', async () => {
  await fetchBookInfo()
})

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
  height: 160px;
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
  height: 150px;
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
.field-container {
  max-height: 1.5em; /* Adjust this value as needed */
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
}
</style>
