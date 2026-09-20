<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '../../services/api'

const stats = ref(null)

const statusLabels = {
  pending_payment: 'بانتظار الدفع',
  pending_confirmation: 'بانتظار التأكيد',
  confirmed: 'مؤكد',
  cancelled: 'ملغي',
  completed: 'مكتمل',
  expired: 'منتهي',
}

const donutOptions = computed(() => ({
  chart: { type: 'donut', fontFamily: 'Tajawal, sans-serif' },
  labels: Object.keys(stats.value?.bookings?.by_status || {}).map((k) => statusLabels[k] || k),
  legend: { position: 'bottom' },
}))
const donutSeries = computed(() => Object.values(stats.value?.bookings?.by_status || {}))

const revenueOptions = computed(() => ({
  chart: { type: 'bar', fontFamily: 'Tajawal, sans-serif' },
  xaxis: { categories: stats.value?.months || [] },
  colors: ['#006c35'],
  plotOptions: { bar: { borderRadius: 5 } },
}))
const revenueSeries = computed(() => [{ name: 'الإيراد', data: stats.value?.monthly_revenue || [] }])

const bookingsOptions = computed(() => ({
  chart: { type: 'line', fontFamily: 'Tajawal, sans-serif' },
  xaxis: { categories: stats.value?.months || [] },
  colors: ['#1a5490'],
  stroke: { width: 3, curve: 'smooth' },
}))
const bookingsSeries = computed(() => [{ name: 'الحجوزات', data: stats.value?.monthly_bookings || [] }])

onMounted(async () => {
  const { data } = await api.get('/admin/stats')
  stats.value = data.data
})
</script>

<template>
  <div class="container py-4">
    <h4 class="mb-4">👑 لوحة الأدمن</h4>
    <div v-if="!stats" class="text-center py-5">جارِ التحميل...</div>
    <template v-else>
      <div class="row g-3 mb-4">
        <div class="col-md-3">
          <div class="card shadow-sm text-center"><div class="card-body">
            <div class="fs-3 fw-bold text-primary">{{ stats.users.total }}</div>
            <div class="text-muted">المستخدمون</div>
            <div class="small">سياح {{ stats.users.tourists }} — ملاك {{ stats.users.owners }} — أدمن {{ stats.users.admins }}</div>
          </div></div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm text-center"><div class="card-body">
            <div class="fs-3 fw-bold text-success">{{ stats.hotels.total }}</div>
            <div class="text-muted">الفنادق</div>
            <div class="small">معتمدة {{ stats.hotels.approved }} — بانتظار {{ stats.hotels.pending }}</div>
          </div></div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm text-center"><div class="card-body">
            <div class="fs-3 fw-bold text-info">{{ stats.bookings.total }}</div>
            <div class="text-muted">الحجوزات</div>
            <div class="small">إجمالي الإيراد: {{ stats.payments.total_success_amount }} ريال</div>
          </div></div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm text-center"><div class="card-body">
            <div class="fs-3 fw-bold text-warning">{{ stats.payments.under_review }}</div>
            <div class="text-muted">دفعات بانتظار المراجعة</div>
          </div></div>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-lg-4">
          <div class="card shadow-sm"><div class="card-body">
            <h6 class="mb-3">الحجوزات حسب الحالة</h6>
            <apexchart v-if="donutSeries.length" type="donut" height="260" :options="donutOptions" :series="donutSeries" />
            <div v-else class="text-muted small">لا حجوزات بعد.</div>
          </div></div>
        </div>
        <div class="col-lg-4">
          <div class="card shadow-sm"><div class="card-body">
            <h6 class="mb-3">الإيراد الشهري</h6>
            <apexchart type="bar" height="260" :options="revenueOptions" :series="revenueSeries" />
          </div></div>
        </div>
        <div class="col-lg-4">
          <div class="card shadow-sm"><div class="card-body">
            <h6 class="mb-3">نمو الحجوزات</h6>
            <apexchart type="line" height="260" :options="bookingsOptions" :series="bookingsSeries" />
          </div></div>
        </div>
      </div>

      <div class="d-flex gap-2 flex-wrap">
        <router-link to="/admin/hotels" class="btn btn-outline-primary">اعتماد الفنادق</router-link>
        <router-link to="/admin/bookings" class="btn btn-outline-primary">كل الحجوزات</router-link>
        <router-link to="/admin/payments" class="btn btn-outline-primary">كل الدفعات</router-link>
        <router-link to="/admin/settings" class="btn btn-outline-primary">المدن وطرق الدفع</router-link>
      </div>
    </template>
  </div>
</template>