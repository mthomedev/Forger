<template>
  <div class="input-wrapper" :class="{ 'has-error': error, 'is-focused': isFocused }">
    <label v-if="label" :for="id" class="input-label">{{ label }}</label>

    <div class="input-container">
      <div v-if="$slots.prefix" class="input-prefix">
        <slot name="prefix" />
      </div>

      <input
        v-if="type !== 'textarea'"
        :id="id"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :required="required"
        :disabled="disabled"
        :autocomplete="autocomplete"
        class="base-input"
        @input="$emit('update:modelValue', $event.target.value)"
        @focus="isFocused = true"
        @blur="isFocused = false"
        v-bind="$attrs"
      />

      <textarea
        v-else
        :id="id"
        :value="modelValue"
        :placeholder="placeholder"
        :required="required"
        :disabled="disabled"
        :rows="rows"
        class="base-input base-textarea"
        @input="$emit('update:modelValue', $event.target.value)"
        @focus="isFocused = true"
        @blur="isFocused = false"
        v-bind="$attrs"
      />

      <div v-if="$slots.suffix" class="input-suffix">
        <slot name="suffix" />
      </div>
    </div>

    <span v-if="error" class="input-error-text" role="alert">{{ error }}</span>
  </div>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
  modelValue: { type: [String, Number], default: '' },
  label: { type: String, default: '' },
  type: { type: String, default: 'text' },
  placeholder: { type: String, default: '' },
  error: { type: String, default: '' },
  required: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  autocomplete: { type: String, default: 'off' },
  id: { type: String, default: () => `input-${Math.random().toString(36).substring(2, 9)}` },
  rows: { type: Number, default: 3 }
})

defineEmits(['update:modelValue'])

const isFocused = ref(false)
</script>

<style scoped>
.input-wrapper {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  width: 100%;
}

.input-label {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--text-secondary);
}

.input-container {
  position: relative;
  display: flex;
  align-items: center;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 8px;
  transition: all 0.2s ease;
  overflow: hidden;
}

.input-container:hover {
  border-color: var(--border-light);
}

.input-wrapper.is-focused .input-container {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px var(--accent-glow);
}

.input-wrapper.has-error .input-container {
  border-color: var(--danger);
  box-shadow: 0 0 0 3px rgba(255, 77, 77, 0.2);
}

.base-input {
  width: 100%;
  background: transparent;
  border: none;
  color: var(--text-primary);
  padding: 0.75rem 1rem;
  font-family: inherit;
  font-size: 0.95rem;
  outline: none;
}

.base-input::placeholder {
  color: var(--text-secondary);
  opacity: 0.7;
}

.input-prefix, .input-suffix {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 0.75rem;
  color: var(--text-secondary);
}

.input-error-text {
  font-size: 0.8rem;
  color: var(--danger);
  margin-top: 0.1rem;
}
</style>