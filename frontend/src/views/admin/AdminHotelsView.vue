<script setup>
import { ref, onMounted, watch } from 'vue'
import api from '../../services/api'

const hotels = ref([])
const loading = ref(true)
const statusFilter = ref('pending')
const message = ref('')

const statusLabels = { pending: 'بانتظار الموافقة', approved: 'معتمد', rejected: 'مرفوض', suspended: 'موقوف' }
const statusClasses = { pending: 'bg-warning', approved: 'bg-success', rejected: 'bg-danger', suspended: 'bg-secondary' }

async function fetchHotels() {
  loading.value = true
  try {
    const params = statusFilter.value ? { status: statusFilter.value } : {}
    const { data } = await api.get('/admin/hotels', { params })
    hotels.value = data.data
  } finally {
    loading.value = false
  }
}

onMounted(fetchHotels)
watch(statusFilter, fetchHotels)

async function approve(h) {
  if (!confirm(`اعتماد فندق ${h.name}؟ سيظهر في البحث فوراً.`)) return
  message.value = ''
  try {
    await api.patch(`/admin/hotels/${h.id}/approve`)
    message.value = 'تم اعتماد الفندق.'
    fetchHotels()
  } catch (e) {
    message.value = e.response?.data?.message || 'تعذر الاعتماد.'
  }
}

async function reject(h) {
  if (!confirm(`رفض فندق ${h.name}؟`)) return
  message.value = ''
  try {
    await api.patch(`/admin/hotels/${h.id}/reject`)
    message.value = 'تم رفض الفندق.'
    fetchHotels()
  } catch (e) {
    message.value = e.response?.data?.message || 'تعذر الرفض.'
  }
}
</script>

<template>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="mb-0">🏨 إدارة الفنادق</h4>
      <select v-model="statusFilter" class="form-select" style="width: auto">
        <option value="pending">بانتظار الموافقة</option>
        <option value="">الكل</option>
        <option value="approved">معتمدة</option>
        <option value="rejected">مرفوضة</option>
        <option value="suspended">موقوفة</option>
      </select>
    </div>

    <div v-if="message" class="alert alert-info py-2">{{ message }}</div>
    <div v-if="loading" class="text-center py-5">جارِ التحميل...</div>

    <div v-else-if="hotels.length" class="d-flex flex-column gap-2">
      <div v-for="h in hotels" :key="h.id" class="card shadow-sm">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div>
            <div class="fw-semibold">
              {{ h.name }}
              <span class="badge ms-1" :class="statusClasses[h.status]">{{ statusLabels[h.status] }}</span>
            </div>
            <div class="small text-muted">{{ h.city }} — المالك: {{ h.owner_name }} — {{ h.star_rating }} نجوم</div>
          </div>
          <div v-if="h.status === 'pending'" class="d-flex gap-1">
            <button class="btn btn-success btn-sm" @click="approve(h)">اعتماد</button>
            <button class="btn btn-outline-danger btn-sm" @click="reject(h)">رفض</button>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="alert alert-success">لا توجد فنادق بهذه الحالة ✔</div>
  </div>
</template>