<template>
  <q-card class="demand-card q-mb-sm" flat bordered>
    <q-card-section>
      <div class="text-body1 text-weight-bold">{{ demand.titulo }}</div>
      <div v-if="demand.responsavel" class="text-caption text-grey">
        <q-icon name="person" class="q-mr-xs" />{{ demand.responsavel }}
      </div>
    </q-card-section>

    <q-card-section class="q-pt-none">
      <q-chip
        v-if="demand.priority_obj"
        :label="demand.priority_obj.name"
        color="primary"
        text-color="white"
        size="sm"
        dense
      />
      <q-chip
        v-if="demand.department_obj"
        :label="demand.department_obj.name"
        color="secondary"
        text-color="white"
        size="sm"
        dense
        class="q-ml-xs"
      />
    </q-card-section>

    <!-- You could add more details here, expandable or otherwise -->

  </q-card>
</template>

<script setup lang="ts">
import type { PropType } from 'vue';
import type { Demand } from './models';

defineProps({
  demand: {
    type: Object as PropType<Demand>,
    required: true,
  },
});
</script>

<style lang="scss" scoped>
.demand-card {
  cursor: grab;
}
</style>
