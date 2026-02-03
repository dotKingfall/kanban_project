<template>
  <q-page class="q-pa-md column no-wrap" style="height: calc(100vh - 50px);">
    <div v-if="loading" class="flex flex-center absolute-center">
      <q-spinner size="3em" color="primary" />
    </div>

    <div v-else-if="client" style="height: calc(100vh - 50px);">
      <!-- Header -->
      <div class="row items-center q-mb-md flex-shrink-0">
        <q-btn flat round icon="arrow_back" color="primary" @click="router.back()" class="q-mr-sm" />
        <div class="text-h4">{{ client.nome }}</div>
      </div>

      <!-- Kanban Board Area -->
      <div class="row no-wrap q-gutter-x-md full-height items-start scroll-x-auto">
        <div
          v-for="col in localColumns"
          :key="col.id"
          class="kanban-column column no-wrap rounded-borders"
          :class="{ 'is-hidden': col.is_hidden, 'bg-grey-2': !col.is_hidden, 'bg-grey-3': col.is_hidden }"
          @drop="onDrop($event, col)"
          @dragover.prevent
          @dragenter.prevent
        >
          <!-- Column Header -->
          <div
            class="column-header row items-center justify-between q-pa-sm bg-grey-3 rounded-borders"
            :class="{ 'cursor-move': !col.is_fixed }"
            :draggable="!col.is_fixed"
            @dragstart="onDragStart($event, col)"
          >
            <div class="row items-center no-wrap full-width" :class="{ 'column justify-center q-py-md': col.is_hidden }">
              
              <!-- Title -->
              <div class="text-subtitle1 text-weight-bold ellipsis" :class="{ 'vertical-text': col.is_hidden }">
                {{ col.name }}
              </div>

              <q-space v-if="!col.is_hidden" />

              <!-- Actions -->
              <div class="row no-wrap items-center" :class="{ 'column q-gutter-y-sm q-mt-sm': col.is_hidden }">
                <q-btn
                  flat round dense size="sm"
                  :icon="col.is_fixed ? 'push_pin' : 'o_push_pin'"
                  :color="col.is_fixed ? 'primary' : 'grey-7'"
                  @click="togglePin(col)"
                >
                  <q-tooltip>{{ col.is_fixed ? 'Unpin' : 'Pin' }}</q-tooltip>
                </q-btn>

                <q-btn
                  flat round dense size="sm"
                  :icon="col.is_hidden ? 'visibility_off' : 'visibility'"
                  :color="col.is_hidden ? 'negative' : 'grey-7'"
                  @click="toggleHide(col)"
                >
                  <q-tooltip>{{ col.is_hidden ? 'Show' : 'Hide' }}</q-tooltip>
                </q-btn>
              </div>
            </div>
          </div>

          <!-- Column Content (Placeholder for now) -->
          <div v-if="!col.is_hidden" class="col q-pa-sm scroll-y relative-position">
            <!-- Demands will go here later -->
            <div class="absolute-center text-grey-5 text-center">
              Empty
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="flex flex-center absolute-center text-negative">
      Client not found.
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useRouter } from 'vue-router';
import { api } from 'boot/axios';
import { useQuasar } from 'quasar';
import { useKanbanStore, type ClientWithDemands } from 'src/stores/kanban';
import type { KanbanColumn } from 'src/components/models';

const $q = useQuasar();
const route = useRoute();
const router = useRouter();
const kanbanStore = useKanbanStore();
const client = ref<ClientWithDemands | null>(null);
const loading = ref(true);
const localColumns = ref<KanbanColumn[]>([]);

const draggedCol = ref<KanbanColumn | null>(null);

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

const togglePin = (col: KanbanColumn) => {
  col.is_fixed = !col.is_fixed;
  saveColumns();
};

const toggleHide = (col: KanbanColumn) => {
  col.is_hidden = !col.is_hidden;
  saveColumns();
};

// Drag and Drop Logic
const onDragStart = (evt: DragEvent, col: KanbanColumn) => {
  if (col.is_fixed) {
    evt.preventDefault();
    return;
  }
  draggedCol.value = col;
  if (evt.dataTransfer) {
    evt.dataTransfer.effectAllowed = 'move';
    evt.dataTransfer.dropEffect = 'move';

    // Set drag image to the whole column (parent of header)
    const target = evt.target as HTMLElement;
    const columnEl = target.closest('.kanban-column');
    if (columnEl) {
      evt.dataTransfer.setDragImage(columnEl, 0, 0);
    }
  }
};

const onDrop = (evt: DragEvent, targetCol: KanbanColumn) => {
  if (!draggedCol.value || draggedCol.value.id === targetCol.id) return;
  
  const oldIndex = localColumns.value.findIndex(c => c.id === draggedCol.value?.id);
  const newIndex = localColumns.value.findIndex(c => c.id === targetCol.id);

  // Remove from old position
  localColumns.value.splice(oldIndex, 1);
  // Insert at new position
  localColumns.value.splice(newIndex, 0, draggedCol.value);

  draggedCol.value = null;
  saveColumns();
};
</script>

<style scoped>
.kanban-column {
  width: 300px;
  min-width: 300px;
  height: 95%;
  transition: all 0.3s ease;
}
.kanban-column.is-hidden {
  width: 50px;
  min-width: 50px;
}
.vertical-text {
  writing-mode: vertical-rl;
  transform: rotate(180deg);
  white-space: nowrap;
}
.scroll-x-auto {
  overflow-x: auto;
}
.cursor-move {
  cursor: move;
}
</style>
