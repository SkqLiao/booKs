<template>
  <n-grid cols="5" :x-gap="12">
    <n-grid-item :style="{ marginTop: '50px' }">
      <n-card>
        <p class="book-info-p"><strong>页数：</strong> {{ total_page }} 页</p>
        <p class="book-info-p"><strong>天数：</strong> {{ total_day }} 天</p>
        <p class="book-info-p">
          <strong>时间：</strong> {{ Math.floor(total_time / 60) }} 小时
          {{ total_time % 60 }} 分钟
        </p>
        <p class="book-info-p">
          <strong>摘录：</strong> {{ total_excerpt }} 条
        </p>
        <template #footer>
          <n-button
            class="full-width-button"
            type="info"
            strong
            tertiary
            @click="
              () => {
                showAddModel = true
                addType = 1
              }
            "
            >增加记录</n-button
          >
          <n-button
            class="full-width-button"
            type="success"
            strong
            tertiary
            @click="
              () => {
                showAddModel = true
                addType = 2
              }
            "
            >增加摘录</n-button
          >
        </template>
      </n-card>
    </n-grid-item>
    <n-grid-item :span="4">
      <div id="main" style="width: 100%; height: 375px"></div>
    </n-grid-item>
  </n-grid>
  <addRecord
    :type="addType"
    :show-modal="showAddModel"
    :id="props.bookid"
    @update-add-visible="reload"
  />
  <readingRecord
    :bookid="props.bookid"
    @update-record="reload"
    :reload="updateRecord"
  />
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { getRequest } from '@/service/book/book'
import { getInfo } from '@/service/read/read'
import addRecord from './addRecord.vue'
import readingRecord from './readingRecord.vue'
import { IRecord } from '@/service/read/types'
import * as echarts from 'echarts'

const props = defineProps({
  bookid: {
    type: Number,
    required: true
  }
})

const records = ref<IRecord[]>()
const total_page = ref(0)
const total_time = ref(0)
const total_day = ref(0)
const total_excerpt = ref(0)
const myChart = ref<any>()
const emits = defineEmits(['updatePagePercent'])
const updateRecord = ref(false)

const load = async () => {
  const response = (await getInfo(getRequest, {
    table: 'reading_record',
    fields: ['*'],
    conditions: ['book_id = ' + props.bookid]
  })) as IRecord[]
  records.value = response
  if (records.value?.length === 0) {
    if (myChart.value) {
      myChart.value.dispose()
    }
    return
  }
  const length: number[] = []
  let sum = 0
  records.value?.forEach((item) => {
    sum += item.time_length
    length.push(sum)
  })
  total_page.value = records.value?.[records.value.length - 1].end_page || 0
  total_time.value = sum
  total_day.value = new Set(records.value?.map((item) => item.date)).size
  emits('updatePagePercent', total_page.value)
  const chartDom = document.getElementById('main')!
  if (myChart.value) {
    myChart.value.dispose()
  }
  myChart.value = echarts.init(chartDom)

  const option = {
    xAxis: {
      type: 'category',
      data: records.value?.map((item) => item.date)
    },
    yAxis: [
      {
        type: 'value',
        name: '累计页数',
        position: 'right',
        splitLine: {
          show: false
        },
        axisLabel: {
          formatter: '{value} 页'
        }
      },
      {
        type: 'value',
        name: '阅读时长',
        position: 'left',
        splitLine: {
          show: false
        },
        axisLabel: {
          formatter: '{value} 分钟'
        }
      }
    ],
    series: [
      {
        data: records.value?.map((item) => item.end_page),
        name: '页数',
        type: 'bar',
        yAxisIndex: 0,
        label: {
          show: true,
          formatter: '{c} 页',
        }
      },
      {
        data: length,
        name: '阅读时长',
        type: 'line',
        symbolSize: 10,
        yAxisIndex: 1,
        label: {
          show: true,
          formatter: '{c} 分钟',
        }
      }
    ]
  } as echarts.EChartsOption

  option && myChart.value.setOption(option)
}

onMounted(async () => {
  await reload()
})

const reload = async () => {
  updateRecord.value = false
  showAddModel.value = false
  await load()
  updateRecord.value = true
}

const showAddModel = ref(false)
const addType = ref(1)
</script>

<style scoped>
.book-info-p {
  font-size: 15px;
  padding-bottom: 10px;
}
.full-width-button {
  width: 100%; /* 让按钮宽度占满父元素 */
}
</style>
