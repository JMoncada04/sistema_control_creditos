<template>
  <AppLayout title="Ventas">

    <v-row class="mb-4" align="center">
      <v-col>
        <v-text-field
          v-model="search"
          prepend-icon="mdi-magnify"
          placeholder="Buscar por cliente..."
          variant="outlined" density="comfortable" hide-details
          style="max-width:340px;"
          @update:model-value="buscar"
        />
      </v-col>
      <v-col class="text-right">
        <v-btn color="indigo" variant="flat" prepend-icon="mdi-cart-plus" rounded="lg" @click="dialogs.crear = true">
          Nueva venta
        </v-btn>
      </v-col>
    </v-row>

    <v-card class="modern-card" variant="flat">
      <v-table>
        <thead>
          <tr>
            <th>#</th><th>Cliente</th><th>Fecha</th><th>Ítems</th>
            <th>Total bruto</th><th>Interés</th><th>Total c/interés</th>
            <th>Plazo</th><th>Estado</th><th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="v in ventas.data" :key="v.id">
            <td class="mono" style="font-size:13px;">#{{ v.id }}</td>
            <td style="font-size:13px;font-weight:500;">{{ v.cliente }}</td>
            <td style="font-size:13px;">{{ v.fecha }}</td>
            <td style="font-size:13px;">{{ v.productos }}</td>
            <td class="mono" style="font-size:13px;">L. {{ Number(v.total_bruto).toLocaleString('es-HN') }}</td>
            <td style="font-size:13px;color:#f59e0b;">{{ v.porcentaje }}%</td>
            <td class="mono" style="font-size:13px;font-weight:700;color:#4f46e5;">
              L. {{ Number(v.total_con_interes).toLocaleString('es-HN') }}
            </td>
            <td style="font-size:13px;">{{ v.plazo_meses }} meses</td>
            <td>
              <v-chip
                :color="v.estado === 'Al día' ? 'success' : v.estado === 'Mora' ? 'error' : 'primary'"
                size="small" variant="tonal"
              >
                {{ v.estado }}
              </v-chip>
            </td>
            <td>
              <v-btn
                icon="mdi-calendar-check-outline"
                size="small" variant="text" color="indigo"
                @click="verCuotas(v.id)"
              />
            </td>
          </tr>
          <tr v-if="!ventas.data.length">
            <td colspan="10" style="text-align:center;color:#94a3b8;padding:24px;font-size:13px;">
              No se encontraron ventas.
            </td>
          </tr>
        </tbody>
      </v-table>
    </v-card>

    <div class="d-flex justify-end mt-3">
      <v-pagination
        v-if="ventas.last_page > 1"
        :model-value="ventas.current_page"
        :length="ventas.last_page"
        density="compact"
        @update:model-value="paginar"
      />
    </div>

    <v-dialog v-model="dialogs.crear" max-width="800" persistent>
      <v-card rounded="lg" class="modern-card">
        <v-card-title class="pa-5" style="font-size:17px;font-weight:600;border-bottom:1px solid rgba(0,0,0,0.08);">
          <v-icon class="mr-2" color="indigo">mdi-cart-outline</v-icon>Registrar nueva venta
        </v-card-title>
        <v-card-text class="pa-5">

          <div class="section-label mb-2">1. Seleccionar cliente</div>
          <v-autocomplete
            v-model="form.cliente_id"
            :items="clientes.map(c => ({ title: c.nombre + ' — ' + c.identidad, value: c.id }))"
            label="Buscar cliente"
            variant="outlined" density="comfortable"
            prepend-icon="mdi-account-search-outline"
            class="mb-5"
            :error-messages="errors.cliente_id"
          />

          <div class="section-label mb-2">2. Agregar productos</div>
          <v-row align="center" class="mb-3">
            <v-col cols="5">
              <v-autocomplete
                v-model="itemTemp.producto_id"
                :items="productos.map(p => ({ title: p.nombre + ' (Stock: ' + p.stock_actual + ')', value: p.id }))"
                label="Producto"
                variant="outlined" density="comfortable" hide-details
                prepend-icon="mdi-package-variant-outline"
              />
            </v-col>
            <v-col cols="3">
              <v-text-field
                v-model="itemTemp.cantidad"
                label="Cantidad" type="number" min="1"
                variant="outlined" density="comfortable" hide-details
                prepend-icon="mdi-numeric"
              />
            </v-col>
            <v-col cols="4">
              <v-btn color="indigo" variant="tonal" prepend-icon="mdi-plus" rounded="lg" block @click="agregarItem">
                Agregar
              </v-btn>
            </v-col>
          </v-row>

          <v-alert v-if="stockError" type="warning" variant="tonal" rounded="sm"
            density="compact" closable class="mb-3"
            @click:close="stockError = ''">
            {{ stockError }}
          </v-alert>

          <v-table v-if="form.items.length" density="compact" class="mb-5"
            style="border:1px solid rgba(0,0,0,0.08);border-radius:8px;">
            <thead>
              <tr><th>Producto</th><th>Cant.</th><th>P. Unitario</th><th>Subtotal</th><th></th></tr>
            </thead>
            <tbody>
              <tr v-for="(it, i) in form.items" :key="i">
                <td style="font-size:13px;">{{ it.nombre }}</td>
                <td class="mono" style="font-size:13px;">{{ it.cantidad }}</td>
                <td class="mono" style="font-size:13px;">L. {{ Number(it.precio).toLocaleString('es-HN') }}</td>
                <td class="mono" style="font-size:13px;font-weight:600;">
                  L. {{ Number(it.cantidad * it.precio).toLocaleString('es-HN') }}
                </td>
                <td>
                  <v-btn icon="mdi-close" size="x-small" variant="text" color="error" @click="form.items.splice(i, 1)" />
                </td>
              </tr>
            </tbody>
          </v-table>

          <div class="section-label mb-2">3. Plazo de pago</div>
          <v-row align="start">
            <v-col cols="12" sm="5">
              <v-select
                v-model="form.plazo_meses"
                :items="tasas.map(t => ({ title: t.plazo_meses + ' meses — ' + t.porcentaje + '% interés', value: t.plazo_meses }))"
                label="Plazo"
                variant="outlined" density="comfortable"
                prepend-icon="mdi-calendar-month-outline"
                :error-messages="errors.plazo_meses"
              />
            </v-col>
            <v-col cols="12" sm="7">
              <v-card variant="tonal" color="indigo" rounded="lg" class="pa-3" style="font-size:13px;">
                <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                  <span>Total bruto:</span>
                  <strong class="mono">L. {{ totalBruto.toLocaleString('es-HN') }}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:4px;color:#818cf8;">
                  <span>Interés ({{ tasaActual }}%):</span>
                  <strong class="mono">L. {{ interes.toLocaleString('es-HN') }}</strong>
                </div>
                <v-divider class="my-2" />
                <div style="display:flex;justify-content:space-between;font-weight:700;font-size:15px;">
                  <span>Total a pagar:</span>
                  <strong class="mono">L. {{ totalConInteres.toLocaleString('es-HN') }}</strong>
                </div>
                <div style="color:#818cf8;font-size:12px;margin-top:4px;">
                  Cuota mensual: L. {{ cuotaMensual.toLocaleString('es-HN') }}
                </div>
              </v-card>
            </v-col>
          </v-row>

        </v-card-text>
        <v-card-actions class="pa-4 pt-0">
          <v-spacer />
          <v-btn variant="outlined" rounded="lg" @click="cerrarCrear">Cancelar</v-btn>
          <v-btn color="indigo" variant="flat" rounded="lg" prepend-icon="mdi-check-circle" :loading="loading" @click="guardarVenta">
            Confirmar y generar plan de cuotas
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  ventas:    { type: Object, required: true },
  clientes:  { type: Array,  default: () => [] },
  productos: { type: Array,  default: () => [] },
  tasas:     { type: Array,  default: () => [] },
  filters:   { type: Object, default: () => ({}) },
})

const search  = ref(props.filters.search ?? '')
const loading = ref(false)
const errors  = ref({})
const dialogs = reactive({ crear: false })

const form = reactive({ cliente_id: null, plazo_meses: null, items: [] })
const itemTemp   = reactive({ producto_id: null, cantidad: 1 })
const stockError = ref('')

const buscar  = () => router.get(route('ventas.index'), { search: search.value }, { preserveState: true, replace: true })
const paginar = (p) => router.get(route('ventas.index'), { search: search.value, page: p }, { preserveState: true })
const verCuotas = (id) => router.get(route('cuotas.index'), { search: '#' + id })

const totalBruto = computed(() =>
  form.items.reduce((s, i) => s + i.cantidad * i.precio, 0)
)
const tasaActual = computed(() => {
  const t = props.tasas.find(t => t.plazo_meses === form.plazo_meses)
  return t ? t.porcentaje : 0
})
const interes = computed(() => Math.round(totalBruto.value * tasaActual.value / 100))
const totalConInteres = computed(() => totalBruto.value + interes.value)
const cuotaMensual = computed(() =>
  form.plazo_meses ? Math.round(totalConInteres.value / form.plazo_meses) : 0
)

const agregarItem = () => {
  if (!itemTemp.producto_id) return
  const p = props.productos.find(p => p.id === itemTemp.producto_id)
  if (!p) return
  const existe         = form.items.find(i => i.producto_id === p.id)
  const enCarrito      = existe ? existe.cantidad : 0
  const totalSolicitado = enCarrito + Number(itemTemp.cantidad)
  if (totalSolicitado > p.stock_actual) {
    stockError.value = `Stock insuficiente para "${p.nombre}". Disponible: ${p.stock_actual} unidad(es)${enCarrito ? ` (${enCarrito} ya en carrito)` : ''}.`
    return
  }
  stockError.value = ''
  if (existe) { existe.cantidad += Number(itemTemp.cantidad) }
  else { form.items.push({ producto_id: p.id, nombre: p.nombre, precio: p.precio_venta, cantidad: Number(itemTemp.cantidad) }) }
  itemTemp.producto_id = null
  itemTemp.cantidad = 1
}

const cerrarCrear = () => {
  dialogs.crear = false
  Object.assign(form, { cliente_id: null, plazo_meses: null, items: [] })
  errors.value = {}
  stockError.value = ''
}

const guardarVenta = () => {
  loading.value = true
  router.post(route('ventas.store'), {
    cliente_id:  form.cliente_id,
    plazo_meses: form.plazo_meses,
    items: form.items.map(i => ({ producto_id: i.producto_id, cantidad: i.cantidad })),
  }, {
    onSuccess: () => cerrarCrear(),
    onError: (e) => { errors.value = e },
    onFinish: () => { loading.value = false },
  })
}
</script>
