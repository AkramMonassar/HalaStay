import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
  { path: '/', name: 'home', component: () => import('../views/HomeView.vue'), meta: { title: 'الرئيسية' } },
  { path: '/search', name: 'search', component: () => import('../views/SearchView.vue'), meta: { title: 'البحث' } },
  { path: '/hotels/:id', name: 'hotel', component: () => import('../views/HotelView.vue'), meta: { title: 'تفاصيل الفندق' } },
  { path: '/bookings', name: 'bookings', component: () => import('../views/BookingsView.vue'), meta: { requiresAuth: true, title: 'حجوزاتي' } },
  { path: '/bookings/:id', name: 'booking', component: () => import('../views/BookingDetailsView.vue'), meta: { requiresAuth: true, title: 'تفاصيل الحجز' } },
  
  
    { path: '/owner', name: 'owner-dashboard', component: () => import('../views/owner/OwnerDashboardView.vue'), meta: { requiresAuth: true, requiresRole: 'hotel_owner', title: 'لوحة المالك' } },
  { path: '/owner/hotels', name: 'owner-hotels', component: () => import('../views/owner/OwnerHotelsView.vue'), meta: { requiresAuth: true, requiresRole: 'hotel_owner', title: 'فنادقي' } },
  { path: '/owner/hotels/new', name: 'owner-hotel-create', component: () => import('../views/owner/OwnerHotelCreateView.vue'), meta: { requiresAuth: true, requiresRole: 'hotel_owner', title: 'إضافة فندق' } },
  { path: '/owner/hotels/:id', name: 'owner-hotel-details', component: () => import('../views/owner/OwnerHotelDetailsView.vue'), meta: { requiresAuth: true, requiresRole: 'hotel_owner', title: 'إدارة الفندق' } },
  { path: '/owner/bookings', name: 'owner-bookings', component: () => import('../views/owner/OwnerBookingsView.vue'), meta: { requiresAuth: true, requiresRole: 'hotel_owner', title: 'حجوزات فنادقي' } },
  { path: '/owner/payments', name: 'owner-payments', component: () => import('../views/owner/OwnerPaymentsView.vue'), meta: { requiresAuth: true, requiresRole: 'hotel_owner', title: 'مراجعة الدفعات' } },
   { path: '/admin', name: 'admin-dashboard', component: () => import('../views/admin/AdminDashboardView.vue'), meta: { requiresAuth: true, requiresRole: 'admin', title: 'لوحة الأدمن' } },
  { path: '/admin/hotels', name: 'admin-hotels', component: () => import('../views/admin/AdminHotelsView.vue'), meta: { requiresAuth: true, requiresRole: 'admin', title: 'الفنادق' } },
  { path: '/admin/users', name: 'admin-users', component: () => import('../views/admin/AdminUsersView.vue'), meta: { requiresAuth: true, requiresRole: 'admin', title: 'المستخدمون' } },
  { path: '/admin/bookings', name: 'admin-bookings', component: () => import('../views/admin/AdminBookingsView.vue'), meta: { requiresAuth: true, requiresRole: 'admin', title: 'الحجوزات' } },
  { path: '/admin/payments', name: 'admin-payments', component: () => import('../views/admin/AdminPaymentsView.vue'), meta: { requiresAuth: true, requiresRole: 'admin', title: 'الدفعات' } },
  { path: '/admin/settings', name: 'admin-settings', component: () => import('../views/admin/AdminSettingsView.vue'), meta: { requiresAuth: true, requiresRole: 'admin', title: 'الإعدادات' } },

  { path: '/profile', name: 'profile', component: () => import('../views/ProfileView.vue'), meta: { requiresAuth: true, title: 'ملفي' } },
 
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