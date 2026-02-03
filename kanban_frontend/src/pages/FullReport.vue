<template>
  <q-page class="q-pa-md column no-wrap overflow-hidden" style="height: 100vh;">
    <div class="row items-center q-mb-md no-print">
      <q-btn flat round icon="arrow_back" color="primary" @click="router.back()" />
      <div class="text-h5 q-ml-sm">Demand Report: {{ reportStore.formattedMonth }}</div>
      <q-space />
      <q-btn color="primary" icon="print" label="Print PDF" @click="printPDF" />
    </div>

    <div class="row q-col-gutter-md q-mb-md" v-if="clientData">
      <div class="col-12 col-md-3">
        <q-card dark class="bg-primary text-white shadow-2">
          <q-card-section>
            <div class="text-subtitle2">Total Demands</div>
            <div class="text-h4">{{ clientData.stats.total }}</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-3">
        <q-card dark class="bg-green-7 text-white shadow-2">
          <q-card-section>
            <div class="text-subtitle2">Billed Demands</div>
            <div class="text-h4">{{ clientData.stats.billed }}</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-6">
        <q-card flat bordered class="shadow-1">
          <q-card-section>
            <div class="text-subtitle2 q-mb-xs">Status Overview</div>
            <div class="row q-gutter-sm">
              <q-chip 
                v-for="(count, status) in clientData.stats.by_status" 
                :key="status" 
                outline 
                :color="count > 0 ? 'primary' : 'grey-5'" 
                dense
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
      class="col"
      :rows="clientData?.demands || []"
      :columns="columns"
      row-key="id"
      flat
      bordered
      virtual-scroll
      :rows-per-page-options="[0]"
    >
      <template v-slot:body-cell-billed="props">
        <q-td :props="props">
          <q-chip
            v-if="props.row.cobrada_do_cliente"
            color="green-1"
            text-color="green-9"
            icon="check_circle"
            label="Billed"
            size="sm"
            dense
            class="text-weight-bold"
          />
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
      <q-input
        v-model="observationText"
        type="textarea"
        outlined
        label="Internal Notes"
        placeholder="Type notes here..."
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
import { useKanbanStore } from 'src/stores/kanban';

const route = useRoute();
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
  if (!reportStore.reportCache || !route.query.ids) return null;
  
  const clientId = Number(Array.isArray(route.query.ids) ? route.query.ids[0] : route.query.ids);
  const clientObj = kanbanStore.getClientById(clientId.toString());
  const demands = reportStore.reportCache.demands.filter((d: any) => d.cliente === clientId);
  
  // Initialize statuses based on the client's actual board columns
  const statusSummary: Record<string, number> = {};
  if (clientObj?.kanban_columns) {
    clientObj.kanban_columns.forEach(col => {
      if (!col.is_hidden) statusSummary[col.name] = 0;
    });
  }

  // Calculate totals and tally statuses
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
@media print {
  .no-print { display: none !important; }
  .q-page { height: auto !important; padding: 0 !important; }
  .q-table__middle { overflow: visible !important; }
}
</style>