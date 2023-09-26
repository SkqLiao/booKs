<template>
  <n-modal
    v-model:show="localShowModal"
    preset="card"
    style="width: 18%"
    :style="getStyle"
    :on-after-leave="() => $emit('updateAddVisible')"
  >
    <template #header>
      <div v-if="props.type === 1">增加记录</div>
      <div v-else-if="props.type === 2">增加摘录</div>
    </template>
    <div v-if="props.type === 1">
      <n-form>
        <n-form-item label="起始页码" path="start_page" required>
          <n-input-number
            v-model:value="startPage"
            clearable
            style="width: 100%"
          />
        </n-form-item>
        <n-form-item label="结束页码" path="end_page" required>
          <n-input-number
            v-model:value="endPage"
            clearable
            style="width: 100%"
          />
        </n-form-item>
        <n-form-item label="阅读时长" path="time_length" required>
          <n-input-number
            v-model:value="time_length"
            clearable
            style="width: 100%"
          >
            <template #suffix> 分钟 </template>
          </n-input-number>
        </n-form-item>
        <n-form-item label="开始时间" path="start_time" required>
          <n-date-picker
            v-model:formatted-value="start_time"
            value-format="yyyy-MM-dd HH:mm:ss"
            type="datetime"
            style="width: 100%"
            clearable
          />
        </n-form-item>
        <n-form-item label="已读完" path="finished">
          <n-switch v-model:value="finished" />
        </n-form-item>
      </n-form>
      <div class="centered-button">
        <n-button type="success" @click="addRecord">提交</n-button>
      </div>
    </div>
    <div v-else-if="props.type === 2">
      <n-form>
        <n-grid :cols="24" :x-gap="24">
          <n-form-item-gi :span="8" label="页码" path="excerpt_page" required>
            <n-input-number v-model:value="excerpt_page" clearable>
              <template #suffix> 页 </template>
            </n-input-number>
          </n-form-item-gi>
          <n-form-item-gi :span="16" label="摘录" path="excerpt">
            <n-input
              v-model:value="excerpt"
              clearable
              style="width: 100%"
              type="textarea"
              :rows="10"
            />
          </n-form-item-gi>
          <n-form-item-gi :span="8" label="阅读日期" path="read_date" required>
            <n-date-picker panel v-model:formatted-value="readDate" />
          </n-form-item-gi>
          <n-form-item-gi :span="16" label="心得" path="thoughts">
            <n-input
              v-model:value="thoughts"
              clearable
              style="width: 100%"
              type="textarea"
              :rows="10"
            />
          </n-form-item-gi>
        </n-grid>
      </n-form>
      <div class="centered-button">
        <n-button type="success" @click="addExcerpt">提交</n-button>
      </div>
    </div>
  </n-modal>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import {
  addReadingRecord,
  addReadingExcerpt,
  getInfo,
  getRequest
} from '@/service/book/book'

const props = defineProps({
  type: {
    type: Number,
    required: true
  },
  showModal: {
    type: Boolean,
    required: true
  },
  id: {
    type: Number,
    required: true
  }
})

const getStyle = computed(() => {
  return props.type == 1 ? { width: '18%' } : { width: '50%' }
})

const localShowModal = ref(false)

watch(
  () => props.showModal,
  (newVal) => {
    localShowModal.value = newVal
  }
)

const startPage = ref(0)
const endPage = ref(0)
const time_length = ref(0)
const start_time = ref(new Date().toISOString().replace('T', ' ').split('.')[0])
const readDate = ref(new Date().toISOString().split('T')[0])
const emits = defineEmits(['updateAddVisible'])
const finished = ref(false)

const getStartPage = async () => {
  const data = (await getInfo(getRequest, {
    table: 'reading_record',
    fields: ['MAX(end_page)'],
    conditions: ['book_id=' + props.id]
  })) as [{ 'MAX(end_page)': number }]
  startPage.value = data[0]['MAX(end_page)']
}

onMounted(async () => {
  await getStartPage()
})

const addRecord = async () => {
  try {
    if (startPage.value > endPage.value) {
      window.$message?.warning('起始页码不能大于结束页码')
      return
    }
    const response = await addReadingRecord({
      book_id: props.id,
      start_page: startPage.value,
      end_page: endPage.value,
      start_time: start_time.value,
      time_length: time_length.value,
      finished: finished.value ? 1 : 0
    })
    if (response.code !== 200) {
      window.$message?.warning('增加失败：' + response.message)
      console.log(response?.error)
    } else {
      window.$message?.success('增加成功！')
      localShowModal.value = false
      startPage.value = endPage.value
      emits('updateAddVisible')
    }
  } catch (error) {
    window.$message?.warning('增加失败')
  }
}

const excerpt_page = ref(0)
const excerpt = ref('')
const thoughts = ref('')

const addExcerpt = async () => {
  try {
    if (excerpt_page.value < 0) {
      window.$message?.warning('页码不能为负数')
      return
    }
    if (excerpt.value.length == 0 && thoughts.value.length == 0) {
      window.$message?.warning('摘录和心得不能同时为空')
      return
    }
    const response = await addReadingExcerpt({
      book_id: props.id,
      page: excerpt_page.value,
      excerpt: excerpt.value,
      thoughts: thoughts.value,
      date: readDate.value
    })
    if (response.code !== 200) {
      window.$message?.warning('增加失败：' + response.message)
      console.log(response?.error)
    } else {
      window.$message?.success('增加成功！')
      localShowModal.value = false
      emits('updateAddVisible')
    }
  } catch (error) {
    window.$message?.warning('增加失败')
  }
}
</script>

<style scoped>
.centered-button {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 20px;
}
</style>
