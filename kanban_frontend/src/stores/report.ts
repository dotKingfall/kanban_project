import { defineStore } from 'pinia';
import { ref, watch, computed } from 'vue';
import { api } from 'boot/axios';
import { useKanbanStore } from './kanban';

export const useReportStore = defineStore('report', () => {
  const kanbanStore = useKanbanStore();

  const selectedYear = ref(Number(localStorage.getItem('report-year')) || new Date().getFullYear());
  const selectedMonth = ref(Number(localStorage.getItem('report-month')) || new Date().getMonth() + 1);
  
  const reportCache = ref<any>(null); 
  const loading = ref(false);

  const formattedMonth = computed(() => {
    return `${selectedYear.value}-${String(selectedMonth.value).padStart(2, '0')}`;
  });

  const fetchAllReports = async () => {
    // Wait for kanbanStore if it hasn't fetched clients yet
    if (kanbanStore.clients.length === 0) {
      await kanbanStore.fetchClients();
    }

    const ids = kanbanStore.clients.map(c => c.id);
    if (ids.length === 0) return;

    loading.value = true;
    try {
      const response = await api.get('/reports/clients', {
        params: { 
          ids, 
          month: formattedMonth.value 
        }
      });
      reportCache.value = response.data;
    } catch (error) {
      console.error('Failed to fetch global reports:', error);
    } finally {
      loading.value = false;
    }
  };

  // Watch for date changes to refresh the cache automatically
  watch([selectedYear, selectedMonth], async () => {
    localStorage.setItem('report-year', String(selectedYear.value));
    localStorage.setItem('report-month', String(selectedMonth.value));
    await fetchAllReports();
  });

  return { 
    selectedYear, 
    selectedMonth, 
    formattedMonth, 
    reportCache, 
    loading, 
    fetchAllReports 
  };
});