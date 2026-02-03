<template>
  <q-card class="demand-card" flat bordered>
    <q-card-section class="row no-wrap items-start">
      <div class="col">
        <!-- Title -->
        <div class="text-body1 text-weight-bold ellipsis">{{ demand.titulo }}</div>

        <!-- Responsible -->
        <div v-if="demand.responsavel" class="text-caption text-grey">
          <q-icon name="person" class="q-mr-xs" />{{ demand.responsavel }}
        </div>
      </div>

      <!-- Actions Menu -->
      <div class="col-auto">
        <q-btn flat round dense icon="more_vert" size="sm">
          <q-menu>
            <q-list style="min-width: 100px">
              <q-item clickable v-close-popup @click="$emit('edit', demand)">
                <q-item-section avatar><q-icon name="edit" size="xs" /></q-item-section>
                <q-item-section>Edit</q-item-section>
              </q-item>
              <q-item clickable v-close-popup @click="$emit('delete', demand)" class="text-negative">
                <q-item-section avatar><q-icon name="delete" size="xs" /></q-item-section>
                <q-item-section>Delete</q-item-section>
              </q-item>
            </q-list>
          </q-menu>
        </q-btn>
      </div>
    </q-card-section>

    <!-- Chips for Priority, Department, etc. -->
    <q-card-section class="q-pt-none row items-center q-gutter-sm">
      <!-- Priority -->
      <q-chip
        v-if="demand.prioridade"
        :label="demand.prioridade"
        color="primary"
        text-color="white"
        size="sm"
        icon="priority_high"
        class="text-weight-bold"
        dense
      >
        <q-tooltip>Prioridade</q-tooltip>
      </q-chip>

      <!-- Department -->
      <q-chip
        v-if="demand.department_obj"
        :label="demand.department_obj.name"
        color="secondary"
        text-color="white"
        size="sm"
        icon="groups"
        dense
      >
        <q-tooltip>Setor</q-tooltip>
      </q-chip>

      <!-- Returned Flag -->
      <q-chip
        v-if="demand.flag_returned"
        label="Retornado"
        color="orange"
        text-color="white"
        size="sm"
        icon="replay"
        dense
      >
        <q-tooltip>Esta demanda foi marcada como "retorno"</q-tooltip>
      </q-chip>

      <!-- Billed Flag -->
      <q-chip
        v-if="demand.cobrada_do_cliente"
        label="Cobrada"
        color="green"
        text-color="white"
        size="sm"
        icon="attach_money"
        dense
      >
        <q-tooltip>Esta demanda foi cobrada do cliente</q-tooltip>
      </q-chip>
    </q-card-section>

    <!-- Time tracking -->
    <q-card-section class="q-pt-none text-caption text-grey-8">
      <div class="row justify-between">
        <div>
          <q-icon name="timer" class="q-mr-xs" />
          Estimado: <strong>{{ demand.tempo_estimado }}h</strong>
        </div>
        <div>
          <q-icon name="hourglass_top" class="q-mr-xs" />
          Gasto: <strong>{{ demand.tempo_gasto }}h</strong>
        </div>
      </div>
    </q-card-section>

    <q-separator />

    <!-- Expansion -->
    <q-card-actions align="right" class="q-py-none">
      <q-btn
        flat
        round
        dense
        :icon="isExpanded ? 'expand_less' : 'expand_more'"
        @click="isExpanded = !isExpanded"
      />
    </q-card-actions>

    <q-slide-transition>
      <div v-show="isExpanded">
        <q-separator />
        <q-card-section class="q-pt-sm text-body2">
          <div class="text-weight-medium q-mb-xs">Título Completo:</div>
          <p class="q-pl-sm">{{ demand.titulo || 'N/A' }}</p>

          <div class="text-weight-medium q-mb-xs">Descrição Detalhada:</div>
          <p class="q-pl-sm">{{ demand.descricao_detalhada || 'N/A' }}</p>

          <div class="text-weight-medium q-mb-xs">Quem Deve Testar:</div>
          <p class="q-pl-sm">{{ demand.quem_deve_testar || 'N/A' }}</p>

          <div class="text-caption text-grey q-mt-md">
            Cadastrado em: {{ new Date(demand.data_cadastro).toLocaleString() }}
          </div>
        </q-card-section>
      </div>
    </q-slide-transition>
  </q-card>
</template>

<script setup lang="ts">
import { ref, computed, type PropType } from 'vue';
import type { Demand, Client } from './models';
import { useKanbanStore } from 'src/stores/kanban';

defineEmits(['edit', 'delete']);

const props = defineProps({
  demand: {
    type: Object as PropType<Demand>,
    required: true,
  },
});

const isExpanded = ref(false);
const kanbanStore = useKanbanStore();

const client = computed<Client | undefined>(() => {
  // The store needs to be loaded with clients for this to work.
  // In the context of KanbanPage, it will be.
  return kanbanStore.getClientById(props.demand.cliente);
});
</script>

<style lang="scss" scoped>
.demand-card {
  cursor: grab;
  user-select: none; /* Prevent text selection while dragging */
}
</style>
