import type { RouteRecordRaw } from 'vue-router';

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', component: () => import('pages/LoginPage.vue'), meta: { guestOnly: true } },
      { path: 'dashboard', component: () => import('pages/DashboardPage.vue'), meta: { requiresAuth: true } },
      { path: 'kanban/:clientId', component: () => import('pages/KanbanPage.vue'), meta: { requiresAuth: true } },
      { path: 'all-demands', component: () => import('pages/AllDemandsPage.vue'), meta: { requiresAuth: true } },
      { path: 'reports', component: () => import('pages/ReportsPage.vue'), meta: { requiresAuth: true } },
      { path: 'full-reports', component: () => import('pages/FullReport.vue'), meta: { requiresAuth: true } },
    ],
  },

  // Always leave this as last one,
  // but you can also remove it
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue'),
  },
];

export default routes;
