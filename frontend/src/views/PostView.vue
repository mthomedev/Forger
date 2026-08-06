<template>
  <div class="layout">
    <main class="main-content">
      <div v-if="loading" class="state-box" role="status" aria-live="polite">
        <div class="spinner spinner-lg"></div>
        <p>Loading post...</p>
      </div>

      <div v-else-if="!post" class="state-box">
        <div class="error-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" width="48" height="48" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
        </div>
        <h2>Post not found</h2>
        <p>This post may have been deleted or is no longer available.</p>
        <router-link to="/" class="back-link">Back to Feed</router-link>
      </div>

      <div v-else class="post-container">
        <div class="post-image-section">
          <img :src="getImageUrl(post.image_url)" class="post-image" alt="Post content" @dblclick="toggleLike" title="Double-click to like" />
        </div>

        <div class="post-info-section">
          <div class="post-header">
            <router-link :to="`/u/${post.user.username}`" class="user-info">
              <img :src="getAvatarUrl(post.user.avatar_url, post.user.username)" class="avatar" />
              <div class="user-meta">
                <span class="username">{{ post.user.username }}</span>
                <span class="post-time">{{ timeAgo(post.created_at) }}</span>
              </div>
            </router-link>
            <button v-if="isOwner" class="delete-btn" @click="deletePost" title="Delete post">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z" />
              </svg>
            </button>
          </div>

          <div class="comments-section">
            <div class="comment-item caption-item">
              <img :src="getAvatarUrl(post.user.avatar_url, post.user.username)" class="comment-avatar" />
              <div class="comment-content">
                <router-link :to="`/u/${post.user.username}`" class="username">{{ post.user.username }}</router-link>
                <span class="text">{{ post.caption }}</span>
              </div>
            </div>

            <div v-if="comments.length === 0" class="no-comments">
              No comments yet. Be the first to share your thoughts!
            </div>

            <div v-for="comment in comments" :key="comment.id" class="comment-item">
              <router-link :to="`/u/${comment.user.username}`">
                <img :src="getAvatarUrl(comment.user.avatar_url, comment.user.username)" class="comment-avatar" />
              </router-link>
              <div class="comment-content">
                <router-link :to="`/u/${comment.user.username}`" class="username">{{ comment.user.username }}</router-link>
                <span class="text">{{ comment.body }}</span>
                <div class="comment-meta">
                  <span class="timestamp">{{ timeAgo(comment.created_at) }}</span>
                  <button v-if="authStore.user?.id === comment.user.id" class="delete-comment-btn" @click="deleteComment(comment.id)">Delete</button>
                </div>
              </div>
            </div>
          </div>

          <div class="post-actions-wrapper">
            <div class="post-actions">
              <button
                class="action-btn"
                :class="{ liked: post.is_liked, 'like-pulse': likePulse }"
                @click="toggleLike"
                :aria-label="post.is_liked ? 'Unlike' : 'Like'"
                :disabled="likeLoading"
              >
                <svg viewBox="0 0 24 24" width="28" height="28" :fill="post.is_liked ? 'currentColor' : 'none'" :stroke="post.is_liked ? 'none' : 'currentColor'" stroke-width="2">
                  <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                </svg>
              </button>
            </div>
            <div class="likes-count">{{ post.likes_count }} likes</div>
            <div class="timestamp main-timestamp">{{ timeAgo(post.created_at) }}</div>
          </div>

          <div v-if="commentError" class="error-banner comment-error" role="alert">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
            <span>{{ commentError }}</span>
          </div>

          <form @submit.prevent="addComment" class="add-comment-form">
            <input
              v-model="newComment"
              type="text"
              placeholder="Add a comment..."
              class="comment-input"
              required
              maxlength="300"
              @input="commentError = ''"
            />
            <button type="submit" class="post-btn" :disabled="!newComment.trim() || submittingComment">
              {{ submittingComment ? 'Posting...' : 'Post' }}
            </button>
          </form>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import postService from '../services/postService'
import { getAvatarUrl, getImageUrl } from '../utils/media'
import { timeAgo } from '../utils/time'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const post = ref(null)
const comments = ref([])
const loading = ref(true)
const newComment = ref('')
const submittingComment = ref(false)
const commentError = ref('')
const likeLoading = ref(false)
const likePulse = ref(false)

const isOwner = computed(() => authStore.user && post.value && authStore.user.id === post.value.user.id)

onMounted(async () => {
  const postId = route.params.id
  try {
    const postData = await postService.getPost(postId)
    post.value = postData
    const commentsData = await postService.getComments(postId)
    comments.value = commentsData || []
  } catch (err) {
    console.error('Failed to load post', err)
    post.value = null
  } finally {
    loading.value = false
  }
})

const toggleLike = async () => {
  if (!post.value || likeLoading.value) return
  likeLoading.value = true
  try {
    const res = await postService.toggleLike(post.value.id)
    post.value.is_liked = res.liked
    post.value.likes_count += (res.liked ? 1 : -1)
    if (res.liked) {
      likePulse.value = true
      setTimeout(() => { likePulse.value = false }, 300)
    }
  } catch (err) {
    console.error('Like failed', err)
  } finally {
    likeLoading.value = false
  }
}

const deletePost = async () => {
  if (confirm('Delete this post?')) {
    try {
      await postService.deletePost(post.value.id)
      router.push('/')
    } catch (err) {
      console.error('Delete failed', err)
    }
  }
}

const addComment = async () => {
  if (!newComment.value.trim() || submittingComment.value) return
  submittingComment.value = true
  commentError.value = ''
  try {
    const res = await postService.addComment(post.value.id, newComment.value)
    comments.value.push(res)
    newComment.value = ''
  } catch (err) {
    commentError.value = err?.message || 'Failed to post comment. Please try again.'
    console.error('Failed to add comment', err)
  } finally {
    submittingComment.value = false
  }
}

const deleteComment = async (commentId) => {
  if (confirm('Delete this comment?')) {
    try {
      await postService.deleteComment(commentId)
      comments.value = comments.value.filter(c => c.id !== commentId)
    } catch (err) {
      console.error('Delete failed', err)
    }
  }
}
</script>

<style scoped>
.layout {
  min-height: 100vh;
  background-color: var(--bg-primary, #0a0a0a);
  color: var(--text-primary, #fafafa);
}

.main-content {
  max-width: 935px;
  margin: 0 auto;
  padding: 1rem;
}

.state-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 5rem 1.5rem;
  color: var(--text-secondary, #a8a8a8);
  gap: 1rem;
}

.state-box h2 {
  color: var(--text-primary, #fafafa);
  font-size: 1.5rem;
  margin: 0;
}

.state-box p {
  margin: 0;
  line-height: 1.5;
}

.spinner-lg {
  margin-bottom: 0.5rem;
}

.error-icon {
  color: var(--danger, #ff4d4d);
}

.back-link {
  display: inline-block;
  background: var(--accent, #e1306c);
  color: white;
  text-decoration: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  transition: opacity 0.2s;
}

.back-link:hover {
  opacity: 0.9;
}

.post-container {
  display: flex;
  flex-direction: column;
  background: var(--bg-card, #1a1a1a);
  border: 1px solid var(--border, #2a2a2a);
  border-radius: 12px;
  overflow: hidden;
}

@media (min-width: 768px) {
  .post-container {
    flex-direction: row;
    max-height: 80vh;
  }
}

.post-image-section {
  flex: 1;
  background: black;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.post-image {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.post-info-section {
  width: 100%;
  display: flex;
  flex-direction: column;
}

@media (min-width: 768px) {
  .post-info-section {
    width: 350px;
    border-left: 1px solid var(--border, #2a2a2a);
  }
}

.post-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.9rem 1rem;
  border-bottom: 1px solid var(--border, #2a2a2a);
}

.user-info {
  display: flex;
  align-items: center;
  text-decoration: none;
  color: inherit;
  gap: 0.75rem;
}

.user-meta {
  display: flex;
  flex-direction: column;
}

.post-time {
  color: var(--text-secondary, #a8a8a8);
  font-size: 0.75rem;
}

.avatar, .comment-avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
}

.username {
  font-weight: 600;
  text-decoration: none;
  color: var(--text-primary, #fafafa);
}

.delete-btn {
  background: none;
  border: none;
  color: var(--text-secondary, #a8a8a8);
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 50%;
  transition: color 0.2s, background 0.2s;
}

.delete-btn:hover {
  color: #ff4d4d;
  background: rgba(255, 77, 77, 0.1);
}

.comments-section {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.comment-item {
  display: flex;
  gap: 0.75rem;
}

.comment-content {
  flex: 1;
  font-size: 0.95rem;
  line-height: 1.4;
}

.comment-content .text {
  margin-left: 0.4rem;
  word-break: break-word;
}

.caption-item .text {
  color: var(--text-primary, #fafafa);
}

.comment-meta {
  display: flex;
  gap: 1rem;
  margin-top: 0.25rem;
}

.timestamp {
  color: var(--text-secondary, #a8a8a8);
  font-size: 0.75rem;
}

.no-comments {
  text-align: center;
  color: var(--text-secondary, #a8a8a8);
  font-size: 0.9rem;
  padding: 2rem 1rem;
}

.delete-comment-btn {
  background: none;
  border: none;
  color: var(--text-secondary, #a8a8a8);
  font-size: 0.75rem;
  cursor: pointer;
  padding: 0;
  transition: color 0.2s;
}

.delete-comment-btn:hover {
  color: #ff4d4d;
}

.post-actions-wrapper {
  padding: 0.9rem 1rem;
  border-top: 1px solid var(--border, #2a2a2a);
}

.post-actions {
  display: flex;
  gap: 1rem;
  margin-bottom: 0.4rem;
}

.action-btn {
  background: none;
  border: none;
  padding: 0;
  color: var(--text-primary, #fafafa);
  cursor: pointer;
  transition: transform 0.15s ease, color 0.2s;
}

.action-btn:disabled {
  cursor: default;
}

.action-btn.liked {
  color: var(--accent, #e1306c);
}

.action-btn.like-pulse {
  animation: heartPop 0.3s ease;
}

@keyframes heartPop {
  0% { transform: scale(1); }
  50% { transform: scale(1.3); }
  100% { transform: scale(1); }
}

.likes-count {
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.main-timestamp {
  text-transform: uppercase;
}

.comment-error {
  font-size: 0.85rem;
  padding: 0.6rem 0.9rem;
  margin: 0 1rem;
}

.add-comment-form {
  display: flex;
  align-items: center;
  padding: 0.9rem 1rem;
  border-top: 1px solid var(--border, #2a2a2a);
  gap: 0.5rem;
}

.comment-input {
  flex: 1;
  background: transparent;
  border: none;
  color: var(--text-primary, #fafafa);
  font-family: inherit;
  font-size: 0.95rem;
  resize: none;
}

.comment-input::placeholder {
  color: var(--text-secondary, #a8a8a8);
}

.comment-input:focus {
  outline: none;
}

.post-btn {
  background: none;
  border: none;
  color: #0095f6;
  font-weight: 600;
  cursor: pointer;
  font-size: 0.95rem;
  white-space: nowrap;
  transition: opacity 0.2s;
}

.post-btn:disabled {
  opacity: 0.4;
  cursor: default;
}
</style>
