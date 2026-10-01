<script setup>
import { ref, onMounted } from 'vue'
import api from '../services/api'

const props = defineProps({ hotelId: { type: [Number, String], required: true } })
const reviews = ref([])
const loading = ref(true)

onMounted(async () => {
  try {
    const { data } = await api.get(`/hotels/${props.hotelId}/reviews`)
    reviews.value = data.data
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <section class="mt-4">
    <h5 class="mb-3">⭐ {{ $t('reviews.title') }} ({{ reviews.length }})</h5>
    <div v-if="loading" class="text-muted small">{{ $t('common.loading') }}</div>
    <div v-else-if="reviews.length" class="d-flex flex-column gap-2">
      <div v-for="r in reviews" :key="r.id" class="border rounded p-3">
        <div class="d-flex justify-content-between align-items-center">
          <div class="fw-semibold">{{ r.reviewer }}</div>
          <div class="text-warning">{{ '★'.repeat(r.rating) }}{{ '☆'.repeat(5 - r.rating) }}</div>
        </div>
        <div v-if="r.comment" class="small text-muted mt-1">{{ r.comment }}</div>
        <div class="small text-muted mt-1">{{ r.created_at }}</div>
      </div>
    </div>
    <div v-else class="alert alert-info mb-0">{{ $t('reviews.empty') }}</div>
  </section>
</template>