<template>
  <CommonPage :show-footer="true">
    <h2>阅读时刻热力图</h2>
    <div class="heatmap">
      <div v-for="x in 24" :key="x" class="row">
        <div
          v-for="y in 60"
          :key="y"
          class="cell"
          ref="cells"
          v-tippy="{
            content: `${x.toString().padStart(2, '0')}:${y
              .toString()
              .padStart(2, '0')} ${time[x - 1][y - 1]}`,
            placement: 'top',
            arrow: true
          }"
        ></div>
      </div>
    </div>
  </CommonPage>
</template>

<script setup lang="ts">
import { ref, Ref, onMounted } from 'vue'
import { getInfo, getRequest } from '@/service/book/book'
import * as chroma from 'chroma.ts'
import * as d3 from 'd3'
import 'tippy.js/dist/tippy.css'

const getReadingTime = async () => {
  const res = (await getInfo(getRequest, {
    table: 'reading_record',
    fields: ['start_time', 'time_length']
  })) as { start_time: string; time_length: number }[]
  return res
}

const getNextMinute = (hour: number, minute: number) => {
  if (minute === 59) {
    return [(hour + 1) % 24, 0]
  } else {
    return [hour, minute + 1]
  }
}

const time = ref(new Array(24).fill(0).map(() => new Array(60).fill(0)))
const finished = ref(false)

const calculateReadingTime = (
  res: { start_time: string; time_length: number }[]
) => {
  res.map((item) => {
    item.start_time = item.start_time.split(' ')[1]
  })
  res.map((item) => {
    let [hour, minute, second] = item.start_time.split(':').map(Number)
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

const cells: Ref<HTMLBaseElement[]> = ref([])
const colors: Ref<chroma.Color[]> = ref([])

function chroma_scale(length: number, chroma_scale: any) {
  const scale = d3.scaleLinear([1, length], [0, 1])
  const colors = []
  for (var i = 1; i <= length; i++) {
    const value = scale(i)
    colors.push(chroma_scale(value))
  }

  return colors
}

onMounted(async () => {
  colors.value = chroma_scale(16, chroma.scale('BuGn'))
  calculateReadingTime(await getReadingTime())
  finished.value = true
  const max = Math.max(...time.value.map((item) => Math.max(...item)))

  cells.value.forEach((cell, index) => {
    const x = Math.floor(index / 60)
    const y = index % 60
    cell.setAttribute('style', getCellStyle(time.value[x][y] / max))
  })
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
