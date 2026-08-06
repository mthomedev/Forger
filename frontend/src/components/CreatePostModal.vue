<template>
  <BaseModal
    :show="show"
    title="Forge New Creation"
    max-width="560px"
    :close-on-backdrop="false"
    @close="$emit('update:show', false)"
  >
    <template #default>
      <form @submit.prevent="submitPost" class="create-post-form">
        <div class="image-section">
          <label v-if="!previewUrl" class="upload-area" aria-label="Upload image">
            <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4m14-7-5-5-5 5m5-5v12" />
            </svg>
            <span>Select from computer</span>
            <span class="upload-hint">or drag & drop</span>
            <input type="file" accept="image/*" @change="onFileChange" class="visually-hidden" />
          </label>

          <div v-else class="preview-area">
            <img :src="previewUrl" class="preview-image" alt="Post preview" />
            <BaseButton
              variant="ghost"
              size="sm"
              class="change-btn"
              @click="removeImage"
              aria-label="Remove selected image"
            >
              Change
            </BaseButton>
          </div>
        </div>

        <BaseInput
          v-model="caption"
          type="textarea"
          :rows="3"
          placeholder="Write a caption... (optional)"
          :maxlength="2200"
          class="caption-input"
          aria-label="Post caption"
        >
          <template #suffix>
            <span class="char-count">{{ caption.length }}/2200</span>
          </template>
        </BaseInput>

        <div v-if="error" class="error-msg" role="alert">{{ error }}</div>
      </form>
    </template>

    <template #footer>
      <BaseButton
        variant="secondary"
        @click="$emit('update:show', false)"
        :disabled="loading"
      >
        Cancel
      </BaseButton>
      <BaseButton
        variant="primary"
        :loading="loading"
        :disabled="!imageFile || loading"
        @click="submitPost"
      >
        Share
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script setup>
import { ref, watch } from 'vue'
import BaseModal from './common/BaseModal.vue'
import BaseButton from './common/BaseButton.vue'
import BaseInput from './common/BaseInput.vue'
import postService from '../services/postService'

const props = defineProps({
  show: { type: Boolean, default: false }
})

const emit = defineEmits(['created', 'update:show'])

const imageFile = ref(null)
const previewUrl = ref(null)
const caption = ref('')
const loading = ref(false)
const error = ref('')

const onFileChange = (e) => {
  const file = e.target.files[0]
  if (file) {
    validateAndSetFile(file)
    e.target.value = ''
  }
}

const validateAndSetFile = (file) => {
  const validTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif']
  if (!validTypes.includes(file.type)) {
    error.value = 'Please select a valid image (JPEG, PNG, WebP, or GIF)'
    return
  }
  if (file.size > 10 * 1024 * 1024) {
    error.value = 'Image must be less than 10MB'
    return
  }
  error.value = ''
  imageFile.value = file
  previewUrl.value = URL.createObjectURL(file)
}

const removeImage = () => {
  if (previewUrl.value) {
    URL.revokeObjectURL(previewUrl.value)
  }
  imageFile.value = null
  previewUrl.value = null
  error.value = ''
}

const submitPost = async () => {
  if (!imageFile.value || loading.value) return

  loading.value = true
  error.value = ''

  const formData = new FormData()
  formData.append('image', imageFile.value)
  formData.append('caption', caption.value)

  try {
    const newPost = await postService.createPost(formData)
    emit('created', newPost)
    resetForm()
    emit('update:show', false)
  } catch (err) {
    error.value = err.message || 'Failed to create post. Please try again.'
  } finally {
    loading.value = false
  }
}

const resetForm = () => {
  caption.value = ''
  removeImage()
}

// Cleanup on unmount
watch(() => props.show, (newVal) => {
  if (!newVal) {
    resetForm()
    loading.value = false
    error.value = ''
  }
})
</script>

<style scoped>
.create-post-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.image-section {
  position: relative;
}

.upload-area {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border: 2px dashed var(--border-light);
  border-radius: var(--radius-lg);
  background: var(--bg-elevated);
  transition: all var(--transition-base);
  cursor: pointer;
  padding: 3rem 2rem;
  gap: 0.75rem;
  color: var(--text-secondary);
}

.upload-area:hover {
  border-color: var(--accent);
  background: var(--accent-subtle);
}

.upload-area:focus-within {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px var(--accent-glow);
}

.upload-hint {
  font-size: 0.8rem;
  color: var(--text-tertiary);
}

.preview-area {
  position: relative;
  border-radius: var(--radius-lg);
  overflow: hidden;
  background: var(--bg-primary);
}

.preview-image {
  width: 100%;
  max-height: 400px;
  object-fit: contain;
  display: block;
}

.change-btn {
  position: absolute;
  top: 1rem;
  right: 1rem;
  z-index: 1;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(8px);
}

.caption-input :deep(.base-input) {
  min-height: 100px;
  resize: vertical;
}

.char-count {
  font-size: 0.75rem;
  color: var(--text-tertiary);
  padding-right: 0.5rem;
  pointer-events: none;
}

.error-msg {
  color: var(--danger);
  font-size: 0.85rem;
  padding: 0.5rem;
  background: rgba(239, 68, 68, 0.1);
  border-radius: var(--radius-md);
  border: 1px solid rgba(239, 68, 68, 0.2);
}

@media (max-width: 480px) {
  .upload-area {
    padding: 2rem 1rem;
  }

  .preview-image {
    max-height: 300px;
  }
}
</style>