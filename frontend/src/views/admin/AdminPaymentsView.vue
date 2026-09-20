<script setup>
import { ref, onMounted, watch } from 'vue'
import api from '../../services/api'

const payments = ref([])
const loading = ref(true)
const statusFilter = ref('')

const statusLabels = { pending: 'قيد الانتظار', under_review: 'قيد المراجعة', success: 'ناجحة', failed: 'مرفوضة', refunded: 'مستردة' }
const statusClasses = { pending: 'bg-warning', under_review: 'bg-info', success: 'bg-success', failed: 'bg-danger', refunded: 'bg-secondary' }

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
      <h4 class="mb-0">💳 كل الدفعات</h4>
      <select v-model="statusFilter" class="form-select" style="width: auto">
        <option value="">كل الحالات</option>
        <option value="under_review">قيد المراجعة</option>
        <option value="success">ناجحة</option>
        <option value="failed">مرفوضة</option>
        <option value="refunded">مستردة</option>
      </select>
    </div>

    <div v-if="loading" class="text-center py-5">جارِ التحميل...</div>
    <div v-else-if="payments.length" class="d-flex flex-column gap-2">
      <div v-for="p in payments" :key="p.id" class="card shadow-sm">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div>
            <div class="fw-semibold">
              {{ p.payment_number }} — {{ p.hotel_name }}
              <span class="badge ms-1" :class="statusClasses[p.payment_status]">{{ statusLabels[p.payment_status] }}</span>
            </div>
            <div class="small text-muted">الضيف: {{ p.guest_name }} | {{ p.payment_method }} | {{ p.amount }} {{ p.currency_code }}</div>
            <a v-if="p.receipt_image" :href="p.receipt_image" target="_blank" class="btn btn-outline-secondary btn-sm mt-1">عرض الإشعار</a>
          </div>
        </div>
      </div>
    </div>
    <div v-else class="alert alert-info">لا توجد دفعات بهذه الحالة.</div>
  </div>
</template>