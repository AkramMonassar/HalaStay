<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import api from '../services/api'
import BookingWidget from '../components/BookingWidget.vue'

const route = useRoute()
const hotel = ref(null)
const loading = ref(true)
const storageBase = (import.meta.env.VITE_API_BASE_URL || '').replace('/api/v1', '') + '/storage/'

const gallery = computed(() => (hotel.value?.images || []).map((i) => storageBase + i.image_path))
const galleryCount = computed(() => Math.min(gallery.value.length, 4))

onMounted(async () => {
  try {
    const { data } = await api.get(`/hotels/${route.params.id}`)
    hotel.value = data.data
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div v-if="loading" class="container py-5 text-center">جارِ التحميل...</div>

  <div v-else-if="hotel" class="hotel-page">
    <section class="hotel-head">
      <div class="container">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
          <div>
            <h2 class="mb-1">{{ hotel.name }}</h2>
            <div class="head-meta">📍 {{ hotel.city }} · {{ '★'.repeat(hotel.star_rating) }}</div>
          </div>
          <span v-if="hotel.review_score" class="review-pill-big">{{ hotel.review_score }}</span>
        </div>
      </div>
    </section>

    <section v-if="gallery.length" class="container mt-3">
      <div class="gallery-grid" :class="'count-' + gallerycount">
        <div v-for="(src, i) in gallery.slice(0, 4)" :key="i" class="gallery-item" :class="{ main: i === 0 }">
          <img :src="src" alt="" />
        </div>
      </div>
    </section>

    <div class="container mt-4 mb-5">
      <div class="row g-4">
        <div class="col-lg-8">
          <section class="info-card">
            <h5>عن الفندق</h5>
            <p class="text-muted mb-0">{{ hotel.description || 'لا وصف متاح حالياً.' }}</p>
          </section>

          <section class="mt-4">
            <h5 class="mb-3">أنواع الإقامة المتاحة</h5>
            <div class="small text-muted mb-2">للتحقق من التوفر والحجز استخدم نموذج الحجز جانباً.</div>
            <div v-if="hotel.accommodation_types?.length" class="d-flex flex-column gap-3">
              <div v-for="t in hotel.accommodation_types" :key="t.id" class="type-card">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                  <div>
                    <div class="type-name">{{ t.name }}</div>
                    <div class="small text-muted">
                      حتى {{ t.max_adults }} بالغين + {{ t.max_children }} أطفال · {{ t.stay_type }}
                    </div>
                  </div>
                  <div class="type-price">{{ Number(t.base_price) }} <span>ريال / ليلة</span></div>
                </div>
              </div>
            </div>
            <div v-else class="alert alert-info mb-0">لا أنواع متاحة حالياً.</div>
          </section>
        </div>

        <div class="col-lg-4">
          <div class="sticky-widget">
            <BookingWidget :hotel="hotel" />
          </div>
        </div>
      </div>
    </div>
  </div>

  <div v-else class="container py-5">
    <div class="alert alert-danger">الفندق غير موجود.</div>
  </div>
</template>

<style lang="scss" scoped>
.hotel-head {
  background: linear-gradient(135deg, #006c35, #004d26);
  color: #fff;
  padding: 2rem 0;
}

.hotel-head h2 {
  color: #fff;
}

.head-meta {
  color: #d3e9dc;
}

.review-pill-big {
  background: #ffc107;
  color: #212529;
  font-weight: 800;
  padding: 6px 16px;
  border-radius: 24px;
  font-size: 1.1rem;
}

.gallery-grid { display: grid; grid-template-columns: 2fr 1fr 1fr; grid-template-rows: 160px 160px; gap: 8px; }
.gallery-grid.count-1 { grid-template-columns: 1fr; grid-template-rows: 320px; }
.gallery-grid.count-2 { grid-template-columns: 1fr 1fr; grid-template-rows: 260px; }
.gallery-grid.count-3 { grid-template-columns: 2fr 1fr; }
.gallery-grid.count-1 .gallery-item.main,
.gallery-grid.count-2 .gallery-item.main { grid-row: auto; }
.gallery-item { border-radius: 12px; overflow: hidden; }
.gallery-item.main { grid-row: span 2; }
.gallery-item img { width: 100%; height: 100%; object-fit: cover; } 

.info-card {
  background: #fff;
  border: 1px solid #e9ecef;
  border-radius: 14px;
  padding: 1.4rem;
}

.type-card {
  background: #fff;
  border: 1px solid #e9ecef;
  border-radius: 14px;
  padding: 1rem 1.2rem;
  transition: all 0.2s ease;
}



.type-name {
  font-weight: 700;
}

.type-price {
  font-size: 1.2rem;
  font-weight: 800;
  color: #006c35;
}

.type-price span {
  font-size: 0.72rem;
  color: #6c757d;
  font-weight: 500;
}

.sticky-widget {
  position: sticky;
  top: 90px;
}
</style>