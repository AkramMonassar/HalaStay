<script setup>
import { onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '../services/api'

const { t } = useI18n()
const items = ref([])
const loading = ref(true)
const armClear = ref(false)

const icons = {
  booking_confirmed: '✅',
  booking_rejected: '❌',
  booking_expired: '⏳',
  payment_submitted: '💳',
  payment_reviewed: '💰',
  hotel_approved: '🏨',
  hotel_rejected: '🚫',
  review: '⭐',
}

async function load() {
  const { data } = await api.get('/notifications')
  items.value = data.data ?? data
}

onMounted(async () => {
  try {
    await load()
    // القراءة الدائمة: تُكتب في القاعدة لا في الذاكرة
    await api.post('/notifications/read-all').catch(() => {})
    window.dispatchEvent(new CustomEvent('halastay:notifications-read'))
  } finally {
    loading.value = false
  }
})

function removeOne(n) {
  api.delete(`/notifications/${n.id}`).then(() => {
    items.value = items.value.filter((x) => x.id !== n.id)
  })
}

function clearAll() {
  if (!armClear.value) {
    armClear.value = true
    setTimeout(() => (armClear.value = false), 3000)
    return
  }
  api.delete('/notifications').then(() => {
    items.value = []
    window.dispatchEvent(new CustomEvent('halastay:notifications-read'))
  })
}
</script>

<template>
  <div class="container py-4" style="max-width: 720px">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="mb-0">🔔 {{ $t('notif.title') }}</h5>
      <button v-if="items.length" class="btn btn-outline-danger btn-sm" @click="clearAll">
        {{ armClear ? $t('notif.clearConfirm') : $t('notif.clearAll') }}
      </button>
    </div>

    <div v-if="loading" class="text-muted small">{{ $t('common.loading') }}</div>

    <div v-else-if="!items.length" class="alert alert-info small mb-0">
      {{ $t('notif.empty') }}
    </div>

    <div v-else class="d-flex flex-column gap-2">
      <div
        v-for="n in items"
        :key="n.id"
        class="border rounded p-2 d-flex justify-content-between align-items-start"
        :class="{ 'opacity-50': n.is_read }"
      >
        <div>
          <div class="small fw-semibold">{{ icons[n.type] || '🔔' }} {{ n.title }}</div>
          <div v-if="n.body" class="small text-muted">{{ n.body }}</div>
        </div>
        <div class="d-flex align-items-center gap-2">
          <span class="small text-muted">{{ new Date(n.created_at).toLocaleString() }}</span>
          <button class="btn btn-outline-secondary btn-sm" :title="$t('notif.deleteOne')" @click="removeOne(n)">🗑</button>
        </div>
      </div>
    </div>
  </div>
</template>