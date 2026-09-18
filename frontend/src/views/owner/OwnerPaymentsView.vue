<script setup>
import { ref, onMounted, watch } from 'vue'
import api from '../../services/api'
import AppModal from '../../components/AppModal.vue'
import AppSkeleton from '../../components/AppSkeleton.vue'
import { useToastStore } from '../../stores/toast'

const toast = useToastStore()

const payments = ref([])
const loading = ref(true)
const statusFilter = ref('under_review')
const modal = ref(null)

const statusLabels = { pending: 'قيد الانتظار', under_review: 'قيد المراجعة', success: 'ناجحة', failed: 'مرفوضة', refunded: 'مستردة' }
const statusClasses = { pending: 'bg-warning', under_review: 'bg-info', success: 'bg-success', failed: 'bg-danger', refunded: 'bg-secondary' }

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
      action === 'approve' ? 'تم اعتماد الدفعة وتأكيد الحجز.' : 'تم رفض الدفعة وإعادة الحجز لانتظار الدفع.',
      action === 'approve' ? 'success' : 'warning'
    )
    fetchPayments()
  } catch (e) {
    toast.push(e.response?.data?.message || 'تعذرت المراجعة.', 'danger')
  }
}
</script>

<template>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="mb-0">💳 مراجعة الدفعات</h4>
      <select v-model="statusFilter" class="form-select" style="width: auto">
        <option value="under_review">قيد المراجعة</option>
        <option value="">الكل</option>
        <option value="success">ناجحة</option>
        <option value="failed">مرفوضة</option>
        <option value="pending">قيد الانتظار</option>
        <option value="refunded">مستردة</option>
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
              <span class="badge ms-1" :class="statusClasses[p.payment_status]">{{ statusLabels[p.payment_status] }}</span>
            </div>
            <div class="small text-muted">
              الضيف: {{ p.guest_name }} | الحجز: {{ p.booking_number }} | {{ p.payment_method }} | {{ p.amount }} {{ p.currency_code }}
            </div>
            <a v-if="p.receipt_image" :href="p.receipt_image" target="_blank" class="btn btn-outline-secondary btn-sm mt-2">
              عرض الإشعار
            </a>
          </div>
          <div v-if="p.payment_status === 'under_review'" class="d-flex gap-1">
            <button class="btn btn-success btn-sm" @click="askReview(p, 'approve')">اعتماد</button>
            <button class="btn btn-outline-danger btn-sm" @click="askReview(p, 'reject')">رفض</button>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="alert alert-success">لا توجد دفعات بهذه الحالة — الطابور نظيف ✔</div>

    <AppModal
      :show="!!modal"
      :title="modal?.action === 'approve' ? 'اعتماد الدفعة' : 'رفض الدفعة'"
      :message="modal ? `الدفعة ${modal.payment.payment_number} بمبلغ ${modal.payment.amount} ريال.` : ''"
      :with-note="modal?.action === 'reject'"
      :tone="modal?.action === 'approve' ? 'success' : 'danger'"
      @confirm="confirmReview"
      @cancel="modal = null"
    />
  </div>
</template>