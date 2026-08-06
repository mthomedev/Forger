<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div
        v-if="show"
        class="modal-backdrop"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="titleId"
        @click.self="onBackdropClick"
      >
        <div class="modal-card" :style="{ maxWidth }">
          <header class="modal-header">
            <h2 :id="titleId" class="modal-title">{{ title }}</h2>
            <button class="close-btn" aria-label="Close modal" @click="close">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 6L6 18M6 6l12 12" />
              </svg>
            </button>
          </header>

          <div class="modal-body">
            <slot />
          </div>

          <footer v-if="$slots.footer" class="modal-footer">
            <slot name="footer" />
          </footer>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { onMounted, onUnmounted, computed } from 'vue'

const props = defineProps({
  show: { type: Boolean, default: false },
  title: { type: String, default: '' },
  maxWidth: { type: String, default: '520px' },
  closeOnBackdrop: { type: Boolean, default: true }
})

const emit = defineEmits(['close'])

const titleId = computed(() => `modal-title-${Math.random().toString(36).substring(2, 9)}`)

const close = () => {
  emit('close')
}

const onBackdropClick = () => {
  if (props.closeOnBackdrop) {
    close()
  }
}

const handleKeyDown = (e) => {
  if (props.show && e.key === 'Escape') {
    close()
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown)
})
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.75);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}

.modal-card {
  background: var(--bg-card);
  border: 1px solid var(--border-light);
  border-radius: 16px;
  width: 100%;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
  overflow: hidden;
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid var(--border);
}

.modal-title {
  font-size: 1.15rem;
  font-weight: 700;
  color: var(--text-primary);
}

.close-btn {
  background: transparent;
  border: none;
  color: var(--text-secondary);
  cursor: pointer;
  padding: 0.35rem;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.close-btn:hover {
  background: var(--bg-elevated);
  color: var(--text-primary);
}

.modal-body {
  padding: 1.5rem;
  overflow-y: auto;
  flex: 1;
}

.modal-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid var(--border);
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  background: var(--bg-secondary);
}

/* Animations */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
  transform: scale(0.96);
}
</style>