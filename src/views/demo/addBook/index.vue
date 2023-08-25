<template>
  <CommonPage :show-footer="true">
    <n-form ref="formRef" :model="bookInfo" :rules="rules">
      <n-grid x-gap="30" cols="3">
        <n-gi>
          <!-- 左侧封面图片 -->
          <div class="cover-container">
            <img :src="decodedCover" alt="封面图像" class="cover-image" />
          </div>
          <n-form-item label="ISBN" required>
            <n-input v-model:value="isbn" />
          </n-form-item>
        </n-gi>
        <n-gi>
          <!-- 中间表单字段 -->
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
              <n-date-picker v-model:formatted-value="bookInfo.buy_date" />
            </n-form-item>
            <n-form-item label="购买价格" path="real_price">
              <n-input-number v-model:value="bookInfo.real_price">
                <template #prefix> ￥ </template>
              </n-input-number>
            </n-form-item>
            <n-form-item label="购买平台" path="buy_pos">
              <n-select
                v-model:value="bookInfo.buy_pos"
                :options="buy_pos_options"
                required
              />
            </n-form-item>
          </n-card>
        </n-gi>
      </n-grid>
    </n-form>
    <div class="centered-button">
      <n-button type="info" @click="queryBook">查询</n-button>
      <n-button type="success" @click="addBook">提交</n-button>
    </div>
  </CommonPage>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Ibook, DoubanAPI, IbookRating } from '@/service/book/types'
import { bookDoubanRequest, bookAddRequest } from '@/service/book/book'
import defaultCoverImage from '@/assets/images/default_cover.jpg'
import { FormInst, FormItemRule } from 'naive-ui'

const bookInfo = ref<Ibook>({} as Ibook)
const isbn = ref('')
const decodedCover = ref(defaultCoverImage)
const authors = ref(''), translators = ref('')

const queryBook = async () => {
  try {
    const response = (await bookDoubanRequest(isbn.value)) as DoubanAPI
    window.$message?.success('查询成功')
    bookInfo.value = {
      ...bookInfo.value,
      ...response.data
    } as Ibook
    bookInfo.value.douban_id = response.data.id
    bookInfo.value.rating = {
      count: response.data.rating.count as number,
      value: response.data.rating.value as number,
      percent: [
        response.data.rating.one_star_per as number,
        response.data.rating.two_star_per as number,
        response.data.rating.three_star_per as number,
        response.data.rating.four_star_per as number,
        response.data.rating.five_star_per as number
      ] as number[]
    } as IbookRating
    if (response.data.cover) {
      decodedCover.value = response.data.cover
      bookInfo.value.cover_base64 = decodedCover.value
    }
    authors.value = bookInfo.value.author.join(',')
    translators.value = bookInfo.value.translator.join(',')
  } catch (error) {
    window.$message?.warning('查询失败')
  }
}

const formRef = ref<FormInst | null>(null)
const rules = {
  pages: {
    validator(rule: FormItemRule, x: string) {
      if (x == '') return true
      const num = parseInt(x)
      return !isNaN(num) && num > 0 && Number.isInteger(num)
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
    required: true,
    validator(rule: FormItemRule, x: number) {
      return x >= 0
    }
  },
  buy_pos: {
    required: true
  }
}

const buy_pos = ['京东', '淘宝', '拼多多', '其他', '当当', '线下']
const buy_pos_options = buy_pos.map((item) => {
  return {
    label: item,
    value: item
  }
})

const addBook = async () => {
  try {
    bookInfo.value.author = authors.value.split(',')
    bookInfo.value.translator = translators.value.split(',')
    console.log(bookInfo.value)
    await formRef.value?.validate(async (errors) => {
      if (errors) {
        window.$message?.warning('验证失败')
        return
      }
      window.$message?.success('验证通过')
      try {
        const response = await bookAddRequest(bookInfo.value)
        if (response.code == 200) {
          window.$message?.success('增加成功 ')
        } else {
          window.$message?.warning('增加失败：' + response.response)
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
