<template>
  <div class="layout">
    <main class="main-content">
      <header class="page-header">
        <h1 class="page-title">Notifications</h1>
        <p class="page-subtitle">Recent activity on your projects and profile.</p>
      </header>

      <div class="results-container">
        <div v-if="loading" class="results-list" role="status" aria-label="Loading notifications">
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
            <svg viewBox="0 0 24 24" width="40" height="40" fill="currentColor">
              <path
                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"
              />
            </svg>
          </div>
          <h3>Unable to load notifications</h3>
          <p>{{ errorMessage }}</p>
          <button class="retry-btn" @click="loadNotifications">Try Again</button>
        </div>

        <div v-else-if="notifications.length === 0" class="empty-state">
          <div class="empty-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="40" height="40" fill="currentColor">
              <path
                d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"
              />
            </svg>
          </div>
          <h3>No notifications yet</h3>
          <p>When someone likes, comments on your projects or follows you, you'll see it here.</p>
        </div>

        <div v-else class="results-list">
          <router-link
            v-for="notification in notifications"
            :key="notification.id"
            :to="
              notification.post
                ? `/posts/${notification.post.id}`
                : `/u/${notification.user.username}`
            "
            class="notification-item"
          >
            <BaseAvatar
              :src="notification.user.avatar_url"
              :username="notification.user.username"
              size="sm"
              glow
            />
            <div class="notification-details">
              <p class="notification-text">
                <strong class="actor-name">@{{ notification.user.username }}</strong>
                {{ messageFor(notification) }}
              </p>
              <span class="notification-time">{{ timeAgo(notification.created_at) }}</span>
            </div>
            <img
              v-if="notification.post"
              :src="notification.post.image_url"
              :alt="notification.post.caption || 'Post thumbnail'"
              class="post-thumb"
              loading="lazy"
            />
          </router-link>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { notificationService } from "../services/notificationService";
import { timeAgo } from "../utils/time";
import BaseAvatar from "../components/common/BaseAvatar.vue";

const notifications = ref([]);
const loading = ref(false);
const errorMessage = ref("");

const messageFor = (notification) => {
  if (notification.type === "like") return "liked your project";
  if (notification.type === "comment") return `commented: "${notification.comment_body}"`;
  if (notification.type === "follow") return "started following you";
  return "interacted with you";
};

const loadNotifications = async () => {
  loading.value = true;
  errorMessage.value = "";

  try {
    const res = await notificationService.getNotifications();
    notifications.value = res?.data || [];
  } catch (err) {
    errorMessage.value = err?.message || "Failed to load notifications. Please try again.";
    console.error("Failed to load notifications", err);
  } finally {
    loading.value = false;
  }
};

onMounted(loadNotifications);
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
}

.page-header {
  padding: 1.5rem 1rem 1rem;
}

.page-title {
  font-size: var(--text-2xl);
  font-weight: var(--font-extrabold);
  margin: 0 0 0.25rem;
  background: linear-gradient(135deg, var(--gold) 30%, var(--accent) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.page-subtitle {
  color: var(--text-secondary);
  font-size: var(--text-sm);
  margin: 0;
}

.results-container {
  padding: 0 1rem 2rem;
}

.results-list {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  overflow: hidden;
}

.notification-item {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  padding: 0.85rem 1rem;
  color: var(--text-primary);
  text-decoration: none;
  border-bottom: 1px solid var(--border);
  transition: background-color var(--transition-fast);
}

.notification-item:last-child {
  border-bottom: none;
}

.notification-item:hover {
  background: var(--bg-elevated);
}

.notification-details {
  flex: 1;
  min-width: 0;
}

.notification-text {
  margin: 0;
  font-size: var(--text-sm);
  line-height: var(--leading-normal);
  color: var(--text-secondary);
  word-break: break-word;
}

.actor-name {
  color: var(--text-primary);
  font-weight: var(--font-semibold);
}

.notification-time {
  display: block;
  margin-top: 0.2rem;
  font-size: var(--text-xs);
  color: var(--text-tertiary);
}

.post-thumb {
  width: 44px;
  height: 44px;
  border-radius: var(--radius-sm);
  object-fit: cover;
  flex-shrink: 0;
  border: 1px solid var(--border);
}

.skeleton-row {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  padding: 1rem;
  border-bottom: 1px solid var(--border);
}

.skeleton-row:last-child {
  border-bottom: none;
}

.skeleton-lines {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.empty-state {
  text-align: center;
  padding: 3rem 1.5rem;
  color: var(--text-secondary);
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
}

.empty-icon {
  width: 72px;
  height: 72px;
  margin: 0 auto 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: var(--accent-subtle);
  color: var(--accent);
  border: 1px solid var(--accent-glow);
}

.empty-state h3 {
  color: var(--text-primary);
  margin: 0 0 0.5rem;
  font-size: var(--text-lg);
}

.empty-state p {
  margin: 0 0 1.25rem;
  font-size: var(--text-sm);
}

.retry-btn {
  background: linear-gradient(135deg, var(--accent), var(--accent-hover));
  color: var(--text-on-accent);
  border: none;
  border-radius: var(--radius-md);
  padding: 0.6rem 1.4rem;
  font-weight: var(--font-semibold);
  font-size: var(--text-sm);
  cursor: pointer;
  transition: all var(--transition-base);
  box-shadow: 0 4px 15px var(--accent-glow);
}

.retry-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 20px var(--accent-glow-strong);
}

@media (max-width: 767px) {
  .page-header {
    padding-top: 1rem;
  }
}
</style>
