<template>
  <n-row>
    <n-col :span="5">
      <n-statistic label="已阅读"> {{ readingBookNumber[1] }} 本 </n-statistic>
    </n-col>
    <n-col :span="5">
      <n-statistic label="书籍总数"> {{ bookNumber }} 本 </n-statistic>
    </n-col>
    <n-col :span="5">
      <n-statistic label="阅读中"> {{ readingBookNumber[0] }} 本 </n-statistic>
    </n-col>
    <n-col :span="5">
      <n-statistic label="阅读天数"> {{ readingDateNumber }} 天 </n-statistic>
    </n-col>
    <n-col :span="4">
      <n-statistic label="阅读总时长">
        {{ Math.floor(readingTimeLength / 60) }} 小时
        {{ readingTimeLength % 60 }} 分钟
      </n-statistic>
    </n-col>
  </n-row>
  <div id="main" style="width: 100%; height: 400px; padding-top: 20px"></div>
  <status-drawer
    :show="showDrawer"
    :date="drawerDate"
    @update-drawer-visible="updateDrawerVisible"
  />
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { getInfo, getRequest } from '@/service/book/book'
import * as echarts from 'echarts'
import statusDrawer from './statusDrawer.vue'

const bookNumber = ref(0)
const readingBookNumber = ref([0, 0])
const readingDateNumber = ref(0)
const readingTimeLength = ref(0)
const showDrawer = ref(false)
const drawerDate = ref('')

const getBookNumber = async () => {
  const response = (await getInfo(getRequest, {
    table: 'basic_info',
    fields: ['COUNT(*) as count']
  })) as { count: number }[]
  bookNumber.value = response[0].count
}

const getReadingBookNumber = async () => {
  const response = (await getInfo(getRequest, {
    table: 'reading_status',
    fields: [
      'COUNT(CASE WHEN finished = 0 THEN 1 END) AS count0',
      'COUNT(CASE WHEN finished = 1 THEN 1 END) AS count1'
    ]
  })) as { count0: number; count1: number }[]
  readingBookNumber.value[0] = response[0].count0
  readingBookNumber.value[1] = response[0].count1
}

const getReadingDateNumber = async () => {
  const response = (await getInfo(getRequest, {
    table: 'reading_record',
    fields: ['COUNT(DISTINCT date) as count']
  })) as { count: number }[]
  readingDateNumber.value = response[0].count
}

const getReadingInfo = async (field: string, condition: string) => {
  const response = (await getInfo(getRequest, {
    table: 'reading_record',
    fields: [field],
    conditions: [condition]
  })) as any[]
  return response[0]
}

const initChart = async () => {
  const startDate = (
    (await getInfo(getRequest, {
      table: 'reading_record',
      fields: ['MIN(date) as date']
    })) as { date: string }[]
  )[0].date
  const endDate = new Date().toISOString().split('T')[0]
  const year_s = parseInt(startDate.split('-')[0])
  const year_e = parseInt(endDate.split('-')[0])
  const month_s = parseInt(startDate.split('-')[1])
  const month_e = parseInt(endDate.split('-')[1])
  let x = []
  let y0: number[] = []
  let y1: number[] = []
  for (let year = year_s; year <= year_e; year++) {
    let month_l = year == year_s ? month_s : 1
    let month_r = year == year_e ? month_e : 12
    for (let month = month_l; month <= month_r; ++month) {
      const condition = `YEAR(date) = ${year} AND MONTH(date) = ${month}`
      x.push(`${year}年${month.toString().padStart(2, '0')}月`)
      const time_length = await getReadingInfo(
        'SUM(time_length) as length',
        condition
      )
      y1.push((time_length.length as number) ?? 0)
      const day_num = await getReadingInfo(
        'COUNT(DISTINCT date) as count',
        condition
      )
      y0.push((day_num.count as number) ?? 0)
    }
  }
  const chartDom = document.getElementById('main')!
  const myChart = echarts.init(chartDom)
  const option = {
    tooltip: {
      trigger: 'axis',
      axisPointer: {
        type: 'shadow'
      },
      formatter: '{b}<br />{a0}: {c0} 天<br />{a1}: {c1} 分钟'
    },
    xAxis: {
      type: 'category',
      data: x,
      axisLabel: {
        interval: 0,
        rotate: 45
      }
    },
    yAxis: [
      {
        type: 'value',
        name: '阅读天数',
        position: 'left',
        splitLine: {
          show: false
        },
        axisLabel: {
          formatter: '{value} 天'
        },
        min: 0,
        max: 31
      },
      {
        type: 'value',
        name: '阅读时长',
        position: 'right',
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
        data: y0,
        name: '天数',
        type: 'bar',
        yAxisIndex: 0,
        label: {
          formatter: '{c} 天'
        }
      },
      {
        data: y1,
        name: '阅读时长',
        type: 'line',
        symbolSize: 10,
        yAxisIndex: 1,
        label: {
          show: false,
          formatter: '{c} 分钟'
        }
      }
    ]
  } as echarts.EChartsOption
  option && myChart.setOption(option)
  myChart.on('click', (params) => {
    showDrawer.value = true
    drawerDate.value = params.name
  })
}

function updateDrawerVisible() {
  showDrawer.value = false
}

onMounted(async () => {
  await getBookNumber()
  await getReadingBookNumber()
  await getReadingDateNumber()
  readingTimeLength.value =
    ((await getReadingInfo('SUM(time_length) as length', '1=1'))
      .length as number) ?? 0
  await initChart()
})
</script>

<style scoped></style>
