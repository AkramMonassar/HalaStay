<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const props = defineProps({ hotel: Object })
const route = useRoute()

const storageBase = (import.meta.env.VITE_API_BASE_URL || '').replace('/api/v1', '') + '/storage/'

const cover = computed(() => {
  if (props.hotel.cover_image) return storageBase + props.hotel.cover_image
  const img = props.hotel.images?.find((i) => i.is_cover) || props.hotel.images?.[0]
  return img ? storageBase + img.image_path : null
})

const minPrice = computed(() => {
  const prices = (props.hotel.available_types || []).map((t) => Number(t.base_price))
  return prices.length ? Math.min(...prices) : null
})

/** نحمل تواريخ البحث ومعاييره معنا إلى صفحة الفندق */
const carryQuery = computed(() => {
  const q = {}
  ;['check_in', 'check_out', 'adults', 'children', 'rooms'].forEach((k) => {
    if (route.query[k]) q[k] = route.query[k]
  })
  return q
})
</script>

<template>
  <div class="card shadow-sm h-100">
    <div class="bg-light d-flex align-items-center justify-content-center" style="height: 170px">
      <img v-if="cover" :src="cover" class="w-100 h-100" style="object-fit: cover" alt="" />
      <span v-else class="fs-1">🏨</span>
    </div>
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-start">
        <h6 class="mb-1">{{ hotel.name }}</h6>
        <span class="badge bg-warning text-dark">{{ '★'.repeat(hotel.star_rating) }}</span>
      </div>
      <div class="text-muted small mb-2">{{ hotel.city }}</div>
      <div class="d-flex justify-content-between align-items-center">
        <span class="badge bg-success-subtle text-success-emphasis">تقييم {{ hotel.review_score }}</span>
        <span v-if="minPrice" class="fw-bold text-primary">{{ minPrice }} ريال / ليلة</span>
      </div>
      <div class="small text-muted mt-1">{{ hotel.available_types?.length || 0 }} أنواع متاحة</div>
    </div>
    <div class="card-footer bg-white">
      <router-link
        :to="{ name: 'hotel', params: { id: hotel.id }, query: carryQuery }"
        class="btn btn-outline-primary btn-sm w-100"
      >
        عرض التفاصيل
      </router-link>
    </div>
  </div>
</template>