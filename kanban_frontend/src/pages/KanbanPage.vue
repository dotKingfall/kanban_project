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
            @edit-demand="openEditDemandDialog"
            @delete-demand="confirmDeleteDemand"
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
          <div class="text-h6">{{ newDemand.id ? 'Edit Demand' : 'New Demand' }}</div>
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
              :rules="[val => !!val || 'Status is required']"
              outlined
              dense
            />
            <q-input
              v-model="newDemand.responsavel"
              label="Responsible"
              :rules="[val => !!val || 'Responsible is required']"
              outlined
              dense />
            
            <q-select
              v-model="newDemand.priority_table_id"
              :options="priorityOptions"
              option-label="name"
              option-value="id"
              label="Priority"
              outlined
              :rules="[val => val !== null && val !== undefined || 'Priority is required']"
              dense
              emit-value
              map-options
            />
            <q-select
              v-model="newDemand.department_table_id"
              :options="departmentOptions"
              option-label="name"
              option-value="id"
              label="Department"
              outlined
              dense
              emit-value
              map-options
            />
            
            <q-input v-model="newDemand.quem_deve_testar" label="Tester" outlined dense />
            <q-input
              v-model="newDemand.descricao_detalhada"
              label="Detailed Description"
              type="textarea"
              outlined
              dense
            />
            <div class="row q-gutter-sm items-center">
              <q-input v-model.number="newDemand.tempo_estimado" type="number" label="Estimated Time (h)" outlined dense style="width: 150px" />
              <q-toggle v-model="newDemand.cobrada_do_cliente" label="Bill to client" />
              <q-toggle v-model="newDemand.flag_returned" label="Returned" color="orange" />
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
const priorityOptions = computed(() => kanbanStore.priorities);
const departmentOptions = computed(() => kanbanStore.departments);

onMounted(async () => {
  try {
    // Ensure we have data. If user refreshed page directly here, this will fetch it.
    // If coming from Dashboard, this uses the cache immediately.
    await Promise.all([
      kanbanStore.fetchClients(),
      kanbanStore.fetchLookups()
    ]);

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

  // Calculate the next position in the column (0-indexed).
  const demandsInColumn = getDemandsForColumn(column.id);
  const newPosition = demandsInColumn.length;

  newDemand.value = makeEmptyDemand(column.id, client.value.id);
  newDemand.value.position_in_column = newPosition;
  newDemand.value.status = column.name;
  showCreateDemandDialog.value = true;
};

const openEditDemandDialog = (demand: Demand) => {
  newDemand.value = { ...demand };
  showCreateDemandDialog.value = true;
};

const saveNewDemand = async () => {
  if (!newDemand.value.titulo) {
    $q.notify({ type: 'negative', message: 'Title is required' });
    return;
  }

  try {
    if (newDemand.value.id) {
      // Update existing
      const response = await api.patch(`/demands/${newDemand.value.id}`, newDemand.value);
      const updatedDemand: Demand = response.data;

      if (client.value?.demands) {
        const index = client.value.demands.findIndex(d => d.id === updatedDemand.id);
        if (index !== -1) {
          client.value.demands[index] = updatedDemand;
        }
      }
      $q.notify({ type: 'positive', message: 'Demand updated successfully' });
    } else {
      // Create new
      const response = await api.post('/demands', newDemand.value);
      const createdDemand: Demand = response.data;

      if (client.value?.demands) {
        client.value.demands.push(createdDemand);
      }
      $q.notify({ type: 'positive', message: 'Demand created successfully' });
    }

    showCreateDemandDialog.value = false;
  } catch (error) {
    console.error('Failed to save demand', error);
    $q.notify({ type: 'negative', message: 'Failed to save demand' });
  }
};

const confirmDeleteDemand = (demand: Demand) => {
  $q.dialog({
    title: 'Confirm',
    message: `Are you sure you want to delete "${demand.titulo}"?`,
    cancel: true,
    persistent: true
  }).onOk(async () => {
    try {
      await api.delete(`/demands/${demand.id}`);
      if (client.value?.demands) {
        client.value.demands = client.value.demands.filter(d => d.id !== demand.id);
      }
      $q.notify({ type: 'positive', message: 'Demand deleted' });
    } catch (error) {
      console.error('Failed to delete demand:', error);
      $q.notify({ type: 'negative', message: 'Failed to delete demand' });
    }
  });
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
