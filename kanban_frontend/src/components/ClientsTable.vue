<template>
  <q-page class="q-pa-md column no-wrap overflow-hidden" style="height: 100vh;">
    <q-table
      title="Clientes"
      :rows="filteredClients"
      :columns="columns"
      row-key="id"
      :loading="loading"
      flat
      bordered
      @row-click="onRowClick"

      virtual-scroll
      class="col" 
      :rows-per-page-options="[0]"
    >
      <template v-slot:top-right>
        <div class="row q-gutter-sm items-center">
          <client-search-bar
            v-model:search="search"
            v-model:searchType="searchType"
          />

        <q-btn
          flat
          round
          color="primary"
          icon="refresh"
          @click="fetchClients"
        >
          <q-tooltip>Recarregar</q-tooltip>
        </q-btn>

        <q-btn
          flat
          round
          color="primary"
          icon="add"
          @click="openCreateDialog"
        >
          <q-tooltip>Criar Novo</q-tooltip>
        </q-btn>
        </div>
      </template>

      <template #body-cell-observacao="props">
        <q-td :props="props" style="max-width: 200px; cursor: pointer" @click.stop="openObservationDialog(props.value)">
          <div class="ellipsis">
            {{ props.value }}
          </div>
          <q-tooltip v-if="props.value">Clique para ver a observação completa.</q-tooltip>
        </q-td>
      </template>

      <template v-slot:body-cell-avisar_por_email="props">
        <q-td :props="props">
          <q-icon :name="props.value ? 'check_circle' : 'cancel'" :color="props.value ? 'primary' : 'grey'" size="sm" />
        </q-td>
      </template>

      <template v-slot:body-cell-avisar_por_whatsapp="props">
        <q-td :props="props">
          <q-icon :name="props.value ? 'check_circle' : 'cancel'" :color="props.value ? 'primary' : 'grey'" size="sm" />
        </q-td>
      </template>

      <template v-slot:body-cell-actions="props">
        <q-td :props="props" align="right">
          <q-btn flat round color="primary" icon="edit" size="sm" @click.stop="openEditDialog(props.row)">
            <q-tooltip>Editar</q-tooltip>
          </q-btn>
          <q-btn flat round color="negative" icon="delete" size="sm" @click.stop="confirmDelete(props.row)">
            <q-tooltip>Excluir</q-tooltip>
          </q-btn>
        </q-td>
      </template>
    </q-table>

    <!-- Edit Dialog -->
    <q-dialog v-model="showEditDialog">
      <q-card style="min-width: 500px">
        <q-card-section>
          <div class="text-h6">{{ editingClient.id ? 'Editar Cliente' : 'Novo Cliente' }}</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-form @submit="saveClient" class="q-gutter-md">
            <q-input
              v-model="editingClient.nome"
              label="Nome"
              :rules="[val => !!val || 'Insira o nome']"
              outlined
              dense
            />
            
            <div class="row q-col-gutter-sm">
              <div class="col-12 col-md-6">
                <q-input 
                  v-model="editingClient.email" 
                  label="Email" 
                  type="email" 
                  outlined 
                  dense 
                  :rules="[val => !!val || !!editingClient.whatsapp || 'Email ou WhatsApp é obrigatório']"
                />
              </div>
              <div class="col-12 col-md-6">
                <q-input 
                  v-model="editingClient.whatsapp" 
                  label="WhatsApp" 
                  outlined 
                  dense 
                  :rules="[val => !!val || !!editingClient.email || 'Email ou WhatsApp é obrigatório']"
                />
              </div>
            </div>

            <q-input
              v-model="editingClient.observacao"
              label="Observações"
              type="textarea"
              outlined
              dense
              height="150px"
            />

            <div class="row q-col-gutter-md">
              <div class="col-6">
                <q-toggle v-model="editingClient.avisar_por_email" label="Notificar por Email" />
              </div>
              <div class="col-6">
                <q-toggle v-model="editingClient.avisar_por_whatsapp" label="Notificar por WhatsApp" />
              </div>
            </div>

            <div align="right" class="q-mt-md">
              <q-btn flat label="Cancelar" color="primary" v-close-popup />
              <q-btn label="Save" type="submit" color="primary" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- Observation View Dialog -->
    <q-dialog v-model="showObservationDialog">
      <q-card style="min-width: 400px">
        <q-card-section>
          <div class="text-h6">Observação</div>
        </q-card-section>

        <q-card-section class="q-pt-none" style="white-space: pre-wrap;">
          {{ selectedObservation }}
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat label="Close" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { api } from 'boot/axios';
import { useRouter } from 'vue-router';
import { useAuthStore } from 'stores/auth';
import { useKanbanStore } from 'stores/kanban';
import { useQuasar, type QTableColumn } from 'quasar';
import ClientSearchBar from 'src/components/ClientSearchBar.vue';
import type { Client } from 'src/components/models';

const $q = useQuasar();
const router = useRouter();
const authStore = useAuthStore();
const kanbanStore = useKanbanStore();
const loading = ref(false);
const showEditDialog = ref(false);
const editingClient = ref<Client>({} as Client);
const isSaving = ref(false);
const showObservationDialog = ref(false);
const selectedObservation = ref('');

const search = ref('');
const searchType = ref('nome');

const filteredClients = computed(() => {
  if (!search.value) return kanbanStore.clients;
  const term = search.value.toLowerCase();
  return kanbanStore.clients.filter((c) => {
    
    const key = searchType.value as keyof typeof c;
    const val = c[key];
    
    return typeof val === 'string' && val.toLowerCase().includes(term);
  });
});

const columns: QTableColumn[] = [
  { name: 'nome', label: 'Nome', field: 'nome', sortable: true, align: 'left' },
  { name: 'email', label: 'Email', field: 'email', sortable: true, align: 'left' },
  { name: 'observacao', label: 'Observação', field: 'observacao', align: 'left' },
  { name: 'whatsapp', label: 'WhatsApp', field: 'whatsapp', align: 'left' },
  { name: 'avisar_por_email', label: 'Notificar por Email', field: 'avisar_por_email', align: 'center' },
  { name: 'avisar_por_whatsapp', label: 'Notificar por WhatsApp', field: 'avisar_por_whatsapp', align: 'center' },
  { name: 'actions', label: 'Ações', field: 'actions', align: 'right' },
];

const fetchClients = async () => {
  loading.value = true;
  try {
    await kanbanStore.fetchClients(true);
  } catch (error) {
    console.error(error);
    $q.notify({ type: 'negative', message: 'Não foi possível carregar clientes' });
  } finally {
    loading.value = false;
  }
};

const onRowClick = async (evt: Event, row: Client) => {
  try {
    await router.push(`/kanban/${row.id}`);
  } catch (error) {
    console.error('Navigation error:', error);
  }
};

const openCreateDialog = () => {
  editingClient.value = {
    id: 0,
    nome: '',
    email: '',
    whatsapp: '',
    observacao: '',
    avisar_por_email: false,
    avisar_por_whatsapp: false,
    reverse_filter: false,
    user_id: authStore.user?.id || 0,
  } as Client;
  showEditDialog.value = true;
};

const openObservationDialog = (text: string) => {
  if (!text) return;
  selectedObservation.value = text;
  showObservationDialog.value = true;
};

const openEditDialog = (client: Client) => {
  editingClient.value = { ...client };
  showEditDialog.value = true;
};

const saveClient = async () => {
  if (isSaving.value) return;
  isSaving.value = true;

  try {
    if (editingClient.value.id) {
      await api.put(`/clients/${editingClient.value.id}`, editingClient.value);
      $q.notify({ type: 'positive', message: 'Client updated successfully' });
    } else {
      await api.post('/clients', editingClient.value);
      $q.notify({ type: 'positive', message: 'Client created successfully' });
    }
    showEditDialog.value = false;
    await kanbanStore.fetchClients(true);
  } catch (error) {
    console.error(error);
    $q.notify({ type: 'negative', message: 'Failed to save client' });
  } finally {
    isSaving.value = false;
  }
};

const confirmDelete = (client: Client) => {
  $q.dialog({
    title: 'Confirmar Exclusão',
    message: `Tem certeza que deseja excluir o cliente ${client.nome}?`,
    cancel: {
      label: 'Cancelar',
      color: 'negative',
    },
    ok: {
      label: 'Excluir',
      color: 'primary',
    },
    persistent: true
  }).onOk(() => {
    void async function deleteClient() {
      try {
        await api.delete(`/clients/${client.id}`);
        $q.notify({ type: 'positive', message: 'Cliente excluído com sucesso' });
        await kanbanStore.fetchClients(true);
      } catch (error) {
        console.error(error);
        $q.notify({ type: 'negative', message: 'Falha ao excluir cliente' });
      }
    }();
  });
};

onMounted(async () => {
  loading.value = true;
  try {
    await kanbanStore.fetchClients();
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
});
</script>
