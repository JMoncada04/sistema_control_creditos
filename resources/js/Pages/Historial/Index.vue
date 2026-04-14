<template>
  <AppLayout title="Historial de créditos">

    <v-row class="mb-4" align="center">
      <v-col>
        <v-text-field
          v-model="search"
          prepend-icon="mdi-magnify"
          placeholder="Buscar por cliente, identidad o venta..."
          variant="outlined" density="comfortable" hide-details
          style="max-width:400px;"
          @update:model-value="buscar"
        />
      </v-col>
    </v-row>

    <v-card class="modern-card" variant="flat">
      <v-table>
        <thead>
          <tr>
            <th>N° Identidad</th><th>Cliente</th><th>Venta #</th><th>Fecha</th>
            <th>Total crédito</th><th>Pagado</th><th>Saldo pendiente</th>
            <th>Avance</th><th>Estado</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="h in historial.data" :key="h.id">
            <td class="mono" style="font-size:13px;">{{ h.identidad }}</td>
            <td style="font-size:13px;font-weight:500;">{{ h.cliente }}</td>
            <td class="mono" style="font-size:13px;">#{{ h.venta_id }}</td>
            <td style="font-size:13px;">{{ h.fecha }}</td>
            <td class="mono" style="font-size:13px;font-weight:700;color:#4f46e5;">
              L. {{ Number(h.total).toLocaleString('es-HN') }}
            </td>
            <td class="mono" style="font-size:13px;color:#16a34a;">
              L. {{ Number(h.pagado).toLocaleString('es-HN') }}
            </td>
            <td class="mono" style="font-size:13px;" :style="h.saldo > 0 ? 'color:#dc2626;' : 'color:#16a34a;'">
              L. {{ Number(Math.max(0, h.saldo)).toLocaleString('es-HN') }}
            </td>
            <td style="min-width:130px;">
              <div style="display:flex;align-items:center;gap:8px;">
                <div class="progress-track" style="flex:1;">
                  <div
                    class="progress-fill"
                    :style="{ width: (h.cuotas_pagadas / h.cuotas_total * 100) + '%' }"
                  />
                </div>
                <span style="font-size:12px;color:#64748b;white-space:nowrap;">
                  {{ h.cuotas_pagadas }}/{{ h.cuotas_total }}
                </span>
              </div>
            </td>
            <td>
              <span :class="'badge-' + h.estado.toLowerCase()">{{ h.estado }}</span>
            </td>
          </tr>
          <tr v-if="!historial.data.length">
            <td colspan="9" style="text-align:center;color:#94a3b8;padding:24px;font-size:13px;">
              No se encontraron registros.
            </td>
          </tr>
        </tbody>
      </v-table>
    </v-card>

    <div class="d-flex justify-end mt-3">
      <v-pagination
        v-if="historial.last_page > 1"
        :model-value="historial.current_page"
        :length="historial.last_page"
        density="compact"
        @update:model-value="paginar"
      />
    </div>

  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  historial: { type: Object, required: true },
  filters:   { type: Object, default: () => ({}) },
})

const search = ref(props.filters.search ?? '')

const buscar  = () => router.get(route('historial.index'), { search: search.value }, { preserveState: true, replace: true })
const paginar = (p) => router.get(route('historial.index'), { search: search.value, page: p }, { preserveState: true })
</script>
