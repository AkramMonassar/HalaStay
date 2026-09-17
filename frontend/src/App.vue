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
  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <router-link class="navbar-brand" to="/">🏨 هلا ستاي</router-link>
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <template v-if="auth.isAuthenticated">
          <template v-if="auth.role === 'hotel_owner'">
            <router-link class="btn btn-outline-success btn-sm" to="/owner">فنادقي</router-link>
            <router-link class="btn btn-outline-success btn-sm" to="/owner/bookings">حجوزات فنادقي</router-link>
            <router-link class="btn btn-outline-success btn-sm" to="/owner/payments">مراجعة الدفعات</router-link>
          </template>
          <template v-if="auth.role === 'admin'">
            <router-link class="btn btn-outline-dark btn-sm" to="/admin">لوحة الأدمن</router-link>
            <router-link class="btn btn-outline-dark btn-sm" to="/admin/hotels">الفنادق</router-link>
            <router-link class="btn btn-outline-dark btn-sm" to="/admin/users">المستخدمون</router-link>
            <router-link class="btn btn-outline-dark btn-sm" to="/admin/settings">الإعدادات</router-link>
          </template>
          <router-link class="btn btn-outline-primary btn-sm" to="/bookings">حجوزاتي</router-link>
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

  <main class="main-content">
    <router-view />
  </main>
</template>

<style lang="scss" scoped>
.main-content {
  min-height: calc(100vh - 80px);
  padding-bottom: 2rem;
}
</style>