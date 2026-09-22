import { defineStore } from 'pinia'
import { ref, watch } from 'vue'

export const useThemeStore = defineStore('theme', () => {
  const mode = ref(localStorage.getItem('halastay-theme') || 'light')

  function apply() {
    document.documentElement.setAttribute('data-theme', mode.value)
  }

  function toggle() {
    mode.value = mode.value === 'light' ? 'dark' : 'light'
  }

  watch(mode, () => {
    localStorage.setItem('halastay-theme', mode.value)
    apply()
  }, { immediate: true })

  return { mode, toggle, apply }
})