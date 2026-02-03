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
            flat round dense size="sm"
            icon="add_circle"
            color="positive"
            @click="$emit('create-demand', column)"
          >
            <q-tooltip>Create Demand</q-tooltip>
          </q-btn>

          <q-btn
            flat round dense size="sm"
            :icon="column.is_hidden ? 'visibility_off' : 'visibility'"
            :color="column.is_hidden ? 'negative' : 'grey-7'"
            @click="$emit('toggle-hide', column)"
          >
            <q-tooltip>{{ column.is_hidden ? 'Show' : 'Hide' }}</q-tooltip>
          </q-btn>
        </div>
      </div>
    </div>

    <!-- Column Content -->
    <div v-if="!column.is_hidden" class="col q-pa-sm scroll-y relative-position">
      <div v-if="demands.length > 0" class="q-gutter-y-sm">
        <demand-card
          v-for="demand in demands"
          :key="demand.id"
          :demand="demand"
          @edit="$emit('edit-demand', $event)"
          @delete="$emit('delete-demand', $event)"
        />
      </div>
      <div v-else class="absolute-center text-grey-5 text-center">
        Empty
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { PropType } from 'vue';
import type { KanbanColumn, Demand } from './models';
import DemandCard from './DemandCard.vue';

defineEmits(['create-demand', 'toggle-hide', 'edit-demand', 'delete-demand']);

defineProps({
  column: {
    type: Object as PropType<KanbanColumn>,
    required: true,
  },
  demands: {
    type: Array as PropType<Demand[]>,
    default: () => [],
  },
});
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
</style>
