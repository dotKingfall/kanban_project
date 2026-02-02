<template>
  <q-page padding>
    <div v-if="loading" class="flex flex-center absolute-center">
      <q-spinner size="3em" color="primary" />
    </div>

    <div v-else-if="client">
      <div class="row items-center q-mb-md">
        <q-btn flat round icon="arrow_back" color="primary" @click="router.back()" class="q-mr-sm" />
        <div class="text-h4">{{ client.nome }}</div>
      </div>

      <q-card flat bordered>
        <q-card-section>
          <div class="text-h6 q-mb-md">Demands</div>
          <div v-if="client.demands && client.demands.length > 0">
            <q-list separator>
              <q-item v-for="demand in client.demands" :key="demand.id">
                <q-item-section>
                  <q-item-label>{{ demand.titulo }}</q-item-label>
                  <q-item-label caption>{{ demand.status }} - {{ demand.prioridade }}</q-item-label>
                </q-item-section>
              </q-item>
            </q-list>
          </div>
          <div v-else class="text-grey q-pa-md text-center">
            No demands found for this client.
          </div>
        </q-card-section>
      </q-card>
    </div>

    <div v-else class="flex flex-center absolute-center text-negative">
      Client not found.
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useRouter } from 'vue-router';
import { api } from 'boot/axios';
import type { Client } from 'src/components/models';

// Extend Client interface locally to include demands if not already present in models
interface ClientWithDemands extends Client {
  demands?: any[];
}

const route = useRoute();
const router = useRouter();
const client = ref<ClientWithDemands | null>(null);
const loading = ref(true);

onMounted(async () => {
  try {
    const response = await api.get('/clients', { params: { client_id: route.params.clientId } });
    client.value = response.data;
  } catch (error) {
    console.error('Failed to fetch client', error);
  } finally {
    loading.value = false;
  }
});
</script>
