<script setup>
import { ref, onMounted, watch } from 'vue'
import api from '../../services/api'

const payments = ref([])
const loading = ref(true)
const statusFilter = ref('under_review')
const message = ref('')

const statusLabels = {
  pending: 'قيد الانتظار',
  under_review: 'قيد المراجعة',
  success: 'ناجحة',
  failed: 'مرفوضة',
  refunded: 'مستردة',
}
const statusClasses = {
  pending: 'bg-warning',
  under_review: 'bg-info',
  success: 'bg-success',
  failed: 'bg-danger',
  refunded: 'bg-secondary',
}

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

async function review(p, action) {
  let note = null

  if (action === 'approve') {
    if (!confirm(`اعتماد الدفعة ${p.payment_number} بمبلغ ${p.amount}؟ سيُؤكد الحجز فوراً.`)) return
  } else {
    note = prompt('سبب الرفض (سيصل للسائح):')
    if (note === null) return
  }

  message.value = ''
  try {
    await api.patch(`/owner/payments/${p.id}/review`, { action, admin_note: note })
    message.value = action === 'approve'
      ? 'تم اعتماد الدفعة وتأكيد الحجز.'
      : 'تم رفض الدفعة وإعادة الحجز لانتظار الدفع.'
    fetchPayments()
  } catch (e) {
    message.value = e.response?.data?.message || 'تعذرت المراجعة.'
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

    <div v-if="message" class="alert alert-info py-2">{{ message }}</div>

    <div v-if="loading" class="text-center py-5">جارِ التحميل...</div>

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
            <button class="btn btn-success btn-sm" @click="review(p, 'approve')">اعتماد</button>
            <button class="btn btn-outline-danger btn-sm" @click="review(p, 'reject')">رفض</button>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="alert alert-success">لا توجد دفعات بهذه الحالة — الطابور نظيف ✔</div>
  </div>
</template>