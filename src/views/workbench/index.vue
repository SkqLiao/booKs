<template>
  <CommonPage :show-footer="true">
    <n-grid cols="4">
      <n-grid-item :span="3">
        <h2 style="text-align: center">阅读时刻热力图</h2>
        <div class="heatmap">
          <div v-for="x in 24" :key="x" class="row">
            <div
              v-for="y in 60"
              :key="y"
              class="cell"
              ref="cells"
              v-tippy="{
                content: `${(x - 1).toString().padStart(2, '0')}:${(y - 1)
                  .toString()
                  .padStart(2, '0')} ${time[x - 1][y - 1]}`,
                placement: 'top',
                arrow: true
              }"
            ></div>
          </div>
        </div>
      </n-grid-item>
      <n-grid-item :span="1">
        <n-timeline>
          <n-timeline-item
            :title="title[item.book_id]"
            :content="item.start_page + ' ~ ' + item.end_page + '页'"
            :type="item.finished ? 'success' : 'info'"
            :time="item.start_time"
            :key="index"
            v-for="(item, index) in getreadingRecord(page)"
          />
        </n-timeline>
        <n-pagination
          v-model:page="page"
          :page-size="pageSize"
          :item-count="readingRecord.length"
          simple
        />
      </n-grid-item>
    </n-grid>

    <!-- <h2>
      最喜欢阅读的时间：{{ l_hour }}:{{ l_minute }} ~ {{ r_hour }}:{{
        r_minute
      }} 
    </h2> -->
  </CommonPage>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { getInfo, getRequest } from '@/service/book/book'
import { IRecord } from '@/service/book/types'
import * as chroma from 'chroma.ts'
import * as d3 from 'd3'

const page = ref(1)
const pageSize = 5

const title: Record<number, string> = {}

const getreadingRecord = (page: number) => {
  const start = (page - 1) * pageSize
  const end = Math.min(page * pageSize, readingRecord.value.length)
  return readingRecord.value.slice(start, end)
}

const getReadingTime = async () => {
  const res = (await getInfo(getRequest, {
    table: 'reading_record',
    fields: ['*'],
    order_by: 'start_time DESC'
  })) as IRecord[]
  for (const item of res) {
    if (!title[item.book_id]) {
      title[item.book_id] = await queryBook(item.book_id)
    }
  }
  return res
}

const queryBook = async (book_id: number) => {
  const res = (await getInfo(getRequest, {
    table: 'basic_info',
    fields: ['title'],
    conditions: ['id =' + book_id]
  })) as { title: string }[]
  return res[0].title
}

const getNextMinute = (hour: number, minute: number, step = 1) => {
  let next_minute = (minute + step) % 60
  let next_hour = (hour + Math.floor((minute + step) / 60)) % 24
  return [next_hour, next_minute]
}

const time = ref(new Array(24).fill(0).map(() => new Array(60).fill(0)))

const calculateReadingTime = () => {
  const res = readingRecord.value
  res.map((item) => {
    let [hour, minute, second] = item.start_time
      .split(' ')[1]
      .split(':')
      .map(Number)
    if (second > 30) {
      const [nextHour, nextMinute] = getNextMinute(hour, minute)
      hour = nextHour
      minute = nextMinute
    }
    for (let i = 0; i < item.time_length; i++) {
      time.value[hour][minute] += 1
      const [nextHour, nextMinute] = getNextMinute(hour, minute)
      hour = nextHour
      minute = nextMinute
    }
  })
}

function getCellStyle(value: number): string {
  return `background-color: ${colors.value[Math.round(value * 15)]}`
}

const cells = ref<HTMLBaseElement[]>([])
const colors = ref<chroma.Color[]>([])

function chroma_scale(length: number, chroma_scale: any) {
  const scale = d3.scaleLinear([1, length], [0, 1])
  const colors = []
  for (var i = 1; i <= length; i++) {
    const value = scale(i)
    colors.push(chroma_scale(value))
  }

  return colors
}

const readingRecord = ref<IRecord[]>([])

const l_hour = ref(0)
const l_minute = ref(0)
const r_hour = ref(0)
const r_minute = ref(0)

const getMaxInterval = (interval_length: number) => {
  let sum_time = new Array(24 * 60).fill(0)
  for (let hour = 0; hour < 24; ++hour) {
    for (let minute = 0; minute < 60; ++minute) {
      sum_time[hour * 60 + minute] += time.value[hour][minute]
      sum_time[hour * 60 + minute + 1] == sum_time[hour][minute]
    }
  }
  let max_count = 0
  let result = [0, 0]
  for (let hour = 0; hour < 24; ++hour) {
    for (let minute = 0; minute < 60; ++minute) {
      const l = hour * 60 + minute
      const r = (l + interval_length - 1) % (24 * 60)
      let sum_count = 0
      if (r > l) {
        sum_count = sum_time[r] - (l ? sum_time[l - 1] : 0)
      } else {
        sum_count = sum_time[24 * 60 - 1] - sum_time[l] + sum_time[r]
      }
      if (sum_count > max_count) {
        max_count = sum_count
        result = [hour, minute]
      }
    }
  }
  return result
}

const getHeatmap = async () => {
  colors.value = chroma_scale(16, chroma.scale('BuGn'))
  calculateReadingTime()
  const max = Math.max(...time.value.map((item) => Math.max(...item)))

  cells.value.forEach((cell, index) => {
    const x = Math.floor(index / 60)
    const y = index % 60
    cell.setAttribute('style', getCellStyle(time.value[x][y] / max))
  })
  const result = getMaxInterval(45)
  l_hour.value = result[0]
  l_minute.value = result[1]
  const [rh, rm] = getNextMinute(result[0], result[1], 45)
  r_hour.value = rh
  r_minute.value = rm
}

const getTimeLine = async () => {
  const res = readingRecord.value
}

onMounted(async () => {
  readingRecord.value = await getReadingTime()
  await getHeatmap()
  await getTimeLine()
})
</script>

<style scoped lang="scss">
.heatmap {
  display: flex;
  justify-content: center; /* 水平居中 */
  align-items: center; /* 垂直居中 */
  flex-direction: column;
}

.row {
  display: flex;
}

.cell {
  width: 10px;
  height: 10px;
  background-color: #f0f0f0; /* 设置方格的背景颜色 */
  border: 1px solid #ddd;
  margin: 1px;
}

/* 这里可以根据你的需求定义热力图的颜色映射，例如通过颜色渐变表示不同的热度值 */
</style>
