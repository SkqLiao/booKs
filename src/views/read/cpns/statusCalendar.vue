<template>
  <CommonPage :show-footer="true">
    <VCalendar
      show-weeknumbers
      :attributes="attributes"
      :columns="4"
      :rows="3"
      expanded
      :initial-page="{ month: 1, year: new Date().getFullYear() }"
      @update:pages="updateView"
    >
    </VCalendar>
  </CommonPage>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { IRecord } from '@/service/book/types'
import { getRequest } from '@/service/book/book'
import { getInfo } from '@/service/book/book'

const lastYear = ref(0)
const attributes = ref()

interface DateInterval {
  start: Date
  end: Date
}

function getIntervals(dates: Date[]): DateInterval[] {
  if (dates.length === 0) {
    return []
  }
  const sortedDates = dates.sort((a, b) => a.getTime() - b.getTime())
  const intervals: DateInterval[] = [
    { start: sortedDates[0], end: sortedDates[0] }
  ]

  for (let i = 1; i < sortedDates.length; i++) {
    const currentInterval = intervals[intervals.length - 1]
    const currentDate = sortedDates[i]
    if (currentDate.getTime() - currentInterval.end.getTime() <= 86400000) {
      intervals[intervals.length - 1].end = currentDate
    } else {
      intervals.push({ start: currentDate, end: currentDate })
    }
  }

  return intervals
}

const updateView = async (pages: { month: number; year: number }[]) => {
  const year = pages[0].year
  if (year === lastYear.value) {
    return
  }
  lastYear.value = year
  const readingInfo = (await getInfo(getRequest, {
    table: 'reading_record',
    fields: ['*'],
    conditions: ['YEAR(date) = ' + year]
  })) as IRecord[]
  const ids = new Set(readingInfo.map((item) => item.book_id))
  const titles = new Map<number, string>()
  for (const id of ids) {
    const response = await getRequest({
      table: 'basic_info',
      fields: ['title'],
      conditions: ['id=' + id]
    })
    titles.set(id, response.data[0]!.title)
  }
  const titleInfo = readingInfo.map((info) => {
    return titles.get(info.book_id) as string
  })
  const dates = readingInfo.map((item) => new Date(item.date))
  const intervals = getIntervals(dates)

  const colors = [
    'red',
    'orange',
    'yellow',
    'green',
    'teal',
    'blue',
    'indigo',
    'purple',
    'pink'
  ]

  const getColor = (id: number) => {
    const len = [1, 3, 7, 14, 30, 60, 90, 180, 365]
    const index = len.findIndex((item) => item >= id)
    return colors[index]
  }

  attributes.value = readingInfo.map((item: IRecord, index: number) => {
    return {
      dot: colors[item.book_id % 9],
      dates: [new Date(item.date)],
      popover: {
        label:
          titleInfo[index] +
          '：' +
          item.start_page +
          '-' +
          item.end_page +
          '页',
        visibility: 'hover'
      }
    }
  })
  intervals.forEach((element) => {
    attributes.value.push({
      highlight: getColor(
        (element.end.getTime() - element.start.getTime()) / 86400000
      ),
      dates: [[element.start, element.end]]
    })
  })
}
</script>

<style scope></style>
