import { defineStore } from 'pinia'
import { ref } from 'vue'
import { i18n } from '../i18n'

export const useLangStore = defineStore('lang', () => {
  const locale = ref(i18n.global.locale.value)

  function apply() {
    i18n.global.locale.value = locale.value
    document.documentElement.lang = locale.value
    document.documentElement.dir = locale.value === 'ar' ? 'rtl' : 'ltr'

    const rtl = document.getElementById('bs-rtl')
    const ltr = document.getElementById('bs-ltr')
    if (rtl) rtl.disabled = locale.value !== 'ar'
    if (ltr) ltr.disabled = locale.value === 'ar'

    localStorage.setItem('halastay-lang', locale.value)
  }

  function toggle() {
    locale.value = locale.value === 'ar' ? 'en' : 'ar'
    apply()
  }

  return { locale, toggle, apply }
})