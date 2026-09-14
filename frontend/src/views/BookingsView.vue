<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const router = useRouter()
const bookings = ref([])
const loading = ref(true)
const meta = ref(null)

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

onMounted(async () => {
  try {
    const { data } = await api.get('/user/bookings')
    bookings.value = data.data
    meta.value = data.meta
  } finally {
    loading.value = false
  }
})

function viewDetails(id) {
  router.push({ name: 'booking', params: { id } })
}
</script>

<template>
  <div class="container py-4">
    <h4 class="mb-4">🧾 حجوزاتي</h4>

    <div v-if="loading" class="text-center py-5">جارِ التحميل...</div>

    <div v-else-if="bookings.length" class="row g-3">
      <div v-for="b in bookings" :key="b.id" class="col-md-6 col-xl-4">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <h6 class="mb-1">{{ b.hotel }}</h6>
              <span class="badge" :class="statusClasses[b.booking_status]">
                {{ statusLabels[b.booking_status] }}
              </span>
            </div>
            <div class="small text-muted mb-2">{{ b.accommodation_type }}</div>
            <div class="small">
              📅 {{ b.check_in }} → {{ b.check_out }} ({{ b.nights }} ليالي)
            </div>
            <div class="small">
              👥 {{ b.adults }} بالغين + {{ b.children }} أطفال × {{ b.rooms_count }} غرف
            </div>
            <div class="fw-bold text-primary mt-2">{{ b.total_price }} {{ b.currency_code }}</div>
            <div class="small text-muted">رقم الحجز: {{ b.booking_number }}</div>
          </div>
          <div class="card-footer bg-white">
            <button class="btn btn-outline-primary btn-sm w-100" @click="viewDetails(b.id)">
              عرض التفاصيل
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="alert alert-info">
      لا توجد حجوزات بعد — ابدأ البحث واحجز فندقك الأول!
    </div>
  </div>
</template>