<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '../../services/api'
import { useToastStore } from '../../stores/toast'

const { t } = useI18n()
const toast = useToastStore()

const cities = ref([])
const countries = ref([])
const methods = ref([])
const newCity = ref({ country_id: '', name: '' })

async function fetchAll() {
  const [c, co, m] = await Promise.all([
    api.get('/admin/cities'),
    api.get('/countries'),
    api.get('/admin/payment-methods'),
  ])
  cities.value = c.data.data
  countries.value = co.data.data
  methods.value = m.data.data
}

onMounted(fetchAll)

async function addCity() {
  try {
    await api.post('/admin/cities', newCity.value)
    newCity.value = { country_id: '', name: '' }
    toast.push(t('settings.addedCity'), 'success')
    fetchAll()
  } catch (e) {
    const errors = e.response?.data?.errors
    toast.push(errors ? Object.values(errors).flat()[0] : t('auth.loginFailed'), 'danger')
  }
}

async function toggleCity(c) {
  try {
    await api.patch(`/admin/cities/${c.id}`, { is_active: !c.is_active })
    fetchAll()
  } catch (e) {
    toast.push(e.response?.data?.message || t('auth.loginFailed'), 'danger')
  }
}

async function toggleMethod(m) {
  try {
    await api.patch(`/admin/payment-methods/${m.id}/toggle`)
    fetchAll()
  } catch (e) {
    toast.push(e.response?.data?.message || t('auth.loginFailed'), 'danger')
  }
}
</script>

<template>
  <div class="container py-4">
    <h4 class="mb-4">⚙️ {{ $t('admin.settingsTitle') }}</h4>

    <div class="row g-4">
      <div class="col-lg-6">
        <div class="card shadow-sm">
          <div class="card-header bg-white fw-semibold">{{ $t('settings.cities') }}</div>
          <div class="card-body">
            <div class="row g-2 mb-3">
              <div class="col-5">
                <select v-model="newCity.country_id" class="form-select">
                  <option value="" disabled>{{ $t('settings.country') }}</option>
                  <option v-for="co in countries" :key="co.id" :value="co.id">{{ co.name }}</option>
                </select>
              </div>
              <div class="col-4"><input v-model="newCity.name" class="form-control" :placeholder="$t('settings.cityName')" /></div>
              <div class="col-3"><button class="btn btn-primary w-100" @click="addCity">{{ $t('settings.addCity') }}</button></div>
            </div>

            <div v-for="c in cities" :key="c.id" class="d-flex justify-content-between align-items-center border rounded p-2 mb-1">
              <div>
                {{ c.name }}
                <span class="badge ms-1" :class="c.is_active ? 'bg-success' : 'bg-secondary'">
                  {{ c.is_active ? $t('settings.activeF') : $t('settings.inactiveF') }}
                </span>
              </div>
              <button class="btn btn-outline-secondary btn-sm" @click="toggleCity(c)">
                {{ c.is_active ? $t('users.disable') : $t('users.enable') }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card shadow-sm">
          <div class="card-header bg-white fw-semibold">{{ $t('settings.methods') }}</div>
          <div class="card-body">
            <div v-for="m in methods" :key="m.id" class="d-flex justify-content-between align-items-center border rounded p-2 mb-1">
              <div>
                {{ m.name }}
                <span class="badge ms-1 bg-light text-dark">{{ m.type }}</span>
                <span class="badge ms-1" :class="m.is_active ? 'bg-success' : 'bg-secondary'">
                  {{ m.is_active ? $t('settings.activeF') : $t('settings.inactiveF') }}
                </span>
              </div>
              <button class="btn btn-outline-secondary btn-sm" @click="toggleMethod(m)">
                {{ m.is_active ? $t('users.disable') : $t('users.enable') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>