<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../services/api'
import BookingWidget from '../components/BookingWidget.vue'

const route = useRoute()
const hotel = ref(null)
const loading = ref(true)
const storageBase = (import.meta.env.VITE_API_BASE_URL || '').replace('/api/v1', '') + '/storage/'

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
  <div class="container py-4">
    <div v-if="loading" class="text-center py-5">جارِ التحميل...</div>

    <div v-else-if="hotel" class="row g-4">
      <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h3 class="mb-0">{{ hotel.name }}</h3>
          <span class="badge bg-warning text-dark fs-6">{{ '★'.repeat(hotel.star_rating) }}</span>
        </div>
        <p class="text-muted mb-1">{{ hotel.city }} — {{ hotel.address }}</p>
        <p>{{ hotel.description }}</p>

        <div class="row g-2 mb-4">
          <div v-for="img in hotel.images" :key="img.id" class="col-6 col-md-3">
            <img
              :src="storageBase + img.image_path"
              class="rounded w-100" style="height: 140px; object-fit: cover" alt=""
            />
          </div>
        </div>

        <h5>أنواع الإقامة</h5>
        <div class="row g-3">
          <div v-for="t in hotel.accommodation_types" :key="t.id" class="col-md-6">
            <div class="card shadow-sm">
              <div class="card-body">
                <div class="d-flex justify-content-between">
                  <h6 class="mb-1">{{ t.name }}</h6>
                  <span class="fw-bold text-primary">{{ t.base_price }} ريال</span>
                </div>
                <div class="small text-muted">
                  {{ t.stay_type }} — حتى {{ t.max_adults }} بالغين + {{ t.max_children }} أطفال
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div style="position: sticky; top: 1rem">
          <BookingWidget :hotel-id="hotel.id" />
        </div>
      </div>
    </div>

    <div v-else class="alert alert-danger">الفندق غير موجود.</div>
  </div>
</template>