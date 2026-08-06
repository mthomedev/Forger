import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useUiStore = defineStore('ui', () => {
  const createPostOpen = ref(false)

  function openCreatePost() {
    createPostOpen.value = true
  }

  function closeCreatePost() {
    createPostOpen.value = false
  }

  return { createPostOpen, openCreatePost, closeCreatePost }
})
