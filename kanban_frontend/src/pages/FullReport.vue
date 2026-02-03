<template>
  <q-page class="q-pa-md column no-wrap overflow-hidden" style="height: 100vh;">
    <div class="row items-center q-mb-md no-print">
      <q-btn flat round icon="arrow_back" @click="router.back()" />
      <div class="text-h5 q-ml-sm">Demand Report: {{ reportStore.formattedMonth }}</div>
      <q-space />
      <q-btn color="primary" icon="print" label="Print PDF" @click="printPDF" />
    </div>

    <div class="row q-col-gutter-md q-mb-md" v-if="clientData">
      <div class="col-12 col-md-3">
        <q-card dark class="bg-primary text-white">
          <q-card-section>
            <div class="text-subtitle2">Total Demands</div>
            <div class="text-h4">{{ clientData.stats.total }}</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-3">
        <q-card dark class="bg-green text-white">
          <q-card-section>
            <div class="text-subtitle2">Billed Demands</div>
            <div class="text-h4">{{ clientData.stats.billed }}</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-6">
        <q-card flat bordered>
          <q-card-section>
            <div class="text-subtitle2">By Status</div>
            <div class="row q-gutter-sm q-mt-xs">
              <q-chip v-for="(count, status) in clientData.stats.by_status" :key="status" outline color="primary" dense>
                <strong>{{ status }}:</strong> {{ count }}
              </q-chip>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <q-table
      class="col"
      :rows="clientData?.demands || []"
      :columns="columns"
      row-key="id"
      flat
      bordered
      virtual-scroll
      :rows-per-page-options="[0]"
    />

    <div class="q-mt-md no-print">
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
import { ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import type { QTableColumn } from 'quasar';
import { useReportStore } from 'src/stores/report';

const route = useRoute();
const router = useRouter();
const reportStore = useReportStore();
const observationText = ref('');

const printPDF = () => {
  window.print();
};

const columns: QTableColumn[] = [
  { name: 'titulo', label: 'Title', field: 'titulo', align: 'left', sortable: true },
  { name: 'status', label: 'Status', field: 'status', align: 'center', sortable: true },
  { name: 'tempo_estimado', label: 'Est. Time (h)', field: 'tempo_estimado', align: 'right', sortable: true },
  { name: 'tempo_gasto', label: 'Spent Time (h)', field: 'tempo_gasto', align: 'right', sortable: true },
];

/**
 * Filter the global cache for this specific client and 
 * recalculate the stats for just them.
 */
const clientData = computed(() => {
  if (!reportStore.reportCache || !route.query.ids) return null;
  
  const clientId = Number(Array.isArray(route.query.ids) ? route.query.ids[0] : route.query.ids);
  
  // 1. Get demands just for this client
  const demands = reportStore.reportCache.demands.filter((d: any) => d.cliente === clientId);
  
  // 2. Calculate stats for this specific client set
  const billed = demands.filter((d: any) => d.cobrada_do_cliente).length;
  
  const by_status = demands.reduce((acc: any, d: any) => {
    acc[d.status] = (acc[d.status] || 0) + 1;
    return acc;
  }, {});

  return {
    demands,
    stats: {
      total: demands.length,
      billed,
      by_status
    }
  };
});
</script>

<style lang="scss">
@media print {
  .no-print { display: none !important; }
}
</style>