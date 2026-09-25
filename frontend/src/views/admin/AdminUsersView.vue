<script setup>
import { ref, onMounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '../../services/api'
import { useAuthStore } from '../../stores/auth'
import { useToastStore } from '../../stores/toast'

const { t } = useI18n()
const auth = useAuthStore()
const toast = useToastStore()

const users = ref([])
const loading = ref(true)
const roleFilter = ref('')
const search = ref('')

const roleKey = { tourist: 'admin.tourists', hotel_owner: 'admin.owners', admin: 'admin.admins' }

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
  try {
    const { data } = await api.patch(`/admin/users/${u.id}/toggle-active`)
    const idx = users.value.findIndex((x) => x.id === u.id)
    if (idx >= 0) users.value[idx] = data.data
    toast.push(data.message)
  } catch (e) {
    toast.push(e.response?.data?.message || t('auth.loginFailed'), 'danger')
  }
}
</script>

<template>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 gap-2 flex-wrap">
      <h4 class="mb-0">👥 {{ $t('admin.usersTitle') }}</h4>
      <div class="d-flex gap-2">
        <input v-model="search" class="form-control" :placeholder="$t('users.searchPh')" style="width: 220px" />
        <select v-model="roleFilter" class="form-select" style="width: auto">
          <option value="">{{ $t('users.allRoles') }}</option>
          <option value="tourist">{{ $t('admin.tourists') }}</option>
          <option value="hotel_owner">{{ $t('admin.owners') }}</option>
          <option value="admin">{{ $t('admin.admins') }}</option>
        </select>
      </div>
    </div>

    <div v-if="loading" class="text-center py-5">{{ $t('common.loading') }}</div>
    <div v-else class="d-flex flex-column gap-2">
      <div v-for="u in users" :key="u.id" class="card shadow-sm">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div>
            <div class="fw-semibold">
              {{ u.name }}
              <span class="badge ms-1 bg-primary">{{ $t(roleKey[u.role]) }}</span>
              <span class="badge ms-1" :class="u.is_active ? 'bg-success' : 'bg-danger'">
                {{ u.is_active ? $t('users.active') : $t('users.inactive') }}
              </span>
            </div>
            <div class="small text-muted">
              {{ u.email }} <span v-if="u.phone">· <span dir="ltr">{{ u.phone }}</span></span>
            </div>
          </div>
          <button v-if="u.id !== auth.user?.id" class="btn btn-outline-secondary btn-sm" @click="toggle(u)">
            {{ u.is_active ? $t('users.disable') : $t('users.enable') }}
          </button>
          <span v-else class="small text-muted">{{ $t('users.self') }}</span>
        </div>
      </div>
    </div>
  </div>
</template>