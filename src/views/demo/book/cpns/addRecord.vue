<template>
  <n-modal
    v-model:show="localShowModal"
    preset="card"
    style="width: 18%"
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
        <n-form-item label="阅读日期" path="read_date" required>
          <n-date-picker panel v-model:formatted-value="readDate" />
        </n-form-item>
      </n-form>
      <div class="centered-button">
        <n-button type="success" @click="addRecord">提交</n-button>
      </div>
    </div>
    <div v-else-if="props.type === 2">增加摘录</div>
  </n-modal>
</template>

<script setup lang="ts">
import { addReadingRecord } from '@/service/read/read'

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

const localShowModal = ref(false)

watch(
  () => props.showModal,
  (newVal, oldVal) => {
    localShowModal.value = newVal
  }
)

const startPage = ref(0)
const endPage = ref(0)
const time_length = ref(0)
const readDate = ref(new Date().toISOString().split('T')[0])
const emits = defineEmits(['updateAddVisible'])

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
      read_date: readDate.value,
      time_length: time_length.value,
      date: readDate.value
    })
    console.log(response)
    if (response.code !== 200) {
      window.$message?.warning('增加失败 ' + response.message)
    } else {
      window.$message?.success('增加成功')
      localShowModal.value = false
      emits('updateAddVisible')
    }
  } catch (error) {
    window.$message?.warning('增加失败 ')
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
