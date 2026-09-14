<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../services/api'

const route = useRoute()
const router = useRouter()

const booking = ref(null)
const loading = ref(true)

const paymentMethods = ref([])
const selectedMethod = ref('')
const transactionId = ref('')
const receiptFile = ref(null)
const paymentLoading = ref(false)
const paymentError = ref('')
const paymentSuccess = ref('')

const storageBase = (import.meta.env.VITE_API_BASE_URL || '').replace('/api/v1', '') + '/storage/'

const statusLabels = {
  pending_payment: 'بانتظار الدفع',
  pending_confirmation: 'بانتظار التأكيد',
  confirmed: 'مؤكد',
  cancelled: 'ملغي',
  completed: 'مكتمل',
  expired: 'منتهي',
}

const statusClasses = {
  pending_payment: 'bg-warning',
  pending_confirmation: 'bg-info',
  confirmed: 'bg-success',
  cancelled: 'bg-danger',
  completed: 'bg-primary',
  expired: 'bg-secondary',
}

const paymentStatusLabels = {
  pending: 'قيد الانتظار',
  under_review: 'قيد المراجعة',
  success: 'ناجح',
  failed: 'مرفوض',
  refunded: 'مسترد',
}

onMounted(async () => {
  try {
    const [bookingRes, methodsRes] = await Promise.all([
      api.get(`/bookings/${route.params.id}`),
      api.get('/payment-methods'),
    ])
    booking.value = bookingRes.data.data
    paymentMethods.value = methodsRes.data.data.filter((m) => m.is_active && m.type === 'manual')
  } finally {
    loading.value = false
  }
})

const canPay = computed(() => booking.value?.booking_status === 'pending_payment')
const hasActivePayment = computed(() =>
  booking.value?.payments?.some((p) => ['pending', 'under_review'].includes(p.payment_status))
)

async function submitPayment() {
  paymentError.value = ''
  paymentSuccess.value = ''
  paymentLoading.value = true
  try {
    const formData = new FormData()
    formData.append('booking_id', booking.value.id)
    formData.append('payment_method_id', selectedMethod.value)
    if (transactionId.value) formData.append('transaction_id', transactionId.value)
    if (receiptFile.value) formData.append('receipt', receiptFile.value)

    const { data } = await api.post('/payments/manual', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    paymentSuccess.value = 'تم إنشاء الدفعة بنجاح وهي بانتظار المراجعة.'
    booking.value.payments = booking.value.payments || []
    booking.value.payments.push(data.data)
    booking.value.booking_status = 'pending_confirmation'
    booking.value.payment_status = 'under_review'

    selectedMethod.value = ''
    transactionId.value = ''
    receiptFile.value = null
  } catch (e) {
    const errors = e.response?.data?.errors
    paymentError.value = errors
      ? Object.values(errors).flat()[0]
      : (e.response?.data?.message || 'تعذر إنشاء الدفعة.')
  } finally {
    paymentLoading.value = false
  }
}

async function uploadReceipt(payment) {
  if (!receiptFile.value) {
    paymentError.value = 'يرجى اختيار صورة الإشعار.'
    return
  }

  paymentError.value = ''
  paymentLoading.value = true
  try {
    const formData = new FormData()
    formData.append('receipt', receiptFile.value)

    const { data } = await api.post(`/payments/manual/${payment.id}/receipt`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    const idx = booking.value.payments.findIndex((p) => p.id === payment.id)
    if (idx >= 0) booking.value.payments[idx] = data.data

    paymentSuccess.value = 'تم رفع إشعار الدفع بنجاح.'
    receiptFile.value = null
  } catch (e) {
    const errors = e.response?.data?.errors
    paymentError.value = errors
      ? Object.values(errors).flat()[0]
      : (e.response?.data?.message || 'تعذر رفع الإشعار.')
  } finally {
    paymentLoading.value = false
  }
}

function cancelBooking() {
  if (!confirm('هل أنت متأكد من إلغاء الحجز؟')) return

  api
    .post(`/bookings/${booking.value.id}/cancel`, { reason: 'إلغاء من قبل المستخدم' })
    .then(({ data }) => {
      booking.value = data.data
      paymentSuccess.value = 'تم إلغاء الحجز بنجاح.'
    })
    .catch((e) => {
      paymentError.value = e.response?.data?.message || 'تعذر إلغاء الحجز.'
    })
}
</script>

<template>
  <div class="container py-4">
    <div v-if="loading" class="text-center py-5">جارِ التحميل...</div>

    <div v-else-if="booking" class="row g-4">
      <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h4 class="mb-0">تفاصيل الحجز</h4>
          <span class="badge fs-6" :class="statusClasses[booking.booking_status]">
            {{ statusLabels[booking.booking_status] }}
          </span>
        </div>
        <p class="text-muted mb-1">{{ booking.hotel }} — {{ booking.accommodation_type }}</p>
        <p class="small text-muted">رقم الحجز: {{ booking.booking_number }}</p>

        <div class="card shadow-sm mb-3">
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="small text-muted">الدخول</div>
                <div class="fw-semibold">{{ booking.check_in }}</div>
              </div>
              <div class="col-md-6">
                <div class="small text-muted">الخروج</div>
                <div class="fw-semibold">{{ booking.check_out }}</div>
              </div>
            </div>
            <div class="row mt-2">
              <div class="col-md-4">
                <div class="small text-muted">الليالي</div>
                <div class="fw-semibold">{{ booking.nights }}</div>
              </div>
              <div class="col-md-4">
                <div class="small text-muted">الضيوف</div>
                <div class="fw-semibold">{{ booking.adults }} + {{ booking.children }} أطفال</div>
              </div>
              <div class="col-md-4">
                <div class="small text-muted">الغرف</div>
                <div class="fw-semibold">{{ booking.rooms_count }}</div>
              </div>
            </div>
            <div class="mt-3">
              <div class="small text-muted">الإجمالي</div>
              <div class="fw-bold text-primary fs-5">{{ booking.total_price }} {{ booking.currency_code }}</div>
            </div>
          </div>
        </div>

        <h5>الدفعات</h5>
        <div v-if="booking.payments && booking.payments.length" class="d-flex flex-column gap-2">
          <div v-for="p in booking.payments" :key="p.id" class="card shadow-sm">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="fw-semibold">{{ p.payment_method }}</div>
                  <div class="small text-muted">{{ p.payment_number }}</div>
                </div>
                <span class="badge bg-info">{{ paymentStatusLabels[p.payment_status] }}</span>
              </div>
              <div class="small mt-2">المبلغ: {{ p.amount }} {{ p.currency_code }}</div>
              <div v-if="p.receipt_image" class="mt-2">
                <a :href="p.receipt_image" target="_blank" class="btn btn-sm btn-outline-secondary">
                  عرض الإشعار
                </a>
              </div>
              <div v-else-if="p.payment_status === 'pending'" class="mt-2">
                <input type="file" class="form-control form-control-sm mb-2" @change="(e) => (receiptFile = e.target.files[0])" />
                <button class="btn btn-sm btn-primary" :disabled="paymentLoading" @click="uploadReceipt(p)">
                  رفع الإشعار
                </button>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="alert alert-light border">لا توجد دفعات بعد.</div>

        <div v-if="booking.status_history && booking.status_history.length" class="mt-4">
          <h5>سجل الحالات</h5>
          <ul class="list-group">
            <li v-for="h in booking.status_history" :key="h.id" class="list-group-item">
              <div class="d-flex justify-content-between">
                <span>{{ h.old_status || '—' }} → {{ h.new_status }}</span>
                <small class="text-muted">{{ new Date(h.created_at).toLocaleString('ar-EG') }}</small>
              </div>
              <div class="small text-muted">{{ h.note }}</div>
            </li>
          </ul>
        </div>
      </div>

      <div class="col-lg-4">
        <div style="position: sticky; top: 1rem">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="mb-3">💳 إتمام الدفع</h5>

              <div v-if="!canPay" class="alert alert-info py-2">
                الحجز ليس بحالة "بانتظار الدفع" — لا يمكن إنشاء دفعة جديدة.
              </div>

              <div v-else-if="hasActivePayment" class="alert alert-warning py-2">
                توجد دفعة قيد المراجعة — انتظر المراجعة أو ارفع الإشعار.
              </div>

              <div v-else>
                <div class="mb-3">
                  <label class="form-label">طريقة الدفع</label>
                  <select v-model="selectedMethod" class="form-select" required>
                    <option value="" disabled>اختر طريقة الدفع</option>
                    <option v-for="m in paymentMethods" :key="m.id" :value="m.id">{{ m.name }}</option>
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label">رقم العملية (اختياري)</label>
                  <input v-model="transactionId" type="text" class="form-control" placeholder="TRX-..." />
                </div>

                <div class="mb-3">
                  <label class="form-label">إشعار الدفع (اختياري)</label>
                  <input type="file" class="form-control" @change="(e) => (receiptFile = e.target.files[0])" />
                </div>

                <button class="btn btn-primary w-100" :disabled="!selectedMethod || paymentLoading" @click="submitPayment">
                  {{ paymentLoading ? 'جارِ الإنشاء...' : 'إنشاء الدفعة' }}
                </button>
              </div>

              <div v-if="paymentError" class="alert alert-danger py-2 mt-3 mb-0">{{ paymentError }}</div>
              <div v-if="paymentSuccess" class="alert alert-success py-2 mt-3 mb-0">{{ paymentSuccess }}</div>

              <hr />

              <button
                v-if="['pending_payment', 'pending_confirmation'].includes(booking.booking_status)"
                class="btn btn-outline-danger w-100"
                @click="cancelBooking"
              >
                إلغاء الحجز
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="alert alert-danger">الحجز غير موجود.</div>
  </div>
</template>