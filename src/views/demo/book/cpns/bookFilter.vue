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
      <n-form-item label="出版社">
        <n-select
          v-model:value="selectedPublish"
          :options="publishOptions"
          multiple
          clearable
        >
        </n-select>
      </n-form-item>

      <n-form-item label="出版方">
        <n-select
          v-model:value="selectedProduce"
          :options="produceOptions"
          multiple
          clearable
        >
        </n-select>
      </n-form-item>

      <n-form-item label="丛书">
        <n-select
          v-model:value="selectedSeries"
          :options="seriesOptions"
          multiple
          clearable
        >
        </n-select>
      </n-form-item>

      <n-form-item label="购买日期">
        <n-date-picker
          v-model:value="selectedDateRange"
          type="daterange"
          format="yyyy-MM-dd"
          range-separator="至"
          start-placeholder="开始日期"
          end-placeholder="结束日期"
        />
      </n-form-item>

      <n-form-item label="购买价格区间">
        <div class="price-range">
          <n-input-number v-model:value="priceRange[0]" />
          <span> - </span>
          <n-input-number
            v-model:value="priceRange[1]"
            :validator="
              (x: number) => {
                return x >= priceRange[0]
              }
            "
          />
        </div>
      </n-form-item>

      <n-button type="primary" @click="applyFilter">确定</n-button>
      <n-button @click="clearFilter">重置</n-button>
    </n-form>
  </n-modal>
</template>

<script setup lang="ts">
import { ref, Ref, watch, onMounted } from 'vue'
import { bookParamRequest } from '@/service/book/book'
import { IbookParams } from '@/service/book/types'
import eventBus from '@/eventbus/index'
import { useBookStore } from '@/store'

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
const selectedDateRange = ref<[number, number]>([
  Date.parse(bookStore.getParams?.buy_date_from ?? '2000-01-01'),
  Date.parse(bookStore.getParams?.buy_date_to ?? new Date(Date.now())
      .toISOString()
      .slice(0, 10),)
])
const priceRange = ref<[number, number]>([
  bookStore.getParams?.buy_price_from ?? 0,
  bookStore.getParams?.buy_price_to ?? 10000
])
const seriesOptions = ref<{ label: string; value: string }[]>([])
const publishOptions = ref<{ label: string; value: string }[]>([])
const produceOptions = ref<{ label: string; value: string }[]>([])

const getInfo = async (params: string) => {
  try {
    const response = await bookParamRequest(params)
    if (response.code != 200) {
      console.log('Error fetching publish options')
    } else {
      let info = response.data.map((item: IbookParams) => ({
        label: item.value + '(' + item.count + ')',
        value: item.value
      }))
      info.sort((a, b) => a.value.localeCompare(b.value))
      return info
    }
  } catch (error) {
    console.error('Error fetching publish options', error)
  }
  return []
}

const applyFilter = () => {
  const filters = {
    publish: selectedPublish.value,
    series: selectedSeries.value,
    producer: selectedProduce.value,
    buy_date_from: new Date(selectedDateRange.value[0])
      .toISOString()
      .slice(0, 10),
    buy_date_to: new Date(selectedDateRange.value[1])
      .toISOString()
      .slice(0, 10),
    buy_price_from: priceRange.value[0],
    buy_price_to: priceRange.value[1]
  }
  bookStore.setParams(filters)
  eventBus.emit('updateFilter')
  emits('updateFilterVisible')
}

const clearFilter = () => {
  selectedPublish.value = []
  selectedSeries.value = []
  selectedProduce.value = []
  selectedDateRange.value = [Date.parse('2000-01-01'), Date.now()]
  priceRange.value = [0, 10000]
}

eventBus.on('updateFilter', async () => {
  selectedPublish.value = bookStore.getParams?.publish ?? []
  selectedSeries.value = bookStore.getParams?.series ?? []
  selectedProduce.value = bookStore.getParams?.producer ?? []
})

onMounted(async () => {
  publishOptions.value = await getInfo('publish')
  produceOptions.value = await getInfo('producer')
  seriesOptions.value = await getInfo('series')
})
</script>

<style scoped>
.price-range {
  display: flex;
  align-items: center;
}
</style>
