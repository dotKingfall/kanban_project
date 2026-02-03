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
            @demand-update="handleDemandUpdate"
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

            <!-- Attachments -->
            <div class="q-mt-md">
              <div class="row items-center justify-between q-mb-xs">
                <div class="text-subtitle2">Links & Anexos</div>
                <q-btn 
                  flat round dense 
                  color="primary" 
                  icon="add_link" 
                  @click="addLinkRow"
                >
                  <q-tooltip>Adicionar Link</q-tooltip>
                </q-btn>
              </div>

              <q-scroll-area 
                style="height: 150px; border: 1px solid rgba(0,0,0,0.12);" 
                class="rounded-borders q-pa-sm bg-grey-1"
              >
                <div v-if="!newDemand.anexos || newDemand.anexos.length === 0" class="flex flex-center full-height text-grey-5">
                  Nenhum link adicionado.
                </div>

                <div 
                  v-for="(link, index) in newDemand.anexos" 
                  :key="index"
                  class="row no-wrap items-center q-mb-sm q-pa-xs rounded-borders link-row cursor-pointer"
                  @click="openLink(link.url)"
                >
                  <q-input 
                    v-model="link.url" 
                    dense 
                    outlined 
                    placeholder="https://..." 
                    class="col q-mr-sm bg-white"
                    @click.stop 
                  />
                  
                  <q-btn 
                    flat round dense 
                    color="negative" 
                    icon="remove_circle_outline" 
                    size="sm"
                    @click.stop="removeExistingAttachment(index)"
                  />
                </div>
              </q-scroll-area>
            </div>

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
const newFiles = ref<File[]>([]);
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
  return client.value.demands
    .filter(d => d.kanban_column_id === columnId)
    .sort((a, b) => a.position_in_column - b.position_in_column);
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
  newFiles.value = [];
  showCreateDemandDialog.value = true;
};

const openEditDemandDialog = (demand: Demand) => {
  newDemand.value = { ...demand };
  // Create a copy of the attachments array to avoid modifying the original object
  // if the user cancels the edit.
  if (newDemand.value.anexos) {
    newDemand.value.anexos = [...newDemand.value.anexos];
  }
  newFiles.value = [];
  showCreateDemandDialog.value = true;
};

const addLinkRow = () => {
  if (!newDemand.value.anexos) {
    newDemand.value.anexos = [];
  }
  // We use url property to match your JSON structure
  newDemand.value.anexos.push({ name: 'Link', url: '' });
};

const openLink = (url: string) => {
  if (!url || url.trim() === '') return;
  
  // Ensure the URL has a protocol
  const target = url.startsWith('http') ? url : `https://${url}`;
  
  // Securely open in a new tab
  window.open(target, '_blank', 'noopener,noreferrer');
};

const removeExistingAttachment = (index: number) => {
  if (newDemand.value.anexos) {
    newDemand.value.anexos.splice(index, 1);
  }
};

const saveNewDemand = async () => {
  if (!newDemand.value.titulo) {
    $q.notify({ type: 'negative', message: 'Title is required' });
    return;
  }

  // We no longer need FormData since we are just sending text/links
  // Just send the newDemand object directly
  const payload = {
    ...newDemand.value,
    // Ensure anexos is sent as an array (Laravel will handle the JSON cast)
    anexos: newDemand.value.anexos || [] 
  };

  try {
    if (newDemand.value.id) {
      // Update existing
      const response = await api.patch(`/demands/${newDemand.value.id}`, payload);
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
      const response = await api.post('/demands', payload);
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
  }).onOk(() => {
    void(async () => {
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
    })();
  });
};

interface ReorderPayload {
  id: number;
  position_in_column: number;
  kanban_column_id: number;
}

// eslint-disable-next-line @typescript-eslint/no-explicit-any
const handleDemandUpdate = async ({ change, column }: { change: any, column: KanbanColumn }) => {
  if (!client.value?.demands) return;

  if (change.moved) {
    // --- A demand was reordered within the SAME column ---
    const list = getDemandsForColumn(column.id);
    const movedItem = list.find(d => d.id === change.moved.element.id);
    if (!movedItem) return;

    list.splice(list.indexOf(movedItem), 1);
    list.splice(change.moved.newIndex, 0, movedItem);

    const payload: ReorderPayload[] = list.map((demand, index) => {
      demand.position_in_column = index; // Optimistic update
      return { id: demand.id, position_in_column: index, kanban_column_id: column.id };
    });

    try {
      // Send batch update
      await api.post('/demands/reorder', { demands: payload });
      $q.notify({ type: 'positive', message: 'Demand reordered' });
    } catch (error) {
      console.error('Failed to reorder demand:', error);
      $q.notify({ type: 'negative', message: 'Failed to reorder demand' });
      await kanbanStore.fetchClients(true);
      client.value = kanbanStore.getClientById(route.params.clientId as string) || null;
    }
  } else if (change.added) {
    // --- A demand was moved to a NEW column ---
    const movedDemand = client.value.demands.find(d => d.id === change.added.element.id);
    if (!movedDemand) return;

    const sourceColumnId = movedDemand.kanban_column_id;
    const targetColumn = column;

    // 1. Prepare Source List: Remove the moved item and re-index
    // We filter from the main list, excluding the moved item
    const sourceList = client.value.demands
      .filter(d => d.kanban_column_id === sourceColumnId && d.id !== movedDemand.id)
      .sort((a, b) => a.position_in_column - b.position_in_column);

    // 2. Prepare Target List: Get existing items, insert moved item at new index
    const targetList = client.value.demands
      .filter(d => d.kanban_column_id === targetColumn.id && d.id !== movedDemand.id)
      .sort((a, b) => a.position_in_column - b.position_in_column);
    
    // Optimistic Update on the moved object
    movedDemand.kanban_column_id = targetColumn.id;
    movedDemand.status = targetColumn.name;

    // Insert into target list at the correct visual position
    targetList.splice(change.added.newIndex, 0, movedDemand);

    try {
      const sourcePayload = sourceList.map((d, i) => {
        d.position_in_column = i;
        return { id: d.id, position_in_column: i, kanban_column_id: sourceColumnId };
      });

      const targetPayload = targetList.map((d, i) => {
        d.position_in_column = i;
        return { id: d.id, position_in_column: i, kanban_column_id: targetColumn.id };
      });

      await api.post('/demands/reorder', { demands: [...sourcePayload, ...targetPayload] });
      $q.notify({ type: 'positive', message: `Moved to ${targetColumn.name}` });
    } catch (error) {
      console.error('Failed to move demand:', error);
      $q.notify({ type: 'negative', message: 'Failed to move demand' });
      await kanbanStore.fetchClients(true);
      client.value = kanbanStore.getClientById(route.params.clientId as string) || null;
    }
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

.link-row {
  transition: background 0.2s ease;
}
.link-row:hover {
  background: rgba(0, 0, 0, 0.05) !important;
}
</style>
