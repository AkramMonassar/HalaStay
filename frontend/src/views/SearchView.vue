<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import api from '../services/api'
import HotelCard from '../components/HotelCard.vue'
import FilterSidebar from '../components/FilterSidebar.vue'
import AppSkeleton from '../components/AppSkeleton.vue'

const route = useRoute()
const hotels = ref([])
const loading = ref(false)

async function fetchResults() {
  loading.value = true
  try {
    const { data } = await api.get('/search', { params: route.query })
    hotels.value = data.data
  } finally {
    loading.value = false
  }
}

onMounted(fetchResults)
watch(() => route.query, fetchResults)
</script>

<template>
  <div class="container py-4">
    <div class="row g-4">
      <div class="col-lg-3">
        <FilterSidebar />
      </div>
      <div class="col-lg-9">
        <div v-if="loading" class="row g-3">
          <div v-for="i in 3" :key="i" class="col-md-4">
            <AppSkeleton variant="card" />
          </div>
        </div>

        <template v-else>
          <div v-if="hotels.length" class="mb-3">{{ $t('search.resultsFound') }}</div>
          <div v-else class="alert alert-info">{{ $t('search.noResults') }}</div>

          <div class="row g-3">
            <div v-for="h in hotels" :key="h.id" class="col-md-4">
              <HotelCard :hotel="h" />
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>