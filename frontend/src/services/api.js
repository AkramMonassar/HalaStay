import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1',
  headers: { Accept: 'application/json' },
})

// 📤 كل طلب يخرج: ألصق التوكن إن وجد
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('halastay_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// 📥 كل استجابة تعود: إن كانت 401 نظّف الجلسة وارجع للدخول
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('halastay_token')
      if (window.location.pathname !== '/login') {
        window.location.href = '/login'
      }
    }
    return Promise.reject(error)
  }
)

export default api