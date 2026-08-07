<template>
  <main class="main-content" role="main">
    <div class="feed-container">
      <header class="feed-header" aria-label="Feed header">
        <h1 class="page-title">Forger Feed</h1>
        <BaseButton
          variant="primary"
          size="sm"
          @click="uiStore.openCreatePost()"
          aria-label="Create new post"
        >
          <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true">
            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z" />
          </svg>
          <span class="btn-text">Forge</span>
        </BaseButton>
      </header>

      <div
        v-if="loading && posts.length === 0"
        class="loading-state"
        role="status"
        aria-live="polite"
      >
        <div class="spinner spinner-lg" aria-hidden="true"></div>
        <p>Forging your feed...</p>
      </div>

      <div v-else-if="error" class="error-state" role="alert">
        <div class="error-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" width="48" height="48" fill="currentColor">
            <path d="M12 2L1 21h22L12 2zm1 14h-2v-2h2v2zm0-4h-2V7h2v5z" />
          </svg>
        </div>
        <h2>Unable to load feed</h2>
        <p>{{ error }}</p>
        <BaseButton variant="primary" @click="loadFeed" :disabled="loading">Try Again</BaseButton>
      </div>

      <div v-else-if="posts.length === 0" class="empty-state">
        <div class="empty-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" width="56" height="56" fill="currentColor">
            <path d="M7 2v11h3v9l7-12h-4l4-8H7z" />
          </svg>
        </div>
        <h2>Welcome to Forger</h2>
        <p>
          Your workshop feed is empty. Follow fellow creators to see their projects and creations
          here.
        </p>
        <div class="empty-actions">
          <BaseButton variant="primary" @click="uiStore.openCreatePost()"
            >Create Your First Post</BaseButton
          >
          <BaseButton variant="secondary" @click="$router.push('/search')"
            >Explore Makers</BaseButton
          >
        </div>
      </div>

      <div v-else class="posts-list">
        <PostCard
          v-for="post in posts"
          :key="post.id"
          :post="post"
          @liked="updatePostLike"
          @deleted="removePost"
        />

        <div v-if="hasMore" class="load-more-wrapper">
          <BaseButton
            variant="outline"
            class="load-more-btn"
            @click="loadFeed"
            :disabled="loading"
            :loading="loading"
            block
          >
            {{ loading ? "Forging more..." : "Load More Creations" }}
          </BaseButton>
        </div>
      </div>
    </div>

    <aside class="sidebar-container" aria-label="Suggestions">
      <div class="suggestions-panel">
        <header class="suggestions-header">
          <h2>Makers to Follow</h2>
        </header>

        <div v-if="loadingSuggestions" class="loading-suggestions" role="status" aria-live="polite">
          <div class="skeleton skeleton-avatar"></div>
          <div class="skeleton skeleton-text"></div>
          <div class="skeleton skeleton-text"></div>
          <div class="skeleton skeleton-text"></div>
        </div>

        <div v-else-if="suggestions.length === 0" class="no-suggestions">
          <p>No suggestions available at the moment.</p>
        </div>

        <div v-else class="suggestions-list">
          <div v-for="user in suggestions" :key="user.id" class="suggestion-item">
            <router-link
              :to="`/u/${user.username}`"
              class="suggestion-user"
              aria-label="View {{ user.username }}'s workshop"
            >
              <BaseAvatar :src="user.avatar_url" :username="user.username" size="sm" />
              <div class="suggestion-info">
                <div class="suggestion-username">@{{ user.username }}</div>
                <div class="suggestion-name">{{ user.name }}</div>
              </div>
            </router-link>
            <BaseButton
              :variant="user.is_following ? 'secondary' : 'primary'"
              size="sm"
              class="follow-btn"
              @click="toggleFollow(user)"
              :loading="user._followLoading"
            >
              {{ user.is_following ? "Following" : "Follow" }}
            </BaseButton>
          </div>
        </div>
      </div>
    </aside>
  </main>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import PostCard from "../components/PostCard.vue";
import BaseButton from "../components/common/BaseButton.vue";
import BaseAvatar from "../components/common/BaseAvatar.vue";
import postService from "../services/postService";
import userService from "../services/userService";
import { useUiStore } from "../stores/ui";

const uiStore = useUiStore();

const posts = ref([]);
const page = ref(1);
const hasMore = ref(true);
const loading = ref(false);
const error = ref(null);

const suggestions = ref([]);
const loadingSuggestions = ref(false);

onMounted(() => {
  loadFeed();
  loadSuggestions();
});

watch(
  () => uiStore.createdPost,
  (post) => {
    if (post) {
      posts.value.unshift(post);
      uiStore.setCreatedPost(null);
    }
  },
);

const loadFeed = async () => {
  if (loading.value || !hasMore.value) return;
  loading.value = true;
  error.value = null;
  try {
    const res = await postService.getFeed(page.value);
    if (res && res.data) {
      if (page.value === 1) {
        posts.value = res.data;
      } else {
        posts.value = [...posts.value, ...res.data];
      }
      hasMore.value = res.current_page < res.last_page;
      page.value++;
    }
  } catch (err) {
    console.error("Failed to load feed", err);
    if (page.value === 1) {
      error.value = err.message || "Failed to load feed. Please try again.";
    }
  } finally {
    loading.value = false;
  }
};

const loadSuggestions = async () => {
  loadingSuggestions.value = true;
  try {
    const data = await userService.getSuggestions();
    suggestions.value = data || [];
  } catch (err) {
    console.error("Failed to load suggestions", err);
  } finally {
    loadingSuggestions.value = false;
  }
};

const toggleFollow = async (user) => {
  user._followLoading = true;
  try {
    const res = await userService.toggleFollow(user.id);
    user.is_following = res.following;
  } catch (err) {
    console.error("Toggle follow failed", err);
  } finally {
    user._followLoading = false;
  }
};

const updatePostLike = ({ postId, is_liked, likes_count }) => {
  const post = posts.value.find((p) => p.id === postId);
  if (post) {
    post.is_liked = is_liked;
    post.likes_count = likes_count;
  }
};

const removePost = (postId) => {
  posts.value = posts.value.filter((p) => p.id !== postId);
};
</script>

<style scoped>
.main-content {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  padding: 2rem 1.5rem;
  gap: 2.5rem;
  justify-content: center;
}

.feed-container {
  width: 100%;
  max-width: 640px;
  flex: 1;
}

.feed-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--border);
}

.page-title {
  font-size: 1.75rem;
  font-weight: 800;
  background: linear-gradient(135deg, var(--gold) 30%, var(--accent) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin: 0;
}

.btn-text {
  display: none;
}

@media (min-width: 480px) {
  .btn-text {
    display: inline;
  }
}

.loading-state,
.empty-state,
.error-state {
  text-align: center;
  padding: 3rem 1.5rem;
  color: var(--text-secondary);
}

.spinner-lg {
  margin: 0 auto 1rem;
}

.loading-state p {
  font-size: 1rem;
  color: var(--text-secondary);
}

.error-icon {
  color: var(--danger);
  margin-bottom: 1rem;
}

.error-state h2 {
  color: var(--text-primary);
  margin-bottom: 0.5rem;
  font-size: 1.25rem;
}

.error-state p {
  margin-bottom: 1.5rem;
  color: var(--text-secondary);
}

.empty-icon {
  color: var(--accent);
  margin-bottom: 1rem;
  filter: drop-shadow(0 0 16px var(--accent-glow));
}

.empty-state h2 {
  color: var(--text-primary);
  margin-bottom: 0.75rem;
  font-size: 1.5rem;
}

.empty-state p {
  margin-bottom: 1.5rem;
  max-width: 320px;
  margin-left: auto;
  margin-right: auto;
  line-height: 1.6;
}

.empty-actions {
  display: flex;
  gap: 0.75rem;
  justify-content: center;
  flex-wrap: wrap;
}

.posts-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.load-more-wrapper {
  margin-top: 1rem;
}

.load-more-btn {
  padding: 1rem 1.5rem;
  font-weight: 600;
}

.sidebar-container {
  display: none;
  width: 320px;
  flex-shrink: 0;
}

@media (min-width: 1024px) {
  .sidebar-container {
    display: block;
  }
}

.suggestions-panel {
  position: sticky;
  top: 100px;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 1.5rem;
}

.suggestions-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.25rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid var(--border);
}

.suggestions-header h2 {
  font-size: 0.95rem;
  color: var(--text-secondary);
  font-weight: 600;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  margin: 0;
}

.loading-suggestions {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.skeleton-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
}

.skeleton-text {
  height: 12px;
  border-radius: 4px;
}

.no-suggestions {
  color: var(--text-secondary);
  font-size: 0.9rem;
  text-align: center;
  padding: 1.5rem 0;
}

.suggestions-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.suggestion-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
}

.suggestion-user {
  display: flex;
  align-items: center;
  text-decoration: none;
  color: inherit;
  gap: 0.75rem;
  flex: 1;
  min-width: 0;
}

.suggestion-user:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
  border-radius: 8px;
}

.suggestion-info {
  display: flex;
  flex-direction: column;
  min-width: 0;
  overflow: hidden;
}

.suggestion-username {
  font-weight: 600;
  font-size: 0.9rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.suggestion-name {
  color: var(--text-secondary);
  font-size: 0.8rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.follow-btn {
  flex-shrink: 0;
  white-space: nowrap;
}
</style>
