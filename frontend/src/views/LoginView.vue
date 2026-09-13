<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

const form = ref({ email: '', password: '' })
const error = ref('')
const loading = ref(false)

async function submit() {
  error.value = ''
  loading.value = true
  try {
    await auth.login(form.value)
    router.push(route.query.redirect || '/dashboard')
  } catch (e) {
    const errors = e.response?.data?.errors
    error.value = errors
      ? Object.values(errors).flat()[0]
      : (e.response?.data?.message || 'فشل تسجيل الدخول.')
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="row justify-content-center py-5">
    <div class="col-md-5">
      <div class="card shadow-sm">
        <div class="card-body p-4">
          <h4 class="mb-3 text-center">تسجيل الدخول</h4>
          <div v-if="error" class="alert alert-danger py-2">{{ error }}</div>
          <form @submit.prevent="submit">
            <div class="mb-3">
              <label class="form-label">البريد الإلكتروني</label>
              <input v-model="form.email" type="email" class="form-control" required />
            </div>
            <div class="mb-3">
              <label class="form-label">كلمة المرور</label>
              <input v-model="form.password" type="password" class="form-control" required />
            </div>
            <button class="btn btn-primary w-100" :disabled="loading">
              {{ loading ? 'جارِ الدخول...' : 'دخول' }}
            </button>
          </form>
          <p class="text-center mt-3 mb-0">
            ليس لديك حساب؟ <router-link to="/register">سجل الآن</router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>