<script setup>
import { useAuthStore } from './stores/auth'
import { useRouter } from 'vue-router'
import ToastContainer from './components/ToastContainer.vue'
import { useThemeStore } from './stores/theme'
const theme = useThemeStore()
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
            <router-link class="btn btn-outline-success btn-sm" to="/owner">لوحة المالك</router-link>
            <router-link class="btn btn-outline-success btn-sm" to="/owner/hotels">فنادقي</router-link>
            <router-link class="btn btn-outline-success btn-sm" to="/owner/bookings">حجوزات فنادقي</router-link>
            <router-link class="btn btn-outline-success btn-sm" to="/owner/payments">مراجعة الدفعات</router-link>
            <router-link class="btn btn-outline-primary btn-sm" to="/bookings">حجوزاتي</router-link>
          </template>
          <template v-else-if="auth.role === 'admin'">
            <router-link class="btn btn-outline-dark btn-sm" to="/admin">لوحة الأدمن</router-link>
            <router-link class="btn btn-outline-dark btn-sm" to="/admin/hotels">الفنادق</router-link>
            <router-link class="btn btn-outline-dark btn-sm" to="/admin/users">المستخدمون</router-link>
            <router-link class="btn btn-outline-dark btn-sm" to="/admin/settings">الإعدادات</router-link>
          </template>
          <template v-else>
            <router-link class="btn btn-outline-primary btn-sm" to="/bookings">حجوزاتي</router-link>
            <router-link class="btn btn-outline-primary btn-sm" to="/dashboard">لوحتي</router-link>
          </template>

          <router-link class="btn btn-outline-secondary btn-sm" to="/profile">👤 ملفي</router-link>
          <span class="nav-divider" aria-hidden="true"></span>
          <button class="btn btn-outline-danger btn-sm" @click="logout">خروج</button>
        </template>
        <template v-else>
          <router-link class="btn btn-outline-primary btn-sm" to="/login">دخول</router-link>
          <router-link class="btn btn-primary btn-sm" to="/register">حساب جديد</router-link>
        </template>

        <button
          class="btn btn-light btn-sm theme-toggle"
          :title="theme.mode === 'light' ? 'الوضع الليلي' : 'الوضع النهاري'"
          @click="theme.toggle"
        >
          {{ theme.mode === 'light' ? '🌙' : '☀️' }}
        </button>
      </div>
    </div>
  </nav>

  <main class="main-content">
    <router-view v-slot="{ Component }">
      <transition name="page-fade" mode="out-in">
        <component :is="Component" />
      </transition>
    </router-view>
    <ToastContainer />
  </main>
</template>

<style lang="scss" scoped>
.main-content {
  min-height: calc(100vh - 80px);
  padding-bottom: 2rem;
}
</style>