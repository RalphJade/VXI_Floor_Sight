import { createRouter, createWebHistory } from 'vue-router';

// Lazy‑load components
const Dashboard = () => import('@/components/Dashboard.vue');
const Login = () => import('@/components/Login.vue');
const Profile = () => import('@/components/Profile.vue');
const NotFound = { template: '<div class="flex items-center justify-center h-screen"><h1 class="text-2xl font-bold">Page not found</h1></div>' };

const routes = [
  {
    path: '/',
    name: 'dashboard',
    component: Dashboard,
    meta: { requiresAuth: true },
  },
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: { guestOnly: true },
  },
  {
    path: '/profile',
    name: 'profile',
    component: Profile,
    meta: { requiresAuth: true },
  },
  {
    path: '/:catchAll(.*)',
    name: 'notfound',
    component: NotFound,
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Global navigation guard for auth (session-based)
// We read a data-auth="1" attribute injected by Laravel's Blade into #app
// because the laravel_session cookie is HttpOnly and not readable by JS.
router.beforeEach((to, from, next) => {
  const appEl = document.getElementById('app');
  const isAuthenticated = appEl?.dataset?.auth === '1';
  if (to.meta.requiresAuth && !isAuthenticated) {
    return next({ name: 'login' });
  }
  if (to.meta.guestOnly && isAuthenticated) {
    return next({ name: 'dashboard' });
  }
  return next();
});

export default router;
