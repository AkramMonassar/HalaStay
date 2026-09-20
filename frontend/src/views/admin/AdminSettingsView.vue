<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'

const cities = ref([])
const countries = ref([])
const methods = ref([])
const message = ref('')
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
  message.value = ''
  try {
    await api.post('/admin/cities', newCity.value)
    newCity.value = { country_id: '', name: '' }
    message.value = 'تم إضافة المدينة.'
    fetchAll()
  } catch (e) {
    const errors = e.response?.data?.errors
    message.value = errors ? Object.values(errors).flat()[0] : 'تعذر الإضافة.'
  }
}

async function toggleCity(c) {
  try {
    await api.patch(`/admin/cities/${c.id}`, { is_active: !c.is_active })
    fetchAll()
  } catch (e) {
    message.value = e.response?.data?.message || 'تعذر التبديل.'
  }
}

async function toggleMethod(m) {
  try {
    await api.patch(`/admin/payment-methods/${m.id}/toggle`)
    fetchAll()
  } catch (e) {
    message.value = e.response?.data?.message || 'تعذر التبديل.'
  }
}
</script>

<template>
  <div class="container py-4">
    <h4 class="mb-4">⚙️ الإعدادات</h4>
    <div v-if="message" class="alert alert-info py-2">{{ message }}</div>

    <div class="row g-4">
      <div class="col-lg-6">
        <div class="card shadow-sm">
          <div class="card-header bg-white fw-semibold">المدن</div>
          <div class="card-body">
            <div class="row g-2 mb-3">
              <div class="col-5">
                <select v-model="newCity.country_id" class="form-select">
                  <option value="" disabled>الدولة</option>
                  <option v-for="co in countries" :key="co.id" :value="co.id">{{ co.name }}</option>
                </select>
              </div>
              <div class="col-4"><input v-model="newCity.name" class="form-control" placeholder="اسم المدينة" /></div>
              <div class="col-3"><button class="btn btn-primary w-100" @click="addCity">إضافة</button></div>
            </div>

            <div v-for="c in cities" :key="c.id" class="d-flex justify-content-between align-items-center border rounded p-2 mb-1">
              <div>
                {{ c.name }}
                <span class="badge ms-1" :class="c.is_active ? 'bg-success' : 'bg-secondary'">{{ c.is_active ? 'نشطة' : 'موقوفة' }}</span>
              </div>
              <button class="btn btn-outline-secondary btn-sm" @click="toggleCity(c)">{{ c.is_active ? 'إيقاف' : 'تفعيل' }}</button>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card shadow-sm">
          <div class="card-header bg-white fw-semibold">طرق الدفع</div>
          <div class="card-body">
            <div v-for="m in methods" :key="m.id" class="d-flex justify-content-between align-items-center border rounded p-2 mb-1">
              <div>
                {{ m.name }}
                <span class="badge ms-1 bg-light text-dark">{{ m.type }}</span>
                <span class="badge ms-1" :class="m.is_active ? 'bg-success' : 'bg-secondary'">{{ m.is_active ? 'نشطة' : 'موقوفة' }}</span>
              </div>
              <button class="btn btn-outline-secondary btn-sm" @click="toggleMethod(m)">{{ m.is_active ? 'إيقاف' : 'تفعيل' }}</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>