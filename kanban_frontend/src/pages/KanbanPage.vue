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
            @toggle-pin="togglePin"
            @toggle-hide="toggleHide"
          />
        </template>
      </draggable>
    </div>

    <div v-else class="flex flex-center absolute-center text-negative">
      Client not found.
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useRouter } from 'vue-router';
import { api } from 'boot/axios';
import { useQuasar } from 'quasar';
import draggable from 'vuedraggable';
import { useKanbanStore, type ClientWithDemands } from 'src/stores/kanban';
import type { KanbanColumn, Demand } from 'src/components/models';
import KanbanColumnComponent from 'src/components/KanbanColumn.vue';

const $q = useQuasar();
const route = useRoute();
const router = useRouter();
const kanbanStore = useKanbanStore();
const client = ref<ClientWithDemands | null>(null);
const loading = ref(true);
const localColumns = ref<KanbanColumn[]>([]);

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
  return client.value.demands.filter(d => d.column_id === columnId);
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

const togglePin = async (col: KanbanColumn) => {
  col.is_fixed = !col.is_fixed;
  await saveColumns();
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
