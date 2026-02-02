import { defineStore } from 'pinia';
import { api } from 'boot/axios';
import type { User } from 'src/components/models';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null as User | null,
  }),
  actions: {
    async fetchUser() {
      try {
        const response = await api.get('/user');
        this.user = response.data;
        return this.user;
      } catch (error) {
        this.user = null;
        throw error;
      }
    },
    async login(payload: { email: string; password: string }) {
      try {
        const response = await api.post('/login', payload);

        // 2. If successful, update state and fetch user data
        if (response.status === 200 || response.status === 204) {
          localStorage.setItem('is_logged_in', 'true');
          return await this.fetchUser();
        }
      } catch (error) {
        this.user = null;
        localStorage.removeItem('is_logged_in');
        throw error; 
      }
    },
    async logout() {
      try {
        await api.post('/logout');
      } finally {

        this.user = null;
        localStorage.removeItem('is_logged_in');
      }
    }
  }
});
