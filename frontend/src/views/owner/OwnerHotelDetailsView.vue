<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import api from '../../services/api'
import { useToastStore } from '../../stores/toast'

const { t } = useI18n()
const toast = useToastStore()
const route = useRoute()

const hotel = ref(null)
const loading = ref(true)
const saving = ref(false)
const form = ref({ name: '', description: '', address: '', phone: '', email: '', star_rating: 3 })
const typeForm = ref({ name: '', stay_type: 'room', base_price: 100, max_adults: 2, max_children: 0, units_count: 1 })
const storageBase = (import.meta.env.VITE_API_BASE_URL || '').replace('/api/v1', '') + '/storage/'

const statusClasses = { pending: 'bg-warning', approved: 'bg-success', rejected: 'bg-danger', suspended: 'bg-secondary' }

async function fetchHotel() {
  loading.value = true
  try {
    const { data } = await api.get(`/owner/hotels/${route.params.id}`)
    hotel.value = data.data
    form.value = {
      name: hotel.value.name,
      description: hotel.value.description || '',
      address: hotel.value.address || '',
      phone: hotel.value.phone || '',
      email: hotel.value.email || '',
      star_rating: hotel.value.star_rating,
    }
  } finally {
    loading.value = false
  }
}

onMounted(fetchHotel)

async function save() {
  saving.value = true
  try {
    await api.patch(`/owner/hotels/${hotel.value.id}`, form.value)
    toast.push(t('ownerHotel.save') + ' ✔', 'success')
    fetchHotel()
  } catch (e) {
    const errors = e.response?.data?.errors
    toast.push(errors ? Object.values(errors).flat()[0] : t('auth.loginFailed'), 'danger')
  } finally {
    saving.value = false
  }
}

async function uploadImage(e) {
  const file = e.target.files[0]
  if (!file) return
  const fd = new FormData()
  fd.append('image', file)
  try {
    await api.post(`/owner/hotels/${hotel.value.id}/images`, fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    toast.push(t('ownerHotel.imagesTitle') + ' ✔', 'success')
    fetchHotel()
  } catch (err) {
    toast.push(err.response?.data?.message || t('auth.loginFailed'), 'danger')
  }
}

async function addType() {
  try {
    await api.post(`/owner/hotels/${hotel.value.id}/types`, typeForm.value)
    toast.push(t('ownerHotel.addType') + ' ✔', 'success')
    fetchHotel()
  } catch (e) {
    const errors = e.response?.data?.errors
    toast.push(errors ? Object.values(errors).flat()[0] : t('auth.loginFailed'), 'danger')
  }
}
</script>

<template>
  <div v-if="loading" class="container py-5 text-center">{{ $t('common.loading') }}</div>
  <div v-else-if="hotel" class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="mb-0">🛠 {{ $t('ownerHotel.manageTitle', { name: hotel.name }) }}</h4>
      <span class="badge" :class="statusClasses[hotel.status]">{{ $t('statuses.' + hotel.status) }}</span>
    </div>

    <div class="row g-4">
      <div class="col-lg-6">
        <div class="card shadow-sm mb-4">
          <div class="card-header bg-white fw-semibold">{{ $t('ownerHotel.dataTitle') }}</div>
          <div class="card-body">
            <div class="mb-2">
              <label class="form-label">{{ $t('ownerHotel.name') }}</label>
              <input v-model="form.name" class="form-control" />
            </div>
            <div class="mb-2">
              <label class="form-label">{{ $t('ownerHotel.description') }}</label>
              <textarea v-model="form.description" class="form-control" rows="3"></textarea>
            </div>
            <div class="row g-2">
              <div class="col-6">
                <label class="form-label">{{ $t('ownerHotel.address') }}</label>
                <input v-model="form.address" class="form-control" />
              </div>
              <div class="col-6">
                <label class="form-label">{{ $t('ownerHotel.phone') }}</label>
                <input v-model="form.phone" class="form-control" />
              </div>
              <div class="col-6">
                <label class="form-label">{{ $t('ownerHotel.email') }}</label>
                <input v-model="form.email" class="form-control" />
              </div>
              <div class="col-6">
                <label class="form-label">{{ $t('ownerHotel.stars') }}</label>
                <input v-model.number="form.star_rating" type="number" min="1" max="5" class="form-control" />
              </div>
            </div>
            <button class="btn btn-success w-100 mt-3" :disabled="saving" @click="save">
              {{ saving ? $t('common.loading') : $t('ownerHotel.save') }}
            </button>
          </div>
        </div>

        <div class="card shadow-sm">
          <div class="card-header bg-white fw-semibold">{{ $t('ownerHotel.imagesTitle') }}</div>
          <div class="card-body">
            <div v-if="hotel.images?.length" class="row g-2 mb-3">
              <div v-for="img in hotel.images" :key="img.id" class="col-4">
                <img :src="storageBase + img.image_path" class="w-100 rounded" style="height: 90px; object-fit: cover" alt="" loading="lazy" />
              </div>
            </div>
            <input type="file" class="form-control" @change="uploadImage" />
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card shadow-sm">
          <div class="card-header bg-white fw-semibold">{{ $t('ownerHotel.typesTitle') }}</div>
          <div class="card-body">
            <div v-for="ty in hotel.accommodation_types || []" :key="ty.id" class="border rounded p-2 mb-2">
              <div class="fw-semibold">
                {{ ty.name }}
                <span class="badge bg-light text-dark">{{ $t('search.stayTypes.' + ty.stay_type) }}</span>
              </div>
              <div class="small text-muted">
                {{ ty.base_price }} — {{ ty.max_adults }} {{ $t('ownerHotel.maxAdults') }}
                / {{ ty.max_children }} {{ $t('ownerHotel.maxChildren') }}
                / {{ ty.units_count }} {{ $t('ownerHotel.units') }}
              </div>
            </div>

            <h6 class="mt-4 mb-2">{{ $t('ownerHotel.addType') }}</h6>
            <div class="row g-2">
              <div class="col-6">
                <input v-model="typeForm.name" class="form-control" :placeholder="$t('ownerHotel.typeName')" />
              </div>
              <div class="col-6">
                <select v-model="typeForm.stay_type" class="form-select">
                  <option value="room">{{ $t('search.stayTypes.room') }}</option>
                  <option value="apartment">{{ $t('search.stayTypes.apartment') }}</option>
                  <option value="suite">{{ $t('search.stayTypes.suite') }}</option>
                  <option value="hall">{{ $t('search.stayTypes.hall') }}</option>
                </select>
              </div>
              <div class="col-4">
                <input v-model.number="typeForm.max_adults" type="number" min="1" class="form-control" :title="$t('ownerHotel.maxAdults')" />
              </div>
              <div class="col-4">
                <input v-model.number="typeForm.max_children" type="number" min="0" class="form-control" :title="$t('ownerHotel.maxChildren')" />
              </div>
              <div class="col-4">
                <input v-model.number="typeForm.units_count" type="number" min="1" class="form-control" :title="$t('ownerHotel.units')" />
              </div>
              <div class="col-6">
                <input v-model.number="typeForm.base_price" type="number" min="1" class="form-control" :placeholder="$t('ownerHotel.price')" />
              </div>
              <div class="col-6">
                <button class="btn btn-success w-100" @click="addType">{{ $t('ownerHotel.add') }}</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>