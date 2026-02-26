import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('../pages/LoginFormPage.vue'),
    meta: { guest: true }
  },
  {
    path: '/2fa',
    name: '2fa',
    component: () => import('../pages/TwoFactorVerifyPage.vue'),
    meta: { guest: true }
  },
  {
    path: '/',
    component: () => import('../layouts/AdminLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: 'users',
        name: 'users',
        component: () => import('../pages/users/List.vue'),
        meta: { requiresAdmin: true }
      },
      {
        path: 'users/create',
        name: 'users.create',
        component: () => import('../pages/users/Form.vue'),
        meta: { requiresAdmin: true }
      },
      {
        path: 'users/:id/edit',
        name: 'users.edit',
        component: () => import('../pages/users/Form.vue'),
        meta: { requiresAdmin: true }
      },
      // Settings
      {
        path: 'settings',
        name: 'settings',
        component: () => import('../pages/Setting/Settings.vue'),
        meta: { requiresAdmin: true, title: 'الإعدادات' }
      },
      {
        path: 'settings/create',
        name: 'settings.create',
        component: () => import('../pages/Setting/SettingForm.vue'),
        meta: { requiresAdmin: true, title: 'إضافة إعداد' }
      },
      {
        path: 'settings/:id/edit',
        name: 'settings.edit',
        component: () => import('../pages/Setting/SettingForm.vue'),
        meta: { requiresAdmin: true, title: 'تعديل إعداد' }
      },
      // RBAC Routes
      {
        path: 'roles',
        name: 'roles',
        component: () => import('../pages/RBAC/Roles.vue'),
        meta: { requiresAdmin: true, title: 'إدارة الأدوار' }
      },
      {
        path: 'permissions',
        name: 'permissions',
        component: () => import('../pages/RBAC/Permissions.vue'),
        meta: { requiresAdmin: true, title: 'إدارة الصلاحيات' }
      },
    ]
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/'
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation guards
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  
  // Wait if store is still loading (optional, but good for refresh)
  const isAuthenticated = authStore.isAuthenticated
  const isAdmin = authStore.isAdmin

  // Check verification
  if (authStore.requiresVerification && to.name !== '2fa') {
    next({ name: '2fa' })
  }
  // Prevent accessing 2fa page if not requiring verification
  else if (to.name === '2fa' && !authStore.requiresVerification) {
    if (isAuthenticated) {
      next({ name: 'users' })
    } else {
      next({ name: 'login' })
    }
  }
  // Check authentication
  else if (to.meta.requiresAuth && !isAuthenticated) {
    next({ name: 'login' })
  } 
  // Check guest access
  else if (to.meta.guest && isAuthenticated) {
    next({ name: 'users' })
  }
  // Check admin privileges
  else if (to.meta.requiresAdmin && !isAdmin) {
    next({ name: 'roles' })
  }
  else {
    next()
  }
})

export default router
