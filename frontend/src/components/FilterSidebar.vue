<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

const stayTypes = ['room', 'apartment', 'suite', 'hall']
const stars = [5, 4, 3, 2, 1]

function asArray(v) {
  if (!v) return []
  return Array.isArray(v) ? v : [v]
}

const selectedTypes = computed(() => asArray(route.query.stay_types))
const selectedStars = computed(() => asArray(route.query.stars).map(Number))
const minRating = computed(() => route.query.min_rating || '')

function push(patch) {
  router.push({ query: { ...route.query, ...patch } })
}

function toggleType(t) {
  const arr = [...selectedTypes.value]
  const i = arr.indexOf(t)
  i >= 0 ? arr.splice(i, 1) : arr.push(t)
  push({ stay_types: arr.length ? arr : undefined })
}

function toggleStar(s) {
  const arr = [...selectedStars.value]
  const i = arr.indexOf(s)
  i >= 0 ? arr.splice(i, 1) : arr.push(s)
  push({ stars: arr.length ? arr : undefined })
}

function setMinRating(v) {
  push({ min_rating: v || undefined })
}
</script>

<template>
  <div class="card shadow-sm">
    <div class="card-header bg-white fw-semibold">{{ $t('search.filters') }}</div>
    <div class="card-body">
      <div class="mb-3">
        <div class="fw-semibold mb-2">{{ $t('search.stayType') }}</div>
        <div v-for="t in stayTypes" :key="t" class="form-check">
          <input :id="'st-' + t" type="checkbox" class="form-check-input" :checked="selectedTypes.includes(t)" @change="toggleType(t)" />
          <label class="form-check-label" :for="'st-' + t">{{ $t('search.stayTypes.' + t) }}</label>
        </div>
      </div>

      <div class="mb-3">
        <div class="fw-semibold mb-2">{{ $t('search.stars') }}</div>
        <div v-for="s in stars" :key="s" class="form-check">
          <input :id="'star-' + s" type="checkbox" class="form-check-input" :checked="selectedStars.includes(s)" @change="toggleStar(s)" />
          <label class="form-check-label" :for="'star-' + s">{{ s }} {{ $t('search.starsWord') }}</label>
        </div>
      </div>

      <div>
        <label class="form-label">{{ $t('search.minRating') }}</label>
        <select :value="minRating" class="form-select" @change="setMinRating($event.target.value)">
          <option value="">{{ $t('search.all') }}</option>
          <option value="7">7+</option>
          <option value="8">8+</option>
          <option value="9">9+</option>
        </select>
      </div>
    </div>
  </div>
</template>