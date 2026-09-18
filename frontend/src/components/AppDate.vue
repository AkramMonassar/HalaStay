<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  modelValue: { type: String, default: '' },
  label: { type: String, default: 'التاريخ' },
})
const emit = defineEmits(['update:modelValue'])

const monthNames = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر']
const dayNames = ['ح', 'ن', 'ث', 'ر', 'خ', 'ج', 'س']

const today = new Date()
const view = ref({
  y: props.modelValue ? Number(props.modelValue.slice(0, 4)) : today.getFullYear(),
  m: props.modelValue ? Number(props.modelValue.slice(5, 7)) - 1 : today.getMonth(),
})

const cells = computed(() => {
  const first = new Date(view.value.y, view.value.m, 1)
  const days = new Date(view.value.y, view.value.m + 1, 0).getDate()
  const lead = first.getDay()
  const arr = Array(lead).fill(null)
  for (let d = 1; d <= days; d++) arr.push(d)
  return arr
})

const selectedLabel = computed(() => {
  if (!props.modelValue) return 'لم يُحدد'
  const [y, m, d] = props.modelValue.split('-').map(Number)
  return `${d} ${monthNames[m - 1]} ${y}`
})

function iso(d) {
  return `${view.value.y}-${String(view.value.m + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`
}

function pick(d) {
  emit('update:modelValue', iso(d))
}

function shift(delta) {
  let m = view.value.m + delta
  let y = view.value.y
  if (m < 0) { m = 11; y-- }
  if (m > 11) { m = 0; y++ }
  view.value = { y, m }
}
</script>

<template>
  <div class="app-date">
    <label class="form-label mb-1">{{ label }}</label>
    <div class="d-flex justify-content-between align-items-center mb-2">
      <button type="button" class="btn btn-sm btn-light" @click="shift(-1)">›</button>
      <div class="fw-semibold small">{{ monthNames[view.m] }} {{ view.y }}</div>
      <button type="button" class="btn btn-sm btn-light" @click="shift(1)">‹</button>
    </div>
    <div class="date-grid">
      <div v-for="(d, i) in dayNames" :key="'n' + i" class="date-cell head">{{ d }}</div>
      <template v-for="(c, i) in cells" :key="i">
        <div v-if="c === null" class="date-cell blank"></div>
        <button v-else type="button" class="date-cell" :class="{ selected: iso(c) === modelValue }" @click="pick(c)">
          {{ c }}
        </button>
      </template>
    </div>
    <div class="small text-muted mt-2">المحدد: {{ selectedLabel }}</div>
  </div>
</template>

<style lang="scss" scoped>
.app-date { border: 1px solid #dee2e6; border-radius: 12px; padding: 0.75rem; background: #fff; }
.date-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 3px; }
.date-cell { height: 30px; display: flex; align-items: center; justify-content: center; border: none; background: transparent; border-radius: 8px; cursor: pointer; font-weight: 500; font-size: 0.85rem; }
.date-cell:hover { background: #e9f7ef; }
.date-cell.head { color: #6c757d; font-size: 0.75rem; cursor: default; }
.date-cell.blank { cursor: default; }
.date-cell.selected { background: #006c35; color: #fff; }
</style>