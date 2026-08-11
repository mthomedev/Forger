<template>
  <div class="auth-page">
    <div class="auth-card">
      <div class="brand-container">
        <svg
          class="brand-icon"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2.5"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <path d="M16 4 L16 18" />
          <path d="M10 18 L16 26 L22 18" />
          <circle cx="16" cy="18" r="5" />
        </svg>
        <h1 class="brand-title">Forger</h1>
      </div>
      <p class="subtitle">Access your maker workshop</p>

      <form @submit.prevent="handleLogin" class="auth-form" novalidate>
        <div class="input-group">
          <span class="input-icon" aria-hidden="true">
            <svg
              viewBox="0 0 24 24"
              width="18"
              height="18"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path
                d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"
              />
              <polyline points="22,6 12,13 2,6" />
            </svg>
          </span>
          <input
            v-model="form.email"
            type="email"
            placeholder="Email address"
            required
            class="auth-input"
          />
        </div>
        <div class="input-group">
          <span class="input-icon" aria-hidden="true">
            <svg
              viewBox="0 0 24 24"
              width="18"
              height="18"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
              <path d="M7 11V7a5 5 0 0 1 10 0v4" />
            </svg>
          </span>
          <input
            v-model="form.password"
            type="password"
            placeholder="Password"
            required
            class="auth-input"
          />
        </div>

        <div v-if="errorMessage" class="error-banner" role="alert">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
            <path
              d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"
            />
          </svg>
          <span>{{ errorMessage }}</span>
        </div>

        <button type="submit" class="auth-btn" :disabled="authStore.loading">
          <span v-if="authStore.loading" class="spinner"></span>
          <span>{{ authStore.loading ? "Entering Workshop..." : "Log In" }}</span>
        </button>
      </form>
    </div>

    <div class="auth-card switch-card">
      <p>
        New to the forge? <router-link to="/register" class="link">Create an account</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";

const router = useRouter();
const authStore = useAuthStore();

const form = ref({ email: "", password: "" });
const errorMessage = ref("");

const handleLogin = async () => {
  try {
    errorMessage.value = "";
    await authStore.login(form.value);
    router.push("/");
  } catch (err) {
    errorMessage.value = err.message || err.errors?.email?.[0] || "Login failed";
  }
};
</script>

<style scoped>
.auth-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background-color: var(--bg-primary, #0a0a0a);
  padding: 1.5rem;
}

.auth-card {
  background: var(--bg-card, #1a1a1a);
  border: 1px solid var(--border, #2a2a2a);
  border-radius: 16px;
  width: 100%;
  max-width: 400px;
  padding: 2.5rem;
  margin-bottom: 1rem;
  text-align: center;
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.5);
}

.brand-container {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  margin-bottom: 0.5rem;
}

.brand-icon {
  width: 38px;
  height: 38px;
  color: var(--accent);
  filter: drop-shadow(0 0 12px var(--accent-glow));
}

.brand-title {
  font-size: 2.2rem;
  font-weight: 800;
  background: linear-gradient(135deg, var(--gold) 30%, var(--accent) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.subtitle {
  color: var(--text-secondary, #a8a8a8);
  font-size: 0.95rem;
  margin-bottom: 2rem;
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.input-group {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 1rem;
  color: var(--text-secondary, #a8a8a8);
  display: flex;
  align-items: center;
  pointer-events: none;
}

.auth-input {
  width: 100%;
  background: var(--bg-elevated, #242424);
  border: 1px solid var(--border, #2a2a2a);
  color: var(--text-primary, #fafafa);
  padding: 0.9rem 1rem 0.9rem 2.8rem;
  border-radius: 10px;
  font-size: 0.95rem;
  transition: all 0.2s ease;
}

.auth-input:focus {
  outline: none;
  border-color: var(--accent);
  box-shadow: 0 0 0 3px var(--accent-glow);
}

.auth-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  background: linear-gradient(135deg, var(--accent), #ff8534);
  color: white;
  border: none;
  padding: 0.95rem;
  border-radius: 10px;
  font-weight: 650;
  font-size: 1rem;
  cursor: pointer;
  transition:
    opacity 0.2s,
    transform 0.1s;
  box-shadow: 0 4px 20px var(--accent-glow);
}

.auth-btn:hover:not(:disabled) {
  opacity: 0.95;
  transform: translateY(-1px);
}

.auth-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.switch-card {
  padding: 1.5rem;
  color: var(--text-secondary, #a8a8a8);
  font-size: 0.95rem;
}

.link {
  color: var(--accent, #e1306c);
  text-decoration: none;
  font-weight: 650;
}

.link:hover {
  text-decoration: underline;
}
</style>
