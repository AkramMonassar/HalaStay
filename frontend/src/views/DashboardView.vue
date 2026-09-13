<script setup>
import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()

async function logout() {
  await auth.logout()
  router.push('/')
}
</script>

<template>
  <div class="container py-5">
    <div class="card shadow-sm mx-auto" style="max-width: 480px">
      <div class="card-body p-4 text-center">
        <div class="fs-1">👋</div>
        <h4 class="mb-1">{{ auth.user?.name }}</h4>
        <p class="text-muted mb-2">{{ auth.user?.email }}</p>
        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle mb-3">
          الدور: {{ auth.user?.role }}
        </span>
        <div class="d-grid">
          <button class="btn btn-outline-danger" @click="logout">تسجيل الخروج</button>
        </div>
      </div>
    </div>
  </div>
</template>