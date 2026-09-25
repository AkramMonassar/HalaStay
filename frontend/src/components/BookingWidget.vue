<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import api from '../services/api'
import AppDate from './AppDate.vue'

const { t } = useI18n()
const props = defineProps({ hotel: { type: Object, required: true } })
const router = useRouter()

const iso = (d) => d.toISOString().slice(0, 10)
const tomorrow = new Date(Date.now() + 86400000)
const afterTomorrow = new Date(Date.now() + 2 * 86400000)

const form = ref({ check_in: iso(tomorrow), check_out: iso(afterTomorrow), adults: 2, children: 0, rooms: 1 })
const types = ref([])
const loading = ref(false)
const booking = ref(false)
const error = ref('')

function step(key, delta, min = 0) {
  form.value[key] = Math.max(min, form.value[key] + delta)
}

async function check() {
  error.value = ''
  types.value = []
  loading.value = true
  try {
    const { data } = await api.get(`/hotels/${props.hotel.id}/availability`, { params: form.value })
    types.value = data.data
  } catch (e) {
    const errors = e.response?.data?.errors
    error.value = errors ? Object.values(errors).flat()[0] : (e.response?.data?.message || t('widget.none'))
  } finally {
    loading.value = false
  }
}

async function book(ty) {
  error.value = ''
  booking.value = true
  try {
    const { data } = await api.post('/bookings', { ...form.value, accommodation_type_id: ty.id })
    router.push({ name: 'booking', params: { id: data.data.id } })
  } catch (e) {
    const errors = e.response?.data?.errors
    error.value = errors ? Object.values(errors).flat()[0] : (e.response?.data?.message || t('auth.loginFailed'))
  } finally {
    booking.value = false
  }
}
</script>

<template>
  <div class="card shadow-sm">
    <div class="card-header bg-white fw-semibold">📅 {{ $t('widget.title') }}</div>
    <div class="card-body">
      <div class="d-flex flex-column gap-2 mb-3">
        <AppDate v-model="form.check_in" :label="$t('widget.checkIn')" />
        <AppDate v-model="form.check_out" :label="$t('widget.checkOut')" />
      </div>

      <div class="row g-2 mb-3 text-center">
        <div class="col-4">
          <div class="small text-muted mb-1">{{ $t('search.adults') }}</div>
          <div class="stepper">
            <button type="button" class="btn btn-sm btn-light" @click="step('adults', 1)">+</button>
            <span>{{ form.adults }}</span>
            <button type="button" class="btn btn-sm btn-light" @click="step('adults', -1, 1)">−</button>
          </div>
        </div>
        <div class="col-4">
          <div class="small text-muted mb-1">{{ $t('search.children') }}</div>
          <div class="stepper">
            <button type="button" class="btn btn-sm btn-light" @click="step('children', 1)">+</button>
            <span>{{ form.children }}</span>
            <button type="button" class="btn btn-sm btn-light" @click="step('children', -1)">−</button>
          </div>
        </div>
        <div class="col-4">
          <div class="small text-muted mb-1">{{ $t('search.rooms') }}</div>
          <div class="stepper">
            <button type="button" class="btn btn-sm btn-light" @click="step('rooms', 1)">+</button>
            <span>{{ form.rooms }}</span>
            <button type="button" class="btn btn-sm btn-light" @click="step('rooms', -1, 1)">−</button>
          </div>
        </div>
      </div>

      <button class="btn btn-primary w-100" :disabled="loading" @click="check">
        {{ loading ? $t('widget.checking') : $t('widget.check') }}
      </button>

      <div v-if="error" class="alert alert-danger py-2 small mt-2">{{ error }}</div>

      <div v-if="types.length" class="mt-3">
        <div class="small text-muted mb-2">{{ $t('widget.available') }}</div>
        <div v-for="ty in types" :key="ty.id" class="border rounded p-2 mb-2 d-flex justify-content-between align-items-center">
          <div>
            <div class="fw-semibold small">{{ ty.name }}</div>
            <div class="small text-muted">
              {{ $t('widget.remaining') }} {{ ty.available_units }} — {{ ty.base_price }} {{ $t('hotel.perNight') }}
            </div>
          </div>
          <button class="btn btn-success btn-sm" :disabled="booking" @click="book(ty)">{{ $t('widget.book') }}</button>
        </div>
      </div>
      <div v-else-if="!loading && !error" class="small text-muted mt-2">{{ $t('widget.none') }}</div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.stepper { display: flex; align-items: center; justify-content: space-between; border: 1px solid #dee2e6; border-radius: 8px; padding: 2px 6px; }
.stepper span { font-weight: 600; }
</style>