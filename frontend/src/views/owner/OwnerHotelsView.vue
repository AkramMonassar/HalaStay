<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'

const hotels = ref([])
const loading = ref(true)

const statusClasses = { pending: 'bg-warning', approved: 'bg-success', rejected: 'bg-danger', suspended: 'bg-secondary' }

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
      <h4 class="mb-0">🏨 {{ $t('owner.hotelsTitle') }}</h4>
      <router-link to="/owner/hotels/create" class="btn btn-success">+ {{ $t('owner.addHotel') }}</router-link>
    </div>

    <div v-if="loading" class="text-center py-5">{{ $t('common.loading') }}</div>
    <div v-else class="row g-3">
      <div v-for="h in hotels" :key="h.id" class="col-md-4">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
              <div class="fw-semibold">{{ h.name }}</div>
              <span class="badge" :class="statusClasses[h.status]">{{ $t('statuses.' + h.status) }}</span>
            </div>
            <div class="small text-muted mt-1">{{ h.city }} — {{ h.star_rating }} ★</div>
          </div>
          <div class="card-footer bg-white">
            <router-link :to="{ name: 'owner-hotel-details', params: { id: h.id } }" class="btn btn-outline-success btn-sm w-100">
              {{ $t('owner.manage') }}
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>