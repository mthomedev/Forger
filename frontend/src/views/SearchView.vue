<template>
  <div class="layout">
    <main class="main-content">
      <header class="search-header">
        <h1 class="page-title">Search Makers</h1>
        <p class="page-subtitle">Find creators and makers in the Forger community.</p>
        <div class="search-input-wrapper" :class="{ 'is-loading': loading }">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" class="search-icon">
            <path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0016 9.5 6.5 6.5 0 109.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
          </svg>
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Search by username..."
            class="search-input"
            aria-label="Search users"
          />
          <div v-if="loading" class="input-spinner" aria-hidden="true"></div>
          <button v-else-if="searchQuery" class="clear-btn" @click="searchQuery = ''" aria-label="Clear search">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
          </button>
        </div>
      </header>

      <div class="results-container">
        <div v-if="loading && page === 1" class="results-list" aria-label="Searching" role="status">
          <div v-for="i in 4" :key="i" class="skeleton-row">
            <div class="skeleton skeleton-avatar"></div>
            <div class="skeleton-lines">
              <div class="skeleton skeleton-line skeleton-line-sm"></div>
              <div class="skeleton skeleton-line"></div>
            </div>
          </div>
        </div>

        <div v-else-if="errorMessage" class="empty-state">
          <div class="empty-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="40" height="40" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
          </div>
          <h3>Search failed</h3>
          <p>{{ errorMessage }}</p>
          <button class="retry-btn" @click="performSearch(true)">Try Again</button>
        </div>

        <div v-else-if="users.length === 0" class="empty-state">
          <div class="empty-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="40" height="40" fill="currentColor"><path d="M9.5 15.5a6 6 0 1 1 0-12 6 6 0 0 1 0 12zM2 21c0-3.87 3.13-7 7-7h1c3.87 0 7 3.13 7 7v1H2v-1z"/></svg>
          </div>
          <h3>{{ searchQuery ? 'No makers found' : 'Start searching' }}</h3>
          <p v-if="searchQuery">No results for "{{ searchQuery }}". Try a different username.</p>
          <p v-else>Search for makers by username to find and follow them.</p>
        </div>

        <div v-else class="results-list">
          <div v-for="user in users" :key="user.id" class="user-item">
            <router-link :to="`/u/${user.username}`" class="user-info-link">
              <img :src="getAvatarUrl(user.avatar_url, user.username)" class="avatar" />
              <div class="user-details">
                <span class="username">@{{ user.username }}</span>
                <span class="name">{{ user.name }} • {{ user.followers_count || 0 }} followers</span>
              </div>
            </router-link>

            <button
              v-if="authStore.user?.id !== user.id"
              class="follow-btn"
              :class="{ following: user.is_following }"
              @click="toggleFollow(user)"
              :disabled="user._followLoading"
            >
              <span v-if="user._followLoading" class="spinner spinner-sm"></span>
              <span>{{ user.is_following ? 'Following' : 'Follow' }}</span>
            </button>
          </div>

          <button v-if="hasMore" class="load-more-btn" @click="loadMore" :disabled="loading">
            <span v-if="loading" class="spinner spinner-sm"></span>
            <span>{{ loading ? 'Loading...' : 'Load More' }}</span>
          </button>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import userService from '../services/userService'
import { useAuthStore } from '../stores/auth'
import { getAvatarUrl } from '../utils/media'

const authStore = useAuthStore()
const searchQuery = ref('')
const users = ref([])
const loading = ref(false)
const page = ref(1)
const hasMore = ref(false)
const errorMessage = ref('')
let debounceTimeout = null

const performSearch = async (reset = false) => {
  if (reset) {
    page.value = 1
    users.value = []
  }
  loading.value = true
  errorMessage.value = ''

  try {
    const res = await userService.search(searchQuery.value, page.value)
    if (res && res.data) {
      if (reset) {
        users.value = res.data
      } else {
        users.value = [...users.value, ...res.data]
      }
      hasMore.value = res.current_page < res.last_page
    }
  } catch (err) {
    errorMessage.value = err?.message || 'Failed to search. Please try again.'
    if (reset) {
      users.value = []
    }
    console.error('Search failed', err)
  } finally {
    loading.value = false
  }
}

watch(searchQuery, () => {
  clearTimeout(debounceTimeout)
  debounceTimeout = setTimeout(() => {
    performSearch(true)
  }, 400)
})

onMounted(() => {
  performSearch(true)
})

const loadMore = () => {
  if (!loading.value && hasMore.value) {
    page.value++
    performSearch()
  }
}

const toggleFollow = async (user) => {
  if (user._followLoading) return
  user._followLoading = true
  try {
    const res = await userService.toggleFollow(user.id)
    user.is_following = res.following
    if (res.following) {
      user.followers_count = (user.followers_count || 0) + 1
    } else {
      user.followers_count = Math.max(0, (user.followers_count || 1) - 1)
    }
  } catch (err) {
    console.error('Toggle follow failed', err)
  } finally {
    user._followLoading = false
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
  max-width: 600px;
  margin: 0 auto;
  padding: 1.5rem 1rem;
}

.search-header {
  margin-bottom: 2rem;
}

.page-title {
  font-size: 1.75rem;
  font-weight: 800;
  margin: 0 0 0.25rem;
  background: linear-gradient(135deg, var(--text-primary, #fafafa) 30%, var(--accent, #e1306c) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.page-subtitle {
  color: var(--text-secondary, #a8a8a8);
  font-size: 0.95rem;
  margin: 0 0 1.25rem;
}

.search-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.search-icon {
  position: absolute;
  left: 14px;
  color: var(--text-secondary, #a8a8a8);
  pointer-events: none;
}

.search-input {
  width: 100%;
  background: var(--bg-elevated, #242424);
  border: 1px solid var(--border, #2a2a2a);
  color: var(--text-primary, #fafafa);
  padding: 0.85rem 3rem 0.85rem 2.8rem;
  border-radius: 12px;
  font-family: inherit;
  font-size: 1rem;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.search-input:focus {
  outline: none;
  border-color: var(--accent);
  box-shadow: 0 0 0 3px var(--accent-glow);
}

.input-spinner {
  position: absolute;
  right: 14px;
  width: 18px;
  height: 18px;
  border: 2px solid var(--border, #2a2a2a);
  border-top-color: var(--accent);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

.clear-btn {
  position: absolute;
  right: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: none;
  border: none;
  color: var(--text-secondary, #a8a8a8);
  cursor: pointer;
  padding: 4px;
  border-radius: 50%;
  transition: color 0.2s, background 0.2s;
}

.clear-btn:hover {
  color: var(--text-primary, #fafafa);
  background: var(--bg-card, #1a1a1a);
}

.results-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.user-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem;
  border-radius: 12px;
  transition: background 0.2s;
}

.user-item:hover {
  background: var(--bg-card, #1a1a1a);
}

.user-info-link {
  display: flex;
  align-items: center;
  text-decoration: none;
  color: inherit;
  gap: 1rem;
  flex: 1;
  min-width: 0;
}

.avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
}

.user-details {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.username {
  font-weight: 600;
  font-size: 1rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.name {
  color: var(--text-secondary, #a8a8a8);
  font-size: 0.85rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.follow-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  background: var(--accent, #e1306c);
  color: white;
  border: none;
  padding: 0.5rem 1.25rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: background 0.2s, opacity 0.2s;
  flex-shrink: 0;
  min-width: 100px;
}

.follow-btn:hover:not(:disabled) {
  opacity: 0.9;
}

.follow-btn.following {
  background: var(--bg-elevated, #242424);
  color: var(--text-primary, #fafafa);
  border: 1px solid var(--border, #2a2a2a);
}

.follow-btn:disabled {
  opacity: 0.7;
  cursor: wait;
}

.following .spinner {
  border-color: var(--border, #2a2a2a);
  border-top-color: var(--text-primary, #fafafa);
}

.skeleton-row {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem;
}

.skeleton-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  flex-shrink: 0;
}

.skeleton-lines {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.skeleton-line {
  height: 14px;
}

.skeleton-line-sm {
  width: 30%;
}

.empty-state {
  text-align: center;
  padding: 3.5rem 1rem;
  color: var(--text-secondary, #a8a8a8);
}

.empty-icon {
  color: var(--border, #2a2a2a);
  margin-bottom: 1rem;
  display: flex;
  justify-content: center;
}

.empty-state h3 {
  color: var(--text-primary, #fafafa);
  font-size: 1.25rem;
  margin: 0 0 0.5rem;
}

.empty-state p {
  margin: 0 auto 1.25rem;
  max-width: 320px;
  line-height: 1.5;
  word-break: break-word;
}

.retry-btn {
  background: var(--accent, #e1306c);
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.2s;
}

.retry-btn:hover {
  opacity: 0.9;
}

.load-more-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  width: 100%;
  padding: 0.9rem;
  background: var(--bg-card, #1a1a1a);
  border: 1px solid var(--border, #2a2a2a);
  color: var(--text-primary, #fafafa);
  border-radius: 10px;
  cursor: pointer;
  margin-top: 0.5rem;
  font-size: 0.95rem;
  transition: border-color 0.2s;
}

.load-more-btn:hover:not(:disabled) {
  border-color: var(--text-secondary, #a8a8a8);
}

.load-more-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
