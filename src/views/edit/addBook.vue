<template>
  <CommonPage :show-footer="true">
    <edit-book :bookInfo="bookInfo" ref="child"></edit-book>
    <div class="centered-button">
      <n-button type="info" @click="queryBook">查询</n-button>
      <n-button type="success" @click="addBook" :disabled="disabled_submit"
        >提交</n-button
      >
    </div>
  </CommonPage>
</template>

<script setup lang="ts">
import EditBook from './cpns/editForm.vue'
import { ref } from 'vue'
import { Ibook, DoubanAPI } from '@/service/book/types'
import { bookDoubanRequest, bookAddRequest } from '@/service/book/book'

const child = ref()
const bookInfo = ref({} as Ibook)
const disabled_submit = ref(true)

const queryBook = async () => {
  disabled_submit.value = true
  const isbn = child.value.isbn ?? ''
  try {
    const response = (await bookDoubanRequest(isbn)) as DoubanAPI
    window.$message?.success('查询成功')
    bookInfo.value = {
      ...bookInfo.value,
      ...response.data
    } as Ibook
    bookInfo.value.douban_id = response.data.id
    bookInfo.value.rating_count = response.data.rating.count as number
    bookInfo.value.rating_value = response.data.rating.value as number
    bookInfo.value.rating_percent = [
      response.data.rating.one_star_per as number,
      response.data.rating.two_star_per as number,
      response.data.rating.three_star_per as number,
      response.data.rating.four_star_per as number,
      response.data.rating.five_star_per as number
    ] as number[]
    if (response.data.cover) {
      bookInfo.value.cover_base64 = response.data.cover
    }
  } catch (error) {
    window.$message?.warning('查询失败')
  }
  disabled_submit.value = false
}

const addBook = async () => {
  const bookInfo = child.value.bookInfo
  const authors = child.value?.authors
  const translators = child.value?.translators
  const formRef = child.value?.formRef
  try {
    bookInfo.author = authors.split(',') ?? []
    bookInfo.translator = translators.split(',') ?? []
    await formRef?.validate(async (errors: any) => {
      if (errors) {
        window.$message?.warning('验证失败')
        return
      }
      window.$message?.success('验证通过！')
      try {
        const response = await bookAddRequest(bookInfo)
        if (response.code == 200) {
          window.$message?.success(response.message)
          disabled_submit.value = true
        } else {
          window.$message?.warning('增加失败：' + response.message)
          if (response?.sql) {
            console.log('SQL:', response?.sql)
          }
          if (response?.error) {
            console.log('ERROR:', response?.error)
          }
        }
      } catch (error) {
        window.$message?.warning('增加失败')
        console.log('增加失败', error)
      }
    })
  } catch (error) {
    console.log('验证失败', error)
  }
}
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

.centered-button {
  display: flex;
  justify-content: center;
  margin-top: 20px;
}
</style>
