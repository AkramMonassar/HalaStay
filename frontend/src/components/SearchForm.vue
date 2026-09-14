<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const props = defineProps({
  initial: { type: Object, default: null },
})

const router = useRouter()

const stored = JSON.parse(localStorage.getItem('halastay_last_search') || 'null')

const cities = ref([])
const error = ref('')
const form = ref({
  city_id: props.initial?.city_id || stored?.city_id || '',
  check_in: props.initial?.check_in || stored?.check_in || '',
  check_out: props.initial?.check_out || stored?.check_out || '',
  adults: Number(props.initial?.adults ?? stored?.adults) || 2,
  children: Number(props.initial?.children ?? stored?.children) || 0,
  rooms: Number(props.initial?.rooms ?? stored?.rooms) || 1,
})

onMounted(async () => {
  const { data } = await api.get('/cities')
  cities.value = data.data
})

function submit() {
  error.value = ''

  if (!form.value.city_id) {
    error.value = 'يرجى اختيار المدينة.'
    return
  }

  if (!form.value.check_in || !form.value.check_out) {
    error.value = 'يرجى تحديد تاريخي الدخول والخروج.'
    return
  }

  if (new Date(form.value.check_out) <= new Date(form.value.check_in)) {
    error.value = 'تاريخ الخروج يجب أن يكون بعد تاريخ الدخول.'
    return
  }

  localStorage.setItem('halastay_last_search', JSON.stringify(form.value))
  router.push({ name: 'search', query: { ...form.value } })
}
</script>

<template>
  <form @submit.prevent="submit">
    <div v-if="error" class="alert alert-danger py-2 mb-2 text-start">{{ error }}</div>

    <div class="row g-2 align-items-end">
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
    </div>
  </form>
</template>