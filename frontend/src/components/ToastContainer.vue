<script setup>
import { useToastStore } from '../stores/toast'

const toast = useToastStore()

const toneClasses = {
  success: 'bg-success',
  danger: 'bg-danger',
  info: 'bg-primary',
  warning: 'bg-warning text-dark',
}
</script>

<template>
  <Teleport to="body">
    <div class="position-fixed p-3 d-flex flex-column gap-2" style="z-index: 1080; bottom: 0; inset-inline-start: 0">
      <div v-for="t in toast.toasts" :key="t.id" class="toast show text-white" :class="toneClasses[t.tone]" role="alert">
        <div class="d-flex align-items-center">
          <div class="toast-body">{{ t.message }}</div>
          <button type="button" class="btn-close btn-close-white ms-auto me-2" @click="toast.dismiss(t.id)"></button>
        </div>
      </div>
    </div>
  </Teleport>
</template>