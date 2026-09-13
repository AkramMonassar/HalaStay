import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
  { path: '/', name: 'home', component: () => import('../views/HomeView.vue'), meta: { title: 'الرئيسية' } },
  { path: '/login', name: 'login', component: () => import('../views/LoginView.vue'), meta: { guest: true, title: 'تسجيل الدخول' } },
  { path: '/register', name: 'register', component: () => import('../views/RegisterView.vue'), meta: { guest: true, title: 'حساب جديد' } },
  { path: '/dashboard', name: 'dashboard', component: () => import('../views/DashboardView.vue'), meta: { requiresAuth: true, title: 'لوحتي' } },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()

  // توكن موجود بلا بيانات؟ اجلبها (إنتهاء صلاحية الصفحة لا ينهي الجلسة)
  if (auth.token && !auth.user) {
    try {
      await auth.fetchUser()
    } catch {
      auth.clearAuth()
    }
  }

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  if (to.meta.guest && auth.isAuthenticated) {
    return { name: 'dashboard' }
  }
})

export default router