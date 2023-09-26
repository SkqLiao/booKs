<template>
  <CommonPage :show-footer="true">
    <n-switch @update:value="handleChange">
      <template #checked> 时间倒序 </template>
      <template #unchecked> 随机排序 </template>
    </n-switch>
    <n-carousel
      effect="card"
      prev-slide-style="transform: translateX(-150%) translateZ(-800px);"
      next-slide-style="transform: translateX(50%) translateZ(-800px);"
      style="height: 300px"
      :show-dots="false"
      :on-update:current-index="reload"
      autoplay
      show-arrow
    >
      <n-carousel-item
        v-for="id in bookIds"
        :key="id"
        :style="{ width: '60%' }"
      >
        <book-card :id="id" />
      </n-carousel-item>
    </n-carousel>
    <div id="statistics" style="width: 100%; height: 350px"></div>
  </CommonPage>
</template>

<script setup lang="ts">
import { onMounted, ref, Ref } from 'vue'
import { getRequest, getInfo } from '@/service/book/book'
import bookCard from '@/views/book/cpns/bookCard.vue'
import { IRecord } from '@/service/book/types'
import * as echarts from 'echarts'

const bookIds: Ref<number[]> = ref([])

const fetchBookNumber = async (sortBy: string) => {
  const data = (await getInfo(getRequest, {
    table: 'reading_record',
    fields: ['book_id', 'start_time'],
    conditions: ['finished=1']
  })) as [{ book_id: number; start_time: string }]
  if (sortBy === 'time') {
    data.sort((a, b) => {
      return new Date(a.start_time).getTime() - new Date(b.start_time).getTime()
    })
  } else {
    data.sort(() => Math.random() - 0.5)
  }
  bookIds.value = data.map((item) => item.book_id)
  await load(bookIds.value[0])
}

const records = ref<IRecord[]>()
const myChart = ref<any>()

const handleChange = (value: boolean) => {
  fetchBookNumber(value ? 'random' : 'time')
}

const reload = async (currentIndex: number, lastIndex: number) => {
  await load(bookIds.value[currentIndex])
}

const load = async (bookid: number) => {
  const response = (await getInfo(getRequest, {
    table: 'reading_record',
    fields: ['*'],
    conditions: ['book_id = ' + bookid]
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
  const chartDom = document.getElementById('statistics')!
  if (myChart.value) {
    myChart.value.dispose()
  }
  myChart.value = echarts.init(chartDom)

  const option = {
    title: {
      text: '阅读记录',
      left: 'center'
    },
    xAxis: {
      type: 'category',
      data: records.value?.map((item) => item.start_time.split(' ')[0])
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
          formatter: '{c} 页'
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
          formatter: '{c} 分钟'
        }
      }
    ]
  } as echarts.EChartsOption

  option && myChart.value.setOption(option)
}

onMounted(async () => {
  await fetchBookNumber('random')
})
</script>

<style scoped>
.carousel-img {
  margin: 0 auto;
  width: 100%;
  height: 100%;
  object-fit: cover;
}
</style>
