<template>
  <q-page class="flex flex-center column q-gutter-md">
    <div v-if="user" class="text-center">
      <h4 class="q-mb-sm">Welcome, {{ user.first_name }} {{ user.last_name }}</h4>
      <div class="text-subtitle1 q-mb-md">Email: {{ user.email }}</div>
      
      <q-btn 
        color="negative" 
        label="Logout" 
        @click="handleLogout" 
        icon="logout"
      />
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

const handleLogout = async () => {
  try {
    await authStore.logout();
  } catch (error) {
    console.error('Logout failed:', error);
  } finally {
    // Always redirect to login, even if the API call failed
    await router.push('/login');
  }
};
</script>
