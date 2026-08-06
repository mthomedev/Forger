<template>
  <footer class="app-footer" role="contentinfo">
    <div class="footer-container">
      <div class="footer-brand">
        <span class="brand-name">Forger</span>
        <span class="brand-tagline">— Platform for Creators & Makers</span>
      </div>

      <div class="footer-status" aria-label="System status">
        <span class="status-dot"></span>
        <span class="status-text">Systems Operational</span>
      </div>

      <div class="footer-copyright">
        &copy; {{ currentYear }} Forger. All rights reserved.
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue'

const currentYear = computed(() => new Date().getFullYear())
</script>

<style scoped>
.app-footer {
  border-top: 1px solid var(--border);
  background-color: var(--bg-secondary);
  color: var(--text-muted);
  font-size: 0.85rem;
  padding: 1.5rem 1rem;
  margin-top: auto;
}

.footer-container {
  max-width: var(--max-content-width);
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  text-align: center;
}

.footer-brand {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.brand-name {
  font-weight: 700;
  color: var(--text-secondary);
}

.brand-tagline {
  color: var(--text-muted);
}

.footer-status {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.8rem;
  color: var(--text-secondary);
  background: var(--bg-card);
  padding: 0.25rem 0.75rem;
  border-radius: var(--radius-full);
  border: 1px solid var(--border-light);
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background-color: var(--success);
  box-shadow: 0 0 8px var(--success);
}

.footer-copyright {
  font-size: 0.8rem;
  color: var(--text-muted);
}

@media (min-width: 768px) {
  .footer-container {
    flex-direction: row;
    text-align: left;
    padding: 0 1rem;
  }
}
</style>
