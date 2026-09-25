<script setup>
import { ref, onMounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '../../services/api'
import { useToastStore } from '../../stores/toast'

const { t } = useI18n()
const toast = useToastStore()

const hotels = ref([])
const loading = ref(true)
const statusFilter = ref('pending')

const statusClasses = { pending: 'bg-warning', approved: 'bg-success', rejected: 'bg-danger', suspended: 'bg-secondary' }

async function fetchHotels() {
  loading.value = true
  try {
    const params = statusFilter.value ? { status: statusFilter.value } : {}
    const { data } = await api.get('/admin/hotels', { params })
    hotels.value = data.data
  } finally {
    loading.value = false
  }
}

onMounted(fetchHotels)
watch(statusFilter, fetchHotels)

async function approve(h) {
  try {
    await api.patch(`/admin/hotels/${h.id}/approve`)
    toast.push(t('admin.approvedToast'), 'success')
    fetchHotels()
  } catch (e) {
    toast.push(e.response?.data?.message || t('auth.loginFailed'), 'danger')
  }
}

async function reject(h) {
  try {
    await api.patch(`/admin/hotels/${h.id}/reject`)
    toast.push(t('admin.rejectedToast'), 'warning')
    fetchHotels()
  } catch (e) {
    toast.push(e.response?.data?.message || t('auth.loginFailed'), 'danger')
  }
}
</script>

<template>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="mb-0">🏨 {{ $t('admin.hotelsTitle') }}</h4>
      <select v-model="statusFilter" class="form-select" style="width: auto">
        <option value="pending">{{ $t('statuses.pending') }}</option>
        <option value="">{{ $t('bookings.allStatuses') }}</option>
        <option value="approved">{{ $t('statuses.approved') }}</option>
        <option value="rejected">{{ $t('statuses.rejected') }}</option>
        <option value="suspended">{{ $t('statuses.suspended') }}</option>
      </select>
    </div>

    <div v-if="loading" class="text-center py-5">{{ $t('common.loading') }}</div>
    <div v-else-if="hotels.length" class="d-flex flex-column gap-2">
      <div v-for="h in hotels" :key="h.id" class="card shadow-sm">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div>
            <div class="fw-semibold">
              {{ h.name }}
              <span class="badge ms-1" :class="statusClasses[h.status]">{{ $t('statuses.' + h.status) }}</span>
            </div>
            <div class="small text-muted">{{ h.city }} — {{ $t('admin.ownerLabel') }}: {{ h.owner_name }} — {{ h.star_rating }} ★</div>
          </div>
          <div v-if="h.status === 'pending'" class="d-flex gap-1">
            <button class="btn btn-success btn-sm" @click="approve(h)">{{ $t('owner.approve') }}</button>
            <button class="btn btn-outline-danger btn-sm" @click="reject(h)">{{ $t('owner.reject') }}</button>
          </div>
        </div>
      </div>
    </div>
    <div v-else class="alert alert-success">{{ $t('admin.emptyHotels') }}</div>
  </div>
</template>