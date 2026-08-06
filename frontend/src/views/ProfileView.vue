<template>
  <div class="layout">
    <main class="main-content">
      <div v-if="loading" class="skeleton-wrapper" aria-label="Loading profile" role="status">
        <div class="skeleton-profile">
          <div class="skeleton skeleton-avatar-lg"></div>
          <div class="skeleton-lines">
            <div class="skeleton skeleton-line skeleton-line-lg"></div>
            <div class="skeleton skeleton-line"></div>
            <div class="skeleton skeleton-line skeleton-line-sm"></div>
          </div>
        </div>
        <div class="skeleton-grid">
          <div v-for="i in 6" :key="i" class="skeleton skeleton-post"></div>
        </div>
      </div>

      <div v-else class="profile-container">
        <header class="profile-header">
          <div class="avatar-container">
            <label class="avatar-wrapper" title="Change profile picture">
              <img :src="getAvatarUrl(authStore.user?.avatar_url, authStore.user?.username)" class="profile-avatar" />
              <div class="avatar-overlay">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="white"><path d="M4 4h3l2-2h6l2 2h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2zm8 3a5 5 0 1 0 0 10 5 5 0 0 0 0-10zm0 2a3 3 0 1 1 0 6 3 3 0 0 1 0-6z"/></svg>
              </div>
              <input type="file" ref="fileInput" @change="uploadAvatar" accept="image/*" class="visually-hidden" aria-label="Upload profile picture" />
            </label>
          </div>

          <div class="profile-info">
            <div class="profile-title-row">
              <h2 class="username">{{ authStore.user?.username }}</h2>
              <div class="profile-actions">
                <button class="edit-btn" @click="toggleEdit">
                  {{ isEditing ? 'Close' : 'Edit Profile' }}
                </button>
                <button class="logout-btn" @click="handleLogout">Logout</button>
              </div>
            </div>

            <div class="profile-stats">
              <span><strong>{{ profile?.posts_count || 0 }}</strong> posts</span>
              <span><strong>{{ profile?.followers_count || 0 }}</strong> followers</span>
              <span><strong>{{ profile?.following_count || 0 }}</strong> following</span>
            </div>

            <div class="profile-bio">
              <div class="name">{{ authStore.user?.name }}</div>
              <div class="bio" v-if="profile?.bio">{{ profile?.bio }}</div>
            </div>
          </div>
        </header>

        <transition name="fade">
          <div v-if="isEditing" class="edit-profile-section">
            <h3>Edit Profile</h3>
            <form @submit.prevent="saveProfile" class="edit-form">
              <div class="form-group">
                <label for="edit-name">Name</label>
                <input id="edit-name" v-model="editForm.name" type="text" autocomplete="name" />
              </div>
              <div class="form-group">
                <label for="edit-username">Username</label>
                <input id="edit-username" v-model="editForm.username" type="text" autocomplete="username" />
              </div>
              <div class="form-group">
                <label for="edit-bio">Bio</label>
                <textarea id="edit-bio" v-model="editForm.bio" rows="3" maxlength="150" placeholder="Tell makers about yourself..."></textarea>
                <span class="char-count">{{ editForm.bio?.length || 0 }}/150</span>
              </div>

              <div v-if="formError" class="error-banner" role="alert">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                <span>{{ formError }}</span>
              </div>

              <div v-if="formSuccess" class="success-banner" role="status">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                <span>Profile updated successfully</span>
              </div>

              <div class="edit-actions">
                <button type="submit" class="save-btn" :disabled="saving">
                  <span v-if="saving" class="spinner spinner-sm"></span>
                  <span>{{ saving ? 'Saving...' : 'Save Changes' }}</span>
                </button>
                <button type="button" class="cancel-btn" @click="isEditing = false">Cancel</button>
              </div>
            </form>
          </div>
        </transition>

        <div class="posts-grid-container">
          <div class="tabs">
            <div class="tab active">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M3 3h8v8H3zm10 0h8v8h-8zM3 13h8v8H3zm10 0h8v8h-8z"/></svg>
              POSTS
            </div>
          </div>

          <div v-if="loadingPosts && posts.length === 0" class="posts-loading">
            <div v-for="i in 3" :key="i" class="skeleton skeleton-post"></div>
          </div>
          <div v-else-if="posts.length === 0" class="empty-posts">
            <div class="empty-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="40" height="40" fill="currentColor"><path d="M21.19 21.19L2.81 2.81 1.39 4.22l2.27 2.27A9.92 9.92 0 0 0 2 12c0 5.52 4.48 10 10 10 2.04 0 3.93-.62 5.51-1.66l2.27 2.27 1.41-1.42zM12 4c5.52 0 10 4.48 10 10 0 1.41-.3 2.75-.83 3.97l-8.86-8.86A9.87 9.87 0 0 0 16 4.83 7.87 7.87 0 0 0 12 4zM4.59 2.3L3 1.7 1.39 3.31l.3.3A11.95 11.95 0 0 0 12 22c2.38 0 4.59-.7 6.46-1.91l2.72 2.72 1.41-1.41L4.59 2.3z"/></svg>
            </div>
            <h3>No posts yet</h3>
            <p>Your creations will appear here. Start forging your first project!</p>
          </div>

          <div v-else class="posts-grid">
            <router-link
              v-for="post in posts"
              :key="post.id"
              :to="`/posts/${post.id}`"
              class="grid-item"
            >
              <img :src="getImageUrl(post.image_url)" class="grid-image" loading="lazy" />
              <div class="grid-overlay">
                <span class="overlay-stat">
                  <svg viewBox="0 0 24 24" width="20" height="20" fill="white"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                  {{ post.likes_count }}
                </span>
                <span class="overlay-stat">
                  <svg viewBox="0 0 24 24" width="20" height="20" fill="white"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>
                  {{ post.comments_count }}
                </span>
              </div>
            </router-link>
          </div>

          <button v-if="hasMorePosts" class="load-more-btn" @click="loadPosts" :disabled="loadingPosts">
            <span v-if="loadingPosts" class="spinner spinner-sm"></span>
            <span>{{ loadingPosts ? 'Loading...' : 'Load More' }}</span>
          </button>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { profileService } from '../services/profileService'
import postService from '../services/postService'
import { useAuthStore } from '../stores/auth'
import { getAvatarUrl, getImageUrl } from '../utils/media'

const router = useRouter()
const authStore = useAuthStore()

const profile = ref(null)
const loading = ref(true)

const posts = ref([])
const page = ref(1)
const hasMorePosts = ref(false)
const loadingPosts = ref(false)

const isEditing = ref(false)
const saving = ref(false)
const formError = ref('')
const formSuccess = ref('')
const editForm = ref({
  name: '',
  username: '',
  bio: ''
})

onMounted(async () => {
  await loadProfile()
  if (authStore.user) {
    loadPosts()
  }
})

const loadProfile = async () => {
  loading.value = true
  try {
    const data = await profileService.getProfile()
    profile.value = data
  } catch (err) {
    console.error('Failed to load profile', err)
  } finally {
    loading.value = false
  }
}

const loadPosts = async () => {
  if (!authStore.user) return
  loadingPosts.value = true
  try {
    const res = await postService.getUserPosts(authStore.user.id, page.value)
    if (res && res.data) {
      if (page.value === 1) {
        posts.value = res.data
      } else {
        posts.value = [...posts.value, ...res.data]
      }
      hasMorePosts.value = res.current_page < res.last_page
      page.value++
    }
  } catch (err) {
    console.error('Failed to load posts', err)
  } finally {
    loadingPosts.value = false
  }
}

const toggleEdit = () => {
  if (isEditing.value) {
    isEditing.value = false
    formError.value = ''
    formSuccess.value = ''
    return
  }
  editForm.value = {
    name: authStore.user?.name || '',
    username: authStore.user?.username || '',
    bio: profile.value?.bio || ''
  }
  formError.value = ''
  formSuccess.value = ''
  isEditing.value = true
}

const saveProfile = async () => {
  saving.value = true
  formError.value = ''
  formSuccess.value = ''
  try {
    const res = await profileService.updateProfile(editForm.value)
    authStore.user = res
    profile.value.bio = editForm.value.bio
    isEditing.value = false
    formSuccess.value = true
    setTimeout(() => { formSuccess.value = '' }, 3000)
  } catch (err) {
    formError.value = err?.message || 'Failed to update profile. Please try again.'
    console.error('Profile update failed', err)
  } finally {
    saving.value = false
  }
}

const uploadAvatar = async (e) => {
  const file = e.target.files[0]
  if (!file) return
  try {
    const res = await profileService.uploadAvatar(file)
    authStore.user.avatar_url = res.avatar_url
  } catch (err) {
    console.error('Avatar upload failed', err)
  } finally {
    e.target.value = ''
  }
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
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
  padding: 2rem 1rem;
}

.skeleton-wrapper {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.skeleton-profile {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.5rem;
}

@media (min-width: 768px) {
  .skeleton-profile {
    flex-direction: row;
    align-items: flex-start;
  }
}

.skeleton-avatar-lg {
  width: 150px;
  height: 150px;
  border-radius: 50%;
  flex-shrink: 0;
}

.skeleton-lines {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  width: 100%;
  padding-top: 1rem;
}

.skeleton-line {
  height: 14px;
  width: 40%;
}

.skeleton-line-lg {
  height: 24px;
  width: 30%;
}

.skeleton-line-sm {
  width: 60%;
}

.skeleton-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 4px;
}

@media (min-width: 768px) {
  .skeleton-grid {
    gap: 1.5rem;
  }
}

.skeleton-post {
  aspect-ratio: 1 / 1;
  border-radius: 0;
}

.profile-header {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 2.5rem;
  gap: 2rem;
}

@media (min-width: 768px) {
  .profile-header {
    flex-direction: row;
    align-items: flex-start;
  }
}

.avatar-container {
  flex-shrink: 0;
  display: flex;
  justify-content: center;
}

@media (min-width: 768px) {
  .avatar-container {
    width: 290px;
  }
}

.avatar-wrapper {
  position: relative;
  cursor: pointer;
  border-radius: 50%;
  overflow: hidden;
  border: 2px solid var(--border, #2a2a2a);
}

.profile-avatar {
  width: 150px;
  height: 150px;
  object-fit: cover;
  display: block;
}

.avatar-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.2s;
}

.avatar-wrapper:hover .avatar-overlay,
.avatar-wrapper:focus-within .avatar-overlay {
  opacity: 1;
}

.profile-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  align-items: center;
}

@media (min-width: 768px) {
  .profile-info {
    align-items: flex-start;
  }
}

.profile-title-row {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

@media (min-width: 768px) {
  .profile-title-row {
    flex-direction: row;
  }
}

.username {
  font-size: 1.5rem;
  font-weight: 600;
  margin: 0;
}

.profile-actions {
  display: flex;
  gap: 0.75rem;
}

.edit-btn, .logout-btn {
  padding: 0.5rem 1.25rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  font-size: 0.9rem;
  transition: background 0.2s, border-color 0.2s;
}

.edit-btn {
  background: var(--bg-elevated, #242424);
  color: var(--text-primary, #fafafa);
  border: 1px solid var(--border, #2a2a2a);
}

.edit-btn:hover {
  border-color: var(--text-secondary, #a8a8a8);
}

.logout-btn {
  background: transparent;
  color: #ff4d4d;
  border: 1px solid var(--border, #2a2a2a);
}

.logout-btn:hover {
  border-color: #ff4d4d;
}

.profile-stats {
  display: flex;
  gap: 2rem;
}

.profile-stats span {
  font-size: 1rem;
}

.profile-stats strong {
  font-weight: 700;
}

.profile-bio {
  text-align: center;
}

@media (min-width: 768px) {
  .profile-bio {
    text-align: left;
  }
}

.profile-bio .name {
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.bio {
  color: var(--text-secondary, #a8a8a8);
  line-height: 1.5;
  white-space: pre-line;
}

.edit-profile-section {
  background: var(--bg-card, #1a1a1a);
  border: 1px solid var(--border, #2a2a2a);
  border-radius: 12px;
  padding: 1.75rem;
  margin-bottom: 2rem;
}

.edit-profile-section h3 {
  margin: 0 0 1.25rem;
  font-size: 1.1rem;
}

.edit-form .form-group {
  margin-bottom: 1.25rem;
  position: relative;
}

.edit-form label {
  display: block;
  margin-bottom: 0.5rem;
  color: var(--text-secondary, #a8a8a8);
  font-size: 0.9rem;
  font-weight: 600;
}

.edit-form input, .edit-form textarea {
  width: 100%;
  background: var(--bg-elevated, #242424);
  border: 1px solid var(--border, #2a2a2a);
  color: var(--text-primary, #fafafa);
  padding: 0.75rem;
  border-radius: 8px;
  font-family: inherit;
  font-size: 0.95rem;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.edit-form input:focus, .edit-form textarea:focus {
  outline: none;
  border-color: var(--accent);
  box-shadow: 0 0 0 3px var(--accent-glow);
}

.char-count {
  position: absolute;
  right: 0.75rem;
  bottom: 0.6rem;
  font-size: 0.7rem;
  color: var(--text-secondary, #a8a8a8);
}

.error-banner,
.success-banner {
  margin-bottom: 1rem;
}

.edit-actions {
  display: flex;
  gap: 1rem;
}

.save-btn, .cancel-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  font-size: 0.95rem;
}

.save-btn {
  background: var(--accent, #e1306c);
  color: white;
  border: none;
  transition: opacity 0.2s;
}

.save-btn:hover:not(:disabled) {
  opacity: 0.9;
}

.save-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.cancel-btn {
  background: transparent;
  color: var(--text-primary, #fafafa);
  border: 1px solid var(--border, #2a2a2a);
  transition: border-color 0.2s;
}

.cancel-btn:hover {
  border-color: var(--text-secondary, #a8a8a8);
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

.posts-grid-container {
  border-top: 1px solid var(--border, #2a2a2a);
}

.tabs {
  display: flex;
  justify-content: center;
}

.tab {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem 0;
  color: var(--text-secondary, #a8a8a8);
  font-size: 0.85rem;
  font-weight: 600;
  letter-spacing: 1px;
  border-top: 1px solid transparent;
  margin-top: -1px;
}

.tab.active {
  color: var(--text-primary, #fafafa);
  border-top-color: var(--text-primary, #fafafa);
}

.posts-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 4px;
}

@media (min-width: 768px) {
  .posts-grid {
    gap: 1.5rem;
  }
}

.posts-loading {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 4px;
}

@media (min-width: 768px) {
  .posts-loading {
    gap: 1.5rem;
  }
}

.grid-item {
  position: relative;
  aspect-ratio: 1 / 1;
  background: var(--bg-card, #1a1a1a);
  overflow: hidden;
  transition: opacity 0.2s;
}

.grid-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.grid-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1.5rem;
  opacity: 0;
  transition: opacity 0.2s;
}

.grid-item:hover .grid-overlay {
  opacity: 1;
}

.overlay-stat {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: white;
  font-weight: 600;
}

.empty-posts {
  text-align: center;
  padding: 4rem 1rem;
  color: var(--text-secondary, #a8a8a8);
}

.empty-icon {
  color: var(--border, #2a2a2a);
  margin-bottom: 1rem;
  display: flex;
  justify-content: center;
}

.empty-posts h3 {
  color: var(--text-primary, #fafafa);
  font-size: 1.25rem;
  margin: 0 0 0.5rem;
}

.empty-posts p {
  margin: 0;
  max-width: 300px;
  margin-left: auto;
  margin-right: auto;
  line-height: 1.5;
}

.load-more-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  width: 100%;
  max-width: 300px;
  margin: 2rem auto;
  padding: 0.9rem;
  background: var(--bg-card, #1a1a1a);
  border: 1px solid var(--border, #2a2a2a);
  color: var(--text-primary, #fafafa);
  border-radius: 8px;
  cursor: pointer;
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
