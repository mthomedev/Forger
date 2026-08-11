<template>
  <article class="post-card">
    <header class="post-header">
      <router-link :to="`/u/${post.user.username}`" class="user-info" aria-label="View @{{ post.user.username }}'s workshop">
        <BaseAvatar
          :src="post.user.avatar_url"
          :username="post.user.username"
          size="md"
          glow
        />
        <span class="username">{{ post.user.username }}</span>
      </router-link>

      <div v-if="isOwner" class="owner-actions">
        <button
          class="edit-btn"
          @click="emit('edit', props.post)"
          aria-label="Edit this creation"
          title="Edit creation"
        >
          <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true">
            <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34a.9959.9959 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z" />
          </svg>
        </button>

        <button
          class="delete-btn"
          @click="deletePost"
          aria-label="Delete this creation"
          title="Delete creation"
        >
          <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true">
            <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z" />
          </svg>
        </button>
      </div>
    </header>

    <div v-if="post.image_url" class="post-image-container">
      <img
        :src="getImageUrl(post.image_url)"
        class="post-image"
        :alt="post.caption || 'Project creation by ' + post.user.username"
        loading="lazy"
      />
    </div>

    <div class="post-actions">
      <ActionButton
        :icon="likeIcon"
        :active="post.is_liked"
        :active-color="accentColor"
        @click="toggleLike"
        :aria-label="post.is_liked ? 'Unlike' : 'Like'"
        :title="post.is_liked ? 'Unlike' : 'Like'"
      >
        <template #icon>
          <svg viewBox="0 0 24 24" width="26" height="26" :fill="post.is_liked ? 'currentColor' : 'none'" :stroke="post.is_liked ? 'none' : 'currentColor'" stroke-width="2" aria-hidden="true">
            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
          </svg>
        </template>
      </ActionButton>

      <router-link :to="`/posts/${post.id}`" class="action-btn" aria-label="View comments">
        <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z" />
        </svg>
      </router-link>
    </div>

    <div class="post-content">
      <div class="likes-count">{{ post.likes_count }} sparks</div>

      <div class="caption">
        <router-link :to="`/u/${post.user.username}`" class="username">{{ post.user.username }}</router-link>
        {{ post.caption }}
      </div>

      <router-link v-if="post.comments_count > 0" :to="`/posts/${post.id}`" class="view-comments">
        View all {{ post.comments_count }} comments
      </router-link>

      <time class="timestamp" :datetime="formatDateTime(post.created_at)">
        {{ timeAgo(post.created_at) }}
      </time>
    </div>
  </article>
</template>

<script setup>
import { computed } from 'vue'
import { useAuthStore } from '../stores/auth'
import postService from '../services/postService'
import BaseAvatar from './common/BaseAvatar.vue'
import ActionButton from './ActionButton.vue'
import { getImageUrl } from '../utils/media'
import { timeAgo, formatDateTime } from '../utils/time'

const props = defineProps({
  post: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['liked', 'deleted', 'edit'])
const authStore = useAuthStore()

const isOwner = computed(() => authStore.user && authStore.user.id === props.post.user.id)
const accentColor = 'var(--accent)'

const toggleLike = async () => {
  try {
    const res = await postService.toggleLike(props.post.id)
    emit('liked', {
      postId: props.post.id,
      is_liked: res.liked,
      likes_count: props.post.likes_count + (res.liked ? 1 : -1)
    })
  } catch (err) {
    console.error('Like failed', err)
  }
}

const deletePost = async () => {
  const confirmed = window.confirm('Are you sure you want to delete this creation?')
  if (confirmed) {
    try {
      await postService.deletePost(props.post.id)
      emit('deleted', props.post.id)
    } catch (err) {
      console.error('Delete failed', err)
    }
  }
}
</script>

<style scoped>
.post-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 12px;
  margin-bottom: 1.5rem;
  overflow: hidden;
  color: var(--text-primary);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
  transition: border-color 0.2s ease;
}

.post-card:hover {
  border-color: var(--border-light);
}

.post-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.85rem 1rem;
}

.user-info {
  display: flex;
  align-items: center;
  text-decoration: none;
  color: inherit;
  gap: 0.75rem;
}

.user-info:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
  border-radius: 8px;
}

.username {
  font-weight: 600;
  font-size: 0.95rem;
  color: var(--text-primary);
}

.owner-actions {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.edit-btn,
.delete-btn {
  background: none;
  border: none;
  color: var(--text-secondary);
  cursor: pointer;
  padding: 0.25rem;
  border-radius: 4px;
  transition: color 0.2s, background 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.edit-btn:hover {
  color: var(--accent);
  background: rgba(255, 107, 26, 0.1);
}

.edit-btn:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

.delete-btn:hover {
  color: var(--danger);
  background: rgba(239, 68, 68, 0.1);
}

.delete-btn:focus-visible {
  outline: 2px solid var(--danger);
  outline-offset: 2px;
}

.post-image-container {
  width: 100%;
  background: #000;
  max-height: 700px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

.post-image {
  width: 100%;
  max-height: 700px;
  object-fit: cover;
  display: block;
}

.post-actions {
  display: flex;
  padding: 0.75rem 1rem 0.25rem;
  gap: 1rem;
}

.action-btn {
  background: none;
  border: none;
  padding: 0;
  color: var(--text-primary);
  cursor: pointer;
  transition: color 0.2s, transform 0.1s;
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 44px;
  min-height: 44px;
}

.action-btn:hover {
  color: var(--accent);
}

.action-btn:active {
  transform: scale(0.9);
}

.action-btn:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
  border-radius: 8px;
}

.post-content {
  padding: 0.5rem 1rem 1rem;
}

.likes-count {
  font-weight: 700;
  font-size: 0.9rem;
  margin-bottom: 0.35rem;
  color: var(--gold);
}

.caption {
  margin-bottom: 0.35rem;
  word-wrap: break-word;
  font-size: 0.95rem;
  line-height: 1.4;
}

.caption .username {
  margin-right: 0.5rem;
  text-decoration: none;
  color: var(--text-primary);
  font-weight: 600;
}

.caption .username:hover {
  color: var(--accent);
}

.view-comments {
  color: var(--text-secondary);
  text-decoration: none;
  font-size: 0.85rem;
  display: block;
  margin-bottom: 0.35rem;
  transition: color 0.2s;
}

.view-comments:hover {
  color: var(--accent);
}

.view-comments:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
  border-radius: 4px;
}

.timestamp {
  color: var(--text-secondary);
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-top: 0.5rem;
  display: block;
}
</style>