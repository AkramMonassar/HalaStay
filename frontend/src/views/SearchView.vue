<script setup>
import { ref, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../services/api'
import FilterSidebar from '../components/FilterSidebar.vue'
import HotelCard from '../components/HotelCard.vue'
import SearchForm from '../components/SearchForm.vue'

const route = useRoute()
const router = useRouter()

const loading = ref(false)
const results = ref([])
const meta = ref(null)
const message = ref('')
const filters = ref({})

const highCapacityDemand = computed(() => {
  const guests = Number(filters.value.adults || 0) + Number(filters.value.children || 0)
  const rooms = Number(filters.value.rooms || 1)
  return guests > rooms * 4
})

function readQuery() {
  const q = route.query
  filters.value = {
    city_id: q.city_id || '',
    check_in: q.check_in || '',
    check_out: q.check_out || '',
    adults: q.adults || 2,
    children: q.children || 0,
    rooms: q.rooms || 1,
    stay_type: q.stay_type ? (Array.isArray(q.stay_type) ? q.stay_type : [q.stay_type]) : [],
    star_rating: q.star_rating ? (Array.isArray(q.star_rating) ? q.star_rating : [q.star_rating]) : [],
    min_review: q.min_review || '',
    page: q.page || 1,
  }
}

function clean(obj) {
  const out = {}
  Object.entries(obj).forEach(([k, v]) => {
    if (v === '' || v === null || v === undefined) return
    if (Array.isArray(v) && v.length === 0) return
    out[k] = v
  })
  return out
}

async function fetchResults() {
  if (!filters.value.city_id || !filters.value.check_in || !filters.value.check_out) return
  loading.value = true
  try {
    const { data } = await api.get('/search', { params: clean(filters.value) })
    results.value = data.data
    meta.value = data.meta
    message.value = data.message
  } catch (e) {
    message.value = e.response?.data?.message || 'تعذر تنفيذ البحث.'
    results.value = []
  } finally {
    loading.value = false
  }
}

function applyFilters(next) {
  router.push({ name: 'search', query: clean({ ...route.query, ...next, page: undefined }) })
}

function goPage(p) {
  router.push({ name: 'search', query: { ...route.query, page: p } })
}

watch(() => route.query, () => { readQuery(); fetchResults() }, { immediate: true })
</script>

<template>
  <div class="container py-4">
    <div class="card shadow-sm mb-3">
      <div class="card-body">
        <SearchForm :initial="filters" />
      </div>
    </div>

    <div class="row">
      <div class="col-lg-3 mb-3">
        <FilterSidebar :filters="filters" @update="applyFilters" />
      </div>
      <div class="col-lg-9">
        <p class="text-muted mb-3">{{ message }}</p>

        <div v-if="loading" class="text-center py-5">جارِ البحث...</div>

        <div v-else-if="results.length" class="row g-3">
          <div v-for="h in results" :key="h.id" class="col-md-6 col-xl-4">
            <HotelCard :hotel="h" />
          </div>
        </div>

        <div v-else class="alert alert-info">
          لا توجد نتائج مطابقة — جرّب تعديل المعايير.
          <div v-if="highCapacityDemand" class="small mt-2">
            💡 عدد الأفراد كبير مقارنة بعدد الغرف — معظم المنشآت لا تستوعب هذا العدد في غرفة واحدة؛ جرّب زيادة عدد الغرف.
          </div>
        </div>

        <nav v-if="meta && meta.last_page > 1" class="mt-4">
          <ul class="pagination justify-content-center">
            <li class="page-item" :class="{ disabled: meta.current_page === 1 }">
              <button class="page-link" @click="goPage(meta.current_page - 1)">السابق</button>
            </li>
            <li v-for="p in meta.last_page" :key="p" class="page-item" :class="{ active: p === meta.current_page }">
              <button class="page-link" @click="goPage(p)">{{ p }}</button>
            </li>
            <li class="page-item" :class="{ disabled: meta.current_page === meta.last_page }">
              <button class="page-link" @click="goPage(meta.current_page + 1)">التالي</button>
            </li>
          </ul>
        </nav>
      </div>
    </div>
  </div>
</template>