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
    async login(payload: any) {
      // The CSRF cookie handshake is handled in the component for now
      // We just perform the login request here
      await api.post('/login', payload);
      await this.fetchUser();
    },
    async logout() {
      await api.post('/logout');
      this.user = null;
    }
  }
});
