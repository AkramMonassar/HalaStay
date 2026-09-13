<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const auth = useAuthStore()

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
    await auth.register(form.value)
    router.push('/dashboard')
  } catch (e) {
    const errors = e.response?.data?.errors
    error.value = errors
      ? Object.values(errors).flat()[0]
      : (e.response?.data?.message || 'فشل إنشاء الحساب.')
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="row justify-content-center py-5">
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-body p-4">
          <h4 class="mb-3 text-center">حساب جديد</h4>
          <div v-if="error" class="alert alert-danger py-2">{{ error }}</div>
          <form @submit.prevent="submit">
            <div class="mb-3">
              <label class="form-label">الاسم</label>
              <input v-model="form.name" type="text" class="form-control" required />
            </div>
            <div class="mb-3">
              <label class="form-label">البريد الإلكتروني</label>
              <input v-model="form.email" type="email" class="form-control" required />
            </div>
            <div class="row">
              <div class="col mb-3">
                <label class="form-label">كلمة المرور</label>
                <input v-model="form.password" type="password" class="form-control" required />
              </div>
              <div class="col mb-3">
                <label class="form-label">تأكيد كلمة المرور</label>
                <input v-model="form.password_confirmation" type="password" class="form-control" required />
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">نوع الحساب</label>
              <select v-model="form.role" class="form-select">
                <option value="tourist">سائح — أحجز وأستمتع</option>
                <option value="hotel_owner">صاحب فندق — أضيف منشأتي</option>
              </select>
            </div>
            <button class="btn btn-primary w-100" :disabled="loading">
              {{ loading ? 'جارِ الإنشاء...' : 'إنشاء الحساب' }}
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>