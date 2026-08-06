<template>
  <nav class="navbar" role="navigation" aria-label="Main navigation">
    <div class="nav-container">
      <router-link to="/" class="nav-brand" aria-label="Forger - Home">
        <svg class="brand-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M16 4 L16 18" />
          <path d="M10 18 L16 26 L22 18" />
          <circle cx="16" cy="18" r="5" />
        </svg>
        <span class="brand-text">Forger</span>
      </router-link>

      <div class="nav-links" role="menubar">
        <router-link
          to="/"
          class="nav-item"
          role="menuitem"
          :aria-current="isActive('/') ? 'page' : undefined"
          title="Home Feed"
        >
          <svg viewBox="0 0 24 24" fill="currentColor" width="22" height="22" aria-hidden="true">
            <path d="M12 2L2 12h3v8h5v-6h4v6h5v-8h3L12 2z"/>
          </svg>
          <span class="nav-label">Feed</span>
        </router-link>

        <router-link
          to="/search"
          class="nav-item"
          role="menuitem"
          :aria-current="isActive('/search') ? 'page' : undefined"
          title="Search Creators"
        >
          <svg viewBox="0 0 24 24" fill="currentColor" width="22" height="22" aria-hidden="true">
            <path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0016 9.5 6.5 6.5 0 109.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
          </svg>
          <span class="nav-label">Explore</span>
        </router-link>

        <button
          class="nav-item btn-create"
          @click="uiStore.openCreatePost()"
          role="menuitem"
          title="Forge New Creation"
          aria-label="Create new post"
        >
          <svg viewBox="0 0 24 24" fill="currentColor" width="22" height="22" aria-hidden="true">
            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
          </svg>
          <span class="nav-label">Forge</span>
        </button>

        <router-link
          to="/profile"
          class="nav-item"
          role="menuitem"
          :aria-current="isActive('/profile') ? 'page' : undefined"
          title="My Workshop"
        >
          <svg viewBox="0 0 24 24" fill="currentColor" width="22" height="22" aria-hidden="true">
            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
          </svg>
          <span class="nav-label">Workshop</span>
        </router-link>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { useRoute } from 'vue-router'
import { useUiStore } from '../../stores/ui'

const route = useRoute()
const uiStore = useUiStore()

const isActive = (path) => {
  if (path === '/') return route.path === '/'
  return route.path.startsWith(path)
}
</script>

<style scoped>
.navbar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background-color: var(--bg-secondary);
  border-top: 1px solid var(--border);
  backdrop-filter: blur(12px);
  z-index: 50;
  box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.5);
}

.nav-container {
  display: flex;
  justify-content: space-around;
  align-items: center;
  height: 64px;
  max-width: 1100px;
  margin: 0 auto;
  padding: 0 1rem;
}

.nav-brand {
  display: none;
  align-items: center;
  gap: 0.5rem;
  font-weight: 800;
  font-size: 1.5rem;
  color: var(--text-primary);
  letter-spacing: 0.5px;
  text-decoration: none;
}

.brand-icon {
  width: 28px;
  height: 28px;
  color: var(--accent);
  filter: drop-shadow(0 0 8px var(--accent-glow));
  flex-shrink: 0;
}

.brand-text {
  background: linear-gradient(135deg, #f3f3f5 30%, var(--accent) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.nav-links {
  display: flex;
  justify-content: space-around;
  width: 100%;
}

.nav-item {
  color: var(--text-secondary);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  padding: 0.4rem 0.8rem;
  border-radius: 8px;
  transition: all 0.2s ease;
  background: none;
  border: none;
  cursor: pointer;
  gap: 3px;
  min-width: 44px;
  min-height: 44px;
}

.nav-item:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

.nav-label {
  font-size: 0.7rem;
  font-weight: 500;
}

.nav-item:hover,
.nav-item[aria-current="page"] {
  color: var(--accent);
  background: var(--bg-elevated);
}

.nav-item[aria-current="page"] {
  box-shadow: inset 0 -2px 0 var(--accent);
}

.btn-create {
  color: var(--text-primary);
  background: linear-gradient(135deg, var(--accent), #ff8534);
  border-radius: 50%;
  width: 42px;
  height: 42px;
  padding: 0;
  box-shadow: 0 4px 15px var(--accent-glow);
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-create:hover {
  transform: scale(1.05);
  opacity: 0.95;
}

.btn-create:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

.btn-create .nav-label {
  display: none;
}

@media (min-width: 768px) {
  .navbar {
    top: 0;
    bottom: auto;
    border-top: none;
    border-bottom: 1px solid var(--border);
  }

  .nav-container {
    justify-content: space-between;
    padding: 0 2rem;
  }

  .nav-brand {
    display: flex;
  }

  .nav-links {
    width: auto;
    gap: 0.5rem;
  }

  .nav-item {
    flex-direction: row;
    gap: 0.5rem;
    padding: 0.6rem 1rem;
    min-width: auto;
  }

  .nav-label {
    font-size: 0.9rem;
    font-weight: 600;
  }

  .nav-item[aria-current="page"] {
    box-shadow: none;
    background: var(--bg-elevated);
  }

  .btn-create {
    border-radius: 8px;
    width: auto;
    height: auto;
    padding: 0.6rem 1.2rem;
  }

  .btn-create .nav-label {
    display: inline;
  }

  .btn-create:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px var(--accent-glow);
  }
}

@media (max-width: 480px) {
  .nav-container {
    padding: 0 0.5rem;
  }

  .nav-item {
    padding: 0.35rem 0.5rem;
  }

  .nav-label {
    font-size: 0.65rem;
  }
}
</style>