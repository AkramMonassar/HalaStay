<script setup>
import { ref, onMounted } from 'vue'
import api from '../services/api'

const bookings = ref([])
const loading = ref(true)

const statusClasses = {
  pending_payment: 'bg-warning', pending_confirmation: 'bg-info', confirmed: 'bg-success',
  cancelled: 'bg-danger', completed: 'bg-primary', expired: 'bg-secondary',
}

onMounted(async () => {
  try {
    const { data } = await api.get('/bookings')
    bookings.value = data.data
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="container py-4">
    <h4 class="mb-4">🧾 {{ $t('bookings.title') }}</h4>
    <div v-if="loading" class="text-center py-5">{{ $t('common.loading') }}</div>
    <div v-else-if="bookings.length" class="row g-3">
      <div v-for="b in bookings" :key="b.id" class="col-md-6">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
              <div class="fw-semibold">{{ b.hotel }}</div>
              <span class="badge" :class="statusClasses[b.booking_status]">{{ $t('statuses.' + b.booking_status) }}</span>
            </div>
            <div class="small text-muted mt-1">{{ b.accommodation_type }}</div>
            <div class="small mt-2">📅 {{ b.check_in }} → {{ b.check_out }}</div>
            <div class="small">👥 {{ b.adults }} + {{ b.children }} · {{ b.rooms }} {{ $t('bookings.roomsCount') }}</div>
            <div class="fw-bold text-primary mt-2">{{ b.total_price }} {{ b.currency_code }}</div>
            <div class="small text-muted">{{ $t('bookings.bookingNo') }}: {{ b.booking_number }}</div>
          </div>
          <div class="card-footer bg-white">
            <router-link :to="{ name: 'booking', params: { id: b.id } }" class="btn btn-outline-primary btn-sm w-100">
              {{ $t('bookings.details') }}
            </router-link>
          </div>
        </div>
      </div>
    </div>
    <div v-else class="alert alert-info">{{ $t('bookings.empty') }}</div>
  </div>
</template>