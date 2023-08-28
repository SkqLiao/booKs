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
import { IRecord, IDataType } from '@/service/read/types'
import { readingInfoRequest, bookInfoRequest } from '@/service/read/read'

const lastYear = ref(0)
const attributes = ref()

const updateView = async (pages: { month: number; year: number }[]) => {
  const year = pages[0].year
  if (year === lastYear.value) {
    return
  }
  lastYear.value = year
  const readingInfo = (await getInfo(readingInfoRequest, {
    year: year
  })) as IRecord[]
  const ids = new Set(readingInfo.map((item) => item.book_id))
  const titles = new Map<number, string>()
  for (const id of ids) {
    const title = (await getInfo(bookInfoRequest, {
      id: id,
      columns: ['title']
    })) as { title: string }
    titles.set(id, title['title'])
  }
  const titleInfo = readingInfo.map((info) => {
    return titles.get(info.book_id) as string
  })
  attributes.value = readingInfo.map((item: IRecord, index: number) => {
    return {
      bar: numberToColor(item.book_id),
      dates: [new Date(item.start_time)],
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
}

async function getInfo(
  func: (params: object) => Promise<IDataType>,
  params: object
) {
  try {
    const response = await func(params)
    if (response.code !== 200) {
      console.log(response.response)
      throw new Error(response.response)
    }
    //console.log(response.data)
    return response.data
  } catch (error) {
    console.log(error)
    throw new Error(error as string)
  }
}

const numberToColor = (id: number) => {
  const colors = [
    'gray',
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
  return colors[id % colors.length]
}
</script>

<style scope></style>
