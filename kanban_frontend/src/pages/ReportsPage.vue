<template>
  <q-page class="q-pa-md column no-wrap overflow-hidden" style="height: 100vh;">
    <div class="row items-center q-gutter-md q-mb-md">
      <div class="text-h5">Relatórios</div>
      
      <q-space />

      <q-select
        v-model="reportStore.selectedYear"
        :options="yearOptions"
        label="Ano"
        outlined
        dense
        :disable="reportStore.loading"
        style="min-width: 120px"
      />

      <q-select
        v-model="reportStore.selectedMonth"
        :options="monthOptions"
        label="Mês"
        outlined
        dense
        emit-value
        map-options
        :disable="reportStore.loading"
        style="min-width: 150px"
      />
    </div>

    <div class="col relative-position">
      <q-table
        title="Relatório de Clientes"
        :rows="filteredClients"
        :columns="columns"
        row-key="id"
        flat
        bordered
        virtual-scroll
        class="full-height"
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

      <q-inner-loading 
        :showing="reportStore.loading"
        label="Syncing Report Data..."
        label-class="text-primary"
        label-style="font-size: 1.1em"
      >
        <q-spinner-dots size="50px" color="primary" />
      </q-inner-loading>
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useKanbanStore } from 'stores/kanban';
import { useReportStore } from 'stores/report';
import { useRouter } from 'vue-router';
import ClientSearchBar from 'src/components/ClientSearchBar.vue';
import type { QTableColumn } from 'quasar';
import type { Client } from 'src/components/models';

const router = useRouter();
const reportStore = useReportStore();
const kanbanStore = useKanbanStore();

const search = ref('');
const searchType = ref('nome');

const currentYear = new Date().getFullYear();
const yearOptions = Array.from({ length: 101 }, (_, i) => currentYear - i);

const monthOptions = [
  { label: 'Any', value: null },
  { label: 'Janeiro', value: 1 }, { label: 'Fevereiro', value: 2 },
  { label: 'Março', value: 3 }, { label: 'Abril', value: 4 },
  { label: 'Maio', value: 5 }, { label: 'Junho', value: 6 },
  { label: 'Julho', value: 7 }, { label: 'Agosto', value: 8 },
  { label: 'Setembro', value: 9 }, { label: 'Outubro', value: 10 },
  { label: 'Novembro', value: 11 }, { label: 'Dezembro', value: 12 }
];

const columns: QTableColumn[] = [
  { name: 'nome', label: 'Nome', field: 'nome', sortable: true, align: 'left' },
  { name: 'email', label: 'Email', field: 'email', sortable: true, align: 'left' },
  { name: 'observacao', label: 'Observação', field: 'observacao', align: 'left' },
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

const onRowClick = async (evt: Event, row: Client) => {
  await router.push({
    path: '/full-reports',
    query: { 
      ids: [row.id], 
      month: reportStore.formattedMonth 
    }
  });
};

onMounted(async () => {
  if (kanbanStore.clients.length === 0) {
    await kanbanStore.fetchClients();
  }
  
  if (!reportStore.reportCache) {
    await reportStore.fetchAllReports();
  }
});
</script>

<style scoped>
.full-height {
  height: 100%;
}
</style>