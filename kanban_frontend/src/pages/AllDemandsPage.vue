<template>
  <q-page class="q-pa-md">
    <q-table
      title="All Demands"
      :rows="sortedDemands"
      :columns="columns"
      row-key="id"
      :loading="loading"
      :filter="filter"
      flat
      bordered
      @row-click="onRowClick"
      :rows-per-page-options="[0]"
    >
      <template v-slot:top-right>
        <q-input borderless dense debounce="300" v-model="filter" placeholder="Search">
          <template v-slot:append>
            <q-icon name="search" />
          </template>
        </q-input>
        <q-btn
          flat
          round
          color="primary"
          icon="refresh"
          class="q-ml-md"
          @click="() => loadData(true)"
        >
          <q-tooltip>Refresh</q-tooltip>
        </q-btn>
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
const filter = ref('');

const columns: QTableColumn[] = [
  { name: 'titulo', label: 'Title', field: 'titulo', sortable: true, align: 'left' },
  { name: 'client', label: 'Client', field: 'cliente', sortable: true, align: 'left' },
  { name: 'priority', label: 'Priority', field: (row: Demand) => row.priority_table_id, sortable: true, align: 'center' },
  { name: 'status', label: 'Status', field: 'status', sortable: true, align: 'center' },
  { name: 'responsavel', label: 'Responsible', field: 'responsavel', sortable: true, align: 'left' },
  { name: 'tempo_estimado', label: 'Est. Time', field: 'tempo_estimado', sortable: true, align: 'right' },
];

const sortedDemands = computed(() => {
  const allDemands: Demand[] = [];
  
  kanbanStore.clients.forEach(client => {
    if (client.demands) {
      client.demands.forEach(demand => {
        if (demand.status !== 'Concluído') {
          allDemands.push(demand);
        }
      });
    }
  });

  return allDemands.sort((a, b) => {
    const pA = a.priority_table_id || 999;
    const pB = b.priority_table_id || 999;
    return pA - pB;
  });
});

const getClientName = (clientId: number) => {
  const client = kanbanStore.getClientById(clientId);
  return client ? client.nome : 'Unknown';
};

const loadData = async (force = false) => {
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