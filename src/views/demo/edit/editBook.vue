<template>
  <div>
    <n-modal
      v-model:show="showModal"
      class="custom-card"
      preset="card"
      style="width: 50%"
      size="huge"
      :bordered="false"
      :on-after-leave="() => $emit('updateEditVisible', props.id)"
    >
      <template #header> 编辑书籍：{{ props.bookInfo?.isbn }} </template>
      <edit-form
        :bookInfo="props.bookInfo"
        ref="child"
        :disabled="true"
        :isbn="props.bookInfo.isbn"
      ></edit-form>
      <div class="centered-button">
        <n-button type="success" @click="updateBook">提交</n-button>
        <n-popconfirm
          @positive-click="deleteBook"
          positive-text="确定"
          negative-text="取消"
        >
          <template #trigger>
            <n-button type="error">删除</n-button>
          </template>
          你确定删除这本书吗？
        </n-popconfirm>
      </div>
    </n-modal>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { Ibook } from '@/service/book/types'
import EditForm from './cpns/editForm.vue'
import eventBus from '@/eventbus/index'
import { bookInfoUpdate, bookInfoDelete } from '@/service/book/book'

const props = defineProps({
  bookInfo: {
    type: Object as () => Ibook,
    required: true
  },
  showEditModal: {
    type: Boolean,
    required: true
  },
  id: {
    type: Number,
    required: true
  }
})

const showModal = ref(false)

watch(
  () => props.showEditModal,
  (newVal, oldVal) => {
    if (newVal !== oldVal) {
      showModal.value = newVal
    }
  }
)

const emits = defineEmits(['updateEditVisible'])

const child = ref()

const updateBook = async () => {
  const bookInfo = child.value.bookInfo
  const authors = child.value?.authors
  const translators = child.value?.translators
  const formRef = child.value?.formRef
  const uploaded_cover = child.value?.uploaded_cover
  if (uploaded_cover) {
    bookInfo.cover_base64 = uploaded_cover
  }
  try {
    bookInfo.author = authors.split(',')
    bookInfo.translator = translators.split(',')
    await formRef?.validate(async (errors: any) => {
      if (errors) {
        window.$message?.warning('验证失败')
        return
      }
      window.$message?.success('验证通过！')
      try {
        const response = await bookInfoUpdate(bookInfo)
        if (response.code === 200) {
          window.$message?.success('修改成功！')
          emits('updateEditVisible', props.id)
        } else {
          window.$message?.error('修改失败：' + response.message)
        }
      } catch (error) {
        window.$message?.error('修改失败')
        console.log('修改失败', error)
      }
    })
  } catch (error) {
    console.log('验证失败', error)
  }
}

const deleteBook = async () => {
  try {
    const response = await bookInfoDelete({
      isbn: props.bookInfo.isbn
    })
    if (response.code === 200) {
      window.$message?.success('删除成功！')
      emits('updateEditVisible', props.id)
      eventBus.emit('deleteBook', props.id)
    } else {
      window.$message?.error('删除失败：' + response.message)
    }
  } catch (error) {
    window.$message?.error('删除失败')
    console.log('删除失败', error)
  }
}
</script>

<style scoped>
.centered-button {
  display: flex;
  justify-content: center;
  margin-top: 20px;
}
</style>
