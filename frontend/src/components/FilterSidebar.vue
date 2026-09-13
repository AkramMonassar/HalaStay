<script setup>
const props = defineProps({ filters: Object })
const emit = defineEmits(['update'])

const stayTypes = [
  { key: 'room', label: 'غرف' },
  { key: 'apartment', label: 'شقق' },
  { key: 'suite', label: 'أجنحة' },
  { key: 'hall', label: 'قاعات' },
]
const stars = [5, 4, 3, 2, 1]

function toggleStay(key) {
  const list = [...(props.filters.stay_type || [])]
  const i = list.indexOf(key)
  i >= 0 ? list.splice(i, 1) : list.push(key)
  emit('update', { ...props.filters, stay_type: list })
}

function toggleStar(s) {
  const list = [...(props.filters.star_rating || [])]
  const i = list.indexOf(String(s))
  i >= 0 ? list.splice(i, 1) : list.push(String(s))
  emit('update', { ...props.filters, star_rating: list })
}

function setMinReview(v) {
  emit('update', { ...props.filters, min_review: v || undefined })
}
</script>

<template>
  <div class="card shadow-sm">
    <div class="card-body">
      <h6 class="mb-3">تصفية النتائج</h6>

      <div class="mb-3">
        <div class="form-label fw-semibold">نوع الإقامة</div>
        <div v-for="t in stayTypes" :key="t.key" class="form-check">
          <input
            class="form-check-input" type="checkbox" :id="'stay-' + t.key"
            :checked="(filters.stay_type || []).includes(t.key)"
            @change="toggleStay(t.key)"
          />
          <label class="form-check-label" :for="'stay-' + t.key">{{ t.label }}</label>
        </div>
      </div>

      <div class="mb-3">
        <div class="form-label fw-semibold">التصنيف</div>
        <div v-for="s in stars" :key="s" class="form-check">
          <input
            class="form-check-input" type="checkbox" :id="'star-' + s"
            :checked="(filters.star_rating || []).includes(String(s))"
            @change="toggleStar(s)"
          />
          <label class="form-check-label" :for="'star-' + s">{{ s }} نجوم</label>
        </div>
      </div>

      <div class="mb-2">
        <label class="form-label fw-semibold">أدنى تقييم</label>
        <select class="form-select" :value="filters.min_review || ''" @change="setMinReview($event.target.value)">
          <option value="">الكل</option>
          <option value="7">7+</option>
          <option value="8">8+</option>
          <option value="9">9+</option>
        </select>
      </div>
    </div>
  </div>
</template>