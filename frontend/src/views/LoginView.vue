<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '../stores/auth'

const { t } = useI18n()
const auth = useAuthStore()
const router = useRouter()

const form = ref({ email: '', password: '' })
const error = ref('')
const loading = ref(false)

async function submit() {
  error.value = ''
  loading.value = true
  try {
    await auth.login(form.value)
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
  <div class="container py-5" style="max-width: 480px">
    <div class="card shadow-sm">
      <div class="card-header bg-white fw-semibold">{{ $t('auth.loginTitle') }}</div>
      <div class="card-body">
        <div v-if="error" class="alert alert-danger py-2 small">{{ error }}</div>
        <form @submit.prevent="submit">
          <div class="mb-3">
            <label class="form-label">{{ $t('auth.email') }}</label>
            <input v-model="form.email" type="email" class="form-control" required />
          </div>
          <div class="mb-3">
            <label class="form-label">{{ $t('auth.password') }}</label>
            <input v-model="form.password" type="password" class="form-control" required />
          </div>
          <button class="btn btn-primary w-100" :disabled="loading">
            {{ loading ? $t('common.loading') : $t('auth.loginBtn') }}
          </button>
        </form>
        <div class="small text-muted mt-3 text-center">
          {{ $t('auth.noAccount') }}
          <router-link to="/register">{{ $t('auth.registerNow') }}</router-link>
        </div>
      </div>
    </div>
  </div>
</template>