<template>
  <q-page class="q-pa-md column no-wrap overflow-hidden" style="height: 100vh;">
    <q-table
      title="All Demands"
      :rows="filteredDemands"
      :columns="columns"
      row-key="id"
      :loading="loading"
      @row-click="onRowClick"

      flat
      bordered
      virtual-scroll
      class="col" 
      :rows-per-page-options="[0]"
    >
      <template v-slot:top-right>
        <div class="row q-gutter-sm items-center">
          <q-select
            v-model="searchType"
            :options="searchOptions"
            dense
            outlined
            options-dense
            emit-value
            map-options
            style="min-width: 120px"
          />
          <q-input v-model="search" dense outlined placeholder="Search" clearable>
            <template v-slot:append>
              <q-icon name="search" />
            </template>
          </q-input>

        <q-btn
          flat
          round
          color="primary"
          icon="refresh"
          @click="() => loadData(true)"
        >
          <q-tooltip>Refresh</q-tooltip>
        </q-btn>
        </div>
      </template>

      <template v-slot:body-cell-priority="props">
        <q-td :props="props">
          <q-chip
            v-if="props.row.priority_obj"
            :label="props.row.priority_obj.name"
            color="primary"
            text-color="white"
            size="sm"
            dense
          />
          <span v-else>{{ props.value }}</span>
        </q-td>
      </template>

      <template v-slot:body-cell-client="props">
        <q-td :props="props">
          {{ getClientName(props.row.cliente) }}
        </q-td>
      </template>
    </q-table>
  </q-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useKanbanStore } from 'stores/kanban';
import type { QTableColumn } from 'quasar';
import type { Demand } from 'src/components/models';

const router = useRouter();
const kanbanStore = useKanbanStore();
const loading = ref(false);
const search = ref('');
const searchType = ref('titulo');
const searchOptions = [
  { label: 'Title', value: 'titulo' },
];

const columns: QTableColumn[] = [
  { name: 'titulo', label: 'Title', field: 'titulo', sortable: true, align: 'left' },
  { name: 'client', label: 'Client', field: 'cliente', sortable: true, align: 'left' },
  { name: 'priority', label: 'Priority', field: (row: Demand) => row.priority_table_id, sortable: true, align: 'center' },
  { name: 'status', label: 'Status', field: 'status', sortable: true, align: 'center' },
  { name: 'responsavel', label: 'Responsible', field: 'responsavel', sortable: true, align: 'left' },
  { name: 'tempo_estimado', label: 'Est. Time', field: 'tempo_estimado', sortable: true, align: 'right' },
];

const filteredDemands = computed(() => {
  // Use the cached getter from the store for performance
  const demands = kanbanStore.activeDemands;
  if (!search.value) {
    return demands;
  }
  const searchTerm = search.value.toLowerCase();
  // The search is simple and only works on the title for now
  return demands.filter(demand =>
    demand.titulo?.toLowerCase().includes(searchTerm)
  );
});

const getClientName = (clientId: number) => {
  const client = kanbanStore.getClientById(clientId);
  return client ? client.nome : 'Unknown';
};

const loadData = async (force = false) => {
  if (!force && kanbanStore.loaded) return;

  loading.value = true;
  try {
    await kanbanStore.fetchClients(force);
  } catch (error) {
    console.error('Failed to load demands', error);
  } finally {
    loading.value = false;
  }
};

const onRowClick = (evt: Event, row: Demand) => {
  router.push(`/kanban/${row.cliente}`);
};

onMounted(() => {
  loadData();
});
</script>