<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const router = useRouter()

const cities = ref([])
const form = ref({
  city_id: '',
  check_in: '',
  check_out: '',
  adults: 2,
  children: 0,
  rooms: 1,
})

onMounted(async () => {
  const { data } = await api.get('/cities')
  cities.value = data.data
})

function submit() {
  router.push({ name: 'search', query: { ...form.value } })
}
</script>

<template>
  <form class="row g-2 align-items-end" @submit.prevent="submit">
    <div class="col-md-3">
      <label class="form-label">المدينة</label>
      <select v-model="form.city_id" class="form-select" required>
        <option value="" disabled>اختر المدينة</option>
        <option v-for="c in cities" :key="c.id" :value="c.id">{{ c.name }}</option>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label">الدخول</label>
      <input v-model="form.check_in" type="date" class="form-control" required />
    </div>
    <div class="col-md-2">
      <label class="form-label">الخروج</label>
      <input v-model="form.check_out" type="date" class="form-control" required />
    </div>
    <div class="col-md-1">
      <label class="form-label">بالغون</label>
      <input v-model.number="form.adults" type="number" min="1" class="form-control" />
    </div>
    <div class="col-md-1">
      <label class="form-label">أطفال</label>
      <input v-model.number="form.children" type="number" min="0" class="form-control" />
    </div>
    <div class="col-md-1">
      <label class="form-label">غرف</label>
      <input v-model.number="form.rooms" type="number" min="1" class="form-control" />
    </div>
    <div class="col-md-2">
      <button class="btn btn-primary w-100" type="submit">🔍 ابحث</button>
    </div>
  </form>
</template>