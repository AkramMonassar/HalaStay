import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useToastStore = defineStore('toast', () => {
  const toasts = ref([])
  let seq = 0

  function push(message, tone = 'success') {
    const id = ++seq
    toasts.value.push({ id, message, tone })
    setTimeout(() => dismiss(id), 4000)
  }

  function dismiss(id) {
    toasts.value = toasts.value.filter((t) => t.id !== id)
  }

  return { toasts, push, dismiss }
})