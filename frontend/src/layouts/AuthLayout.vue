<template>
  <div class="auth-layout">
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <main id="main-content" class="auth-main" role="main">
      <div class="auth-container">
        <router-view />
      </div>
    </main>

    <AppFooter />
  </div>
</template>

<script setup>
import AppFooter from './components/AppFooter.vue'
</script>

<style scoped>
.auth-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background-color: var(--bg-primary);
  color: var(--text-primary);
}

.auth-main {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 1rem;
  width: 100%;
}

.auth-container {
  width: 100%;
  max-width: 420px;
  margin: 0 auto;
}

@media (min-width: 768px) {
  .auth-main {
    padding: 3rem 2rem;
  }
}
</style>
