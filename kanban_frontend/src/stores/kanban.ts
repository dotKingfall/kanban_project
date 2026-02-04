import { defineStore } from 'pinia';
import { api } from 'boot/axios';
import type { Client, Demand, KanbanColumn, Priority, Department } from 'src/components/models';

export interface ClientWithDemands extends Client {
  demands?: Demand[];
  kanban_columns?: KanbanColumn[];
}

export const useKanbanStore = defineStore('kanban', {
  state: () => ({
    clients: [] as ClientWithDemands[],
    priorities: [] as Priority[],
    departments: [] as Department[],
    loaded: false,
    lookupsLoaded: false,
  }),
  getters: {
    /**
     * Returns a flattened, sorted list of all demands that are not 'Concluído'.
     * This is cached by Pinia and will only re-compute if the client data changes.
     */
    activeDemands(state): Demand[] {
      const allDemands: Demand[] = [];
      state.clients.forEach(client => {
        if (client.demands) {
          client.demands.forEach(demand => {
            if (demand.status !== 'Concluído') {
              allDemands.push(demand);
            }
          });
        }
      });
      // SORT BY PRIORITY (LOWEST NUMBER FIRST)
      return allDemands.sort((a, b) => (a.priority_table_id || 999) - (b.priority_table_id || 999));
    }
  },
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
    
    async fetchLookups(force = false) {
      if (this.lookupsLoaded && !force) return;

      try {
        const [prioritiesRes, departmentsRes] = await Promise.all([
          api.get<Priority[]>('/priorities'),
          api.get<Department[]>('/departments')
        ]);
        this.priorities = prioritiesRes.data;
        this.departments = departmentsRes.data;
        this.lookupsLoaded = true;
      } catch (error) {
        console.error('Error fetching lookups:', error);
        throw error;
      }
    },

    getClientById(id: number | string) {
      const numericId = Number(id);
      return this.clients.find(c => c.id === numericId);
    }
  }
});
