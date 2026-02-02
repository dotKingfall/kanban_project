<template>
  <div>
    <q-table
      title="Clients"
      :rows="clients"
      :columns="columns"
      row-key="id"
      :loading="loading"
      flat
      bordered
    >
      <template v-slot:top-right>
        <q-btn
          flat
          round
          color="primary"
          icon="refresh"
          @click="fetchClients"
        >
          <q-tooltip>Refresh</q-tooltip>
        </q-btn>

        <q-btn
          flat
          round
          color="primary"
          icon="add"
          @click="openCreateDialog"
        >
          <q-tooltip>Create new</q-tooltip>
        </q-btn>
      </template>

      <template v-slot:body-cell-observacao="props">
        <q-td :props="props" style="max-width: 200px; cursor: pointer" @click="openObservationDialog(props.value)">
          <div class="ellipsis">
            {{ props.value }}
          </div>
          <q-tooltip v-if="props.value">Click to view full observation</q-tooltip>
        </q-td>
      </template>

      <template v-slot:body-cell-avisar_por_email="props">
        <q-td :props="props">
          <q-icon :name="props.value ? 'check_circle' : 'cancel'" :color="props.value ? 'positive' : 'grey'" size="sm" />
        </q-td>
      </template>

      <template v-slot:body-cell-avisar_por_whatsapp="props">
        <q-td :props="props">
          <q-icon :name="props.value ? 'check_circle' : 'cancel'" :color="props.value ? 'positive' : 'grey'" size="sm" />
        </q-td>
      </template>

      <template v-slot:body-cell-actions="props">
        <q-td :props="props" align="right">
          <q-btn flat round color="primary" icon="edit" size="sm" @click="openEditDialog(props.row)">
            <q-tooltip>Edit</q-tooltip>
          </q-btn>
          <q-btn flat round color="negative" icon="delete" size="sm" @click="confirmDelete(props.row)">
            <q-tooltip>Delete</q-tooltip>
          </q-btn>
        </q-td>
      </template>
    </q-table>

    <!-- Edit Dialog -->
    <q-dialog v-model="showEditDialog">
      <q-card style="min-width: 500px">
        <q-card-section>
          <div class="text-h6">{{ editingClient.id ? 'Edit Client' : 'New Client' }}</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-form @submit="saveClient" class="q-gutter-md">
            <q-input
              v-model="editingClient.nome"
              label="Name"
              :rules="[val => !!val || 'Name is required']"
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
                  :rules="[val => !!val || !!editingClient.whatsapp || 'At least one contact method is required']"
                />
              </div>
              <div class="col-12 col-md-6">
                <q-input 
                  v-model="editingClient.whatsapp" 
                  label="WhatsApp" 
                  outlined 
                  dense 
                  :rules="[val => !!val || !!editingClient.email || 'At least one contact method is required']"
                />
              </div>
            </div>

            <q-input
              v-model="editingClient.observacao"
              label="Observation"
              type="textarea"
              outlined
              dense
              autogrow
            />

            <div class="row q-col-gutter-md">
              <div class="col-6">
                <q-toggle v-model="editingClient.avisar_por_email" label="Notify by Email" />
              </div>
              <div class="col-6">
                <q-toggle v-model="editingClient.avisar_por_whatsapp" label="Notify by WhatsApp" />
              </div>
            </div>

            <div align="right" class="q-mt-md">
              <q-btn flat label="Cancel" color="primary" v-close-popup />
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
          <div class="text-h6">Observation</div>
        </q-card-section>

        <q-card-section class="q-pt-none" style="white-space: pre-wrap;">
          {{ selectedObservation }}
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat label="Close" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { api } from 'boot/axios';
import { useAuthStore } from 'stores/auth';
import { useQuasar, type QTableColumn } from 'quasar';
import type { Client } from 'src/components/models';

const $q = useQuasar();
const authStore = useAuthStore();
const clients = ref<Client[]>([]);
const loading = ref(false);
const showEditDialog = ref(false);
const editingClient = ref<Client>({} as Client);
const isSaving = ref(false);
const showObservationDialog = ref(false);
const selectedObservation = ref('');

const columns: QTableColumn[] = [
  { name: 'nome', label: 'Name', field: 'nome', sortable: true, align: 'left' },
  { name: 'email', label: 'Email', field: 'email', sortable: true, align: 'left' },
  { name: 'observacao', label: 'Observation', field: 'observacao', align: 'left' },
  { name: 'whatsapp', label: 'WhatsApp', field: 'whatsapp', align: 'left' },
  { name: 'avisar_por_email', label: 'Email Notify', field: 'avisar_por_email', align: 'center' },
  { name: 'avisar_por_whatsapp', label: 'WA Notify', field: 'avisar_por_whatsapp', align: 'center' },
  { name: 'actions', label: 'Actions', field: 'actions', align: 'right' },
];

const fetchClients = async () => {
  loading.value = true;
  try {
    const response = await api.get('/clients');
    clients.value = response.data;
  } catch (error) {
    console.error(error);
    $q.notify({ type: 'negative', message: 'Failed to load clients' });
  } finally {
    loading.value = false;
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
  if (isSaving.value) return; // Prevent double submission
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
    await fetchClients(); // Refresh the client list
  } catch (error) {
    console.error(error);
    $q.notify({ type: 'negative', message: 'Failed to save client' });
  } finally {
    isSaving.value = false; // Reset the saving state
  }
};

const confirmDelete = (client: Client) => {
  $q.dialog({
    title: 'Confirm Deletion',
    message: `Are you sure you want to delete ${client.nome}?`,
    cancel: true,
    persistent: true
  }).onOk(async () => {
    try {
      // Using destroyMany as per controller definition, passing array of IDs
      await api.delete('/clients', { data: { ids: [client.id] } });
      $q.notify({ type: 'positive', message: 'Client deleted' });
      fetchClients();
    } catch (error) {
      console.error(error);
      $q.notify({ type: 'negative', message: 'Failed to delete client' });
    }
  });
};

onMounted(fetchClients);
</script>
