<template>
  <CrudTable
    ref="$table"
    v-model:query-items="queryItems"
    :extra-params="extraParams"
    :scroll-x="1200"
    :columns="columns"
    :get-data="getPosts"
    @on-checked="onChecked"
    :isPagination="false"
  >
  </CrudTable>
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
      <n-form-item label="开始时间" path="start_time">
        <n-date-picker
          v-model:formatted-value="modalForm.start_time"
          value-format="yyyy-MM-dd HH:mm:ss"
          type="datetime"
          clearable
        />
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

<script lang="ts" setup>
import { h, ref, watch, onMounted } from 'vue'
import { NButton } from 'naive-ui'
import { CrudModal, CrudTable, useCRUD } from '@zclzone/crud'
import { renderIcon } from '@/utils'

const props = defineProps({
  bookid: {
    type: Number,
    required: true
  },
  reload: {
    type: Boolean,
    required: true
  }
})

const $table = ref<any>(null)

const queryItems = ref<any>({
  table: 'reading_record',
  fields: ['*'],
  conditions: ['book_id=' + props.bookid]
})

const extraParams = ref<any>({
  order_by: 'start_time ASC'
})

const emits = defineEmits(['updateRecord'])

import { request2 } from '@/utils'

const getPosts = (params = {}) => request2.get('/get.php', { params })
const updatePost = (data: any) => request2.post(`/read/update.php`, data)
const deletePost = (id: any) => request2.post(`/read/delete.php`, { id: id })

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
  doDelete: deletePost,
  doUpdate: updatePost,
  refresh: refresh
})

function refresh() {
  $table.value?.handleSearch()
  emits('updateRecord')
}

const columns: any = [
  { type: 'selection', fixed: 'left' },
  {
    title: '#',
    key: 'id',
    width: 80,
    render(row: any, index: number) {
      return h('span', index)
    }
  },
  {
    title: '开始时间',
    key: 'date',
    width: 150,
    render(row: any) {
      return h('span', row.start_time)
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
    title: '开始页码',
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

watch(
  () => props.reload,
  () => {
    $table.value?.handleSearch()
  }
)

onMounted(() => {
  $table.value?.handleSearch()
})

// 选中事件
function onChecked(rowKeys: string[]) {
  if (rowKeys.length) window.$message?.info(`选中${rowKeys.join(' ')}`)
}
</script>

<style scoped></style>
