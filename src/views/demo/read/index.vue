<template>
  <CommonPage :show-footer="true">
    <VCalendar
      show-weeknumbers
      :attributes="attributes"
      columns="4"
      rows="3"
      expanded
      borderless
      :initial-page="{ month: 1, year: new Date().getFullYear() }"
    />
  </CommonPage>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import 'v-calendar/style.css'

const todos = ref([
  {
    description: 'Take Noah to basketball practice.',
    isComplete: false,
    dates: { weekdays: 6 }, // Every Friday
    color: 'red'
  }
])

const attributes = computed(() => [
  // Attributes for todos
  ...todos.value.map((todo) => ({
    dates: todo.dates,
    dot: {
      color: todo.color,
      class: todo.isComplete ? 'opacity-75' : ''
    },
    popover: {
      label: todo.description
    }
  }))
])

import { useScreens } from 'vue-screen-utils'

const { mapCurrent } = useScreens({
  xs: '0px',
  sm: '640px',
  md: '768px',
  lg: '1024px'
})
const columns = mapCurrent({ lg: 4 }, 12)
</script>

<style scope></style>
