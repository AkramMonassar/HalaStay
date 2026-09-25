<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const props = defineProps({ hotel: { type: Object, required: true } })
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

const carryQuery = computed(() => {
  const q = {}
  ;['check_in', 'check_out', 'adults', 'children', 'rooms'].forEach((k) => {
    if (route.query[k]) q[k] = route.query[k]
  })
  return q
})
</script>

<template>
  <div class="hotel-card">
    <div class="hotel-media">
      <img v-if="cover" :src="cover" alt="" loading="lazy" />
      <span v-else class="hotel-placeholder">🏨</span>
      <span class="hotel-stars">{{ '★'.repeat(hotel.star_rating) }}</span>
    </div>
    <div class="hotel-body">
      <div class="d-flex justify-content-between align-items-start gap-2">
        <h6 class="hotel-name mb-1">{{ hotel.name }}</h6>
        <span class="review-pill">{{ hotel.review_score > 0 ? hotel.review_score : 'جديد' }}</span>
      </div>
      <div class="hotel-city">📍 {{ hotel.city }}</div>
      <div class="d-flex justify-content-between align-items-end mt-3">
        <div v-if="minPrice" class="hotel-price">
          {{ minPrice }} <span class="price-unit">ريال / ليلة</span>
        </div>
        <div v-else class="small text-muted">لا أنواع متاحة</div>
        <span class="types-count">{{ hotel.available_types?.length || 0 }} أنواع</span>
      </div>
    </div>
    <div class="hotel-footer">
      <router-link :to="{ name: 'hotel', params: { id: hotel.id }, query: carryQuery }" class="btn-details">
        عرض التفاصيل
      </router-link>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.hotel-card { background: #fff; border: 1px solid #e9ecef; border-radius: 16px; overflow: hidden; transition: all 0.25s ease; height: 100%; display: flex; flex-direction: column; }
.hotel-card:hover { transform: translateY(-4px); box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12); }
.hotel-media { position: relative; height: 180px; background: #f1f3f5; overflow: hidden; }
.hotel-media img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease; }
.hotel-card:hover .hotel-media img { transform: scale(1.06); }
.hotel-placeholder { display: flex; align-items: center; justify-content: center; height: 100%; font-size: 2.5rem; }
.hotel-stars { position: absolute; top: 10px; inset-inline-start: 10px; background: rgba(0, 0, 0, 0.55); color: #ffc107; padding: 2px 10px; border-radius: 20px; font-size: 0.8rem; }
.hotel-body { padding: 1rem 1.1rem 0; flex: 1; }
.hotel-name { font-weight: 700; }
.review-pill { background: #e6f4ea; color: #006c35; font-weight: 700; font-size: 0.8rem; padding: 2px 10px; border-radius: 20px; white-space: nowrap; }
.hotel-city { color: #6c757d; font-size: 0.85rem; }
.hotel-price { font-size: 1.25rem; font-weight: 800; color: #006c35; }
.price-unit { font-size: 0.75rem; font-weight: 500; color: #6c757d; }
.types-count { font-size: 0.75rem; color: #6c757d; }
.hotel-footer { padding: 0.9rem 1.1rem 1.1rem; }
.btn-details { display: block; text-align: center; background: #006c35; color: #fff; border-radius: 10px; padding: 0.55rem 1rem; font-weight: 600; text-decoration: none; transition: background 0.2s ease; }
.btn-details:hover { background: #00552b; color: #fff; }
</style>