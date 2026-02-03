<template>
  <q-page class="q-pa-md column no-wrap" style="height: 100vh;">
    <div v-if="loading" class="flex flex-center absolute-center">
      <q-spinner size="3em" color="primary" />
    </div>

    <div v-else-if="client" class="col column no-wrap full-width full-height">
      <!-- Header -->
      <div class="row items-center q-mb-md flex-shrink-0">
        <q-btn flat round icon="arrow_back" color="primary" @click="router.back()" class="q-mr-sm" />
        <div class="text-h5">{{ client.nome }}</div>
      </div>

      <!-- Kanban Board Area -->
      <draggable
        v-model="localColumns"
        item-key="id"
        class="row no-wrap q-gutter-x-md full-height items-start scroll-x-auto"
        handle=".cursor-move"
        :animation="200"
        ghost-class="ghost-card"
        @end="saveColumns"
      >
        <template #item="{ element: col }">
          <kanban-column-component
            :column="col"
            :demands="getDemandsForColumn(col.id)"
            @toggle-hide="toggleHide"
            @create-demand="openCreateDemandDialog"
          />
        </template>
      </draggable>
    </div>

    <div v-else class="flex flex-center absolute-center text-negative">
      Client not found.
    </div>

    <!-- Create Demand Dialog -->
    <q-dialog v-model="showCreateDemandDialog">
      <q-card style="min-width: 500px">
        <q-card-section>
          <div class="text-h6">New Demand</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-form @submit="saveNewDemand" class="q-gutter-md">
            <q-input
              v-model="newDemand.titulo"
              label="Title"
              :rules="[val => !!val || 'Title is required']"
              outlined
              dense
            />
            <q-select
              v-model="newDemand.status"
              :options="statusOptions"
              label="Status / Column"
              outlined
              dense
            />
            <q-input v-model="newDemand.responsavel" label="Responsible" outlined dense />
            <q-input v-model="newDemand.prioridade" label="Priority" outlined dense hint="Ex: Urgente, Alta, Média, Baixa" />
            <q-input v-model="newDemand.setor" label="Department" outlined dense />
            <q-input v-model="newDemand.quem_deve_testar" label="Tester" outlined dense />
            <q-input
              v-model="newDemand.descricao_detalhada"
              label="Detailed Description"
              type="textarea"
              outlined
              dense
            />
            <div class="row q-gutter-sm">
              <q-input v-model.number="newDemand.tempo_estimado" type="number" label="Estimated Time (h)" outlined dense style="width: 150px" />
              <q-toggle v-model="newDemand.cobrada_do_cliente" label="Bill to client" />
            </div>

            <div align="right" class="q-mt-md">
              <q-btn flat label="Cancel" color="primary" v-close-popup />
              <q-btn label="Save" type="submit" color="primary" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useRouter } from 'vue-router';
import { api } from 'boot/axios';
import { useQuasar } from 'quasar';
import draggable from 'vuedraggable';
import { useKanbanStore, type ClientWithDemands } from 'src/stores/kanban';
import {
  type KanbanColumn,
  type Demand,
  makeEmptyDemand,
} from 'src/components/models';
import KanbanColumnComponent from 'src/components/KanbanColumn.vue';

const $q = useQuasar();
const route = useRoute();
const router = useRouter();
const kanbanStore = useKanbanStore();
const client = ref<ClientWithDemands | null>(null);
const loading = ref(true);
const localColumns = ref<KanbanColumn[]>([]);
const showCreateDemandDialog = ref(false);
const newDemand = ref<Partial<Demand>>({});

onMounted(async () => {
  try {
    // Ensure we have data. If user refreshed page directly here, this will fetch it.
    // If coming from Dashboard, this uses the cache immediately.
    await kanbanStore.fetchClients();
    
    const foundClient = kanbanStore.getClientById(route.params.clientId as string);
    client.value = foundClient || null;
    
    if (client.value && client.value.kanban_columns) {
      // Sort columns by position initially
      localColumns.value = [...client.value.kanban_columns].sort((a, b) => a.position - b.position);
    }
  } catch (error) {
    console.error('Failed to fetch client', error);
  } finally {
    loading.value = false;
  }
});

const getDemandsForColumn = (columnId: number): Demand[] => {
  if (!client.value || !client.value.demands) return [];
  return client.value.demands.filter(d => d.kanban_column_id === columnId);
};

const statusOptions = computed(() =>
  localColumns.value.filter(c => !c.is_hidden).map(c => c.name)
);

watch(() => newDemand.value?.status, (newStatus) => {
  if (newStatus && newDemand.value) {
    const targetColumn = localColumns.value.find(c => c.name === newStatus);
    if (targetColumn) {
      newDemand.value.kanban_column_id = targetColumn.id;
    }
  }
});

const openCreateDemandDialog = (column: KanbanColumn) => {
  if (!client.value) return;
  newDemand.value = makeEmptyDemand(column.id, client.value.id);
  newDemand.value.status = column.name; // Set initial status based on column
  showCreateDemandDialog.value = true;
};

const saveNewDemand = async () => {
  if (!newDemand.value.titulo) {
    $q.notify({ type: 'negative', message: 'Title is required' });
    return;
  }

  try {
    const response = await api.post('/demands', newDemand.value);
    const createdDemand: Demand = response.data;

    // Optimistic update
    if (client.value?.demands) {
      client.value.demands.push(createdDemand);
    }

    showCreateDemandDialog.value = false;
    $q.notify({ type: 'positive', message: 'Demand created successfully' });
  } catch (error) {
    console.error('Failed to create demand', error);
    $q.notify({ type: 'negative', message: 'Failed to create demand' });
  }
};

const saveColumns = async () => {
  try {
    // Update positions based on current array index
    const updates = localColumns.value.map((col, index) => ({
      id: col.id,
      position: index,
      is_fixed: col.is_fixed,
      is_hidden: col.is_hidden
    }));

    // Optimistic update locally
    localColumns.value.forEach((col, index) => {
      col.position = index;
    });

    await api.post('/kanban-columns/update', { columns: updates });
    
    // Update store if needed, or just rely on next fetch
    if (client.value) {
      client.value.kanban_columns = [...localColumns.value];
    }
  } catch (error) {
    console.error('Failed to save columns', error);
    $q.notify({ type: 'negative', message: 'Failed to save column changes' });
  }
};

const toggleHide = async (col: KanbanColumn) => {
  col.is_hidden = !col.is_hidden;
  await saveColumns();
};
</script>

<style scoped>
.scroll-x-auto {
  overflow-x: auto;
}
.cursor-move {
  cursor: move;
}
.ghost-card {
  opacity: 0.5;
  background: #F2C037;
}
</style>
