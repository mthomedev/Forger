<template>
  <button
    :type="type"
    :class="[
      'base-btn',
      `btn-${variant}`,
      `btn-${size}`,
      { 'is-loading': loading, 'is-block': block }
    ]"
    :disabled="disabled || loading"
    v-bind="$attrs"
  >
    <span v-if="loading" class="spinner" aria-hidden="true"></span>
    <span :class="{ 'hidden-text': loading }" class="btn-content">
      <slot />
    </span>
  </button>
</template>

<script setup>
defineProps({
  type: { type: String, default: 'button' },
  variant: {
    type: String,
    default: 'primary',
    validator: (v) => ['primary', 'secondary', 'ghost', 'danger', 'outline'].includes(v)
  },
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg'].includes(v)
  },
  loading: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  block: { type: Boolean, default: false }
})
</script>

<style scoped>
.base-btn {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  font-family: inherit;
  font-weight: 600;
  border-radius: 8px;
  border: 1px solid transparent;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  user-select: none;
  white-space: nowrap;
}

.base-btn:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

.base-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none !important;
}

/* Sizes */
.btn-sm {
  padding: 0.35rem 0.75rem;
  font-size: 0.8rem;
}
.btn-md {
  padding: 0.6rem 1.25rem;
  font-size: 0.9rem;
}
.btn-lg {
  padding: 0.85rem 1.75rem;
  font-size: 1rem;
}

.is-block {
  width: 100%;
}

/* Variants */
.btn-primary {
  background: linear-gradient(135deg, var(--accent), #ff8534);
  color: #ffffff;
  box-shadow: 0 4px 15px var(--accent-glow);
}
.btn-primary:hover:not(:disabled) {
  opacity: 0.95;
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(255, 107, 26, 0.4);
}
.btn-primary:active:not(:disabled) {
  transform: translateY(0);
}

.btn-secondary {
  background: var(--bg-elevated);
  color: var(--text-primary);
  border-color: var(--border);
}
.btn-secondary:hover:not(:disabled) {
  background: var(--border-light);
  border-color: var(--text-secondary);
}

.btn-outline {
  background: transparent;
  color: var(--accent);
  border-color: var(--accent);
}
.btn-outline:hover:not(:disabled) {
  background: var(--accent);
  color: #ffffff;
}

.btn-ghost {
  background: transparent;
  color: var(--text-secondary);
}
.btn-ghost:hover:not(:disabled) {
  background: var(--bg-elevated);
  color: var(--text-primary);
}

.btn-danger {
  background: rgba(255, 77, 77, 0.1);
  color: var(--danger);
  border-color: rgba(255, 77, 77, 0.3);
}
.btn-danger:hover:not(:disabled) {
  background: var(--danger);
  color: #ffffff;
}

/* Loading spinner */
.btn-content {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}
.hidden-text {
  visibility: hidden;
}

.spinner {
  position: absolute;
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top-color: #ffffff;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>