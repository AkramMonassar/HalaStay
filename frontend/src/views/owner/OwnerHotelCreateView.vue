<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'

const router = useRouter()
const cities = ref([])
const form = ref({
  city_id: '',
  name: '',
  description: '',
  address: '',
  phone: '',
  email: '',
  star_rating: 3,
})
const images = ref([])
const loading = ref(false)
const error = ref('')

onMounted(async () => {
  const { data } = await api.get('/cities')
  cities.value = data.data
})

function onFiles(e) {
  images.value = Array.from(e.target.files)
}

async function submit() {
  error.value = ''
  loading.value = true
  try {
    const fd = new FormData()
    Object.entries(form.value).forEach(([k, v]) => fd.append(k, v))
    images.value.forEach((img) => fd.append('images[]', img))

    const { data } = await api.post('/owner/hotels', fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    router.push({ name: 'owner-hotel-details', params: { id: data.data.id } })
  } catch (e) {
    const errors = e.response?.data?.errors
    error.value = errors
      ? Object.values(errors).flat()[0]
      : (e.response?.data?.message || 'تعذر إنشاء الفندق.')
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="container py-4" style="max-width: 720px">
    <h4 class="mb-4">➕ إضافة فندق جديد</h4>

    <div v-if="error" class="alert alert-danger py-2">{{ error }}</div>

    <form class="card shadow-sm" @submit.prevent="submit">
      <div class="card-body p-4">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">المدينة</label>
            <select v-model="form.city_id" class="form-select" required>
              <option value="" disabled>اختر المدينة</option>
              <option v-for="c in cities" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">التصنيف</label>
            <select v-model.number="form.star_rating" class="form-select">
              <option :value="1">1 نجوم</option>
              <option :value="2">2 نجوم</option>
              <option :value="3">3 نجوم</option>
              <option :value="4">4 نجوم</option>
              <option :value="5">5 نجوم</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">اسم الفندق</label>
            <input v-model="form.name" type="text" class="form-control" required />
          </div>
          <div class="col-12">
            <label class="form-label">الوصف</label>
            <textarea v-model="form.description" class="form-control" rows="3"></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label">العنوان</label>
            <input v-model="form.address" type="text" class="form-control" />
          </div>
          <div class="col-md-3">
            <label class="form-label">الهاتف</label>
            <input v-model="form.phone" type="text" class="form-control" />
          </div>
          <div class="col-md-3">
            <label class="form-label">البريد</label>
            <input v-model="form.email" type="email" class="form-control" />
          </div>
          <div class="col-12">
            <label class="form-label">صور الفندق</label>
            <input type="file" class="form-control" multiple accept="image/*" @change="onFiles" />
            <div class="form-text">أول صورة ستكون الغلاف.</div>
          </div>
        </div>
      </div>
      <div class="card-footer bg-white">
        <button class="btn btn-primary w-100" :disabled="loading">
          {{ loading ? 'جارِ الحفظ...' : 'حفظ الفندق' }}
        </button>
      </div>
    </form>
  </div>
</template>