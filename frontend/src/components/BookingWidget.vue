<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../services/api'
import { useAuthStore } from '../stores/auth'
import AppDate from './AppDate.vue'

const props = defineProps({ hotelId: [Number, String] })

const router = useRouter()
const auth = useAuthStore()
const route = useRoute()

const form = ref({
  check_in: route.query.check_in || '',
  check_out: route.query.check_out || '',
  adults: Number(route.query.adults) || 2,
  children: Number(route.query.children) || 0,
  rooms: Number(route.query.rooms) || 1,
})

onMounted(() => {
  if (form.value.check_in && form.value.check_out) {
    checkAvailability()
  }
})

const loadingAvailability = ref(false)
const availableTypes = ref(null)
const availabilityMessage = ref('')

const bookingLoading = ref(false)
const bookingResult = ref(null)
const bookingError = ref('')

const nights = computed(() => {
  if (!form.value.check_in || !form.value.check_out) return 0
  const a = new Date(form.value.check_in)
  const b = new Date(form.value.check_out)
  const diff = (b - a) / (1000 * 60 * 60 * 24)
  return diff > 0 ? diff : 0
})

function bump(field, delta, min) {
  const next = form.value[field] + delta
  if (next >= min) form.value[field] = next
}

async function checkAvailability() {
  loadingAvailability.value = true
  availableTypes.value = null
  bookingResult.value = null
  bookingError.value = ''
  try {
    const { data } = await api.get(`/hotels/${props.hotelId}/availability`, {
      params: {
        check_in: form.value.check_in,
        check_out: form.value.check_out,
        rooms: form.value.rooms,
        adults: form.value.adults,
        children: form.value.children,
      },
    })
    availableTypes.value = data.data
    availabilityMessage.value = data.message
  } catch (e) {
    const errors = e.response?.data?.errors
    availabilityMessage.value = errors
      ? Object.values(errors).flat()[0]
      : (e.response?.data?.message || 'تعذر فحص التوفر.')
    availableTypes.value = []
  } finally {
    loadingAvailability.value = false
  }
}

async function book(type) {
  if (!auth.isAuthenticated) {
    const q = new URLSearchParams({
      check_in: form.value.check_in,
      check_out: form.value.check_out,
      adults: form.value.adults,
      children: form.value.children,
      rooms: form.value.rooms,
    }).toString()
    router.push({ name: 'login', query: { redirect: `/hotels/${props.hotelId}?${q}` } })
    return
  }

  bookingLoading.value = true
  bookingError.value = ''
  bookingResult.value = null
  try {
    const { data } = await api.post('/bookings', {
      accommodation_type_id: type.id,
      check_in: form.value.check_in,
      check_out: form.value.check_out,
      adults: form.value.adults,
      children: form.value.children,
      rooms: form.value.rooms,
    })
    bookingResult.value = data.data
  } catch (e) {
    const errors = e.response?.data?.errors
    bookingError.value = errors
      ? Object.values(errors).flat()[0]
      : (e.response?.data?.message || 'تعذر إنشاء الحجز.')
  } finally {
    bookingLoading.value = false
  }
}

function totalPrice(type) {
  return Number(type.base_price) * nights.value * form.value.rooms
}
</script>

<template>
  <div class="card shadow-sm">
    <div class="card-body p-4">
      <h5 class="mb-3">📅 احجز إقامتك</h5>

      <div class="row g-2 mb-2">
        <div class="col-6"><AppDate v-model="form.check_in" label="الدخول" /></div>
        <div class="col-6"><AppDate v-model="form.check_out" label="الخروج" /></div>
      </div>

      <div class="d-flex gap-2 mb-3">
        <div class="border rounded px-2 py-1 text-center flex-fill">
          <div class="small text-muted">بالغون</div>
          <div class="d-flex align-items-center justify-content-center gap-2">
            <button class="btn btn-sm btn-light" @click="bump('adults', -1, 1)">-</button>
            <span class="fw-bold">{{ form.adults }}</span>
            <button class="btn btn-sm btn-light" @click="bump('adults', 1, 1)">+</button>
          </div>
        </div>
        <div class="border rounded px-2 py-1 text-center flex-fill">
          <div class="small text-muted">أطفال</div>
          <div class="d-flex align-items-center justify-content-center gap-2">
            <button class="btn btn-sm btn-light" @click="bump('children', -1, 0)">-</button>
            <span class="fw-bold">{{ form.children }}</span>
            <button class="btn btn-sm btn-light" @click="bump('children', 1, 0)">+</button>
          </div>
        </div>
        <div class="border rounded px-2 py-1 text-center flex-fill">
          <div class="small text-muted">غرف</div>
          <div class="d-flex align-items-center justify-content-center gap-2">
            <button class="btn btn-sm btn-light" @click="bump('rooms', -1, 1)">-</button>
            <span class="fw-bold">{{ form.rooms }}</span>
            <button class="btn btn-sm btn-light" @click="bump('rooms', 1, 1)">+</button>
          </div>
        </div>
      </div>

      <button
        class="btn btn-primary w-100 mb-3"
        :disabled="loadingAvailability || !form.check_in || !form.check_out"
        @click="checkAvailability"
      >
        {{ loadingAvailability ? 'جارِ الفحص...' : 'افحص التوفر' }}
      </button>

      <div v-if="availableTypes !== null">
        <p class="text-muted small mb-2">{{ availabilityMessage }}</p>

        <div v-if="availableTypes.length" class="d-flex flex-column gap-2">
          <div
            v-for="t in availableTypes" :key="t.id"
            class="border rounded p-2 d-flex justify-content-between align-items-center"
          >
            <div>
              <div class="fw-semibold">{{ t.name }}</div>
              <div class="small text-muted">
                متبقٍ {{ t.available_units }} — {{ t.base_price }} ريال/ليلة
                <span v-if="nights">| الإجمالي: {{ totalPrice(t) }} ريال</span>
              </div>
            </div>
            <button class="btn btn-success btn-sm" :disabled="bookingLoading" @click="book(t)">
              احجز الآن
            </button>
          </div>
        </div>

        <div v-else class="alert alert-warning py-2 mb-0">
          لا تتوفر وحدات لهذه المعايير — جرّب تعديل التواريخ أو الغرف.
        </div>
      </div>

      <div v-if="bookingError" class="alert alert-danger py-2 mt-3 mb-0">{{ bookingError }}</div>

      <div v-if="bookingResult" class="alert alert-success mt-3 mb-0">
        ✔ تم إنشاء الحجز برقم <b>{{ bookingResult.booking_number }}</b> وحالته بانتظار الدفع.
        <div class="small mt-1">صفحة إتمام الدفع داخل الواجهة تصل في الجلسة 19.</div>
      </div>
    </div>
  </div>
</template>