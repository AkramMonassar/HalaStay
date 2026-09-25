<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '../services/api'
import { useAuthStore } from '../stores/auth'
import { useToastStore } from '../stores/toast'

const { t } = useI18n()
const auth = useAuthStore()
const toast = useToastStore()

const form = ref({ name: '', phone: '' })
const saving = ref(false)

onMounted(async () => {
  const { data } = await api.get('/auth/me')
  form.value.name = data.data.user.name
  form.value.phone = data.data.user.phone || ''
})

async function save() {
  saving.value = true
  try {
    const { data } = await api.patch('/auth/profile', form.value)
    auth.user = data.data
    toast.push(t('profile.saved'))
  } catch (e) {
    const errors = e.response?.data?.errors
    toast.push(errors ? Object.values(errors).flat()[0] : t('auth.loginFailed'), 'danger')
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="container py-5" style="max-width: 560px">
    <div class="card shadow-sm">
      <div class="card-header bg-white fw-semibold">👤 {{ $t('profile.title') }}</div>
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label">{{ $t('profile.emailFixed') }}</label>
          <input class="form-control" :value="auth.user?.email" disabled />
        </div>
        <div class="mb-3">
          <label class="form-label">{{ $t('profile.name') }}</label>
          <input v-model="form.name" class="form-control" />
        </div>
        <div class="mb-3">
          <label class="form-label">{{ $t('profile.phone') }}</label>
          <input v-model="form.phone" class="form-control" placeholder="05xxxxxxxx" />
          <div class="form-text">{{ $t('profile.phoneHint') }}</div>
        </div>
        <button class="btn btn-primary w-100" :disabled="saving" @click="save">
          {{ saving ? $t('profile.saving') : $t('profile.save') }}
        </button>
      </div>
    </div>
  </div>
</template>