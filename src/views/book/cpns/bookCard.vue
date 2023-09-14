<template>
  <div v-if="bookInfo">
    <n-card class="card" :hoverable="true" v-if="props.cardType == 'default'">
      <template #header>
        <h4 @click="showDetailModal = true" style="cursor: pointer">
          {{ bookInfo.title }}
        </h4>
      </template>
      <div style="height: 130px">
        <n-row>
          <n-col :span="6">
            <div class="cover-container">
              <img :src="decodedCover" alt="封面图像" class="cover-image" />
            </div>
          </n-col>
          <n-col :span="18">
            <div class="content">
              <p>
                <strong>作者</strong>：
                <span
                  v-for="(author, index) in bookInfo.author"
                  :key="author"
                  @click="selectFilter('author', author)"
                  style="cursor: pointer"
                >
                  {{
                    author + (index < bookInfo.author.length - 1 ? ' / ' : '')
                  }}
                </span>
              </p>
              <p>
                <strong>出版社</strong>：
                <n-button
                  text
                  quaterary
                  round
                  type="info"
                  @click="selectFilter('publish', bookInfo.publish)"
                >
                  {{ bookInfo.publish }}
                </n-button>
              </p>
              <p v-if="bookInfo.producer">
                <strong>出版方</strong>：
                <n-button
                  text
                  quaterary
                  round
                  type="primary"
                  @click="selectFilter('producer', bookInfo.producer)"
                >
                  {{ bookInfo.producer }}
                </n-button>
              </p>
              <p v-if="bookInfo.series">
                <strong>丛书</strong>：
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
              </p>
            </div>
          </n-col>
        </n-row>
      </div>
      <template #action>
        <span class="time"> 购买于{{ bookInfo.buy_date }}</span>
      </template>
    </n-card>
    <n-card
      class="card"
      :hoverable="true"
      v-else-if="props.cardType == 'status'"
    >
      <div style="height: 130px">
        <n-row>
          <n-col :span="6">
            <div class="cover-container">
              <img :src="decodedCover" alt="封面图像" class="cover-image" />
            </div>
          </n-col>
          <n-col :span="18">
            <div class="content">
              <h3 @click="showDetailModal = true" style="cursor: pointer">
                {{ bookInfo.title }}
              </h3>
              <div>
                <p>
                  <strong>作者</strong>：
                  <span
                    v-for="(author, index) in bookInfo.author"
                    :key="author"
                  >
                    {{
                      author + (index < bookInfo.author.length - 1 ? ' / ' : '')
                    }}
                  </span>
                </p>
                <p>
                  <strong>出版社</strong>：
                  {{ bookInfo.publish }}
                </p>
                <p
                  style="
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                  "
                >
                  <span style="font-size: 12px"
                    >{{ max_page }}/{{ bookInfo.pages }}</span
                  >
                  <span style="font-size: 12px">{{ latest_date }}</span>
                </p>
                <n-progress
                  type="line"
                  style="margin-top: 10px"
                  indicator-placement="inside"
                  :percentage="
                    Math.round((max_page / parseInt(bookInfo.pages)) * 100)
                  "
                />
              </div>
            </div>
          </n-col>
        </n-row>
      </div>
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
      <div
        :style="props.cardType == 'default' ? 'height: 160px' : 'height: 120px'"
      />
      <template #action>
        <span class="time">购买于</span>
      </template>
    </n-card>
  </div>
</template>

<script setup lang="ts">
import { getRequest, getInfo, bookCoverRequest } from '@/service/book/book'
import { ref, onMounted } from 'vue'
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

const max_page = ref(0)
const latest_date = ref('')

const getReadStatus = async () => {
  const data = (await getInfo(getRequest, {
    table: 'reading_record',
    fields: ['MAX(end_page) AS max_page', 'MAX(date) AS latest_date'],
    conditions: ['book_id=' + props.id]
  })) as { max_page: number; latest_date: string }[]
  max_page.value = data[0].max_page
  latest_date.value = data[0].latest_date
}

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
  await getReadStatus()
})
</script>

<style scoped>
/* 样式定义 */
.card {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.n-card >>> .n-card__content {
  --n-padding-left: 10px;
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
  padding-left: 15px;
}
.cover-container {
  max-width: 100%;
  height: 150px;
  overflow: hidden;
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