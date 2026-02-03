<template>
  <q-page class="q-pa-md column no-wrap overflow-hidden" style="height: 100vh;">
    <div class="row items-center q-gutter-md q-mb-md">
      <div class="text-h5">Reports</div>
      
      <q-space />

      <q-select
        v-model="selectedYear"
        :options="yearOptions"
        label="Year"
        outlined
        dense
        style="min-width: 120px"
      />

      <q-select
        v-model="selectedMonth"
        :options="monthOptions"
        label="Month"
        outlined
        dense
        emit-value
        map-options
        style="min-width: 150px"
      />
    </div>

    <q-table
      title="Client Reports"
      :rows="filteredClients"
      :columns="columns"
      row-key="id"
      :loading="loading"
      flat
      bordered
      virtual-scroll
      class="col"
      :rows-per-page-options="[0]"
      @row-click="onRowClick"
    >
      <template v-slot:top-right>
        <client-search-bar
          v-model:search="search"
          v-model:searchType="searchType"
        />
      </template>

      <template #body-cell-observacao="props">
        <q-td :props="props" style="max-width: 300px">
          <div class="ellipsis">
            {{ props.value }}
          </div>
        </q-td>
      </template>
    </q-table>
  </q-page>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useKanbanStore } from 'stores/kanban';
import ClientSearchBar from 'src/components/ClientSearchBar.vue';
import type { QTableColumn } from 'quasar';
import type { Client } from 'src/components/models';

const kanbanStore = useKanbanStore();
const loading = ref(false);
const search = ref('');
const searchType = ref('nome');

// --- Date Filter Logic ---
const currentYear = new Date().getFullYear();
const selectedYear = ref(currentYear);
const selectedMonth = ref(new Date().getMonth() + 1);

// Generate past 100 years
const yearOptions = Array.from({ length: 101 }, (_, i) => currentYear - i);

const monthOptions = [
  { label: 'January', value: 1 },
  { label: 'February', value: 2 },
  { label: 'March', value: 3 },
  { label: 'April', value: 4 },
  { label: 'May', value: 5 },
  { label: 'June', value: 6 },
  { label: 'July', value: 7 },
  { label: 'August', value: 8 },
  { label: 'September', value: 9 },
  { label: 'October', value: 10 },
  { label: 'November', value: 11 },
  { label: 'December', value: 12 }
];

// --- Table Configuration ---
const columns: QTableColumn[] = [
  { name: 'nome', label: 'Name', field: 'nome', sortable: true, align: 'left' },
  { name: 'email', label: 'Email', field: 'email', sortable: true, align: 'left' },
  { name: 'observacao', label: 'Observation', field: 'observacao', align: 'left' },
];

const filteredClients = computed(() => {
  if (!search.value) return kanbanStore.clients;
  const term = search.value.toLowerCase();
  
  return kanbanStore.clients.filter((c) => {
    const key = searchType.value as keyof Client;
    const val = c[key];
    return typeof val === 'string' && val.toLowerCase().includes(term);
  });
});

const onRowClick = (evt: Event, row: Client) => {
  console.log('row clicked', {
    client: row.nome,
    year: selectedYear.value,
    month: selectedMonth.value
  });
};

onMounted(async () => {
  loading.value = true;
  try {
    await kanbanStore.fetchClients();
  } catch (error) {
    console.error('Error fetching clients:', error);
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
/* Ensure the table fills the height provided by .col */
.q-table__container {
  flex: 1;
}
</style>