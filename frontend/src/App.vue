<script setup>
import { ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from './stores/auth'
import { useThemeStore } from './stores/theme'
import { useLangStore } from './stores/lang'
import { useToastStore } from './stores/toast'
import { initEcho, disconnectEcho } from './services/echo'
import api from './services/api'
import ToastContainer from './components/ToastContainer.vue'

const auth = useAuthStore()
const theme = useThemeStore()
const lang = useLangStore()
const toast = useToastStore()
const router = useRouter()
const liveBadge = ref(0)

watch(() => auth.user, (user) => {
  if (!user) {
    disconnectEcho()
    liveBadge.value = 0
    return
  }

  // بذر العداد من المخزون: ما فاتك وأنت غائب
  api.get('/notifications')
    .then(({ data }) => {
      const list = data.data ?? data
      liveBadge.value = list.filter((n) => !n.is_read).length
    })
    .catch(() => {})

  // الاصغاء الحي: ما سيولد وأنت حاضر
  initEcho(auth.token)
    .private(`user.${user.id}`)
    .listen('.NotificationCreated', (payload) => {
      toast.push(payload.title, 'success')
      liveBadge.value++
    })
}, { immediate: true })

// حين تُزار صفحة الإشعارات وتُعلَّم كمقروءة — تصفر الشارة
window.addEventListener('halastay:notifications-read', () => {
  liveBadge.value = 0
})

async function logout() {
  await auth.logout()
  router.push('/')
}
</script>

<template>
  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <router-link class="navbar-brand" to="/">🏨 {{ $t('nav.brand') }}</router-link>
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <template v-if="auth.isAuthenticated">
          <template v-if="auth.role === 'hotel_owner'">
            <router-link class="btn btn-outline-success btn-sm" to="/owner">{{ $t('nav.ownerPanel') }}</router-link>
            <router-link class="btn btn-outline-success btn-sm" to="/owner/hotels">{{ $t('nav.myHotels') }}</router-link>
            <router-link class="btn btn-outline-success btn-sm" to="/owner/bookings">{{ $t('nav.ownerBookings') }}</router-link>
            <router-link class="btn btn-outline-success btn-sm" to="/owner/payments">{{ $t('nav.paymentReview') }}</router-link>
            <router-link class="btn btn-outline-primary btn-sm" to="/bookings">{{ $t('nav.myBookings') }}</router-link>
          </template>
          <template v-else-if="auth.role === 'admin'">
            <router-link class="btn btn-outline-dark btn-sm" to="/admin">{{ $t('nav.adminPanel') }}</router-link>
            <router-link class="btn btn-outline-dark btn-sm" to="/admin/hotels">{{ $t('nav.hotels') }}</router-link>
            <router-link class="btn btn-outline-dark btn-sm" to="/admin/users">{{ $t('nav.users') }}</router-link>
            <router-link class="btn btn-outline-dark btn-sm" to="/admin/settings">{{ $t('nav.settings') }}</router-link>
          </template>
          <template v-else>
            <router-link class="btn btn-outline-primary btn-sm" to="/bookings">{{ $t('nav.myBookings') }}</router-link>
            <router-link class="btn btn-outline-primary btn-sm" to="/dashboard">{{ $t('nav.myDashboard') }}</router-link>
          </template>
          <router-link class="btn btn-outline-secondary btn-sm" to="/profile">👤 {{ $t('nav.profile') }}</router-link>
          <router-link class="btn btn-outline-secondary btn-sm position-relative" to="/notifications">
            🔔
            <span
              v-if="liveBadge"
              class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
            >{{ liveBadge }}</span>
          </router-link>
          <span class="nav-divider" aria-hidden="true"></span>
          <button class="btn btn-outline-danger btn-sm" @click="logout">{{ $t('nav.logout') }}</button>
        </template>
        <template v-else>
          <router-link class="btn btn-outline-primary btn-sm" to="/login">{{ $t('nav.login') }}</router-link>
          <router-link class="btn btn-primary btn-sm" to="/register">{{ $t('nav.register') }}</router-link>
        </template>
        <button
          class="btn btn-light btn-sm theme-toggle"
          :title="theme.mode === 'light' ? $t('nav.themeToDark') : $t('nav.themeToLight')"
          @click="theme.toggle"
        >
          {{ theme.mode === 'light' ? '🌙' : '☀️' }}
        </button>
        <button class="btn btn-light btn-sm theme-toggle" @click="lang.toggle">
          {{ lang.locale === 'ar' ? 'EN' : 'ع' }}
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