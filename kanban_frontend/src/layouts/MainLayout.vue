<template>
  <q-layout view="lHh Lpr lFf">
    <!-- 4. Sidebar: Only visible if logged in -->
    <q-drawer
      v-if="authStore.user"
      v-model="uiStore.isSidebarOpen"
      show-if-above
      bordered
      :mini="uiStore.isSidebarMini"
      :mini-width="70"
      :width="250"
    >
      <div class="column full-height">
        <q-list padding class="col">
          <!-- Collapse Toggle -->
          <q-item clickable v-ripple @click="uiStore.toggleMini">
            <q-item-section avatar>
              <component :is="uiStore.isSidebarMini ? ChevronRight : ChevronLeft" />
            </q-item-section>
            <q-item-section>Retrair</q-item-section>
          </q-item>

          <q-separator class="q-my-sm" />

          <!-- Navigation -->
          <q-item clickable v-ripple to="/dashboard" active-class="text-primary bg-blue-1">
            <q-item-section avatar>
              <LayoutDashboard />
            </q-item-section>

            <q-item-section>
              Clientes
              <!-- Small label below icon in mini mode is tricky in standard Quasar, 
                   standard behavior is hiding text. We use tooltip for mini mode UX -->
              <q-tooltip v-if="uiStore.isSidebarMini" anchor="center right" self="center left" :offset="[10, 10]">
                Clientes
              </q-tooltip>
            </q-item-section>
          </q-item>

          <q-item clickable v-ripple to="/all-demands" active-class="text-primary bg-blue-1">
            <q-item-section avatar>
              <MessageCircleWarning />
            </q-item-section>

            <q-item-section>
              Todas as Demandas
              <!-- Small label below icon in mini mode is tricky in standard Quasar, 
                   standard behavior is hiding text. We use tooltip for mini mode UX -->
              <q-tooltip v-if="uiStore.isSidebarMini" anchor="center right" self="center left" :offset="[10, 10]">
                Todas as Demandas
              </q-tooltip>
            </q-item-section>
          </q-item>

          <q-item clickable v-ripple to="/reports" active-class="text-primary bg-blue-1">
            <q-item-section avatar>
              <Mail />
            </q-item-section>

            <q-item-section>
              Relatórios
              <!-- Small label below icon in mini mode is tricky in standard Quasar, 
                   standard behavior is hiding text. We use tooltip for mini mode UX -->
              <q-tooltip v-if="uiStore.isSidebarMini" anchor="center right" self="center left" :offset="[10, 10]">
                Relatórios
              </q-tooltip>
            </q-item-section>
          </q-item>
        </q-list>

        <q-list padding>
          <q-separator class="q-my-sm" />

          <!-- User Info -->
          <q-item class="q-mb-sm">
            <q-item-section avatar>
              <q-avatar color="primary" text-color="white">
                {{ authStore.user.first_name.charAt(0).toUpperCase() }}
              </q-avatar>
            </q-item-section>
            <q-item-section>
              <q-item-label class="text-weight-bold">{{ authStore.user.first_name }} {{ authStore.user.last_name }}</q-item-label>
              <q-item-label caption>{{ authStore.user.email }}</q-item-label>
            </q-item-section>
          </q-item>

          <q-item clickable v-ripple @click="handleLogout">
            <q-item-section avatar>
              <LogOut :size="20" />
            </q-item-section>
            <q-item-section>
              Logout
              <q-tooltip v-if="uiStore.isSidebarMini" anchor="center right" self="center left" :offset="[10, 10]">
                Logout
              </q-tooltip>
            </q-item-section>
          </q-item>
        </q-list>
      </div>
    </q-drawer>

    <q-page-container>
      <router-view v-slot="{ Component }">
        <transition
          name="slide-left"
          mode="out-in"
        >
          <component :is="Component" />
        </transition>
      </router-view>
    </q-page-container>
  </q-layout>
</template>

<script setup lang="ts">
import { useAuthStore } from 'stores/auth';
import { useUiStore } from 'stores/ui';
import { useRouter } from 'vue-router';
import { LogOut, ChevronLeft, ChevronRight, LayoutDashboard, Mail, MessageCircleWarning } from 'lucide-vue-next';

const authStore = useAuthStore();
const uiStore = useUiStore();
const router = useRouter();

const handleLogout = async () => {
  try {
    await authStore.logout();
  } catch (error) {
    console.error('Logout failed', error);
  } finally {
    await router.push('/');
  }
};
</script>

<style>
.slide-left-enter-active,
.slide-left-leave-active {
  transition: transform 0.15s ease, opacity 0.1s ease;
}

.slide-left-enter-from {
  transform: translateX(100%);
  opacity: 0;
}

.slide-left-leave-to {
  transform: translateX(-100%);
  opacity: 0;
}

.q-page-container {
  overflow-x: hidden;
}
</style>
