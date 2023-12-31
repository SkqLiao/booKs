<template>
  <div>
    <n-modal
      v-model:show="showModal"
      class="custom-card"
      preset="card"
      style="width: 75%"
      size="huge"
      :bordered="false"
      :on-after-leave="() => $emit('updateDetailVisible', props.id)"
    >
      <template #header>
        <div class="title-container">
          <h2>{{ props.bookInfo?.title }}</h2>
          <h3 v-if="props.bookInfo.subtitle">
            ：{{ props.bookInfo.subtitle }}
          </h3>
          <a :href="props.bookInfo?.url" target="_blank">
            <n-button text circle>
              <template #icon>
                <n-icon><link-icon /></n-icon>
              </template>
            </n-button>
          </a>
        </div>
      </template>
      <n-grid x-gap="12" cols="8" item-responsive responsive="screen">
        <n-gi span="0 m:2 l:2">
          <div class="cover-container">
            <img :src="decodedCover" alt="封面图像" class="cover-image" />
          </div>
        </n-gi>
        <n-gi span="0 m:4 l:4">
          <p class="book-info-p" v-if="props.bookInfo.original_title">
            <strong>原作名：</strong>{{ props.bookInfo.original_title }}
          </p>
          <p class="book-info-p">
            <strong>作者：</strong>{{ props.bookInfo.author.join(', ') }}
          </p>
          <p
            class="book-info-p"
            v-if="
              props.bookInfo.translator.length > 0 &&
              !props.bookInfo.translator.every((str) => str.trim() === '')
            "
          >
            <strong>译者：</strong>{{ props.bookInfo.translator.join(', ') }}
          </p>
          <p class="book-info-p">
            <strong>出版社：</strong>{{ props.bookInfo.publish }}
          </p>
          <p class="book-info-p">
            <strong>出版年：</strong>{{ props.bookInfo.publishDate }}
          </p>
          <p class="book-info-p">
            <strong>定价：</strong>{{ props.bookInfo.price }}
          </p>
          <p class="book-info-p">
            <strong>页数：</strong>{{ props.bookInfo.pages }}
          </p>
          <p class="book-info-p">
            <strong>装帧：</strong>{{ props.bookInfo.binding }}
          </p>
          <p class="book-info-p" v-if="props.bookInfo.series.length > 0">
            <strong>丛书：</strong>{{ props.bookInfo.series }}
          </p>
          <p class="book-info-p">
            <strong>ISBN：</strong>{{ props.bookInfo.isbn }}
          </p>
          <n-button type="info" @click="showEditModal = true" secondary
            >编辑</n-button
          >
        </n-gi>
        <n-gi span="0 m:2 l:2">
          <n-card
            title="书籍来源"
            size="small"
            hoverable
            v-if="props.bookInfo.buy_pos === '购买'"
          >
            <n-tag type="info" round> {{ props.bookInfo.buy_pos }}</n-tag>
            <n-tag type="success" round> {{ props.bookInfo.buy_date }}</n-tag>
            <n-tag type="warning" round>
              {{ Math.round(props.bookInfo.real_price) }} 元</n-tag
            >
          </n-card>
          <n-card title="书籍来源" size="small" hoverable v-else>
            <n-tag type="info" round> {{ props.bookInfo.buy_pos }}</n-tag>
          </n-card>
          <n-card title="阅读状态" size="small" hoverable>
            <n-progress
              type="line"
              :percentage="pagePercent"
              :height="15"
              processing
              :indicator-placement="'inside'"
            />
          </n-card>
          <n-card
            :title="'评分 ' + props.bookInfo.rating_value"
            size="small"
            hoverable
          >
            <div
              v-for="(percent, index) in props.bookInfo.rating_percent"
              class="star-progress-container"
              :key="index"
            >
              <div class="star-rating">
                <span>{{ index + 1 }}星</span>
                <n-progress
                  type="line"
                  indicator-placement="inside"
                  color="#8a2be2"
                  :percentage="percent"
                  :height="15"
                  :border-radius="4"
                  :fill-border-radius="0"
                  style="width: 80%"
                />
              </div>
            </div>
            <p class="right-align-text">
              共 {{ props.bookInfo.rating_count }} 人评分
            </p>
          </n-card>
        </n-gi>
      </n-grid>

      <template #footer>
        <read-status
          :bookid="bookInfo.id"
          @update-page-percent="updateReadPercent"
        />
      </template>
    </n-modal>
    <book-edit
      :bookInfo="bookInfo"
      :showEditModal="showEditModal"
      :decodedCover="decodedCover"
      :id="id"
      @update-edit-visible="updateEditVisible"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { Ibook } from '@/service/book/types'
import { ExternalLinkAlt as LinkIcon } from '@vicons/fa'
import BookEdit from '@/views/edit/editBook.vue'
import ReadStatus from './readStatus.vue'

const props = defineProps({
  bookInfo: {
    type: Object as () => Ibook,
    required: true
  },
  showDetailModal: {
    type: Boolean,
    required: true
  },
  decodedCover: {
    type: String,
    required: false
  },
  id: {
    type: Number,
    required: true
  }
})

defineEmits(['updateDetailVisible'])

const showModal = ref(false)

watch(
  () => props.showDetailModal,
  (newVal, oldVal) => {
    if (newVal !== oldVal) {
      showModal.value = newVal
    }
  }
)

const showEditModal = ref(false)

function updateEditVisible(id: number) {
  if (props.id === id) {
    showEditModal.value = false
  }
}

const pagePercent = ref(0)

function updateReadPercent(page: number) {
  if (parseInt(props.bookInfo.pages) === 0) {
    pagePercent.value = 0
  } else {
    pagePercent.value = Math.round(
      (page / parseInt(props.bookInfo.pages)) * 100
    )
  }
}
</script>

<style scoped>
.cover-container {
  width: 70%;
  text-align: center;
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

.star-rating {
  display: flex;
  align-items: center;
}

.star-rating span {
  margin-right: 8px; /* 调整适当的间距 */
}

.title-container {
  display: flex;
  align-items: center;
}

.title-container h2,
.title-container h3 {
  margin-right: 8px; /* 调整文字与按钮之间的距离 */
  display: inline-block;
}
.book-info-p {
  font-size: 18px;
}

.right-align-text {
  text-align: right; /* 将文本靠右对齐 */
  margin: 0;
  padding: 5px 0;
}
</style>
