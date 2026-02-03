<template>
  <div
    class="kanban-column column no-wrap rounded-borders"
    :class="{ 'is-hidden': column.is_hidden, 'bg-grey-2': !column.is_hidden, 'bg-grey-3': column.is_hidden }"
  >
    <!-- Column Header -->
    <div
      class="column-header row items-center justify-between q-pa-sm bg-grey-3 rounded-borders"
      :class="{ 'cursor-move': !column.is_fixed }"
    >
      <div class="row items-center no-wrap full-width" :class="{ 'column justify-center q-py-md': column.is_hidden }">
        
        <!-- Title -->
        <div class="text-subtitle1 text-weight-bold ellipsis" :class="{ 'vertical-text': column.is_hidden }">
          {{ column.name }}
        </div>

        <q-space v-if="!column.is_hidden" />

        <!-- Actions -->
        <div class="row no-wrap items-center" :class="{ 'column q-gutter-y-sm q-mt-sm': column.is_hidden }">
          <q-btn
            v-if="!column.is_hidden"
            flat round dense size="md"
            icon="add"
            color="primary"
            @click="$emit('create-demand', column)"
          >
            <q-tooltip>Criar Demanda</q-tooltip>
          </q-btn>

          <q-btn
            flat round dense size="sm"
            :icon="column.is_hidden ? 'visibility_off' : 'visibility'"
            :color="column.is_hidden ? 'negative' : 'grey-7'"
            @click="$emit('toggle-hide', column)"
          >
            <q-tooltip>{{ column.is_hidden ? 'Exibir' : 'Esconder' }}</q-tooltip>
          </q-btn>
        </div>
      </div>
    </div>

    <!-- COLUMN DRAGGABLE CONTENT -->
    <div v-if="!column.is_hidden" class="col q-pa-sm scroll-y relative-position">
      <draggable
        v-model="localDemands"
        item-key="id"
        class="q-gutter-y-sm full-height"
        group="demands"
        ghost-class="ghost-card"
        @change="onDemandChange"
        style="min-height: 50px"
      >
        <template #item="{ element: demand }">
          <div>
            <demand-card
              :demand="demand"
              @edit="$emit('edit-demand', $event)"
              @delete="$emit('delete-demand', $event)"
            />
          </div>
        </template>
      </draggable>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, type PropType } from 'vue';
import type { KanbanColumn, Demand } from './models';
import draggable from 'vuedraggable';
import DemandCard from './DemandCard.vue';

const emit = defineEmits(['create-demand', 'toggle-hide', 'edit-demand', 'delete-demand', 'demand-update']);

const props = defineProps({
  column: {
    type: Object as PropType<KanbanColumn>,
    required: true,
  },
  demands: {
    type: Array as PropType<Demand[]>,
    default: () => [],
  },
});

const localDemands = ref<Demand[]>([]);

watch(() => props.demands, (newVal) => {
  localDemands.value = [...newVal];
}, { immediate: true });

// eslint-disable-next-line @typescript-eslint/no-explicit-any
const onDemandChange = (change: any) => {
  emit('demand-update', { change, column: props.column });
};
</script>

<style lang="scss" scoped>
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
.cursor-move {
  cursor: move;
}
.ghost-card {
  opacity: 0.5;
  background: #e0e0e0;
}
</style>
