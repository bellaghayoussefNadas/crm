import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

import Layout from '../views/Layout.vue'
import Login from '../views/Login.vue'
import Dashboard from '../views/Dashboard.vue'
import Settings from '../views/Settings.vue'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { guest: true }
  },
  {
    path: '/',
    component: Layout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Dashboard',
        component: Dashboard
      },
      {
        path: 'accounts',
        name: 'Accounts',
        component: () => import('../views/Accounts.vue')
      },
      {
        path: 'accounts/:id',
        name: 'AccountProfile',
        component: () => import('../views/AccountProfile.vue')
      },
      {
        path: 'deals',
        name: 'Deals',
        component: () => import('../views/Deals.vue')
      },
      {
        path: 'contacts',
        name: 'Contacts',
        component: () => import('../views/Contacts.vue')
      },
      {
        path: 'leads',
        name: 'Leads',
        component: () => import('../views/Leads.vue')
      },
      {
        path: 'tasks',
        name: 'Tasks',
        component: () => import('../views/Tasks.vue')
      },
      {
        path: 'activities',
        name: 'Activities',
        component: () => import('../views/Activities.vue')
      },
      {
        path: 'quotes',
        name: 'Quotes',
        component: () => import('../views/Quotes.vue')
      },
      {
        path: 'invoices',
        name: 'Invoices',
        component: () => import('../views/Invoices.vue')
      },
      {
        path: 'tickets',
        name: 'Tickets',
        component: () => import('../views/Tickets.vue')
      },
      {
        path: 'documents',
        name: 'Documents',
        component: () => import('../views/Documents.vue')
      },
      {
        path: 'campaigns',
        name: 'Campaigns',
        component: () => import('../views/Campaigns.vue')
      },
      {
        path: 'settings',
        name: 'Settings',
        component: () => import('../views/Settings.vue')
      },
      {
        path: 'users',
        name: 'Users',
        component: () => import('../views/Users.vue')
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else if (to.meta.guest && authStore.isAuthenticated) {
    next('/')
  } else {
    // If authenticated but user info is missing, fetch it
    if (authStore.isAuthenticated && !authStore.user) {
      await authStore.fetchUser()
    }
    next()
  }
})

export default router
