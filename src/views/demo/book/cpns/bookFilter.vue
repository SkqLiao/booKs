<template>
  <n-modal
    v-model:show="showFilterModal"
    class="custom-card"
    preset="card"
    style="width: 30%"
    size="huge"
    title="筛选书籍"
    :bordered="false"
    :on-after-leave="() => $emit('updateFilterVisible')"
  >
    <n-form>
      <n-form-item label="标题">
        <n-input v-model:value="inputTitle" placeholder="请输出标题" />
      </n-form-item>
      <n-form-item label="出版社">
        <n-select
          v-model:value="selectedPublish"
          :options="publishOptions"
          multiple
          filterable
          clearable
          placeholder="请选择出版社"
        >
        </n-select>
      </n-form-item>

      <n-form-item label="出品方">
        <n-select
          v-model:value="selectedProduce"
          :options="produceOptions"
          multiple
          filterable
          clearable
          placeholder="请选择出品方"
        >
        </n-select>
      </n-form-item>

      <n-form-item label="丛书">
        <n-select
          v-model:value="selectedSeries"
          :options="seriesOptions"
          multiple
          filterable
          clearable
          placeholder="请选择丛书"
        >
        </n-select>
      </n-form-item>

      <n-form-item label="购买日期">
        <n-date-picker
          v-model:formatted-value="selectedDateRange"
          type="daterange"
          format="yyyy-MM-dd"
          range-separator="至"
          start-placeholder="开始日期"
          end-placeholder="结束日期"
          style="width: 100%"
        />
      </n-form-item>

      <n-form-item label="购买价格">
        <div class="price-range">
          <n-input-number v-model:value="priceRange[0]" placeholder="价格下限">
            <template #prefix> ￥ </template>
          </n-input-number>
          <span> — </span>

          <n-input-number
            v-model:value="priceRange[1]"
            :validator="
              (x: number) => {
                return priceRange.length > 0 && x >= priceRange[0]
              }
            "
            placeholder="价格上限"
            ><template #prefix> ￥ </template>
          </n-input-number>
        </div>
      </n-form-item>

      <n-form-item label="购买平台">
        <n-select
          v-model:value="selectedBuyPos"
          :options="buyPosOptions"
          multiple
          filterable
          clearable
          placeholder="请选择购买平台"
        >
        </n-select>
      </n-form-item>

      <n-button type="primary" @click="applyFilter">确定</n-button>
      <n-button @click="clearFilter">重置</n-button>
    </n-form>
  </n-modal>
</template>

<script setup lang="ts">
import { ref, Ref, watch, onMounted } from 'vue'
import { getRequest } from '@/service/book/book'
import eventBus from '@/eventbus/index'
import { useBookStore, BookParams } from '@/store'

const bookStore = useBookStore()

const props = defineProps({
  showFilterModal: {
    type: Boolean,
    required: true
  }
})

const emits = defineEmits(['updateFilterVisible'])

const showFilterModal = ref(false)
watch(
  () => props.showFilterModal,
  (newVal, oldVal) => {
    if (newVal !== oldVal) {
      showFilterModal.value = newVal
    }
  }
)

const selectedPublish: Ref<string[]> = ref([])
const selectedSeries: Ref<string[]> = ref([])
const selectedProduce: Ref<string[]> = ref([])
const selectedBuyPos: Ref<string[]> = ref([])
const selectedDateRange: Ref<[string, string] | undefined> = ref(undefined)
const priceRange = ref<number[]>([])
const seriesOptions = ref<{ label: string; value: string }[]>([])
const publishOptions = ref<{ label: string; value: string }[]>([])
const produceOptions = ref<{ label: string; value: string }[]>([])
const buyPosOptions = ref<{ label: string; value: string }[]>([])
const inputTitle = ref('')

const getInfo = async (group_by: string) => {
  try {
    const response = await getRequest({
      table: 'basic_info',
      fields: [group_by + ' AS field', 'COUNT(*) AS count'],
      group_by: group_by
    })
    if (response.code != 200) {
      console.log(group_by, 'Error fetching options')
      console.log(response?.error)
      console.log(response?.sql)
    } else {
      let info = response.data
        .filter((item) => item.field != '')
        .map((item) => ({
          label: item.field + '(' + item.count + ')',
          value: item.field
        }))
      info.sort((a, b) => a.value.localeCompare(b.value))
      return info
    }
  } catch (error) {
    console.error(group_by, 'Error fetching options', error)
  }
  return []
}

const applyFilter = () => {
  const filters = {} as BookParams
  if (selectedPublish.value.length > 0) {
    filters.publish = selectedPublish.value
  }
  if (selectedSeries.value.length > 0) {
    filters.series = selectedSeries.value
  }
  if (selectedProduce.value.length > 0) {
    filters.producer = selectedProduce.value
  }
  if (selectedDateRange.value) {
    filters.buy_date_from = selectedDateRange.value[0]
    filters.buy_date_to = selectedDateRange.value[1]
  }
  if (priceRange.value.length == 2) {
    filters.buy_price_from = priceRange.value[0]
    filters.buy_price_to = priceRange.value[1]
  }
  if (selectedBuyPos.value.length > 0) {
    filters.buy_pos = selectedBuyPos.value
  }
  if (inputTitle.value.length > 0) {
    filters.title = inputTitle.value
  }
  bookStore.setParams(filters)
  eventBus.emit('updateFilter')
  emits('updateFilterVisible')
}

const clearFilter = () => {
  selectedPublish.value = []
  selectedSeries.value = []
  selectedProduce.value = []
  selectedDateRange.value = undefined
  priceRange.value = []
  selectedBuyPos.value = []
}

onMounted(async () => {
  publishOptions.value = await getInfo('publish')
  produceOptions.value = await getInfo('producer')
  seriesOptions.value = await getInfo('series')
  buyPosOptions.value = await getInfo('buy_pos')
  selectedPublish.value = bookStore.getParams?.publish ?? []
  selectedSeries.value = bookStore.getParams?.series ?? []
  selectedProduce.value = bookStore.getParams?.producer ?? []
  selectedDateRange.value = bookStore.getParams?.buy_date_from
    ? [bookStore.getParams.buy_date_from, bookStore.getParams.buy_date_to ?? '']
    : undefined
  priceRange.value =
    bookStore.getParams?.buy_price_from && bookStore.getParams?.buy_price_to
      ? [bookStore.getParams.buy_price_from, bookStore.getParams.buy_price_to]
      : []
  selectedBuyPos.value = bookStore.getParams?.buy_pos ?? []
  inputTitle.value = bookStore.getParams?.title ?? ''
})
</script>

<style scoped>
.price-range {
  display: flex;
  align-items: center;
  width: 100%;
  justify-content: space-between;
}
</style>
