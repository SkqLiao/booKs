<template>
  <n-drawer
    v-model:show="localShowDrawer"
    width="50%"
    placement="right"
    :on-update:show="() => $emit('updateDrawerVisible')"
    :on-after-enter="async () => getBookIds()"
  >
    <n-drawer-content :title="props.date + '阅读数据'">
      共进行了 {{ book_ids.length }} 本书的阅读：
      <status-panel :book_ids="book_ids" />
    </n-drawer-content>
  </n-drawer>
</template>

<script setup lang="ts">
import { ref, watch, Ref } from 'vue'
import { getInfo, getRequest } from '@/service/book/book'
import statusPanel from '@/views/common/panel.vue'

const props = defineProps({
  showDrawer: {
    type: Boolean,
    default: false
  },
  date: {
    type: String,
    required: true
  }
})
const localShowDrawer = ref(false)

watch(
  () => props.showDrawer,
  (val) => {
    localShowDrawer.value = val
  }
)

const book_ids: Ref<number[]> = ref([])

const getBookIds = async () => {
  const year = props.date.split('年')[0]
  const month = props.date.split('年')[1].split('月')[0]
  const data = (await getInfo(getRequest, {
    table: 'reading_record',
    fields: ['DISTINCT book_id'],
    conditions: [`YEAR(start_time) = ${year} AND MONTH(start_time) = ${month}`]
  })) as { book_id: number }[]
  book_ids.value = data.map((item) => item.book_id)
}
</script>

<style scoped></style>
