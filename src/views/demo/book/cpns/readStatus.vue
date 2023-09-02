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
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { getRequest } from '@/service/book/book'
import { getInfo } from '@/service/read/read'
import addRecord from './addRecord.vue'
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

const emits = defineEmits(['updatePagePercent'])

const load = async () => {
  records.value = (await getInfo(getRequest, {
    table: 'reading_record',
    fields: ['*'],
    conditions: ['book_id = ' + props.bookid]
  })) as IRecord[]
  if (records.value.length === 0) return
  const length: number[] = []
  let sum = 0
  records.value?.forEach((item) => {
    sum += item.time_length
    length.push(sum)
  })
  total_page.value = records.value?.[records.value.length - 1].end_page
  total_time.value = sum
  total_day.value = new Set(records.value?.map((item) => item.date)).size
  emits('updatePagePercent', total_page.value)
  const chartDom = document.getElementById('main')!
  const myChart = echarts.init(chartDom)
  
  const option = {
    tooltip: {
      trigger: 'axis',
      formatter: function (params: any[]) {
        var dataIndex = params[0].dataIndex
        var timeValue = records.value![dataIndex].time_length
        var pageValue =
          records.value![dataIndex].end_page -
          records.value![dataIndex].start_page
        return timeValue + '分钟：' + pageValue + '页'
      }
    },
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
        yAxisIndex: 0
      },
      {
        data: length,
        name: '阅读时长',
        type: 'line',
        symbolSize: 10,
        yAxisIndex: 1
      }
    ]
  } as echarts.EChartsOption

  option && myChart.setOption(option)
}

onMounted(async () => {
  load()
})

const reload = () => {
  showAddModel.value = false
  load()
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
