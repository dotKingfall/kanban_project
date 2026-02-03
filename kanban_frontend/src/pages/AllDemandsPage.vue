<template>
  <q-page class="q-pa-md column no-wrap overflow-hidden" style="height: 100vh;">
    
    <q-table
      title="All Demands"
      :rows="filteredDemands"
      :columns="columns"
      row-key="id"
      :loading="loading"
      flat
      bordered
      virtual-scroll
      class="col" 
      :rows-per-page-options="[0]"
    >
      <template v-slot:top-left>
        <div class="column">
          <div class="text-h6">All Demands</div>
          <div class="row items-center q-gutter-x-xs">
            <div style="width: 12px; height: 12px; border-radius: 2px;" class="bg-orange-2"></div>
            <div class="text-caption text-grey-8">= Returned from test</div>
          </div>
        </div>
      </template>

      <template v-slot:top-right>
        <div class="row q-gutter-sm items-center">
          <q-select
            v-model="searchType"
            :options="searchOptions"
            dense outlined options-dense emit-value map-options
            style="min-width: 120px"
          />
          <q-input v-model="search" dense outlined placeholder="Search" clearable>
            <template v-slot:append><q-icon name="search" /></template>
          </q-input>

          <q-btn flat round color="primary" icon="refresh" @click="() => loadData(true)">
            <q-tooltip>Refresh</q-tooltip>
          </q-btn>
        </div>
      </template>

      <template v-slot:body="props">
        <q-tr 
          :props="props" 
          :class="props.row.flag_returned ? 'row-returned' : ''" 
          class="cursor-pointer"
          @click="onRowClick($event, props.row)"
        >
          <q-td v-for="col in props.cols" :key="col.name" :props="props">
            <template v-if="col.name === 'client'">
              {{ getClientName(props.row.cliente) }}
            </template>
            
            <template v-else-if="col.name === 'priority'">
              <q-chip
                v-if="props.row.priority_obj"
                :label="props.row.priority_obj.name"
                color="primary"
                text-color="white"
                size="sm"
                dense
              />
              <span v-else>{{ props.row.priority_table_id }}</span>
            </template>

            <template v-else>
              {{ col.value }}
            </template>
          </q-td>
        </q-tr>
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
const searchOptions = [{ label: 'Title', value: 'titulo' }];

const columns: QTableColumn[] = [
  { name: 'titulo', label: 'Title', field: 'titulo', sortable: true, align: 'left' },
  { name: 'client', label: 'Client', field: 'cliente', sortable: true, align: 'left' },
  { name: 'priority', label: 'Priority', field: (row: Demand) => row.priority_table_id, sortable: true, align: 'center' },
  { name: 'status', label: 'Status', field: 'status', sortable: true, align: 'center' },
  { name: 'responsavel', label: 'Responsible', field: 'responsavel', sortable: true, align: 'left' },
  { name: 'tempo_estimado', label: 'Est. Time', field: 'tempo_estimado', sortable: true, align: 'right' },
];

const filteredDemands = computed(() => {
  const demands = kanbanStore.activeDemands;
  if (!search.value) return demands;
  const searchTerm = search.value.toLowerCase();
  return demands.filter(demand => demand.titulo?.toLowerCase().includes(searchTerm));
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

const onRowClick = async (evt: Event, row: Demand) => {
  await router.push(`/kanban/${row.cliente}`);
};

onMounted(async () => {
  await loadData();
});
</script>

<style scoped>
.row-returned {
  /* Using your custom border-left suggestion */
  border-left: 5px solid #f2c037 !important; 
  background-color: rgba(242, 192, 55, 0.05) !important;
  transition: background-color 0.3s ease;
}

.row-returned:hover {
  background-color: rgba(242, 192, 55, 0.15) !important;
}
</style>