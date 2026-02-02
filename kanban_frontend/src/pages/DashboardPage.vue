<template>
  <q-page padding>
    <div v-if="user">
      <q-table
        title="Clientes"
        :rows="clients"
        :columns="columns"
        row-key="id"
        :loading="loading"
      >
        <!-- Custom slot for 'contato' -->
        <template v-slot:body-cell-contato="props">
          <q-td :props="props">
            <div>{{ props.row.email }}</div>
            <div class="text-grey-8">{{ props.row.whatsapp }}</div>
          </q-td>
        </template>

        <!-- Custom slot for 'notificar_por' -->
        <template v-slot:body-cell-notificar_por="props">
          <q-td :props="props">
            <q-chip v-if="props.row.avisar_por_email" dense color="blue-2" text-color="primary" icon="email">
              Email
            </q-chip>
            <q-chip v-if="props.row.avisar_por_whatsapp" dense color="green-2" text-color="positive" icon="whatsapp">
              WhatsApp
            </q-chip>
          </q-td>
        </template>

        <!-- Custom slot for 'observacao' -->
        <template v-slot:body-cell-observacao="props">
          <q-td :props="props" class="cursor-pointer" @click="showObservation(props.row.observacao)">
            <div class="ellipsis" style="max-width: 250px;">{{ props.row.observacao || '-' }}</div>
            <q-tooltip v-if="props.row.observacao">Clique para ver a observação completa</q-tooltip>
          </q-td>
        </template>

        <!-- Custom slot for 'actions' -->
        <template v-slot:body-cell-actions="props">
          <q-td :props="props">
            <q-btn flat round icon="more_vert">
              <q-menu>
                <q-list style="min-width: 100px">
                  <q-item clickable v-close-popup @click="editClient(props.row)">
                    <q-item-section>Editar</q-item-section>
                  </q-item>
                  <q-item clickable v-close-popup @click="confirmDelete(props.row)">
                    <q-item-section class="text-negative">Deletar</q-item-section>
                  </q-item>
                </q-list>
              </q-menu>
            </q-btn>
          </q-td>
        </template>
      </q-table>

      <!-- Observation Dialog -->
      <q-dialog v-model="observationDialog">
        <q-card style="min-width: 350px">
          <q-card-section>
            <div class="text-h6">Observação</div>
          </q-card-section>
          <q-card-section class="q-pt-none">
            {{ selectedObservation }}
          </q-card-section>
          <q-card-actions align="right">
            <q-btn flat label="Fechar" color="primary" v-close-popup />
          </q-card-actions>
        </q-card>
      </q-dialog>
    </div>
    <div v-else class="flex flex-center column q-gutter-md absolute-center">
      <q-spinner size="3em" color="primary" />
      <div class="q-mt-sm">Verificando autenticação...</div>
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useAuthStore } from 'stores/auth';
import { useRouter } from 'vue-router';
import { api } from 'boot/axios';
import type { QTableColumn } from 'quasar';
import { useQuasar } from 'quasar';
import type { Client } from 'src/components/models';

const authStore = useAuthStore();
const router = useRouter();
const $q = useQuasar();
const user = computed(() => authStore.user);

const clients = ref<Client[]>([]);
const loading = ref(false);
const observationDialog = ref(false);
const selectedObservation = ref('');

const columns: QTableColumn[] = [
  { name: 'nome', required: true, label: 'Nome', align: 'left', field: 'nome', sortable: true },
  { name: 'contato', label: 'Contato', align: 'left', field: 'email' },
  { name: 'notificar_por', label: 'Notificar Por', align: 'left', field: 'id' },
  { name: 'observacao', label: 'Observação', align: 'left', field: 'observacao' },
  { name: 'actions', label: '', align: 'right', field: 'id' },
];

const fetchClients = async () => {
  loading.value = true;
  try {
    const response = await api.get<Client[]>('/clients');
    clients.value = response.data;
  } catch (error) {
    console.error('Error fetching clients:', error);
    $q.notify({
      color: 'negative',
      message: 'Erro ao carregar clientes',
      icon: 'report_problem'
    });
  } finally {
    loading.value = false;
  }
};

const showObservation = (obs: string | null) => {
  if (!obs) return;
  selectedObservation.value = obs;
  observationDialog.value = true;
};

const editClient = (client: Client) => {
  // Logic to open edit form will go here
  console.log('Edit client', client);
};

const confirmDelete = (client: Client) => {
  $q.dialog({
    title: 'Confirmar Exclusão',
    message: `Tem certeza que deseja excluir o cliente "${client.nome}"?`,
    cancel: true,
    persistent: true,
    ok: { label: 'Excluir', color: 'negative' }
  }).onOk(() => {
    void (async () => {
      try {
        await api.delete('/clients', { data: { ids: [client.id] } });
        await fetchClients();
      } catch (error) {
        console.error('Delete failed', error);
      }
    })();
  });
};

onMounted(async () => {
  // If user is not loaded, try to fetch. If fetch fails, redirect to login.
  if (!user.value) {
    try {
      await authStore.fetchUser();
    } catch {
      await router.push('/login');
    }
  }

  if (user.value) {
    await fetchClients();
  }
});
</script>

<style lang="scss">
.q-table tbody td.cursor-pointer:hover {
  background-color: rgba(0, 0, 0, 0.03);
}
</style>
