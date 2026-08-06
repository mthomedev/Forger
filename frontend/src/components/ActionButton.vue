<template>
  <button
    :class="[
      'action-btn',
      { 'is-active': active, 'has-count': showCount && count !== undefined }
    ]"
    :style="active ? { color: activeColor } : {}"
    @click="handleClick"
    :disabled="disabled"
    :aria-label="ariaLabel"
    :title="title"
    :aria-pressed="active"
    v-bind="$attrs"
  >
    <span class="btn-icon" aria-hidden="true">
      <slot name="icon" />
    </span>

    <span v-if="showCount && count !== undefined" class="btn-count" :aria-label="`${count} ${countLabel}`">
      {{ count }}
    </span>
  </button>
</template>

<script setup>
const props = defineProps({
  active: { type: Boolean, default: false },
  activeColor: { type: String, default: 'var(--accent)' },
  count: { type: Number, default: undefined },
  countLabel: { type: String, default: '' },
  showCount: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  ariaLabel: { type: String, required: true },
  title: { type: String, default: '' }
})

const emit = defineEmits(['click'])

const handleClick = () => {
  if (!props.disabled) {
    emit('click')
  }
}
</script>

<style scoped>
.action-btn {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.35rem;
  background: none;
  border: none;
  padding: 0.5rem;
  color: var(--text-primary);
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  border-radius: 8px;
  min-width: 44px;
  min-height: 44px;
}

.action-btn:hover:not(:disabled) {
  color: var(--accent);
  background: var(--bg-elevated);
}

.action-btn:active:not(:disabled) {
  transform: scale(0.95);
}

.action-btn:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

.action-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
  transform: none !important;
}

.action-btn.is-active {
  color: var(--active-color, var(--accent));
  filter: drop-shadow(0 0 6px var(--accent-glow));
}

.btn-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.btn-count {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-secondary);
  min-width: 1.25rem;
  text-align: center;
}

.is-active .btn-count {
  color: var(--active-color, var(--accent));
}
</style>