<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '../services/api'

const router = useRouter()
const route = useRoute()

const cities = ref([])
const form = ref({
  city_id: route.query.city_id || '',
  check_in: route.query.check_in || '',
  check_out: route.query.check_out || '',
  adults: route.query.adults || 2,
  children: route.query.children || 0,
  rooms: route.query.rooms || 1,
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
      <label class="form-label">{{ $t('search.city') }}</label>
      <select v-model="form.city_id" class="form-select" required>
        <option value="" disabled>{{ $t('search.chooseCity') }}</option>
        <option v-for="c in cities" :key="c.id" :value="c.id">{{ c.name }}</option>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label">{{ $t('search.checkIn') }}</label>
      <input v-model="form.check_in" type="date" class="form-control" required />
    </div>
    <div class="col-md-2">
      <label class="form-label">{{ $t('search.checkOut') }}</label>
      <input v-model="form.check_out" type="date" class="form-control" required />
    </div>
    <div class="col-md-2">
      <label class="form-label">{{ $t('search.adults') }}</label>
      <input v-model.number="form.adults" type="number" min="1" class="form-control" />
    </div>
    <div class="col-md-1">
      <label class="form-label">{{ $t('search.children') }}</label>
      <input v-model.number="form.children" type="number" min="0" class="form-control" />
    </div>
    <div class="col-md-1">
      <label class="form-label">{{ $t('search.rooms') }}</label>
      <input v-model.number="form.rooms" type="number" min="1" class="form-control" />
    </div>
    <div class="col-md-1">
      <button class="btn btn-primary w-100" type="submit">{{ $t('search.searchBtn') }} 🔍</button>
    </div>
  </form>
</template>