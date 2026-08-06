import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      component: () => import('../layouts/MainLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        { path: '', name: 'home', meta: { title: 'Feed' }, component: () => import('../views/HomeView.vue') },
        { path: 'profile', name: 'profile', meta: { title: 'My Workshop' }, component: () => import('../views/ProfileView.vue') },
        { path: 'posts/:id', name: 'post', meta: { title: 'Post' }, component: () => import('../views/PostView.vue') },
        { path: 'search', name: 'search', meta: { title: 'Search' }, component: () => import('../views/SearchView.vue') },
        { path: 'u/:username', name: 'user-profile', meta: { title: 'Profile' }, component: () => import('../views/UserProfileView.vue') },
      ],
    },
    {
      path: '/',
      component: () => import('../layouts/AuthLayout.vue'),
      meta: { guestOnly: true },
      children: [
        { path: 'login', name: 'login', meta: { title: 'Log In' }, component: () => import('../views/LoginView.vue') },
        { path: 'register', name: 'register', meta: { title: 'Sign Up' }, component: () => import('../views/RegisterView.vue') },
      ],
    },
    { path: '/:pathMatch(.*)*', redirect: '/' },
  ],
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else {
      return { top: 0 }
    }
  },
})

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()

  if (authStore.token && !authStore.user) {
    try {
      await authStore.fetchUser()
    } catch {
      // Token invalid or expired
    }
  }

  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
  const guestOnly = to.matched.some(record => record.meta.guestOnly)

  if (requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else if (guestOnly && authStore.isAuthenticated) {
    next('/')
  } else {
    next()
  }
})

router.afterEach((to) => {
  const title = to.meta.title || 'Forger'
  document.title = `${title} | Forger`
})

export default router