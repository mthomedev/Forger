<template>
  <div class="main-layout">
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <NavBar />

    <main id="main-content" class="main-content" role="main">
      <router-view />
    </main>

    <AppFooter />

    <CreatePostModal v-model:show="uiStore.createPostOpen" @created="uiStore.setCreatedPost" />
  </div>
</template>

<script setup>
import NavBar from './components/NavBar.vue'
import AppFooter from './components/AppFooter.vue'
import CreatePostModal from '../components/CreatePostModal.vue'
import { useUiStore } from '../stores/ui'

const uiStore = useUiStore()
</script>

<style scoped>
.main-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background-color: var(--bg-primary);
  color: var(--text-primary);
}

@media (max-width: 767px) {
  .main-layout {
    padding-bottom: 64px;
  }
}

.main-content {
  flex: 1;
  width: 100%;
  max-width: var(--max-content-width);
  margin: 0 auto;
  padding: 1rem 1rem 2rem;
  box-sizing: border-box;
}

@media (min-width: 768px) {
  .main-content {
    padding-top: 80px;
    padding-left: 2rem;
    padding-right: 2rem;
  }
}

@media (min-width: 1024px) {
  .main-content {
    padding-left: 3rem;
    padding-right: 3rem;
  }
}
</style>
