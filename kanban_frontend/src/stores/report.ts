import { defineStore } from 'pinia';
import { ref, watch } from 'vue';

export const useReportStore = defineStore('report', () => {
  const currentYear = new Date().getFullYear();
  const currentMonth = new Date().getMonth() + 1;

  const selectedYear = ref(Number(localStorage.getItem('report-year')) || currentYear);
  const selectedMonth = ref(Number(localStorage.getItem('report-month')) || currentMonth);

  watch(selectedYear, (val) => localStorage.setItem('report-year', String(val)));
  watch(selectedMonth, (val) => localStorage.setItem('report-month', String(val)));

  const getFormattedMonth = () => {
    return `${selectedYear.value}-${String(selectedMonth.value).padStart(2, '0')}`;
  };

  return { selectedYear, selectedMonth, getFormattedMonth };
});