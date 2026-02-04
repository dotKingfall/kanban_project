<template>
  <div class="row q-gutter-sm items-center">
    <q-select
      :model-value="searchType"
      @update:model-value="$emit('update:searchType', $event)"
      :options="searchOptions"
      dense
      outlined
      options-dense
      emit-value
      map-options
      style="min-width: 120px"
    />
    <q-input
      :model-value="search"
      @update:model-value="$emit('update:search', $event as string | null)"
      dense
      outlined
      placeholder="Buscar"
      clearable
    >
      <template v-slot:append>
        <q-icon name="search" />
      </template>
    </q-input>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  search: string | null;
  searchType: string;
}>();

defineEmits<{
  (e: 'update:search', value: string | null): void;
  (e: 'update:searchType', value: string): void;
}>();

const searchOptions = [
  { label: 'Nome', value: 'nome' },
  { label: 'Email', value: 'email' }
];
</script>
