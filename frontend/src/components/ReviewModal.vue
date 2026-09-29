<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '../services/api'
import { useToastStore } from '../stores/toast'

const props = defineProps({
  show: { type: Boolean, default: false },
  bookingId: { type: [Number, String], required: true },
})
const emit = defineEmits(['close', 'saved'])

const { t } = useI18n()
const toast = useToastStore()

const rating = ref(5)
const comment = ref('')
const saving = ref(false)

async function submit() {
  saving.value = true
  try {
    await api.post(`/bookings/${props.bookingId}/review`, { rating: rating.value, comment: comment.value || null })
    toast.push(t('reviews.thanks'), 'success')
    emit('saved')
  } catch (e) {
    toast.push(e.response?.data?.message || t('reviews.denied'), 'danger')
    emit('close')
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div v-if="show" class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-dialog-box" style="max-width: 460px">
      <div class="p-3 border-bottom fw-semibold">⭐ {{ $t('reviews.rateStay') }}</div>
      <div class="p-3">
        <div class="mb-3 text-center">
          <div class="form-label">{{ $t('reviews.rating') }}</div>
          <div>
            <button
              v-for="s in 5"
              :key="s"
              type="button"
              class="btn btn-link p-1 star"
              :class="s <= rating ? 'star-on' : 'star-off'"
              @click="rating = s"
            >★</button>
          </div>
        </div>
        <textarea v-model="comment" class="form-control" rows="3" :placeholder="$t('reviews.comment')"></textarea>
      </div>
      <div class="modal-footer-bar d-flex justify-content-end gap-2">
        <button class="btn btn-light btn-sm" @click="$emit('close')">{{ $t('common.cancel') }}</button>
        <button class="btn btn-success btn-sm" :disabled="saving" @click="submit">
          {{ saving ? $t('common.loading') : $t('reviews.submit') }}
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.star { text-decoration: none; font-size: 1.7rem; }
.star-on { color: #ffc107; }
.star-off { color: rgba(108, 117, 125, 0.35); }
</style>