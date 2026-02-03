<template>
  <q-page class="q-pa-md column no-wrap overflow-hidden" style="height: 100vh;">
    <div class="row items-center q-mb-md">
      <q-btn flat round icon="arrow_back" @click="router.back()" />
      <div class="text-h5 q-ml-sm">Demand Report: {{ route.query.month }}</div>
    </div>

    <div class="row q-col-gutter-md q-mb-md" v-if="reportData">
      <div class="col-12 col-md-3">
        <q-card dark class="bg-primary text-white">
          <q-card-section>
            <div class="text-subtitle2">Total Demands</div>
            <div class="text-h4">{{ reportData.stats.total }}</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-3">
        <q-card dark class="bg-green text-white">
          <q-card-section>
            <div class="text-subtitle2">Billed Demands</div>
            <div class="text-h4">{{ reportData.stats.billed }}</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-6">
        <q-card flat bordered>
          <q-card-section>
            <div class="text-subtitle2">By Status</div>
            <div class="row q-gutter-sm q-mt-xs">
              <q-chip v-for="(count, status) in reportData.stats.by_status" :key="status" outline color="primary" dense>
                <strong>{{ status }}:</strong> {{ count }}
              </q-chip>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <q-table
      class="col"
      :rows="reportData?.demands || []"
      :columns="columns"
      row-key="id"
      :loading="loading"
      flat
      bordered
      virtual-scroll
      :rows-per-page-options="[0]"
    />

    <div class="q-mt-md">
      <q-input
        v-model="observationText"
        type="textarea"
        outlined
        label="Internal Notes"
        placeholder="Space for notes..."
        dense
      />
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { api } from 'boot/axios';
import type { QTableColumn } from 'quasar';

const route = useRoute();
const router = useRouter();
const loading = ref(false);
const reportData = ref<any>(null);
const observationText = ref('');

const columns: QTableColumn[] = [
  { name: 'titulo', label: 'Title', field: 'titulo', align: 'left', sortable: true },
  { name: 'status', label: 'Status', field: 'status', align: 'center', sortable: true },
  { name: 'tempo_estimado', label: 'Est. Time (h)', field: 'tempo_estimado', align: 'right', sortable: true },
  { name: 'tempo_gasto', label: 'Spent Time (h)', field: 'tempo_gasto', align: 'right', sortable: true },
];

onMounted(async () => {
  loading.value = true;
  try {
    const response = await api.get('/reports/clients', {
      params: {
        ids: route.query.ids,
        month: route.query.month
      }
    });
    reportData.value = response.data;
  } catch (error) {
    console.error('Failed to load report:', error);
  } finally {
    loading.value = false;
  }
});
</script>