<script setup>
import { ref, onMounted, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '../../services/api'
import { useThemeStore } from '../../stores/theme'

const { t } = useI18n()
const theme = useThemeStore()
const stats = ref(null)

const ink = computed(() => (theme.mode === 'dark' ? '#e9ecef' : '#373d3f'))
const sub = computed(() => (theme.mode === 'dark' ? '#adb5bd' : '#6c757d'))

const donutOptions = computed(() => ({
  chart: { type: 'donut', fontFamily: 'Tajawal, sans-serif', foreColor: sub.value },
  labels: Object.keys(stats.value?.bookings?.by_status || {}).map((k) => t(`statuses.${k}`)),
  legend: { position: 'bottom', labels: { colors: ink.value } },
  tooltip: { theme: theme.mode },
}))
const donutSeries = computed(() => Object.values(stats.value?.bookings?.by_status || {}))

const revenueOptions = computed(() => ({
  chart: { type: 'bar', fontFamily: 'Tajawal, sans-serif', foreColor: sub.value },
  xaxis: { categories: stats.value?.months || [] },
  legend: { labels: { colors: ink.value } },
  tooltip: { theme: theme.mode },
  colors: ['#006c35'],
  plotOptions: { bar: { borderRadius: 5 } },
}))
const revenueSeries = computed(() => [{ name: t('admin.totalRevenue'), data: stats.value?.monthly_revenue || [] }])

const growthOptions = computed(() => ({
  chart: { type: 'line', fontFamily: 'Tajawal, sans-serif', foreColor: sub.value },
  xaxis: { categories: stats.value?.months || [] },
  legend: { labels: { colors: ink.value } },
  tooltip: { theme: theme.mode },
  colors: ['#1a5490'],
  stroke: { width: 3, curve: 'smooth' },
}))
const growthSeries = computed(() => [{ name: t('admin.bookings'), data: stats.value?.monthly_bookings || [] }])

onMounted(async () => {
  const { data } = await api.get('/admin/stats')
  stats.value = data.data
})
</script>

<template>
  <div class="container py-4">
    <h4 class="mb-4">👑 {{ $t('admin.panelTitle') }}</h4>
    <div v-if="!stats" class="text-center py-5">{{ $t('common.loading') }}</div>
    <template v-else>
      <div class="row g-3 mb-4">
        <div class="col-md-3">
          <div class="card shadow-sm text-center"><div class="card-body">
            <div class="fs-3 fw-bold text-primary">{{ stats.users.total }}</div>
            <div class="text-muted">{{ $t('admin.users') }}</div>
            <div class="small">
              {{ $t('admin.tourists') }} {{ stats.users.tourists }} —
              {{ $t('admin.owners') }} {{ stats.users.owners }} —
              {{ $t('admin.admins') }} {{ stats.users.admins }}
            </div>
          </div></div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm text-center"><div class="card-body">
            <div class="fs-3 fw-bold text-success">{{ stats.hotels.total }}</div>
            <div class="text-muted">{{ $t('admin.hotels') }}</div>
            <div class="small">
              {{ $t('admin.approved') }} {{ stats.hotels.approved }} –
              {{ $t('admin.pendingApproval') }} {{ stats.hotels.pending }}
            </div>
          </div></div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm text-center"><div class="card-body">
            <div class="fs-3 fw-bold text-info">{{ stats.bookings.total }}</div>
            <div class="text-muted">{{ $t('admin.bookings') }}</div>
            <div class="small">{{ $t('admin.totalRevenue') }}: {{ stats.payments.total_success_amount }}</div>
          </div></div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm text-center"><div class="card-body">
            <div class="fs-3 fw-bold text-warning">{{ stats.payments.under_review }}</div>
            <div class="text-muted">{{ $t('admin.underReview') }}</div>
          </div></div>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-lg-4">
          <div class="card shadow-sm"><div class="card-body">
            <h6 class="mb-3">{{ $t('admin.byStatus') }}</h6>
            <apexchart v-if="donutSeries.length" type="donut" height="260" :options="donutOptions" :series="donutSeries" />
            <div v-else class="text-muted small">{{ $t('owner.noBookings') }}</div>
          </div></div>
        </div>
        <div class="col-lg-4">
          <div class="card shadow-sm"><div class="card-body">
            <h6 class="mb-3">{{ $t('admin.monthlyRevenue') }}</h6>
            <apexchart type="bar" height="260" :options="revenueOptions" :series="revenueSeries" />
          </div></div>
        </div>
        <div class="col-lg-4">
          <div class="card shadow-sm"><div class="card-body">
            <h6 class="mb-3">{{ $t('admin.growth') }}</h6>
            <apexchart type="line" height="260" :options="growthOptions" :series="growthSeries" />
          </div></div>
        </div>
      </div>

      <div class="d-flex gap-2 flex-wrap">
        <router-link to="/admin/hotels" class="btn btn-outline-primary">{{ $t('admin.approveHotels') }}</router-link>
        <router-link to="/admin/bookings" class="btn btn-outline-primary">{{ $t('admin.allBookings') }}</router-link>
        <router-link to="/admin/payments" class="btn btn-outline-primary">{{ $t('admin.allPayments') }}</router-link>
        <router-link to="/admin/settings" class="btn btn-outline-primary">{{ $t('admin.citiesMethods') }}</router-link>
      </div>
    </template>
  </div>
</template>