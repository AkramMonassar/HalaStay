<script setup>
import { ref, onMounted, watch } from 'vue'
import api from '../../services/api'

const payments = ref([])
const loading = ref(true)
const statusFilter = ref('')

async function fetchPayments() {
  loading.value = true
  try {
    const params = statusFilter.value ? { status: statusFilter.value } : {}
    const { data } = await api.get('/admin/payments', { params })
    payments.value = data.data
  } finally {
    loading.value = false
  }
}

onMounted(fetchPayments)
watch(statusFilter, fetchPayments)
</script>

<template>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="mb-0">💳 {{ $t('admin.allPayments') }}</h4>
      <select v-model="statusFilter" class="form-select" style="width: auto">
        <option value="">{{ $t('bookings.allStatuses') }}</option>
        <option value="under_review">{{ $t('statuses.under_review') }}</option>
        <option value="success">{{ $t('statuses.success') }}</option>
        <option value="failed">{{ $t('statuses.failed') }}</option>
        <option value="refunded">{{ $t('statuses.refunded') }}</option>
      </select>
    </div>

    <div v-if="loading" class="text-center py-5">{{ $t('common.loading') }}</div>
    <div v-else-if="payments.length" class="d-flex flex-column gap-2">
      <div v-for="p in payments" :key="p.id" class="card shadow-sm">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div>
            <div class="fw-semibold">
              {{ p.payment_number }} — {{ p.hotel_name }}
              <span class="badge ms-1">{{ $t('statuses.' + p.payment_status) }}</span>
            </div>
            <div class="small text-muted">
              {{ $t('bookings.thGuest') }}: {{ p.guest_name }} | {{ p.payment_method }} | {{ p.amount }} {{ p.currency_code }}
            </div>
            <a v-if="p.receipt_image" :href="p.receipt_image" target="_blank" class="btn btn-outline-secondary btn-sm mt-1">
              {{ $t('owner.receipt') }}
            </a>
          </div>
        </div>
      </div>
    </div>
    <div v-else class="alert alert-info">{{ $t('bookings.noResults') }}</div>
  </div>
</template>