<template>
  <CommonPage :show-footer="true">
    <n-row>
      <n-col :span="12">
        <n-statistic label="共购买"> {{ bookNumber }} 本 </n-statistic>
      </n-col>
      <n-col :span="12">
        <n-statistic label="阅读总花费">
          {{ Math.round(totalCost) }} 元
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
import statusDrawer from './cpns/drawer.vue'

const bookNumber = ref(0)
const totalCost = ref(0)
const showDrawer = ref(false)
const drawerDate = ref('')
const startDate = ref('')
const endDate = ref('')
const years = ref([0])
let myChart

const getBookNumber = async (year: number) => {
  const condition = year ? 'YEAR(buy_date) = ' + year : '1=1'
  const response = (await getInfo(getRequest, {
    table: 'basic_info',
    fields: ['COUNT(*) as count'],
    conditions: [condition]
  })) as { count: number }[]
  bookNumber.value = response[0].count
}

const getBookInfo = async (field: string, condition: string) => {
  const response = (await getInfo(getRequest, {
    table: 'basic_info',
    fields: [field],
    conditions: [condition]
  })) as any[]
  return response[0]
}

const initYear = async () => {
  startDate.value = (
    (await getInfo(getRequest, {
      table: 'basic_info',
      fields: ['MIN(buy_date) as date']
    })) as { date: string }[]
  )[0].date
  endDate.value = new Date().toISOString().split('T')[0]
  const year_s = parseInt(startDate.value.split('-')[0])
  const year_e = parseInt(endDate.value.split('-')[0])
  for (let year = year_e; year >= year_s; --year) years.value.push(year)
}

const changeYear = async (name: string) => {
  const year = name === '汇总' ? 0 : parseInt(name.split('年')[0])
  initChart(year)
  await getBookNumber(year)
  const condition = year ? 'YEAR(buy_date) = ' + year : '1=1'
  totalCost.value =
    ((await getBookInfo('SUM(real_price) as price', condition))
      .price as number) ?? 0
  initChart(year)
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
    const condition = `YEAR(buy_date) = ${year} AND MONTH(buy_date) = ${month}`
    x.push(`${year}年${month.toString().padStart(2, '0')}月`)
    const total_price = await getBookInfo('SUM(real_price) as price', condition)
    y1.push(Math.round(total_price.price as number) ?? 0)
    const book_num = await getBookInfo('COUNT(*) as count', condition)
    y0.push((book_num.count as number) ?? 0)
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
      formatter: '{b}<br />{a0}: {c0} 本<br />{a1}: {c1} 元'
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
        name: '数量',
        position: 'left',
        splitLine: {
          show: false
        },
        axisLabel: {
          formatter: '{value} 本'
        }
      },
      {
        type: 'value',
        name: '花费',
        position: 'right',
        splitLine: {
          show: false
        },
        axisLabel: {
          formatter: '{value} 元'
        }
      }
    ],
    series: [
      {
        data: y0,
        name: '数量',
        type: 'bar',
        yAxisIndex: 0,
        label: {
          formatter: '{c} 本'
        }
      },
      {
        data: y1,
        name: '花费',
        type: 'line',
        symbolSize: 10,
        yAxisIndex: 1,
        label: {
          formatter: '{c} 元'
        }
      }
    ]
  } as echarts.EChartsOption
  if (in_year == 0) {
    option.dataZoom = [
      {
        type: 'slider',
        show: true,
        start: 0,
        end: 30,
        handleSize: 8,
        handleStyle: {
          color: '#DCE2E8'
        },
        filterMode: 'filter'
      }
    ]
  }
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
  await getBookNumber(0)
  totalCost.value =
    ((await getBookInfo('SUM(real_price) as price', '1=1')).price as number) ??
    0
  await initYear()
  await initChart(0)
})
</script>

<style scoped></style>
