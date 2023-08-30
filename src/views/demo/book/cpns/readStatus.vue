<template>
  <div id="main" style="width: 70%px; height: 400px"></div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { readingDetailRequest, getInfo } from '@/service/read/read'
import { IRecord } from '@/service/read/types'
import * as echarts from 'echarts'

const props = defineProps({
  bookid: {
    type: Number,
    required: true
  }
})

const records = ref<IRecord[]>()

onMounted(async () => {
  console.log(props.bookid)
  records.value = (await getInfo(readingDetailRequest, {
    bookid: props.bookid
  })) as IRecord[]
  const length: number[] = []
  let sum = 0
  records.value?.forEach((item) => {
    sum += item.time_length
    length.push(sum)
  })
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
})
</script>

<style scoped></style>
