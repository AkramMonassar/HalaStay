<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import api from '../../services/api'
import { useToastStore } from '../../stores/toast'

const { t } = useI18n()
const toast = useToastStore()
const router = useRouter()

const cities = ref([])
const form = ref({ city_id: '', name: '', description: '', address: '', phone: '', email: '', star_rating: 3 })
const saving = ref(false)

onMounted(async () => {
  const { data } = await api.get('/cities')
  cities.value = data.data
})

async function submit() {
  saving.value = true
  try {
    const { data } = await api.post('/owner/hotels', form.value)
    router.push({ name: 'owner-hotel-details', params: { id: data.data.id } })
  } catch (e) {
    const errors = e.response?.data?.errors
    toast.push(errors ? Object.values(errors).flat()[0] : t('auth.loginFailed'), 'danger')
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="container py-5" style="max-width: 640px">
    <div class="card shadow-sm">
      <div class="card-header bg-white fw-semibold">🏨 {{ $t('ownerHotel.createTitle') }}</div>
      <div class="card-body">
        <div class="row g-2">
          <div class="col-6">
            <label class="form-label">{{ $t('ownerHotel.name') }}</label>
            <input v-model="form.name" class="form-control" />
          </div>
          <div class="col-6">
            <label class="form-label">{{ $t('ownerHotel.city') }}</label>
            <select v-model="form.city_id" class="form-select">
              <option value="" disabled>{{ $t('search.chooseCity') }}</option>
              <option v-for="c in cities" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">{{ $t('ownerHotel.description') }}</label>
            <textarea v-model="form.description" class="form-control" rows="3"></textarea>
          </div>
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
        <button class="btn btn-success w-100 mt-3" :disabled="saving" @click="submit">
          {{ saving ? $t('common.loading') : $t('ownerHotel.createBtn') }}
        </button>
      </div>
    </div>
  </div>
</template>