<template>
  <router-view v-slot="{ Component }">
    <transition name="page" mode="out-in">
      <component :is="Component" />
    </transition>
  </router-view>
</template>

<style>
/* ==========================================================================
   Forge & Craft Premium Theme - Complete Design System
   ========================================================================== */

:root {
  /* ===== Color Primitives ===== */
  --color-black: #000000;
  --color-white: #ffffff;

  /* ===== Background Layers ===== */
  --bg-primary: #0d0d0f;
  --bg-secondary: #161619;
  --bg-tertiary: #1a1a1f;
  --bg-card: #1e1e23;
  --bg-elevated: #282830;
  --bg-overlay: rgba(13, 13, 15, 0.85);
  --bg-modal: #1e1e23;

  /* ===== Accent Palette ===== */
  --accent: #ff6b1a;
  --accent-hover: #ff8534;
  --accent-active: #e85d15;
  --accent-glow: rgba(255, 107, 26, 0.25);
  --accent-glow-strong: rgba(255, 107, 26, 0.4);
  --accent-subtle: rgba(255, 107, 26, 0.1);

  /* ===== Gold Accent ===== */
  --gold: #f5a623;
  --gold-hover: #ffb83a;
  --gold-glow: rgba(245, 166, 35, 0.3);

  /* ===== Text Hierarchy ===== */
  --text-primary: #f3f3f5;
  --text-secondary: #9a9aab;
  --text-tertiary: #6b6b7a;
  --text-muted: #4a4a5a;
  --text-inverse: #0d0d0f;
  --text-on-accent: #ffffff;

  /* ===== Border System ===== */
  --border: #2c2c35;
  --border-light: #3e3e4a;
  --border-focus: var(--accent);
  --border-error: var(--danger);

  /* ===== Semantic Feedback Colors ===== */
  --success: #22c55e;
  --success-glow: rgba(34, 197, 94, 0.3);
  --warning: #f5a623;
  --warning-glow: rgba(245, 166, 35, 0.3);
  --danger: #ef4444;
  --danger-glow: rgba(239, 68, 68, 0.3);
  --info: #3b82f6;
  --info-glow: rgba(59, 130, 246, 0.3);

  /* ===== Typography ===== */
  --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  --font-mono: 'JetBrains Mono', 'Fira Code', monospace;

  --text-xs: 0.7rem;
  --text-sm: 0.85rem;
  --text-base: 1rem;
  --text-lg: 1.125rem;
  --text-xl: 1.25rem;
  --text-2xl: 1.5rem;
  --text-3xl: 2rem;
  --text-4xl: 2.5rem;

  --font-light: 300;
  --font-normal: 400;
  --font-medium: 500;
  --font-semibold: 600;
  --font-bold: 700;
  --font-extrabold: 800;

  --leading-tight: 1.1;
  --leading-normal: 1.5;
  --leading-relaxed: 1.75;

  /* ===== Spacing Scale ===== */
  --space-0: 0;
  --space-1: 0.25rem;
  --space-2: 0.5rem;
  --space-3: 0.75rem;
  --space-4: 1rem;
  --space-5: 1.25rem;
  --space-6: 1.5rem;
  --space-8: 2rem;
  --space-10: 2.5rem;
  --space-12: 3rem;
  --space-16: 4rem;
  --space-20: 5rem;

  /* ===== Border Radius ===== */
  --radius-none: 0;
  --radius-sm: 4px;
  --radius-md: 8px;
  --radius-lg: 12px;
  --radius-xl: 16px;
  --radius-2xl: 24px;
  --radius-full: 9999px;

  /* ===== Shadows ===== */
  --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.3);
  --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.4);
  --shadow-lg: 0 8px 30px rgba(0, 0, 0, 0.5);
  --shadow-xl: 0 16px 48px rgba(0, 0, 0, 0.6);
  --shadow-glow: 0 0 20px var(--accent-glow);
  --shadow-glow-strong: 0 0 40px var(--accent-glow-strong);
  --shadow-inner: inset 0 2px 4px rgba(0, 0, 0, 0.3);

  /* ===== Z-Index Scale ===== */
  --z-dropdown: 100;
  --z-sticky: 200;
  --z-fixed: 300;
  --z-modal-backdrop: 400;
  --z-modal: 500;
  --z-popover: 600;
  --z-tooltip: 700;
  --z-toast: 800;

  /* ===== Transitions ===== */
  --transition-fast: 100ms ease;
  --transition-base: 200ms ease;
  --transition-slow: 300ms ease;
  --transition-slower: 500ms ease;

  /* ===== Layout ===== */
  --header-height: 64px;
  --header-height-mobile: 64px;
  --max-content-width: 1200px;
  --sidebar-width: 320px;
}

/* ==========================================================================
   CSS Reset & Base Styles
   ========================================================================== */

*, *::before, *::after {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

html {
  font-size: 16px;
  scroll-behavior: smooth;
  -webkit-text-size-adjust: 100%;
}

@media (prefers-reduced-motion: reduce) {
  html {
    scroll-behavior: auto;
  }

  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}

body {
  font-family: var(--font-sans);
  background-color: var(--bg-primary);
  color: var(--text-primary);
  line-height: var(--leading-normal);
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-rendering: optimizeLegibility;
  min-height: 100vh;
}

#app {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* ==========================================================================
   Typography Base
   ========================================================================== */

h1, h2, h3, h4, h5, h6 {
  font-weight: var(--font-bold);
  line-height: var(--leading-tight);
  color: var(--text-primary);
}

h1 { font-size: var(--text-4xl); }
h2 { font-size: var(--text-3xl); }
h3 { font-size: var(--text-2xl); }
h4 { font-size: var(--text-xl); }
h5 { font-size: var(--text-lg); }
h6 { font-size: var(--text-base); }

p {
  color: var(--text-secondary);
  line-height: var(--leading-relaxed);
}

a {
  color: var(--accent);
  text-decoration: none;
  transition: color var(--transition-fast);
}

a:hover {
  color: var(--accent-hover);
}

a:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
  border-radius: var(--radius-sm);
}

code, pre, kbd, samp {
  font-family: var(--font-mono);
  font-size: 0.9em;
}

/* ==========================================================================
   Form Elements Base
   ========================================================================== */

button {
  font-family: inherit;
  font-size: inherit;
  line-height: inherit;
}

input, textarea, select {
  font-family: inherit;
  font-size: inherit;
  line-height: inherit;
  color: inherit;
}

input:focus-visible,
textarea:focus-visible,
select:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

/* ==========================================================================
   Selection Styles
   ========================================================================== */

::selection {
  background: var(--accent-subtle);
  color: var(--text-primary);
}

/* ==========================================================================
   Scrollbar Styles
   ========================================================================== */

::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: var(--bg-primary);
}

::-webkit-scrollbar-thumb {
  background: var(--border-light);
  border-radius: var(--radius-full);
  border: 2px solid var(--bg-primary);
}

::-webkit-scrollbar-thumb:hover {
  background: var(--accent);
}

::-webkit-scrollbar-corner {
  background: var(--bg-primary);
}

/* Firefox */
* {
  scrollbar-width: thin;
  scrollbar-color: var(--border-light) var(--bg-primary);
}

/* ==========================================================================
   Accessibility Utilities
   ========================================================================== */

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

.skip-link {
  position: absolute;
  top: -100%;
  left: 50%;
  transform: translateX(-50%);
  background: var(--accent);
  color: var(--text-inverse);
  padding: var(--space-3) var(--space-6);
  border-radius: var(--radius-md);
  z-index: var(--z-tooltip);
  font-weight: var(--font-semibold);
  text-decoration: none;
  transition: top var(--transition-fast);
}

.skip-link:focus {
  top: var(--space-4);
  outline: 2px solid var(--gold);
  outline-offset: 2px;
}

/* ==========================================================================
   Focus Styles
   ========================================================================== */

:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

:focus:not(:focus-visible) {
  outline: none;
}

/* ==========================================================================
   Image & Media
   ========================================================================== */

img, video, canvas, svg {
  display: block;
  max-width: 100%;
  height: auto;
}

img {
  image-rendering: auto;
}

/* ==========================================================================
   Page Transition Animations
   ========================================================================== */

.page-enter-active,
.page-leave-active {
  transition: opacity var(--transition-base), transform var(--transition-base);
}

.page-enter-from,
.page-leave-to {
  opacity: 0;
  transform: translateY(8px);
}

/* ==========================================================================
   Utility Classes
   ========================================================================== */

.container {
  width: 100%;
  max-width: var(--max-content-width);
  margin: 0 auto;
  padding: 0 var(--space-4);
}

@media (min-width: 768px) {
  .container {
    padding: 0 var(--space-8);
  }
}

.visually-hidden {
  position: absolute !important;
  width: 1px !important;
  height: 1px !important;
  padding: 0 !important;
  margin: -1px !important;
  overflow: hidden !important;
  clip: rect(0, 0, 0, 0) !important;
  white-space: nowrap !important;
  border: 0 !important;
}

.text-gradient {
  background: linear-gradient(135deg, var(--text-primary) 30%, var(--accent) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.glass {
  background: var(--bg-card);
  backdrop-filter: blur(12px);
  border: 1px solid var(--border);
}

.glow-accent {
  box-shadow: var(--shadow-glow);
}

.glow-gold {
  box-shadow: 0 0 20px var(--gold-glow);
}

/* ==========================================================================
   Skeleton Loading (shared by views)
   ========================================================================== */

.skeleton {
  background: linear-gradient(90deg, var(--bg-elevated) 25%, var(--border) 50%, var(--bg-elevated) 75%);
  background-size: 200% 100%;
  animation: skeleton-shimmer 1.5s infinite;
  border-radius: var(--radius-md);
}

@keyframes skeleton-shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* ==========================================================================
   Spinner (shared by views)
   ========================================================================== */

.spinner {
  display: inline-block;
  width: 18px;
  height: 18px;
  flex-shrink: 0;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #ffffff;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

.spinner-sm {
  width: 14px;
  height: 14px;
}

.spinner-lg {
  width: 40px;
  height: 40px;
  border-width: 3px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* ==========================================================================
   Feedback Messages (shared by views)
   ========================================================================== */

.error-banner {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: var(--danger);
  padding: 0.75rem 1rem;
  border-radius: var(--radius-md);
  font-size: 0.9rem;
  text-align: left;
}

.success-banner {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(34, 197, 94, 0.1);
  border: 1px solid rgba(34, 197, 94, 0.3);
  color: var(--success);
  padding: 0.75rem 1rem;
  border-radius: var(--radius-md);
  font-size: 0.9rem;
}
</style>