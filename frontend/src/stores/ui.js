import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useUiStore = defineStore('ui', () => {
  const createPostOpen = ref(false)
  const createdPost = ref(null)
  const editingPost = ref(null)
  const updatedPost = ref(null)

  function openCreatePost() {
    editingPost.value = null
    createPostOpen.value = true
  }

  function openEditPost(post) {
    editingPost.value = post
    createPostOpen.value = true
  }

  function closeCreatePost() {
    createPostOpen.value = false
  }

  function setEditingPost(post) {
    editingPost.value = post
  }

  function setCreatedPost(post) {
    createdPost.value = post
  }

  function setUpdatedPost(post) {
    updatedPost.value = post
  }

  return {
    createPostOpen,
    createdPost,
    editingPost,
    updatedPost,
    openCreatePost,
    openEditPost,
    closeCreatePost,
    setEditingPost,
    setCreatedPost,
    setUpdatedPost,
  }
})
