import { defineStore } from 'pinia';
import { ref, watch, computed } from 'vue';

export const useReportStore = defineStore('report', () => {
  // 1. Initial State (Try to get from local storage, otherwise use "now")
  const selectedYear = ref(
    Number(localStorage.getItem('report-year')) || new Date().getFullYear()
  );
  const selectedMonth = ref(
    Number(localStorage.getItem('report-month')) || new Date().getMonth() + 1
  );

  // 2. Methods/Computed for formatting
  // This gives you "2026-02" automatically
  const formattedMonth = computed(() => {
    const monthStr = String(selectedMonth.value).padStart(2, '0');
    return `${selectedYear.value}-${monthStr}`;
  });

  // 3. Persist changes whenever the user picks a new date
  watch([selectedYear, selectedMonth], ([newYear, newMonth]) => {
    localStorage.setItem('report-year', String(newYear));
    localStorage.setItem('report-month', String(newMonth));
  });

  return { 
    selectedYear, 
    selectedMonth, 
    formattedMonth 
  };
});