<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  show: { type: Boolean, default: false },
  title: { type: String, default: 'تأكيد' },
  message: { type: String, default: '' },
  confirmText: { type: String, default: 'تأكيد' },
  cancelText: { type: String, default: 'إلغاء' },
  tone: { type: String, default: 'primary' },
  withNote: { type: Boolean, default: false },
})

const emit = defineEmits(['confirm', 'cancel'])
const note = ref('')

watch(() => props.show, (v) => { if (v) note.value = '' })
</script>

<template>
  <Teleport to="body">
    <div v-if="show" class="modal-overlay" @click.self="emit('cancel')">
      <div class="modal-dialog-box" role="dialog" aria-modal="true">
        <div class="modal-header-bar">
          <h5 class="mb-0">{{ title }}</h5>
          <button class="btn-close-x" @click="emit('cancel')">×</button>
        </div>
        <div class="modal-body-bar">
          <p v-if="message" class="mb-2">{{ message }}</p>
          <input v-if="withNote" v-model="note" class="form-control" placeholder="اكتب ملاحظة أو سبباً..." />
        </div>
        <div class="modal-footer-bar">
          <button class="btn btn-light" @click="emit('cancel')">{{ cancelText }}</button>
          <button class="btn" :class="'btn-' + tone" @click="emit('confirm', note)">{{ confirmText }}</button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<style lang="scss" scoped>
.modal-overlay { position: fixed; inset: 0; background: rgba(0, 0, 0, 0.45); display: flex; align-items: center; justify-content: center; z-index: 1050; }
.modal-dialog-box { background: #fff; border-radius: 12px; width: min(440px, 92vw); box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.2); overflow: hidden; animation: pop 0.18s ease-out; }
.modal-header-bar { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid #dee2e6; }
.modal-body-bar { padding: 1.25rem; }
.modal-footer-bar { display: flex; justify-content: flex-end; gap: 0.5rem; padding: 1rem 1.25rem; border-top: 1px solid #dee2e6; background: #f8f9fa; }
.btn-close-x { border: none; background: transparent; font-size: 1.4rem; line-height: 1; cursor: pointer; color: #6c757d; }
@keyframes pop { from { transform: scale(0.94); opacity: 0.6; } to { transform: scale(1); opacity: 1; } }
</style>