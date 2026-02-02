import { defineStore } from 'pinia';
import { api } from 'boot/axios';
import type { Client, Demand } from 'src/components/models';

export interface ClientWithDemands extends Client {
  demands?: Demand[];
}

export const useKanbanStore = defineStore('kanban', {
  state: () => ({
    clients: [] as ClientWithDemands[],
    loaded: false,
  }),
  actions: {
    async fetchClients(force = false) {
      // If we already have data and aren't forcing a refresh, do nothing (Cache)
      if (this.loaded && !force) return;

      try {
        const response = await api.get('/clients');
        this.clients = response.data;
        this.loaded = true;
      } catch (error) {
        console.error('Error fetching clients:', error);
        throw error;
      }
    },
    
    getClientById(id: number | string) {
      // Convert id to number for comparison
      const numericId = Number(id);
      return this.clients.find(c => c.id === numericId);
    }
  }
});
