<template>
  <div :class="['avatar-container', `size-${size}`, { 'has-glow': glow, 'is-clickable': clickable, 'is-rounded': rounded }]">
    <img
      :src="computedAvatarUrl"
      :alt="alt || username || 'User avatar'"
      class="avatar-img"
      @error="handleError"
      loading="lazy"
    />
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { getAvatarUrl } from '../../utils/media'

const props = defineProps({
  src: { type: String, default: '' },
  username: { type: String, default: 'User' },
  alt: { type: String, default: '' },
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(v)
  },
  glow: { type: Boolean, default: false },
  clickable: { type: Boolean, default: false },
  rounded: { type: Boolean, default: false }
})

const imageError = ref(false)

const computedAvatarUrl = computed(() => {
  if (imageError.value || !props.src) {
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(props.username)}&background=282830&color=ff6b1a&bold=true`
  }
  return getAvatarUrl(props.src, props.username)
})

const handleError = () => {
  imageError.value = true
}
</script>

<style scoped>
.avatar-container {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  overflow: hidden;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  transition: all 0.2s ease;
  border-radius: 50%;
}

.is-clickable {
  cursor: pointer;
}

.is-clickable:hover {
  transform: scale(1.05);
  border-color: var(--accent);
}

.has-glow {
  padding: 2px;
  background: linear-gradient(135deg, var(--accent), var(--gold));
  border: none;
}

.has-glow .avatar-img {
  border: 2px solid var(--bg-primary);
}

.avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.is-rounded {
  border-radius: var(--radius-md);
}

/* Sizes */
.size-xs { width: 24px; height: 24px; }
.size-sm { width: 32px; height: 32px; }
.size-md { width: 42px; height: 42px; }
.size-lg { width: 64px; height: 64px; }
.size-xl { width: 120px; height: 120px; }

@media (min-width: 768px) {
  .size-xl { width: 140px; height: 140px; }
}
</style>