<script setup>
import { ref, onMounted } from 'vue'
import api from '../services/api'

const cities = ref([])
const loading = ref(true)

onMounted(async () => {
  try {
    const { data } = await api.get('/cities')
    cities.value = data.data
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div>
    <section class="py-5 text-center bg-white border-bottom">
      <div class="container py-4">
        <h1 class="display-5 fw-bold text-primary">هلا ستاي</h1>
        <p class="lead text-muted mb-0">منصة حجز الفنادق والشقق والقاعات — ابحث واحجز وادفع بثقة.</p>
      </div>
    </section>

    <section class="container py-4">
      <h5 class="mb-3">مدننا المتاحة</h5>
      <p v-if="loading">جارِ التحميل...</p>
      <div v-else class="d-flex flex-wrap gap-2">
        <span
          v-for="city in cities"
          :key="city.id"
          class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle fs-6"
        >
          {{ city.name }}
        </span>
      </div>
    </section>
  </div>
</template>