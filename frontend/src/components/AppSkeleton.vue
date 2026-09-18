<script setup>
defineProps({ variant: { type: String, default: 'card' } })
</script>

<template>
  <div v-if="variant === 'card'" class="skeleton-card">
    <div class="skeleton-img shimmer"></div>
    <div class="skeleton-line shimmer" style="width: 70%"></div>
    <div class="skeleton-line shimmer" style="width: 40%"></div>
  </div>
  <div v-else class="skeleton-line shimmer" :style="{ width: variant === 'row' ? '100%' : '60%', height: '64px' }"></div>
</template>

<style lang="scss" scoped>
.skeleton-card { border: 1px solid #e9ecef; border-radius: 12px; overflow: hidden; background: #fff; }
.skeleton-img { height: 170px; background: #e9ecef; }
.skeleton-line { height: 14px; border-radius: 6px; background: #e9ecef; margin: 12px; }
.shimmer { position: relative; overflow: hidden; }
.shimmer::after { content: ''; position: absolute; inset: 0; background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.7), transparent); transform: translateX(-100%); animation: shimmer 1.4s infinite; }
@keyframes shimmer { 100% { transform: translateX(100%); } }
</style>