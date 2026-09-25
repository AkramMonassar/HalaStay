<script setup>
import { ref, onMounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '../../services/api'
import AppModal from '../../components/AppModal.vue'
import AppSkeleton from '../../components/AppSkeleton.vue'
import { useToastStore } from '../../stores/toast'

const { t } = useI18n()
const toast = useToastStore()

const payments = ref([])
const loading = ref(true)
const statusFilter = ref('under_review')
const modal = ref(null)

async function fetchPayments() {
  loading.value = true
  try {
    const params = statusFilter.value ? { status: statusFilter.value } : {}
    const { data } = await api.get('/owner/payments', { params })
    payments.value = data.data
  } finally {
    loading.value = false
  }
}

onMounted(fetchPayments)
watch(statusFilter, fetchPayments)

function askReview(payment, action) {
  modal.value = { payment, action }
}

async function confirmReview(note) {
  const { payment, action } = modal.value
  modal.value = null
  try {
    await api.patch(`/owner/payments/${payment.id}/review`, { action, admin_note: note || null })
    toast.push(
      action === 'approve' ? t('owner.confirmedToast') : t('owner.rejectedToast'),
      action === 'approve' ? 'success' : 'warning'
    )
    fetchPayments()
  } catch (e) {
    toast.push(e.response?.data?.message || t('auth.loginFailed'), 'danger')
  }
}
</script>

<template>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="mb-0">💳 {{ $t('owner.paymentsTitle') }}</h4>
      <select v-model="statusFilter" class="form-select" style="width: auto">
        <option value="under_review">{{ $t('statuses.under_review') }}</option>
        <option value="">{{ $t('bookings.allStatuses') }}</option>
        <option value="success">{{ $t('statuses.success') }}</option>
        <option value="failed">{{ $t('statuses.failed') }}</option>
        <option value="pending">{{ $t('statuses.pending') }}</option>
        <option value="refunded">{{ $t('statuses.refunded') }}</option>
      </select>
    </div>

    <div v-if="loading" class="d-flex flex-column gap-2">
      <AppSkeleton v-for="i in 3" :key="i" variant="row" />
    </div>

    <div v-else-if="payments.length" class="d-flex flex-column gap-2">
      <div v-for="p in payments" :key="p.id" class="card shadow-sm">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div>
            <div class="fw-semibold">
              {{ p.payment_number }} — {{ p.hotel_name }}
              <span class="badge ms-1">{{ $t('statuses.' + p.payment_status) }}</span>
            </div>
            <div class="small text-muted">
              {{ $t('bookings.thGuest') }}: {{ p.guest_name }} | {{ $t('bookings.bookingNo') }}: {{ p.booking_number }} | {{ p.amount }} {{ p.currency_code }}
            </div>
            <a v-if="p.receipt_image" :href="p.receipt_image" target="_blank" class="btn btn-outline-secondary btn-sm mt-2">
              {{ $t('owner.receipt') }}
            </a>
          </div>
          <div v-if="p.payment_status === 'under_review'" class="d-flex gap-1">
            <button class="btn btn-success btn-sm" @click="askReview(p, 'approve')">{{ $t('owner.approve') }}</button>
            <button class="btn btn-outline-danger btn-sm" @click="askReview(p, 'reject')">{{ $t('owner.reject') }}</button>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="alert alert-success">{{ $t('owner.emptyPayments') }}</div>

    <AppModal
      :show="!!modal"
      :title="modal?.action === 'approve' ? $t('owner.approve') : $t('owner.reject')"
      :message="modal ? `${modal.payment.payment_number} — ${modal.payment.amount}` : ''"
      :with-note="modal?.action === 'reject'"
      :tone="modal?.action === 'approve' ? 'success' : 'danger'"
      @confirm="confirmReview"
      @cancel="modal = null"
    />
  </div>
</template>