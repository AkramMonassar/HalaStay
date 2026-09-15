<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'

const hotels = ref([])
const loading = ref(true)
const storageBase = (import.meta.env.VITE_API_BASE_URL || '').replace('/api/v1', '') + '/storage/'

const statusLabels = { pending: 'بانتظار الموافقة', approved: 'معتمد', rejected: 'مرفوض', suspended: 'موقوف' }
const statusClasses = { pending: 'bg-warning', approved: 'bg-success', rejected: 'bg-danger', suspended: 'bg-secondary' }

function coverOf(h) {
  const img = h.images?.find((i) => i.is_cover) || h.images?.[0]
  return img ? storageBase + img.image_path : null
}

onMounted(async () => {
  try {
    const { data } = await api.get('/owner/hotels')
    hotels.value = data.data
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="mb-0">🏨 فنادقي</h4>
      <router-link to="/owner/hotels/new" class="btn btn-primary">+ إضافة فندق</router-link>
    </div>

    <div v-if="loading" class="text-center py-5">جارِ التحميل...</div>

    <div v-else-if="hotels.length" class="row g-3">
      <div v-for="h in hotels" :key="h.id" class="col-md-6 col-xl-4">
        <div class="card shadow-sm h-100">
          <div class="bg-light d-flex align-items-center justify-content-center" style="height: 120px">
            <img v-if="coverOf(h)" :src="coverOf(h)" class="w-100 h-100" style="object-fit: cover" alt="" />
            <span v-else class="fs-1">🏨</span>
          </div>
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <h6 class="mb-1">{{ h.name }}</h6>
              <span class="badge" :class="statusClasses[h.status]">{{ statusLabels[h.status] }}</span>
            </div>
            <div class="text-muted small mb-2">{{ h.city }} — {{ h.star_rating }} نجوم</div>
          </div>
          <div class="card-footer bg-white">
            <router-link :to="{ name: 'owner-hotel-details', params: { id: h.id } }" class="btn btn-outline-primary btn-sm w-100">
              إدارة الفندق
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="alert alert-info">لا توجد فنادق بعد — أضف فندقك الأول!</div>
  </div>
</template>