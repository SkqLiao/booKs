<template>
  <n-grid cols="5" :x-gap="12">
    <n-grid-item :style="{ marginTop: '50px' }">
      <n-card>
        <p class="book-info-p"><strong>页数：</strong> {{ total_page }} 页</p>
        <p class="book-info-p"><strong>天数：</strong> {{ total_day }} 天</p>
        <p class="book-info-p">
          <strong>时间：</strong> {{ Math.floor(total_time / 60) }} 小时
          {{ total_time % 60 }} 分钟
        </p>
        <p class="book-info-p">
          <strong>摘录：</strong> {{ total_excerpt }} 条
        </p>
        <template #footer>
          <n-button
            class="full-width-button"
            type="info"
            strong
            tertiary
            @click="
              () => {
                showAddModel = true
                addType = 1
              }
            "
            >增加记录</n-button
          >
          <n-button
            class="full-width-button"
            type="success"
            strong
            tertiary
            @click="
              () => {
                showAddModel = true
                addType = 2
              }
            "
            >增加摘录</n-button
          >
        </template>
      </n-card>
    </n-grid-item>
    <n-grid-item :span="4">
      <div id="main" style="width: 100%; height: 375px"></div>
    </n-grid-item>
  </n-grid>
  <addRecord
    :type="addType"
    :show-modal="showAddModel"
    :id="props.bookid"
    @update-add-visible="reload"
  />
  <CrudTable
    ref="$table"
    v-model:query-items="queryItems"
    :extra-params="extraParams"
    :scroll-x="1200"
    :columns="columns"
    :get-data="api.getPosts"
    @on-checked="onChecked"
    :isPagination="false"
  >
  </CrudTable>
  <!-- 新增/编辑/查看 -->
  <CrudModal
    v-model:visible="modalVisible"
    :title="modalTitle"
    :loading="modalLoading"
    :show-footer="modalAction !== 'view'"
    @on-save="handleSave"
    style="width: 400px"
  >
    <n-form
      ref="modalFormRef"
      label-placement="left"
      label-align="left"
      :label-width="120"
      :model="modalForm"
      :disabled="modalAction === 'view'"
    >
      <n-form-item label="日期" path="date">
        <n-date-picker type="date" v-model:formatted-value="modalForm.date" />
      </n-form-item>
      <n-form-item label="时长（分钟）" path="timeLength">
        <n-input-number
          v-model:value="modalForm.time_length"
          placeholder="请输入时长"
        />
      </n-form-item>
      <n-form-item label="开始页数" path="startPage">
        <n-input-number
          v-model:value="modalForm.start_page"
          placeholder="请输入开始页数"
        />
      </n-form-item>
      <n-form-item label="结束页数" path="endPage">
        <n-input-number
          v-model:value="modalForm.end_page"
          placeholder="请输入结束页数"
        />
      </n-form-item>
    </n-form>
  </CrudModal>
</template>

<script setup lang="ts">
import { onMounted, ref, h } from 'vue'
import { getRequest } from '@/service/book/book'
import { getInfo } from '@/service/read/read'
import addRecord from './addRecord.vue'
import { IRecord } from '@/service/read/types'
import * as echarts from 'echarts'

const props = defineProps({
  bookid: {
    type: Number,
    required: true
  }
})

const records = ref<IRecord[]>()
const total_page = ref(0)
const total_time = ref(0)
const total_day = ref(0)
const total_excerpt = ref(0)
const myChart = ref<any>()
const emits = defineEmits(['updatePagePercent'])

const load = async () => {
  const response = (await getInfo(getRequest, {
    table: 'reading_record',
    fields: ['*'],
    conditions: ['book_id = ' + props.bookid]
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
  total_page.value = records.value?.[records.value.length - 1].end_page || 0
  total_time.value = sum
  total_day.value = new Set(records.value?.map((item) => item.date)).size
  emits('updatePagePercent', total_page.value)
  const chartDom = document.getElementById('main')!
  if (myChart.value) {
    myChart.value.dispose()
  }
  myChart.value = echarts.init(chartDom)

  const option = {
    // tooltip: {
    //   trigger: 'axis',
    //   // formatter: function (params: any[]) {
    //   //   var dataIndex = params[0].dataIndex
    //   //   var timeValue = records.value![dataIndex].time_length
    //   //   var pageValue =
    //   //     records.value![dataIndex].end_page -
    //   //     records.value![dataIndex].start_page
    //   //   return timeValue + '分钟：' + pageValue + '页'
    //   // }
    // },
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
        yAxisIndex: 0,
        label: {
          show: true,
          formatter: '{c} 页',
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
          formatter: '{c} 分钟',
        }
      }
    ]
  } as echarts.EChartsOption

  option && myChart.value.setOption(option)
}

onMounted(async () => {
  await reload()
})

const reload = async () => {
  showAddModel.value = false
  $table.value?.handleSearch()
  await load()
}

const showAddModel = ref(false)
const addType = ref(1)

import { NButton } from 'naive-ui'
import { CrudModal, CrudTable, useCRUD } from '@zclzone/crud'
import api from './api'
import { formatDate, renderIcon } from '@/utils'

const $table = ref<any>(null)

const queryItems = ref<any>({
  table: 'reading_record',
  fields: ['*'],
  conditions: ['book_id=' + props.bookid]
})

const extraParams = ref<any>({
  order_by: 'date DESC'
})

const {
  modalVisible,
  modalAction,
  modalTitle,
  modalLoading,
  handleDelete,
  handleEdit,
  handleView,
  handleSave,
  modalForm,
  modalFormRef
} = useCRUD({
  name: '阅读记录',
  doCreate: api.addPost,
  doDelete: api.deletePost,
  doUpdate: api.updatePost,
  refresh: async () => await reload()
})

const columns: any = [
  { type: 'selection', fixed: 'left' },
  {
    title: '#',
    key: 'id',
    width: 80,
    render(row: any) {
      return h('span', row.id)
    }
  },
  {
    title: '阅读日期',
    key: 'date',
    width: 150,
    render(row: any) {
      return h('span', formatDate(row.date))
    }
  },
  {
    title: '时长（分钟）',
    key: 'timeLength',
    width: 150,
    render(row: any) {
      return h('span', row.time_length)
    }
  },
  {
    title: '结束页码',
    key: 'startPage',
    width: 150,
    render(row: any) {
      return h('span', row.start_page)
    }
  },
  {
    title: '结束页码',
    key: 'endPage',
    width: 150,
    render(row: any) {
      return h('span', row.end_page)
    }
  },
  {
    title: '操作',
    key: 'actions',
    width: 240,
    align: 'center',
    fixed: 'right',
    hideInExcel: true,
    render(row: any) {
      return [
        h(
          NButton,
          {
            size: 'small',
            type: 'primary',
            secondary: true,
            onClick: () => handleView(row)
          },
          {
            default: () => '查看',
            icon: renderIcon('majesticons:eye-line', { size: 14 })
          }
        ),
        h(
          NButton,
          {
            size: 'small',
            type: 'primary',
            style: 'margin-left: 15px;',
            onClick: () => handleEdit(row)
          },
          {
            default: () => '编辑',
            icon: renderIcon('material-symbols:edit-outline', { size: 14 })
          }
        ),

        h(
          NButton,
          {
            size: 'small',
            type: 'error',
            style: 'margin-left: 15px;',
            onClick: async () => handleDelete(row.id)
          },
          {
            default: () => '删除',
            icon: renderIcon('material-symbols:delete-outline', { size: 14 })
          }
        )
      ]
    }
  }
]

// 选中事件
function onChecked(rowKeys: string[]) {
  if (rowKeys.length) window.$message?.info(`选中${rowKeys.join(' ')}`)
}
</script>

<style scoped>
.book-info-p {
  font-size: 15px;
  padding-bottom: 10px;
}
.full-width-button {
  width: 100%; /* 让按钮宽度占满父元素 */
}
</style>
