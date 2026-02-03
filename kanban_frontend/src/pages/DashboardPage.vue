<template>
  <q-page>
    <div v-if="user">
      <clients-table />
    </div>
    <div v-else class="flex flex-center column q-gutter-md absolute-center">
      <q-spinner size="3em" color="primary" />
      <div class="q-mt-sm">Verificando autenticação...</div>
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useAuthStore } from 'stores/auth';
import ClientsTable from 'src/components/ClientsTable.vue';

const authStore = useAuthStore();
const user = computed(() => authStore.user);
</script>

<style lang="scss">
.q-table tbody td.cursor-pointer:hover {
  background-color: rgba(0, 0, 0, 0.03);
}
</style>
