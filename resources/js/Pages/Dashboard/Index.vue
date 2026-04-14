<template>
  <AppLayout title="Dashboard">

    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h2 style="font-size:22px;font-weight:700;letter-spacing:-0.4px;">Resumen del negocio</h2>
        <p style="font-size:13px;color:#64748b;margin-top:2px;">Actualizado al momento · vista ejecutiva</p>
      </div>
      <v-btn color="primary" variant="flat" rounded="lg" prepend-icon="mdi-file-pdf-box"
        @click="exportarPDF" :loading="loadingPdf">
        Exportar PDF
      </v-btn>
    </div>

    <v-row class="mb-6">
      <v-col cols="12" sm="6" lg="3" v-for="s in statCards" :key="s.label">
        <v-card class="stat-card-modern pa-5" variant="flat" :style="`border-left:3px solid ${s.color} !important;`">
          <div class="d-flex align-center justify-space-between mb-3">
            <div class="stat-icon-wrap" :style="`background:${s.bg}`">
              <v-icon :color="s.color" size="22">{{ s.icon }}</v-icon>
            </div>
            <span style="font-size:10px;background:#f1f5f9;color:#64748b;padding:3px 8px;border-radius:99px;font-weight:600;">
              {{ s.period }}
            </span>
          </div>
          <div class="stat-label-text">{{ s.label }}</div>
          <div class="stat-value-text" :class="s.money ? 'money' : ''" :style="`color:${s.color}`">
            {{ s.money ? 'L. ' + Number(s.value).toLocaleString('es-HN') : s.value }}
          </div>
        </v-card>
      </v-col>
    </v-row>

    <v-row class="mb-6">
      <v-col cols="12" md="8">
        <v-card class="modern-card" variant="flat">
          <div class="modern-card-header">
            <div>
              <div class="modern-card-title">Cobros mensuales</div>
              <div class="modern-card-sub">Últimos 6 meses (L. hondureños)</div>
            </div>
            <v-icon color="primary" size="20">mdi-chart-bar</v-icon>
          </div>
          <div class="pa-5">
            <canvas ref="barCanvas" height="200"></canvas>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" md="4">
        <v-card class="modern-card" variant="flat" style="height:100%;">
          <div class="modern-card-header">
            <div>
              <div class="modern-card-title">Estado de cuotas</div>
              <div class="modern-card-sub">Distribución actual</div>
            </div>
            <v-icon color="indigo" size="20">mdi-chart-donut</v-icon>
          </div>
          <div class="pa-5 d-flex flex-column align-center">
            <canvas ref="donutCanvas" height="200" style="max-width:220px;"></canvas>
            <div class="mt-4 w-100">
              <div v-for="d in distEstados" :key="d.estado"
                class="d-flex align-center justify-space-between mb-2">
                <div class="d-flex align-center gap-2">
                  <span class="legend-dot" :style="`background:${estadoColor(d.estado)}`"></span>
                  <span style="font-size:12px;color:#64748b;">{{ d.estado }}</span>
                </div>
                <strong style="font-size:13px;">{{ d.total }}</strong>
              </div>
            </div>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <v-row class="mb-6">
      <v-col cols="12" md="12">
        <v-card class="modern-card" variant="flat">
          <div class="modern-card-header">
            <div>
              <div class="modern-card-title">Cuotas próximas a vencer</div>
              <div class="modern-card-sub">Próximos 7 días</div>
            </div>
            <v-chip color="warning" variant="tonal" size="small">
              {{ proximasCuotas.length }} pendientes
            </v-chip>
          </div>
          <v-table density="compact">
            <thead>
              <tr>
                <th>Cliente</th><th>Venta</th><th>Cuota</th>
                <th>Vencimiento</th><th>Monto</th><th>Estado</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="c in proximasCuotas" :key="c.id">
                <td style="font-size:13px;font-weight:500;">{{ c.cliente }}</td>
                <td class="mono" style="font-size:12px;">#{{ c.venta }}</td>
                <td style="font-size:12px;">{{ c.cuota }}</td>
                <td style="font-size:12px;">{{ c.fecha }}</td>
                <td class="mono" style="font-size:13px;font-weight:600;">L. {{ Number(c.monto).toLocaleString('es-HN') }}</td>
                <td><span :class="'badge-' + c.estado.toLowerCase()">{{ c.estado }}</span></td>
              </tr>
              <tr v-if="!proximasCuotas.length">
                <td colspan="6" style="text-align:center;color:#94a3b8;padding:24px;font-size:13px;">
                  Sin cuotas próximas a vencer.
                </td>
              </tr>
            </tbody>
          </v-table>
        </v-card>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12" md="7">
        <v-card class="modern-card" variant="flat">
          <div class="modern-card-header">
            <div class="modern-card-title">Últimas ventas registradas</div>
            <v-icon color="primary" size="20">mdi-cart-check</v-icon>
          </div>
          <v-table density="compact" style="max-height: 500px">
            <thead>
              <tr><th>#</th><th>Cliente</th><th>Fecha</th><th>Total</th><th>Progreso</th></tr>
            </thead>
            <tbody>
              <tr v-for="v in ultimasVentas" :key="v.id">
                <td class="mono" style="font-size:12px;color:#6366f1;">#{{ v.id }}</td>
                <td style="font-size:13px;font-weight:500;">{{ v.cliente }}</td>
                <td style="font-size:12px;color:#64748b;">{{ v.fecha }}</td>
                <td class="mono" style="font-size:13px;font-weight:700;">L. {{ Number(v.total).toLocaleString('es-HN') }}</td>
                <td style="min-width:130px;">
                  <div class="d-flex align-center gap-2">
                    <div class="progress-track" style="flex:1;">
                      <div class="progress-fill" :style="{ width: v.progreso + '%' }" />
                    </div>
                    <span style="font-size:11px;color:#94a3b8;min-width:30px;">{{ v.progreso }}%</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </v-table>
        </v-card>
      </v-col>

      <v-col cols="12" md="5">
        <v-card class="modern-card" variant="flat">
          <div class="modern-card-header">
            <div class="modern-card-title">Productos stock bajo</div>
            <v-icon color="warning" size="20">mdi-package-variant-minus</v-icon>
          </div>
          <div class="pa-4">
            <div v-if="!productosStockBajo.length" style="color:#94a3b8;font-size:13px;">Todos con stock suficiente.</div>
            <div v-for="p in productosStockBajo" :key="p.id" class="d-flex align-center gap-3 mb-3">
              <v-icon :color="p.stock <= p.minimo ? 'error' : 'warning'" size="18">mdi-package-variant</v-icon>
              <div style="flex:1;">
                <div style="font-size:12px;font-weight:500;">{{ p.nombre }}</div>
                <div class="progress-track mt-1">
                  <div class="progress-fill"
                    :style="`width:${Math.min(p.stock/p.max*100,100)}%;background:${p.stock<=p.minimo?'#ef4444':'#f59e0b'}`" />
                </div>
              </div>
              <span :style="`font-size:12px;font-weight:600;color:${p.stock<=p.minimo?'#ef4444':'#f59e0b'}`">
                {{ p.stock }} uds
              </span>
            </div>
          </div>
        </v-card>
      </v-col>
    </v-row>

  </AppLayout>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  stats:               { type: Object, required: true },
  cobradoPorMes:       { type: Array,  default: () => [] },
  distEstados:         { type: Array,  default: () => [] },
  proximasCuotas:      { type: Array,  default: () => [] },
  productosStockBajo:  { type: Array,  default: () => [] },
  ultimasVentas:       { type: Array,  default: () => [] },
})

const barCanvas   = ref(null)
const donutCanvas = ref(null)
const loadingPdf  = ref(false)

const statCards = [
  { label: 'Créditos activos',    icon: 'mdi-credit-card-multiple', color: '#6366f1', bg: '#eef2ff', value: props.stats.creditosActivos,  period: 'Activos',        money: false },
  { label: 'Cobrado este mes',    icon: 'mdi-cash-multiple',         color: '#22c55e', bg: '#f0fdf4', value: props.stats.cobradoEsteMes,   period: 'Este mes',       money: true  },
  { label: 'Cuotas pendientes',   icon: 'mdi-calendar-clock',        color: '#f59e0b', bg: '#fffbeb', value: props.stats.cuotasPendientes, period: '30 días',        money: false },
  { label: 'Clientes en mora',    icon: 'mdi-account-alert',         color: '#ef4444', bg: '#fef2f2', value: props.stats.clientesEnMora,   period: 'Con retraso',    money: false },
]

const estadoColor = (e) => ({ Pagada: '#22c55e', Pendiente: '#f59e0b', Mora: '#ef4444' }[e] ?? '#94a3b8')

const exportarPDF = () => {
  loadingPdf.value = true
  window.open(route('dashboard.pdf'), '_blank')
  setTimeout(() => { loadingPdf.value = false }, 2000)
}

onMounted(async () => {
  await nextTick()
  const script = document.createElement('script')
  script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js'
  script.onload = () => {
    renderBarChart()
    renderDonutChart()
  }
  document.head.appendChild(script)
})

function renderBarChart() {
  if (!barCanvas.value || !window.Chart) return
  const labels = props.cobradoPorMes.map(m => m.mes)
  const data   = props.cobradoPorMes.map(m => m.cobrado)
  new window.Chart(barCanvas.value, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Cobrado (L.)',
        data,
        backgroundColor: data.map((_, i) => i === data.length - 1 ? '#3b82f6' : '#c7d2fe'),
        borderRadius: 8,
        borderSkipped: false,
      }],
    },
    options: {
      responsive: true,
      animation: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: (ctx) => ' L. ' + Number(ctx.parsed.y).toLocaleString('es-HN'),
          },
        },
      },
      scales: {
        x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#94a3b8' } },
        y: {
          grid: { color: 'rgba(0,0,0,0.04)' },
          ticks: { font: { size: 11 }, color: '#94a3b8',
            callback: (v) => 'L. ' + Number(v).toLocaleString('es-HN') },
        },
      },
    },
  })
}

function renderDonutChart() {
  if (!donutCanvas.value || !window.Chart) return
  const labels = props.distEstados.map(d => d.estado)
  const data   = props.distEstados.map(d => d.total)
  new window.Chart(donutCanvas.value, {
    type: 'doughnut',
    data: {
      labels,
      datasets: [{
        data,
        backgroundColor: ['#22c55e', '#f59e0b', '#ef4444'],
        borderWidth: 3,
        borderColor: '#ffffff',
        hoverBorderColor: '#ffffff',
      }],
    },
    options: {
      responsive: true,
      animation: false,
      cutout: '70%',
      plugins: {
        legend: { display: false },
        tooltip: { callbacks: { label: (ctx) => ` ${ctx.label}: ${ctx.parsed}` } },
      },
    },
  })
}
</script>

<style scoped>
.legend-dot { width:10px; height:10px; border-radius:50%; display:inline-block; flex-shrink:0; }
</style>
