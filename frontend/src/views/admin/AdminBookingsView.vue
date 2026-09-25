<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import api from '../../services/api'

const bookings = ref([])
const loading = ref(true)
const statusFilter = ref('')
const search = ref('')

const statusClasses = {
  pending_payment: 'bg-warning', pending_confirmation: 'bg-info', confirmed: 'bg-success',
  cancelled: 'bg-danger', completed: 'bg-primary', expired: 'bg-secondary',
}

async function fetchBookings() {
  loading.value = true
  try {
    const params = statusFilter.value ? { status: statusFilter.value } : {}
    const { data } = await api.get('/admin/bookings', { params })
    bookings.value = data.data
  } finally {
    loading.value = false
  }
}

onMounted(fetchBookings)
watch(statusFilter, fetchBookings)

const filtered = computed(() => {
  const q = search.value.trim()
  if (!q) return bookings.value
  return bookings.value.filter(
    (b) => b.booking_number.includes(q) || b.guest_name?.includes(q) || b.hotel?.includes(q)
  )
})
</script>

<template>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 gap-2 flex-wrap">
      <h4 class="mb-0">🧾 {{ $t('admin.allBookings') }}</h4>
      <div class="d-flex gap-2">
        <input v-model="search" class="form-control" :placeholder="$t('bookings.searchPh')" style="width: 220px" />
        <select v-model="statusFilter" class="form-select" style="width: auto">
          <option value="">{{ $t('bookings.allStatuses') }}</option>
          <option value="pending_payment">{{ $t('statuses.pending_payment') }}</option>
          <option value="pending_confirmation">{{ $t('statuses.pending_confirmation') }}</option>
          <option value="confirmed">{{ $t('statuses.confirmed') }}</option>
          <option value="cancelled">{{ $t('statuses.cancelled') }}</option>
        </select>
      </div>
    </div>

    <div v-if="loading" class="text-center py-5">{{ $t('common.loading') }}</div>
    <div v-else class="card shadow-sm">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>{{ $t('bookings.thNumber') }}</th>
              <th>{{ $t('bookings.thHotel') }}</th>
              <th>{{ $t('bookings.thGuest') }}</th>
              <th>{{ $t('bookings.thDates') }}</th>
              <th>{{ $t('bookings.thTotal') }}</th>
              <th>{{ $t('bookings.thStatus') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="b in filtered" :key="b.id">
              <td class="fw-semibold">{{ b.booking_number }}</td>
              <td>{{ b.hotel }}</td>
              <td>{{ b.guest_name }}</td>
              <td class="small">{{ b.check_in }} → {{ b.check_out }}</td>
              <td>{{ b.total_price }} {{ b.currency_code }}</td>
              <td><span class="badge" :class="statusClasses[b.booking_status]">{{ $t('statuses.' + b.booking_status) }}</span></td>
            </tr>
            <tr v-if="!filtered.length">
              <td colspan="6" class="text-center text-muted py-4">{{ $t('bookings.noResults') }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>