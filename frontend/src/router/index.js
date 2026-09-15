import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
  { path: '/', name: 'home', component: () => import('../views/HomeView.vue'), meta: { title: 'الرئيسية' } },
  { path: '/search', name: 'search', component: () => import('../views/SearchView.vue'), meta: { title: 'البحث' } },
  { path: '/hotels/:id', name: 'hotel', component: () => import('../views/HotelView.vue'), meta: { title: 'تفاصيل الفندق' } },
  { path: '/bookings', name: 'bookings', component: () => import('../views/BookingsView.vue'), meta: { requiresAuth: true, title: 'حجوزاتي' } },
  { path: '/bookings/:id', name: 'booking', component: () => import('../views/BookingDetailsView.vue'), meta: { requiresAuth: true, title: 'تفاصيل الحجز' } },
  { path: '/owner', name: 'owner-hotels', component: () => import('../views/owner/OwnerHotelsView.vue'), meta: { requiresAuth: true, requiresRole: 'hotel_owner', title: 'فنادقي' } },
  { path: '/owner/hotels/new', name: 'owner-hotel-create', component: () => import('../views/owner/OwnerHotelCreateView.vue'), meta: { requiresAuth: true, requiresRole: 'hotel_owner', title: 'إضافة فندق' } },
  { path: '/owner/hotels/:id', name: 'owner-hotel-details', component: () => import('../views/owner/OwnerHotelDetailsView.vue'), meta: { requiresAuth: true, requiresRole: 'hotel_owner', title: 'إدارة الفندق' } },
  { path: '/owner/bookings', name: 'owner-bookings', component: () => import('../views/owner/OwnerBookingsView.vue'), meta: { requiresAuth: true, requiresRole: 'hotel_owner', title: 'حجوزات فنادقي' } },
  { path: '/owner/payments', name: 'owner-payments', component: () => import('../views/owner/OwnerPaymentsView.vue'), meta: { requiresAuth: true, requiresRole: 'hotel_owner', title: 'مراجعة الدفعات' } },
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

  if (to.meta.requiresRole && auth.user?.role !== to.meta.requiresRole) {
    return { name: 'dashboard' }
  }

  if (to.meta.guest && auth.isAuthenticated) {
    return { name: 'dashboard' }
  }
})

export default router