<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import api from '../services/api'
import { useAuthStore } from '../stores/auth'

const { t } = useI18n()
const auth = useAuthStore()
const router = useRouter()

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: 'tourist',
})
const error = ref('')
const loading = ref(false)

async function submit() {
  error.value = ''
  loading.value = true
  try {
    await api.post('/auth/register', form.value)
    await auth.login({ email: form.value.email, password: form.value.password })
    router.push('/')
  } catch (e) {
    const errors = e.response?.data?.errors
    error.value = errors
      ? Object.values(errors).flat()[0]
      : (e.response?.data?.message || t('auth.loginFailed'))
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="container py-5" style="max-width: 520px">
    <div class="card shadow-sm">
      <div class="card-header bg-white fw-semibold">{{ $t('auth.registerTitle') }}</div>
      <div class="card-body">
        <div v-if="error" class="alert alert-danger py-2 small">{{ error }}</div>
        <form @submit.prevent="submit">
          <div class="mb-3">
            <label class="form-label">{{ $t('auth.name') }}</label>
            <input v-model="form.name" class="form-control" required />
          </div>
          <div class="mb-3">
            <label class="form-label">{{ $t('auth.email') }}</label>
            <input v-model="form.email" type="email" class="form-control" required />
          </div>
          <div class="mb-3">
            <label class="form-label">{{ $t('auth.role') }}</label>
            <select v-model="form.role" class="form-select">
              <option value="tourist">{{ $t('auth.tourist') }}</option>
              <option value="hotel_owner">{{ $t('auth.owner') }}</option>
            </select>
          </div>
          <div class="row g-2">
            <div class="col-6">
              <div class="mb-3">
                <label class="form-label">{{ $t('auth.password') }}</label>
                <input v-model="form.password" type="password" class="form-control" required />
              </div>
            </div>
            <div class="col-6">
              <div class="mb-3">
                <label class="form-label">{{ $t('auth.confirm') }}</label>
                <input v-model="form.password_confirmation" type="password" class="form-control" required />
              </div>
            </div>
          </div>
          <button class="btn btn-primary w-100" :disabled="loading">
            {{ loading ? $t('common.loading') : $t('auth.registerBtn') }}
          </button>
        </form>
        <div class="small text-muted mt-3 text-center">
          {{ $t('auth.haveAccount') }}
          <router-link to="/login">{{ $t('auth.loginNow') }}</router-link>
        </div>
      </div>
    </div>
  </div>
</template>