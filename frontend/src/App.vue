<script setup>
import { useAuthStore } from './stores/auth'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()

async function logout() {
  await auth.logout()
  router.push('/')
}
</script>

<template>
  <nav class="navbar navbar-expand-lg bg-white shadow-sm">
    <div class="container">
      <router-link class="navbar-brand fw-bold text-primary" to="/">🏨 هلا ستاي</router-link>
      <div class="d-flex align-items-center gap-2">
        <template v-if="auth.isAuthenticated">
          <router-link class="btn btn-outline-primary btn-sm" to="/dashboard">لوحتي</router-link>
          <button class="btn btn-outline-danger btn-sm" @click="logout">خروج</button>
        </template>
        <template v-else>
          <router-link class="btn btn-outline-primary btn-sm" to="/login">دخول</router-link>
          <router-link class="btn btn-primary btn-sm" to="/register">حساب جديد</router-link>
        </template>
      </div>
    </div>
  </nav>

  <router-view />
</template>