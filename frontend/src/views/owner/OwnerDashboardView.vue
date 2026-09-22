<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '../../services/api'

const stats = ref(null)
import { useThemeStore } from '../../stores/theme'

const theme = useThemeStore()
const ink = computed(() => (theme.mode === 'dark' ? '#e9ecef' : '#373d3f'))
const sub = computed(() => (theme.mode === 'dark' ? '#adb5bd' : '#6c757d'))

const statusLabels = {
  pending_payment: 'بانتظار الدفع',
  pending_confirmation: 'بانتظار التأكيد',
  confirmed: 'مؤكد',
  cancelled: 'ملغي',
  completed: 'مكتمل',
  expired: 'منتهي',
}

const donutOptions = computed(() => ({
  chart: { type: 'donut', fontFamily: 'Tajawal, sans-serif', foreColor: sub.value },
  labels: Object.keys(stats.value?.by_status || {}).map((k) => statusLabels[k] || k),
  legend: { position: 'bottom', labels: { colors: ink.value } },
  tooltip: { theme: theme.mode },
  colors: ['#ffc107', '#0dcaf0', '#198754', '#dc3545', '#0d6efd', '#6c757d'],
}))
const donutSeries = computed(() => Object.values(stats.value?.by_status || {}))

const barOptions = computed(() => ({
  chart: { type: 'bar', fontFamily: 'Tajawal, sans-serif', foreColor: sub.value },
  xaxis: {
    categories: stats.value?.months || [],
    labels: { style: { colors: sub.value, fontFamily: 'Tajawal, sans-serif' } },
  },
  yaxis: {
    labels: { style: { colors: sub.value } },
  },
  legend: { labels: { colors: ink.value } },
  tooltip: { theme: theme.mode },
  colors: ['#006c35'],
  plotOptions: { bar: { borderRadius: 6 } },
}))
const barSeries = computed(() => [{ name: 'الإيراد', data: stats.value?.monthly_revenue || [] }])

onMounted(async () => {
  const { data } = await api.get('/owner/stats')
  stats.value = data.data
})
</script>

<template>
  <div class="container py-4">
    <h4 class="mb-4">📊 لوحة المالك</h4>
    <div v-if="!stats" class="text-center py-5">جارِ التحميل...</div>
    <template v-else>
      <div class="row g-3 mb-4">
        <div class="col-md-3">
          <div class="card shadow-sm text-center"><div class="card-body">
            <div class="fs-3 fw-bold text-primary">{{ stats.bookings_total }}</div>
            <div class="text-muted">إجمالي الحجوزات</div>
          </div></div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm text-center"><div class="card-body">
            <div class="fs-3 fw-bold text-info">{{ stats.pending_confirmation }}</div>
            <div class="text-muted">بانتظار تأكيدك</div>
          </div></div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm text-center"><div class="card-body">
            <div class="fs-3 fw-bold text-warning">{{ stats.under_review }}</div>
            <div class="text-muted">دفعات بانتظار المراجعة</div>
          </div></div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm text-center"><div class="card-body">
            <div class="fs-3 fw-bold text-success">{{ stats.revenue_total }}</div>
            <div class="text-muted">إجمالي الإيراد (ريال)</div>
          </div></div>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-lg-5">
          <div class="card shadow-sm"><div class="card-body">
            <h6 class="mb-3">الحجوزات حسب الحالة</h6>
            <apexchart v-if="donutSeries.length" type="donut" height="280" :options="donutOptions" :series="donutSeries" />
            <div v-else class="text-muted small">لا حجوزات بعد.</div>
          </div></div>
        </div>
        <div class="col-lg-7">
          <div class="card shadow-sm"><div class="card-body">
            <h6 class="mb-3">الإيراد الشهري (آخر 6 أشهر)</h6>
            <apexchart type="bar" height="280" :options="barOptions" :series="barSeries" />
          </div></div>
        </div>
      </div>
    </template>
  </div>
</template>