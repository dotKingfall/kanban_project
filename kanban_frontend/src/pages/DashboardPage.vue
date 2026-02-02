<template>
  <q-page padding>
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
import { computed, onMounted } from 'vue';
import { useAuthStore } from 'stores/auth';
import { useRouter } from 'vue-router';
import ClientsTable from 'src/components/ClientsTable.vue';

const authStore = useAuthStore();
const router = useRouter();
const user = computed(() => authStore.user);


onMounted(async () => {
  // If user is not loaded, try to fetch. If fetch fails, redirect to login.
  if (!user.value) {
    try {
      await authStore.fetchUser();
    } catch {
      await router.push('/login');
    }
  }
});
</script>

<style lang="scss">
.q-table tbody td.cursor-pointer:hover {
  background-color: rgba(0, 0, 0, 0.03);
}
</style>
