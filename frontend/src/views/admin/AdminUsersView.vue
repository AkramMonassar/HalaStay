<script setup>
import { ref, onMounted, watch } from 'vue'
import api from '../../services/api'
import { useAuthStore } from '../../stores/auth'

const auth = useAuthStore()
const users = ref([])
const loading = ref(true)
const roleFilter = ref('')
const search = ref('')
const message = ref('')

const roleLabels = { tourist: 'سائح', hotel_owner: 'صاحب فندق', admin: 'أدمن' }

async function fetchUsers() {
  loading.value = true
  try {
    const params = {}
    if (roleFilter.value) params.role = roleFilter.value
    if (search.value) params.search = search.value
    const { data } = await api.get('/admin/users', { params })
    users.value = data.data
  } finally {
    loading.value = false
  }
}

onMounted(fetchUsers)
watch(roleFilter, fetchUsers)

let timer = null
watch(search, () => {
  clearTimeout(timer)
  timer = setTimeout(fetchUsers, 400)
})

async function toggle(u) {
  message.value = ''
  try {
    const { data } = await api.patch(`/admin/users/${u.id}/toggle-active`)
    const idx = users.value.findIndex((x) => x.id === u.id)
    if (idx >= 0) users.value[idx] = data.data
    message.value = data.message
  } catch (e) {
    message.value = e.response?.data?.message || 'تعذر التبديل.'
  }
}
</script>

<template>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 gap-2 flex-wrap">
      <h4 class="mb-0">👥 المستخدمون</h4>
      <div class="d-flex gap-2">
        <input v-model="search" class="form-control" placeholder="بحث بالاسم أو البريد..." style="width: 220px" />
        <select v-model="roleFilter" class="form-select" style="width: auto">
          <option value="">كل الأدوار</option>
          <option value="tourist">سياح</option>
          <option value="hotel_owner">ملاك</option>
          <option value="admin">أدمن</option>
        </select>
      </div>
    </div>

    <div v-if="message" class="alert alert-info py-2">{{ message }}</div>
    <div v-if="loading" class="text-center py-5">جارِ التحميل...</div>

    <div v-else class="d-flex flex-column gap-2">
      <div v-for="u in users" :key="u.id" class="card shadow-sm">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div>
            <div class="fw-semibold">
              {{ u.name }}
              <span class="badge ms-1 bg-primary">{{ roleLabels[u.role] }}</span>
              <span class="badge ms-1" :class="u.is_active ? 'bg-success' : 'bg-danger'">{{ u.is_active ? 'نشط' : 'موقوف' }}</span>
            </div>
            <div class="small text-muted">{{ u.email }}</div>
            <div v-if="u.phone" class="small text-muted">📞 <span dir="ltr">{{ u.phone }}</span></div>
          </div>
          <button v-if="u.id !== auth.user?.id" class="btn btn-outline-secondary btn-sm" @click="toggle(u)">
            {{ u.is_active ? 'إيقاف' : 'تفعيل' }}
          </button>
          <span v-else class="small text-muted">(حسابك الحالي)</span>
        </div>
      </div>
    </div>
  </div>
</template>