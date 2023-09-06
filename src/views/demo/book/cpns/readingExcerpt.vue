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
    style="width: 1200px"
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
      <n-form-item label="页数" path="p/age">
        <n-input-number
          v-model:value="modalForm.page"
          placeholder="请输入页数"
        />
      </n-form-item>
      <n-form-item label="摘录" path="excerpt">
        <n-input
          v-model:value="modalForm.excerpt"
          placeholder="请输入摘录内容"
          type="textarea"
        />
      </n-form-item>
      <n-form-item label="心得" path="thoughts">
        <n-input
          v-model:value="modalForm.thoughts"
          placeholder="请输入心得"
          type="textarea"
        />
      </n-form-item>
    </n-form>
  </CrudModal>
</template>

<script lang="ts" setup>
import { h } from 'vue'
import { NButton } from 'naive-ui'
import { CrudModal, CrudTable, useCRUD } from '@zclzone/crud'
import { formatDate, renderIcon } from '@/utils'

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
  table: 'reading_excerpt',
  fields: ['*'],
  conditions: ['book_id=' + props.bookid]
})

const extraParams = ref<any>({
  order_by: 'date DESC'
})

import { request2 } from '@/utils'

const getPosts = (params = {}) => request2.get('/get.php', { params })
const updatePost = (data: any) => request2.post(`/excerpt/update.php`, data)
const deletePost = (id: any) => request2.post(`/excerpt/delete.php`, { id: id })

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
  name: '笔记摘录',
  doDelete: deletePost,
  doUpdate: updatePost,
  refresh: refresh
})

function refresh() {
  $table.value?.handleSearch()
}

const columns: any = [
  { type: 'selection', fixed: 'left' },
  {
    title: '#',
    key: 'id',
    width: 50,
    render(row: any) {
      return h('span', row.id)
    }
  },
  {
    title: '摘录日期',
    key: 'date',
    width: 100,
    render(row: any) {
      return h('span', formatDate(row.date))
    }
  },
  {
    title: '页数',
    key: 'timeLength',
    width: 80,
    render(row: any) {
      return h('span', row.page)
    }
  },
  {
    title: '摘录',
    key: 'content',
    width: 250,
    render(row: any) {
      const maxLength = 150 // 最大字符数
      const text = (row.excerpt ?? '') as string

      if (text.length <= maxLength) {
        return h('span', text)
      } else {
        const truncatedText = text.slice(0, maxLength) + '...'
        return h('span', truncatedText)
      }
    }
  },
  {
    title: '笔记',
    key: 'content',
    width: 250,
    render(row: any) {
      const maxLength = 150 // 最大字符数
      const text = (row.thoughts ?? '') as string

      if (text.length <= maxLength) {
        return h('span', text)
      } else {
        const truncatedText = text.slice(0, maxLength) + '...'
        return h('span', truncatedText)
      }
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
