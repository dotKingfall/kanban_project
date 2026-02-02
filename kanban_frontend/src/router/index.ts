import { defineRouter } from '#q-app/wrappers';
import {
  createMemoryHistory,
  createRouter,
  createWebHashHistory,
  createWebHistory,
} from 'vue-router';
import { useAuthStore } from 'stores/auth';
import routes from './routes';

/*
 * If not building with SSR mode, you can
 * directly export the Router instantiation;
 *
 * The function below can be async too; either use
 * async/await or return a Promise which resolves
 * with the Router instance.
 */

export default defineRouter(function ({ store }) {
  const createHistory = process.env.SERVER
    ? createMemoryHistory
    : process.env.VUE_ROUTER_MODE === 'history'
      ? createWebHistory
      : createWebHashHistory;

  const Router = createRouter({
    scrollBehavior: () => ({ left: 0, top: 0 }),
    routes,

    // Leave this as is and make changes in quasar.conf.js instead!
    // quasar.conf.js -> build -> vueRouterMode
    // quasar.conf.js -> build -> publicPath
    history: createHistory(process.env.VUE_ROUTER_BASE),
  });

  Router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore(store);
    const isLoggedIn = !!localStorage.getItem('is_logged_in');

    if (to.meta.requiresAuth && !authStore.user) {
      if (!isLoggedIn) {
        next('/'); // Redirect to Login
        return;
      }

      try {
        await authStore.fetchUser();
        next();
      // eslint-disable-next-line @typescript-eslint/no-unused-vars
      } catch (error) {
        localStorage.removeItem('is_logged_in');
        next('/'); // Redirect to Login
      }
      return;
    }

    if (to.meta.guestOnly) {
      if (isLoggedIn) {
        try {
          // If user visits Login page but has a session, try to restore it and go to Dashboard
          await authStore.fetchUser();
          next('/dashboard');
          return;
          // eslint-disable-next-line @typescript-eslint/no-unused-vars
        } catch (error) {
          localStorage.removeItem('is_logged_in');
        }
      }
    }

    next();
  });

  return Router;
});
