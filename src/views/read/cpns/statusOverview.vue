<template>
  <CommonPage :show-footer="true">
    <n-row>
      <n-col :span="6">
        <n-statistic label="已阅读">
          {{ readingBookNumber[1] }} 本
        </n-statistic>
      </n-col>
      <n-col :span="6">
        <n-statistic label="阅读中">
          {{ readingBookNumber[0] }} 本
        </n-statistic>
      </n-col>
      <n-col :span="6">
        <n-statistic label="阅读天数"> {{ readingDateNumber }} 天 </n-statistic>
      </n-col>
      <n-col :span="6">
        <n-statistic label="阅读总时长">
          {{ Math.floor(readingTimeLength / 60) }} 小时
          {{ readingTimeLength % 60 }} 分钟
        </n-statistic>
      </n-col>
    </n-row>
    <n-row>
      <n-col :span="2">
        <n-tabs
          type="card"
          animated
          placement="left"
          :on-update:value="changeYear"
          :default-value="0"
        >
          <n-tab
            :key="year"
            :value="year"
            :name="year ? year + '年' : '汇总'"
            v-for="year in years"
          >
          </n-tab>
        </n-tabs>
      </n-col>
      <n-col :span="22">
        <div id="overview-dom" style="width: 100%; height: 400px"></div>
      </n-col>
    </n-row>

    <status-drawer
      :show="showDrawer"
      :date="drawerDate"
      @update-drawer-visible="updateDrawerVisible"
    />
  </CommonPage>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { getInfo, getRequest } from '@/service/book/book'
import * as echarts from 'echarts'
import statusDrawer from './statusDrawer.vue'

const readingBookNumber = ref([0, 0])
const readingDateNumber = ref(0)
const readingTimeLength = ref(0)
const showDrawer = ref(false)
const drawerDate = ref('')
const startDate = ref('')
const endDate = ref('')
const years = ref([0])
let myChart

const getReadingBookNumber = async (year: number) => {
  const condition = year ? 'YEAR(date) = ' + year : '1=1'
  const response = (await getInfo(getRequest, {
    table: 'reading_record',
    fields: ['COUNT(DISTINCT book_id) as count'],
    conditions: [condition]
  })) as { count: number }[]
  readingBookNumber.value[0] = response[0].count
  const response2 = (await getInfo(getRequest, {
    table: 'reading_status',
    fields: ['COUNT(*) as count'],
    conditions: [condition + ' AND finished=1']
  })) as { count: number }[]
  readingBookNumber.value[1] = response2[0].count
}

const getReadingDateNumber = async (year: number) => {
  const response = (await getInfo(getRequest, {
    table: 'reading_record',
    fields: ['COUNT(DISTINCT date) as count'],
    conditions: [!year ? '1=1' : 'YEAR(date) = ' + year]
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

const initYear = async () => {
  startDate.value = (
    (await getInfo(getRequest, {
      table: 'reading_record',
      fields: ['MIN(date) as date']
    })) as { date: string }[]
  )[0].date
  endDate.value = new Date().toISOString().split('T')[0]
  const year_s = parseInt(startDate.value.split('-')[0])
  const year_e = parseInt(endDate.value.split('-')[0])
  for (let year = year_s; year <= year_e; ++year) years.value.push(year)
}

const changeYear = async (name: string) => {
  const year = name === '汇总' ? 0 : parseInt(name.split('年')[0])
  initChart(year)
  await getReadingBookNumber(year)
  await getReadingDateNumber(year)
  const condition = year ? 'YEAR(date) = ' + year : '1=1'
  readingTimeLength.value =
    ((await getReadingInfo('SUM(time_length) as length', condition))
      .length as number) ?? 0
}

const initChart = async (in_year: number) => {
  const year_s = in_year ? in_year : parseInt(startDate.value.split('-')[0])
  const year_e = in_year ? in_year : parseInt(endDate.value.split('-')[0])
  const month_s = in_year ? 1 : parseInt(startDate.value.split('-')[1])
  const month_e = in_year ? 12 : parseInt(endDate.value.split('-')[1])
  let x: string[] = []
  let y0: number[] = []
  let y1: number[] = []
  for (let year = year_s, month = month_s; ; ) {
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
    if (year === year_e && month === month_e) break
    if (month == 12) {
      year += 1
      month = 1
    } else {
      month += 1
    }
  }
  const chartDom = document.getElementById('overview-dom')!
  if (myChart) {
    myChart.dispose()
  }
  myChart = echarts.init(chartDom)
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
  myChart.on('click', (params: any) => {
    showDrawer.value = true
    drawerDate.value = params.name
  })
}

function updateDrawerVisible() {
  showDrawer.value = false
}

onMounted(async () => {
  await getReadingBookNumber(0)
  await getReadingDateNumber(0)
  readingTimeLength.value =
    ((await getReadingInfo('SUM(time_length) as length', '1=1'))
      .length as number) ?? 0
  await initYear()
  await initChart(0)
})
</script>

<style scoped></style>
