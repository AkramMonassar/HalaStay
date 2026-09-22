<script setup>
import { ref, onMounted } from 'vue'
import api from '../services/api'
import { useAuthStore } from '../stores/auth'
import { useToastStore } from '../stores/toast'

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
    toast.push('تم حفظ الملف الشخصي.')
  } catch (e) {
    const errors = e.response?.data?.errors
    toast.push(errors ? Object.values(errors).flat()[0] : 'تعذر الحفظ.', 'danger')
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="container py-5" style="max-width: 560px">
    <div class="card shadow-sm">
      <div class="card-header bg-white fw-semibold">👤 ملفي الشخصي</div>
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label">البريد الإلكتروني (ثابت)</label>
          <input class="form-control" :value="auth.user?.email" disabled />
        </div>
        <div class="mb-3">
          <label class="form-label">الاسم الكامل</label>
          <input v-model="form.name" class="form-control" />
        </div>
        <div class="mb-3">
          <label class="form-label">رقم الجوال</label>
          <input v-model="form.phone" class="form-control" placeholder="05xxxxxxxx" />
          <div class="form-text">يستخدمه الفندق للتواصل معك عند تأكيد الحجز.</div>
        </div>
        <button class="btn btn-primary w-100" :disabled="saving" @click="save">
          {{ saving ? 'جارِ الحفظ...' : 'حفظ التغييرات' }}
        </button>
      </div>
    </div>
  </div>
</template>