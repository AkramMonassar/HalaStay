<script setup>
import { ref, onMounted, watch } from 'vue'
import api from '../../services/api'

const bookings = ref([])
const loading = ref(true)
const statusFilter = ref('')
const message = ref('')

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

async function fetchBookings() {
  loading.value = true
  try {
    const params = statusFilter.value ? { status: statusFilter.value } : {}
    const { data } = await api.get('/owner/bookings', { params })
    bookings.value = data.data
  } finally {
    loading.value = false
  }
}

onMounted(fetchBookings)
watch(statusFilter, fetchBookings)

async function confirmBooking(b) {
  if (!confirm(`تأكيد الحجز ${b.booking_number}؟`)) return
  message.value = ''
  try {
    await api.post(`/owner/bookings/${b.id}/confirm`)
    b.booking_status = 'confirmed'
    message.value = 'تم تأكيد الحجز وإشعار السائح.'
  } catch (e) {
    message.value = e.response?.data?.message || 'تعذر التأكيد.'
  }
}

async function rejectBooking(b) {
  const reason = prompt('سبب الرفض (سيصل للسائح):')
  if (reason === null) return
  message.value = ''
  try {
    await api.post(`/owner/bookings/${b.id}/reject`, { reason })
    b.booking_status = 'cancelled'
    message.value = 'تم رفض الحجز وتحرير الوحدات.'
  } catch (e) {
    message.value = e.response?.data?.message || 'تعذر الرفض.'
  }
}
</script>

<template>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="mb-0">🧾 حجوزات فنادقي</h4>
      <select v-model="statusFilter" class="form-select" style="width: auto">
        <option value="">كل الحالات</option>
        <option value="pending_confirmation">بانتظار التأكيد</option>
        <option value="confirmed">مؤكدة</option>
        <option value="cancelled">ملغية</option>
        <option value="pending_payment">بانتظار الدفع</option>
      </select>
    </div>

    <div v-if="message" class="alert alert-info py-2">{{ message }}</div>

    <div v-if="loading" class="text-center py-5">جارِ التحميل...</div>

    <div v-else-if="bookings.length" class="d-flex flex-column gap-2">
      <div v-for="b in bookings" :key="b.id" class="card shadow-sm">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div>
            <div class="fw-semibold">
              {{ b.booking_number }} — {{ b.hotel }}
              <span class="badge ms-1" :class="statusClasses[b.booking_status]">{{ statusLabels[b.booking_status] }}</span>
            </div>
            <div class="small text-muted">
              الضيف: {{ b.guest_name }} | {{ b.check_in }} → {{ b.check_out }} | {{ b.rooms_count }} غرف | {{ b.total_price }} {{ b.currency_code }}
               | جوال الضيف: <span dir="ltr">{{ b.guest_phone || 'غير مضاف' }}</span>
            </div>
          </div>
          <div v-if="b.booking_status === 'pending_confirmation'" class="d-flex gap-1">
            <button class="btn btn-success btn-sm" @click="confirmBooking(b)">تأكيد</button>
            <button class="btn btn-outline-danger btn-sm" @click="rejectBooking(b)">رفض</button>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="alert alert-info">لا توجد حجوزات بهذه الحالة.</div>
  </div>
</template>