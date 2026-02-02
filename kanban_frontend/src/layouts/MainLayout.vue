<template>
  <q-layout view="lHh Lpr lFf">
    <!-- Header: Only visible if user is logged in -->
    <q-header elevated v-if="authStore.user">
      <q-toolbar>

        <q-btn flat round dense @click="handleLogout" class="q-mr-sm">
          <LogOut :size="20" />
          <q-tooltip>Logout</q-tooltip>
        </q-btn>

        <!-- NAME AND EMAIL -->
        <q-toolbar-title>
          {{ authStore.user.first_name }} {{ authStore.user.last_name }}
          <span class="text-subtitle2 text-weight-light">({{ authStore.user.email }})</span>
        </q-toolbar-title>
      </q-toolbar>
    </q-header>

    <!-- 4. Sidebar: Only visible if logged in -->
    <q-drawer
      v-if="authStore.user"
      v-model="uiStore.isSidebarOpen"
      show-if-above
      bordered
      :mini="uiStore.isSidebarMini"
      :width="250"
    >
      <q-list padding>
        <!-- Collapse Toggle -->
        <q-item clickable v-ripple @click="uiStore.toggleMini">
          <q-item-section avatar>
            <component :is="uiStore.isSidebarMini ? ChevronRight : ChevronLeft" />
          </q-item-section>
          <q-item-section>Collapse Sidebar</q-item-section>
        </q-item>

        <q-separator class="q-my-sm" />

        <!-- Navigation -->
        <q-item clickable v-ripple to="/" active-class="text-primary bg-blue-1">
          <q-item-section avatar>
            <LayoutDashboard />
          </q-item-section>
          <q-item-section>
            Dashboard
            <!-- Small label below icon in mini mode is tricky in standard Quasar, 
                 standard behavior is hiding text. We use tooltip for mini mode UX -->
            <q-tooltip v-if="uiStore.isSidebarMini" anchor="center right" self="center left" :offset="[10, 10]">
              Dashboard
            </q-tooltip>
          </q-item-section>
        </q-item>
      </q-list>
    </q-drawer>

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script setup lang="ts">
import { useAuthStore } from 'stores/auth';
import { useUiStore } from 'stores/ui';
import { useRouter } from 'vue-router';
import { LogOut, ChevronLeft, ChevronRight, LayoutDashboard } from 'lucide-vue-next';

const authStore = useAuthStore();
const uiStore = useUiStore();
const router = useRouter();

const handleLogout = async () => {
  try {
    await authStore.logout();
  } catch (error) {
    console.error('Logout failed', error);
  } finally {
    await router.push('/login');
  }
};
</script>
