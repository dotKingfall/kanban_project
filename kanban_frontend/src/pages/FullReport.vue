<template>
  <q-page class="q-pa-md column no-wrap overflow-hidden bg-white" id="printable-area">
    <div class="row items-center q-mb-md no-print">
      <q-btn flat round icon="arrow_back" color="primary" @click="router.back()" />
      <div class="text-h5 q-ml-sm">Demand Report: {{ reportStore.formattedMonth }}</div>
      <q-space />
      <q-btn color="primary" icon="print" label="Print PDF" @click="printPDF" />
    </div>

    <div class="row q-col-gutter-md q-mb-md" v-if="clientData">
      <div class="col-12 col-sm-2">
        <q-card dark class="bg-primary text-white shadow-2 dashboard-card">
          <q-card-section class="flex flex-center column full-height">
            <div class="text-overline">Total</div>
            <div class="text-h4 text-weight-bolder">{{ clientData.stats.total }}</div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-2">
        <q-card dark class="bg-green-7 text-white shadow-2 dashboard-card">
          <q-card-section class="flex flex-center column full-height">
            <div class="text-overline">Billed</div>
            <div class="text-h4 text-weight-bolder">{{ clientData.stats.billed }}</div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-8">
        <q-card flat bordered class="shadow-1 dashboard-card">
          <q-card-section class="full-height column justify-center">
            <div class="text-subtitle2 q-mb-md">Status Overview</div>
            <div class="row items-center q-gutter-md wrap">
              <q-chip 
                v-for="(count, status) in clientData.stats.by_status" 
                :key="status" 
                outline 
                :color="count > 0 ? 'primary' : 'grey-5'" 
                dense
                class="q-ma-none status-chip"
              >
                <span :class="count > 0 ? 'text-weight-bold' : ''">{{ status }}:</span> 
                <span class="q-ml-xs">{{ count }}</span>
              </q-chip>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <q-table
      title="Demand Report"
      class="col report-table"
      :rows="clientData?.demands || []"
      :columns="columns"
      row-key="id"
      flat bordered
      :rows-per-page-options="[0]"
      hide-bottom
    >
      <template v-slot:body-cell-billed="props">
        <q-td :props="props">
          <q-chip v-if="props.row.cobrada_do_cliente" color="green-1" text-color="green-9" icon="check_circle" label="Billed" size="sm" dense class="text-weight-bold" />
          <q-icon v-else name="history" color="grey-4" size="xs" />
        </q-td>
      </template>

      <template v-slot:bottom-row v-if="clientData">
        <q-tr class="bg-grey-1 text-weight-bold">
          <q-td colspan="3" class="text-right">Totals:</q-td>
          <q-td class="text-right">{{ clientData.stats.totalEstimated }}h</q-td>
          <q-td class="text-right">{{ clientData.stats.totalSpent }}h</q-td>
        </q-tr>
      </template>
    </q-table>

    <div class="q-mt-md no-print">
      <q-input v-model="observationText" type="textarea" outlined label="Internal Notes" placeholder="Type notes here..." dense />
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import type { QTableColumn } from 'quasar';
import { useReportStore } from 'src/stores/report';
import { useKanbanStore } from 'src/stores/kanban';

const router = useRouter();
const reportStore = useReportStore();
const kanbanStore = useKanbanStore();
const observationText = ref('');

const printPDF = () => { window.print(); };

const columns: QTableColumn[] = [
  { name: 'titulo', label: 'Title', field: 'titulo', align: 'left', sortable: true },
  { name: 'status', label: 'Status', field: 'status', align: 'center', sortable: true },
  { name: 'billed', label: 'Billing Status', field: 'cobrada_do_cliente', align: 'center', sortable: true },
  { name: 'tempo_estimado', label: 'Est. Time (h)', field: 'tempo_estimado', align: 'right', sortable: true },
  { name: 'tempo_gasto', label: 'Spent Time (h)', field: 'tempo_gasto', align: 'right', sortable: true },
];

const clientData = computed(() => {
  if (!reportStore.reportCache) return null;
  const clientId = Number(router.currentRoute.value.query.ids);
  const clientObj = kanbanStore.getClientById(clientId.toString());
  const demands = reportStore.reportCache.demands.filter((d: any) => d.cliente === clientId);
  
  const statusSummary: Record<string, number> = {};
  if (clientObj?.kanban_columns) {
    clientObj.kanban_columns.forEach(col => {
      if (!col.is_hidden) statusSummary[col.name] = 0;
    });
  }

  let totalEstimated = 0;
  let totalSpent = 0;

  demands.forEach((d: any) => {
    if (d.status in statusSummary) statusSummary[d.status] = (statusSummary[d.status] ?? 0) + 1;;
    totalEstimated += Number(d.tempo_estimado || 0);
    totalSpent += Number(d.tempo_gasto || 0);
  });

  return {
    demands,
    stats: {
      total: demands.length,
      billed: demands.filter((d: any) => d.cobrada_do_cliente).length,
      by_status: statusSummary,
      totalEstimated,
      totalSpent
    }
  };
});
</script>

<style lang="scss">
.dashboard-card {
  min-height: 100px; /* Fixed height to ensure 2 rows of tags fit if needed */
  height: 100%;
}

.status-chip {
  margin: 0 !important;
  flex: 0 1 auto; /* Allows chips to maintain their own width */
}

@media print {
  .q-drawer-container, .q-header, .q-footer, .no-print { 
    display: none !important; 
  }

  .q-layout, .q-page-container, .q-page {
    padding: 0 !important;
    margin: 0 !important;
    display: block !important;
    height: auto !important;
    overflow: visible !important;
  }

  #printable-area {
    width: 100%;
    background: white !important;
  }

  .row { 
    display: flex !important; 
    flex-wrap: wrap !important; /* Changed to wrap for chips logic */
  }

  /* Force dashboard cards to keep row structure in print */
  .col-sm-2 { width: 16.66% !important; }
  .col-sm-8 { width: 66.66% !important; }

  /* Keep colors in print */
  .bg-primary { background-color: #1976D2 !important; -webkit-print-color-adjust: exact; }
  .bg-green-7 { background-color: #388E3C !important; -webkit-print-color-adjust: exact; }
  .text-white { color: white !important; -webkit-print-color-adjust: exact; }

  .q-card { 
    border: 1px solid #e0e0e0 !important;
    box-shadow: none !important;
    page-break-inside: avoid;
  }

  .report-table {
    width: 100% !important;
    border: 1px solid #e0e0e0 !important;
    .q-table__top { border-bottom: 1px solid #e0e0e0 !important; }
  }
}
</style>