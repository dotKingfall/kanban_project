import { defineStore } from 'pinia';
import { ref, watch } from 'vue';

export const useUiStore = defineStore('ui', () => {
  const isSidebarOpen = ref(true);
  const isSidebarMini = ref(false);

  const storedMini = localStorage.getItem('ui-sidebar-mini');
  if (storedMini !== null) {
    isSidebarMini.value = storedMini === 'true';
  }

  const storedOpen = localStorage.getItem('ui-sidebar-open');
  if (storedOpen !== null) {
    isSidebarOpen.value = storedOpen === 'true';
  }

  watch(isSidebarMini, (val) => {
    localStorage.setItem('ui-sidebar-mini', String(val));
  });

  watch(isSidebarOpen, (val) => {
    localStorage.setItem('ui-sidebar-open', String(val));
  });

  function toggleMini() {
    isSidebarMini.value = !isSidebarMini.value;
  }

  return { isSidebarOpen, isSidebarMini, toggleMini };
});
