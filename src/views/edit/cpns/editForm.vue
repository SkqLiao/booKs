<template>
  <n-form ref="formRef" :model="bookInfo" :rules="rules">
    <n-grid x-gap="30" cols="3">
      <n-gi>
        <div class="cover-container">
          <img :src="decodedCover" alt="封面图像" class="cover-image" />
        </div>
        <n-upload
          accept="image/*"
          show-upload-list="false"
          :max="1"
          :custom-request="handleCoverChange"
          :on-remove="removeCover"
        >
          <n-button>选择图片</n-button>
        </n-upload>

        <n-form-item label="ISBN" required>
          <n-input v-model:value="isbn" :disabled="props.disabled" />
        </n-form-item>
      </n-gi>
      <n-gi>
        <n-form-item label="书名">
          <n-input v-model:value="bookInfo.title" />
        </n-form-item>
        <n-form-item label="副标题">
          <n-input v-model:value="bookInfo.subtitle" />
        </n-form-item>
        <n-form-item label="原标题">
          <n-input v-model:value="bookInfo.original_title" />
        </n-form-item>
        <n-form-item label="出版社">
          <n-input v-model:value="bookInfo.publish" />
        </n-form-item>
        <n-form-item label="出品方">
          <n-input v-model:value="bookInfo.producer" />
        </n-form-item>
        <n-form-item label="装帧">
          <n-input v-model:value="bookInfo.binding" />
        </n-form-item>
        <n-form-item label="定价" path="price">
          <n-input v-model:value="bookInfo.price" />
        </n-form-item>
      </n-gi>
      <n-gi>
        <n-form-item label="作者">
          <n-input v-model:value="authors" />
        </n-form-item>
        <n-form-item label="译者">
          <n-input v-model:value="translators" />
        </n-form-item>
        <n-form-item label="系列">
          <n-input v-model:value="bookInfo.series" />
        </n-form-item>
        <n-form-item label="页数" path="pages">
          <n-input v-model:value="bookInfo.pages" />
        </n-form-item>
        <n-card>
          <n-form-item label="购买日期" path="buy_date" required>
            <n-date-picker
              :default-formatted-value="new Date().toISOString().split('T')[0]"
              v-model:formatted-value="bookInfo.buy_date"
              style="width: 100%"
            />
          </n-form-item>
          <n-form-item label="购买价格" path="real_price" required>
            <n-input-number
              v-model:value="bookInfo.real_price"
              style="width: 100%"
            >
              <template #prefix> ￥ </template>
            </n-input-number>
          </n-form-item>
          <n-form-item label="购买平台" path="buy_pos" required>
            <n-select
              v-model:value="bookInfo.buy_pos"
              :options="buy_pos_options"
            />
          </n-form-item>
          <n-form-item label="状态" path="status" required>
            <n-select
              v-model:value="bookInfo.status"
              :default-value="bookInfo.status ?? '购买'"
              :options="status_options"
            />
          </n-form-item>
        </n-card>
      </n-gi>
    </n-grid>
  </n-form>
</template>

<script setup lang="ts">
import { ref, onMounted, PropType, watch } from 'vue'
import { Ibook } from '@/service/book/types'
import defaultCoverImage from '@/assets/images/default_cover.jpg'
import { FormItemRule, FormInst, UploadCustomRequestOptions } from 'naive-ui'

const props = defineProps({
  bookInfo: {
    type: Object as PropType<Ibook>,
    required: true
  },
  disabled: {
    type: Boolean,
    default: false
  },
  isbn: {
    type: String,
    default: ''
  }
})

const isbn = ref('')
const decodedCover = ref(defaultCoverImage)
const authors = ref('')
const translators = ref('')
const bookInfo = ref({} as Ibook)
const uploaded_cover = ref('')

const handleCoverChange = ({ file }: UploadCustomRequestOptions) => {
  const reader = new FileReader()
  reader.readAsDataURL(file.file as File)
  reader.onload = () => {
    const base64Data = reader.result as string
    decodedCover.value = base64Data
    uploaded_cover.value = base64Data.split(',')[1]
  }
}

const removeCover = () => {
  decodedCover.value = base64ToUrl(bookInfo.value.cover_base64)
  uploaded_cover.value = ''
}

watch(
  () => props.bookInfo,
  (newValue) => {
    bookInfo.value = newValue
    authors.value = bookInfo.value.author.join(',')
    translators.value = bookInfo.value.translator.join(',')
  }
)

watch(
  () => bookInfo.value.status,
  (newValue) => {
    if (newValue === '借阅' || newValue === '赠送') {
      bookInfo.value.buy_pos = '其他'
      bookInfo.value.real_price = 0
    }
  }
)

const base64ToUrl = (base64Data: string): string => {
  const byteCharacters = atob(base64Data)
  const byteNumbers = new Uint8Array(byteCharacters.length)

  for (let i = 0; i < byteCharacters.length; i++) {
    byteNumbers[i] = byteCharacters.charCodeAt(i)
  }

  const blob = new Blob([byteNumbers], { type: 'image/jpeg' })
  return URL.createObjectURL(blob)
}

onMounted(() => {
  isbn.value = props.isbn
  bookInfo.value = props.bookInfo
  authors.value = bookInfo.value?.author?.join(',') ?? ''
  translators.value = bookInfo.value?.translator?.join(',') ?? ''
  if (bookInfo.value.cover_base64) {
    decodedCover.value = base64ToUrl(bookInfo.value.cover_base64)
  }
})

const formRef = ref<FormInst | null>(null)
const rules = {
  pages: {
    validator(rule: FormItemRule, x: string) {
      if (x == '') return true
      const num = parseInt(x)
      return !isNaN(num) && num > 0
    }
  },
  price: {
    validator(rule: FormItemRule, x: string) {
      if (x == '') return true
      const num = parseFloat(x)
      return !isNaN(num) && num > 0
    }
  },
  real_price: {
    validator(rule: FormItemRule, x: number) {
      return x >= 0
    }
  }
}

const buy_pos = ['京东', '淘宝', '拼多多', '其他', '当当', '线下']
const buy_pos_options = buy_pos.map((item) => {
  return {
    label: item,
    value: item
  }
})

const status = ['购买', '借阅', '赠送']
const status_options = status.map((item) => {
  return {
    label: item,
    value: item
  }
})

defineExpose({
  bookInfo,
  isbn,
  formRef,
  authors,
  translators,
  uploaded_cover
})
</script>

<style scoped>
.cover-container {
  width: 70%;
  text-align: center;
  overflow: hidden;
  display: flex;
  justify-content: center;
  align-items: center;
  margin-bottom: 20px;
}

.cover-image {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}
</style>
