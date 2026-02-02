<template>
  <q-page class="flex flex-center column q-gutter-md">
    <div v-if="user" class="text-center">
      Clients page
    </div>
    <div v-else>
      <q-spinner size="3em" color="primary" />
      <div class="q-mt-sm">Checking authentication...</div>
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { useAuthStore } from 'stores/auth';
import { useRouter } from 'vue-router';

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
