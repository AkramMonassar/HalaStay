<script setup>
import { ref, onMounted } from 'vue'
import api from '../services/api'
import SearchForm from '../components/SearchForm.vue'

const cities = ref([])

onMounted(async () => {
  try {
    const { data } = await api.get('/cities')
    cities.value = data.data
  } catch {
    cities.value = []
  }
})
</script>

<template>
  <div>
    <section class="hero">
      <div class="container">
        <div class="hero-text">
          <h1>وجهتك التالية تبدأ من هنا</h1>
          <p>فنادق وشقق وقاعات في السعودية واليمن — ابحث، قارن، واحجز بثقة مع توفر لحظي ودفع مرن.</p>
        </div>
        <div class="hero-form">
          <SearchForm />
        </div>
      </div>
    </section>

    <section class="container py-5">
      <h3 class="mb-4">وجهات مميزة</h3>
      <div class="row g-3">
        <div v-for="c in cities" :key="c.id" class="col-6 col-md-3">
          <router-link :to="{ name: 'search', query: { city_id: c.id } }" class="city-card">
            <span class="city-name">{{ c.name }}</span>
            <span class="city-hint">استكشف الفنادق</span>
          </router-link>
        </div>
      </div>
    </section>

    <section class="features-strip">
      <div class="container">
        <div class="row g-4 text-center">
          <div class="col-md-4">
            <div class="feature-icon">🔍</div>
            <h5>بحث ذكي</h5>
            <p class="text-muted small mb-0">فلاتر حية حسب النوع والنجوم والتقييم مع توفر لحظي.</p>
          </div>
          <div class="col-md-4">
            <div class="feature-icon">🔒</div>
            <h5>حجز بلا تعارض</h5>
            <p class="text-muted small mb-0">وحداتك تُحجز لحظة إنشاء الحجز — لا ازدواج ولا مفاجآت.</p>
          </div>
          <div class="col-md-4">
            <div class="feature-icon">💳</div>
            <h5>دفع مرن</h5>
            <p class="text-muted small mb-0">محفظة، تحويل بنكي، أو عند الوصول — ومراجعة شفافة.</p>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>