import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useUiStore = defineStore('ui', () => {
  const createPostOpen = ref(false)
  const createdPost = ref(null)

  function openCreatePost() {
    createPostOpen.value = true
  }

  function closeCreatePost() {
    createPostOpen.value = false
  }

  function setCreatedPost(post) {
    createdPost.value = post
  }

  return { createPostOpen, createdPost, openCreatePost, closeCreatePost, setCreatedPost }
})
