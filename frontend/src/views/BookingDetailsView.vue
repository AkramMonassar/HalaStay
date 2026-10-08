<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import api from '../services/api'
import { useToastStore } from '../stores/toast'
import ReviewModal from '../components/ReviewModal.vue'
const showReview = ref(false)

const { t } = useI18n()
const toast = useToastStore()
const route = useRoute()

const booking = ref(null)
const methods = ref([])
const loading = ref(true)
const paying = ref(false)
const payForm = ref({ payment_method_id: '', receipt_image: null })

const selectedMethod = computed(() => methods.value.find((m) => m.id === Number(payForm.value.payment_method_id)) || null)
const receiptRequired = computed(() => (selectedMethod.value ? selectedMethod.value.requires_receipt !== false : true))


const statusClasses = {
  pending_payment: 'bg-warning', pending_confirmation: 'bg-info', confirmed: 'bg-success',
  cancelled: 'bg-danger', completed: 'bg-primary', expired: 'bg-secondary',
  pending: 'bg-warning', under_review: 'bg-info', success: 'bg-success', failed: 'bg-danger', refunded: 'bg-secondary',
}

const nights = computed(() => {
  if (!booking.value) return 0
  const a = new Date(booking.value.check_in)
  const b = new Date(booking.value.check_out)
  return Math.max(1, Math.round((b - a) / 86400000))
})

const histories = computed(() => booking.value?.status_histories || booking.value?.histories || [])

async function refresh() {
  const { data } = await api.get(`/bookings/${route.params.id}`)
  booking.value = data.data
}

onMounted(async () => {
  try {
    const [b, m] = await Promise.all([
      api.get(`/bookings/${route.params.id}`),
      api.get('/payment-methods'),
    ])
    booking.value = b.data.data
    methods.value = (m.data.data || []).filter((x) => x.is_active)
  } finally {
    loading.value = false
  }
})

function onFile(e) {
  payForm.value.receipt_image = e.target.files[0] || null
}

async function pay() {
  paying.value = true
  try {
    const fd = new FormData()
    fd.append('booking_id', booking.value.id)
    fd.append('payment_method_id', payForm.value.payment_method_id)
    if (payForm.value.receipt_image) fd.append('receipt_image', payForm.value.receipt_image)
    await api.post('/payments', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    toast.push(t('details.payTitle') + ' ✔', 'success')
    await refresh()
  } catch (e) {
    const errorMap = {
      'The receipt image field is required.': t('details.receiptRequired'),
      'The receipt image must be an image.': t('details.receiptMustBeImage'),
      'The receipt image may not be greater than 5120 kilobytes.': t('details.receiptTooLarge'),
      'The selected payment method id is invalid.': t('details.invalidMethod'),
      'This booking is not yours.': t('details.notYourBooking'),
      'The booking is not in "Awaiting Payment" status — a new payment cannot be created.': t('details.payBlocked'),
      'This payment method is not available right now.': t('details.invalidMethod'),
    }
    const errors = e.response?.data?.errors
    const raw = errors ? Object.values(errors).flat()[0] : (e.response?.data?.message || t('auth.loginFailed'))
    toast.push(errorMap[raw] || raw, 'danger')
  } finally {
    paying.value = false
  }
}

async function cancel() {
  try {
    await api.post(`/bookings/${booking.value.id}/cancel`)
    toast.push(t('details.cancelSuccess'), 'success')
  } catch (e) {
    toast.push(e.response?.data?.message || t('details.cancelFailed'), 'danger')
  } finally {
    try { await refresh() } catch {}
  }
}

const EXPIRY_HOURS = 24 // مطابق لافتراضي config/halastay.php

const expiryHours = computed(() => {
  if (!booking.value || booking.value.booking_status !== 'pending_payment') return null
  if (!booking.value.created_at) return null
  const deadline = new Date(new Date(booking.value.created_at).getTime() + EXPIRY_HOURS * 3600 * 1000)
  return Math.max(0, Math.round((deadline - Date.now()) / 3600000))
})

</script>

<template>
  <div v-if="loading" class="container py-5 text-center">{{ $t('common.loading') }}</div>
  <div v-else-if="booking" class="container py-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
      <div>
        <h4 class="mb-1">{{ $t('details.title') }}</h4>
        <div class="text-muted">{{ booking.hotel }} — {{ booking.accommodation_type }}</div>
        <div class="small text-muted">{{ $t('bookings.bookingNo') }}: {{ booking.booking_number }}</div>
      </div>
      <span class="badge" :class="statusClasses[booking.booking_status]">{{ $t('statuses.' + booking.booking_status)
      }}</span>
    </div>

    <div class="row g-4">
      <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
          <div class="card-body">
            <div class="row g-3">
              <div class="col-6 col-md-3">
                <div class="small text-muted">{{ $t('details.checkIn') }}</div>
                <div class="fw-semibold">{{ booking.check_in }}</div>
              </div>
              <div class="col-6 col-md-3">
                <div class="small text-muted">{{ $t('details.checkOut') }}</div>
                <div class="fw-semibold">{{ booking.check_out }}</div>
              </div>
              <div class="col-6 col-md-3">
                <div class="small text-muted">{{ $t('details.nights') }}</div>
                <div class="fw-semibold">{{ nights }}</div>
              </div>
              <div class="col-6 col-md-3">
                <div class="small text-muted">{{ $t('details.rooms') }}</div>
                <div class="fw-semibold">{{ booking.rooms }}</div>
              </div>
              <div class="col-6 col-md-3">
                <div class="small text-muted">{{ $t('details.guests') }}</div>
                <div class="fw-semibold">{{ booking.adults }} {{ $t('details.adults') }} + {{ booking.children }} {{
                  $t('details.children') }}</div>
              </div>
              <div class="col-6 col-md-3">
                <div class="small text-muted">{{ $t('details.total') }}</div>
                <div class="fw-bold text-primary">{{ booking.total_price }} {{ booking.currency_code }}</div>
              </div>
            </div>
          </div>
        </div>

        <h5 class="mb-3">{{ $t('details.paymentsTitle') }}</h5>
        <div v-if="booking.payments?.length" class="d-flex flex-column gap-2 mb-4">
          <div v-for="p in booking.payments" :key="p.id" class="card shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
              <div>
                <div class="fw-semibold">{{ p.payment_method }}</div>
                <div class="small text-muted">{{ p.payment_number }}</div>
                <div class="small">{{ $t('details.amount') }}: {{ p.amount }} {{ p.currency_code }}</div>
                <a v-if="receiptRequired" :href="p.receipt_image" target="_blank"
                  class="btn btn-outline-secondary btn-sm mt-1">{{ $t('details.receipt') }}</a>
                <div v-else class="alert alert-success py-2 small mb-2">{{ $t('details.receiptNotNeeded') }}</div>
              </div>
              <span class="badge" :class="statusClasses[p.payment_status]">{{ $t('statuses.' + p.payment_status)
              }}</span>
            </div>
          </div>
        </div>
        <div v-else class="alert alert-info">{{ $t('bookings.noResults') }}</div>

        <h5 class="mb-3">{{ $t('details.historyTitle') }}</h5>
        <div v-if="histories.length" class="d-flex flex-column gap-2">
          <div v-for="h in histories" :key="h.id" class="border rounded p-2 small d-flex justify-content-between">
            <span>
              {{ $t('statuses.' + (h.new_status || h.status)) }}
              <span v-if="h.note" class="text-muted">— {{ h.note }}</span>
            </span>
            <span class="text-muted">{{ h.created_at }}</span>
          </div>
        </div>
        <div v-else class="text-muted small">—</div>
      </div>

      <div class="col-lg-4">
        <div class="card shadow-sm sticky-side">
          <div class="card-body">
            <h5 class="mb-3">💳 {{ $t('details.payTitle') }}</h5>
            <div v-if="expiryHours !== null" class="alert alert-warning py-2 small mb-2">
              ⏳ {{ $t('details.expiryHint', { h: expiryHours }) }}
            </div>
            <template v-if="booking.booking_status === 'pending_payment'">
              <div class="mb-2">
                <label class="form-label">{{ $t('details.method') }}</label>
                <select v-model="payForm.payment_method_id" class="form-select">
                  <option value="" disabled>{{ $t('details.method') }}</option>
                  <option v-for="m in methods" :key="m.id" :value="m.id">{{ m.name }}</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">{{ $t('details.receiptFile') }}</label>
                <input type="file" class="form-control" @change="onFile" />
              </div>
              <button class="btn btn-primary w-100" :disabled="paying || !payForm.payment_method_id" @click="pay">
                {{ paying ? $t('common.loading') : $t('details.payNow') }}
              </button>
            </template>
            <div v-else class="alert alert-info mb-0">{{ $t('details.payBlocked') }}</div>

            <button v-if="['pending_payment', 'pending_confirmation'].includes(booking.booking_status)"
              class="btn btn-outline-danger w-100 mt-3" @click="cancel">
              {{ $t('details.cancelBtn') }}
            </button>
            <button v-if="booking.booking_status === 'completed'" class="btn btn-warning w-100 mt-3"
              @click="showReview = true">
              ⭐ {{ $t('reviews.rateStay') }}
            </button>
          </div>
        </div>
      </div>
    </div>
    <ReviewModal v-if="booking" :show="showReview" :booking-id="booking.id" @close="showReview = false"
      @saved="showReview = false" />
  </div>

</template>

<style lang="scss" scoped>
.sticky-side {
  position: sticky;
  top: 90px;
}
</style>