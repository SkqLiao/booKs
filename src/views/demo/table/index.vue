<script setup lang="ts">
import { NButton } from 'naive-ui'
import { CrudModal, CrudTable, QueryBarItem, useCRUD } from '@zclzone/crud'
import api from './api'
import { h, ref, onMounted } from 'vue'
import { formatDate, isNullOrUndef, renderIcon } from '@/utils'

const $table = ref<any>(null)
/** QueryBar筛选参数（可选） */
const queryItems = ref<any>({})
/** 补充参数（可选） */
const extraParams = ref<any>({})

const {
  modalVisible,
  modalAction,
  modalTitle,
  modalLoading,
  handleAdd,
  handleDelete,
  handleEdit,
  handleView,
  handleSave,
  modalForm,
  modalFormRef
} = useCRUD({
  name: '文章',
  initForm: { author: '大脸怪' },
  doCreate: api.addPost,
  doDelete: api.deletePost,
  doUpdate: api.updatePost,
  refresh: () => $table.value?.handleSearch()
})

const columns: any = [
  { type: 'selection', fixed: 'left' },
  {
    title: '日期',
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
    title: '开始页数',
    key: 'startPage',
    width: 150,
    render(row: any) {
      return h('span', row.start_page)
    }
  },
  {
    title: '截止页数',
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
            onClick: () => handleDelete(row.id)
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

onMounted(() => {
  $table.value?.handleSearch()
})

const params = {
  table: 'reading_record',
  fields: ['*'],
  conditions: ['book_id=168'],
  order_by: 'date DESC'
}
</script>

<template>
  <CommonPage show-footer title="文章">
    <template #action>
      <div>
        <NButton type="primary" secondary @click="$table?.handleExport()">
          <TheIcon icon="mdi:download" :size="18" class="mr-5" /> 导出
        </NButton>
        <NButton type="primary" class="ml-16" @click="handleAdd">
          <TheIcon icon="material-symbols:add" :size="18" class="mr-5" />
          新建文章
        </NButton>
      </div>
    </template>

    <CrudTable
      ref="$table"
      v-model:query-items="queryItems"
      :extra-params="extraParams"
      :scroll-x="1200"
      :columns="columns"
      :get-data="
        () => {
          return api.getPosts(params)
        }
      "
      @on-checked="onChecked"
      :isPagination="false"
    >
      <template #queryBar>
        <QueryBarItem label="标题" :label-width="50">
          <n-input
            v-model:value="queryItems.title"
            type="text"
            placeholder="请输入标题"
            @keydown.enter="$table?.handleSearch"
          />
        </QueryBarItem>
      </template>
    </CrudTable>
    <!-- 新增/编辑/查看 -->
    <CrudModal
      v-model:visible="modalVisible"
      :title="modalTitle"
      :loading="modalLoading"
      :show-footer="modalAction !== 'view'"
      @on-save="handleSave"
    >
      <n-form
        ref="modalFormRef"
        label-placement="left"
        label-align="left"
        :label-width="80"
        :model="modalForm"
        :disabled="modalAction === 'view'"
      >
        <n-form-item label="日期" path="date">
          <n-input v-model:value="modalForm.date" disabled />
        </n-form-item>
        <n-form-item
          label="时长（分钟）"
          path="timeLength"
          :rule="{
            required: true,
            message: '请输入时长',
            trigger: ['input', 'blur']
          }"
        >
          <n-input
            v-model:value="modalForm.time_length"
            placeholder="请输入文章标题"
          />
        </n-form-item>
        <n-form-item
          label="开始页数"
          path="startPage"
          :rule="{
            required: true,
            message: '请输入开始页数',
            trigger: ['input', 'blur']
          }"
        >
          <n-input-number
            v-model:value="modalForm.start_page"
            placeholder="请输入开始页数"
          />
        </n-form-item>
        <n-form-item
          label="结束页数"
          path="endPage"
          :rule="{
            required: true,
            message: '请输入结束页数',
            trigger: ['input', 'blur']
          }"
        >
          <n-input-number
            v-model:value="modalForm.end_page"
            placeholder="请输入结束页数"
          />
        </n-form-item>
      </n-form>
    </CrudModal>
  </CommonPage>
</template>
