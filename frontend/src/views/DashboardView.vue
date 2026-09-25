<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '../services/api'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const bookings = ref([])
const loading = ref(true)

const stats = computed(() => ({
  total: bookings.value.length,
  confirmed: bookings.value.filter((x) => ['confirmed', 'completed'].includes(x.booking_status)).length,
  pending: bookings.value.filter((x) => ['pending_payment', 'pending_confirmation'].includes(x.booking_status)).length,
  cancelled: bookings.value.filter((x) => ['cancelled', 'expired'].includes(x.booking_status)).length,
}))

const upcoming = computed(() => {
  const today = new Date().toISOString().slice(0, 10)
  return (
    bookings.value
      .filter((b) => b.check_out >= today && ['confirmed', 'pending_payment', 'pending_confirmation'].includes(b.booking_status))
      .sort((a, b) => a.check_in.localeCompare(b.check_in))[0] || null
  )
})

onMounted(async () => {
  try {
    const { data } = await api.get('/bookings')
    bookings.value = Array.isArray(data.data) ? data.data : (data.data?.data || [])
  } catch {
    bookings.value = []
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="container py-4">
    <h4 class="mb-4">👋 {{ $t('dash.welcome') }}، {{ auth.user?.name }}</h4>

    <div v-if="loading" class="text-center py-5">{{ $t('common.loading') }}</div>
    <template v-else>
      <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
          <div class="card shadow-sm text-center"><div class="card-body">
            <div class="fs-3 fw-bold text-primary">{{ stats.total }}</div>
            <div class="text-muted">{{ $t('dash.totalBookings') }}</div>
          </div></div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card shadow-sm text-center"><div class="card-body">
            <div class="fs-3 fw-bold text-success">{{ stats.confirmed }}</div>
            <div class="text-muted">{{ $t('dash.confirmed') }}</div>
          </div></div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card shadow-sm text-center"><div class="card-body">
            <div class="fs-3 fw-bold text-info">{{ stats.pending }}</div>
            <div class="text-muted">{{ $t('dash.pending') }}</div>
          </div></div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card shadow-sm text-center"><div class="card-body">
            <div class="fs-3 fw-bold text-danger">{{ stats.cancelled }}</div>
            <div class="text-muted">{{ $t('dash.cancelled') }}</div>
          </div></div>
        </div>
      </div>

      <div class="card shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">{{ $t('dash.upcoming') }}</div>
        <div class="card-body">
          <div v-if="upcoming" class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
              <div class="fw-semibold">{{ upcoming.hotel }}</div>
              <div class="small text-muted">{{ upcoming.check_in }} → {{ upcoming.check_out }}</div>
            </div>
            <router-link :to="{ name: 'booking', params: { id: upcoming.id } }" class="btn btn-outline-primary btn-sm">
              {{ $t('bookings.details') }}
            </router-link>
          </div>
          <div v-else class="text-muted small">
            {{ $t('dash.noUpcoming') }}
            <router-link to="/" class="ms-1">{{ $t('dash.browse') }}</router-link>
          </div>
        </div>
      </div>

      <router-link to="/bookings" class="btn btn-primary">{{ $t('dash.myBookings') }}</router-link>
    </template>
  </div>
</template>