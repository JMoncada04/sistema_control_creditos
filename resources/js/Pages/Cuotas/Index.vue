<template>
  <AppLayout title="Plan de cuotas">

    <div class="d-flex align-center justify-space-between mb-5 flex-wrap gap-3">
      <div class="d-flex align-center gap-3 flex-wrap">
        <v-text-field v-model="search" prepend-icon="mdi-magnify"
          placeholder="Buscar por cliente o venta (#1001)..."
          variant="outlined" density="comfortable" hide-details style="min-width:300px;"
          @update:model-value="buscar" />
        <v-chip-group v-model="filtroEstado" mandatory @update:model-value="buscar">
          <v-chip v-for="f in estados" :key="f" :value="f"
            variant="outlined" size="small" rounded="lg">{{ f }}</v-chip>
        </v-chip-group>
      </div>
      <div style="font-size:12px;color:#94a3b8;">
        <v-icon size="14" class="mr-1">mdi-information-outline</v-icon>
        Puedes pagar de más — se aplica a cuotas siguientes
      </div>
    </div>

    <v-card class="modern-card" variant="flat">
      <v-table>
        <thead>
          <tr>
            <th>Venta</th><th>Cliente</th><th>Cuota</th><th>Vencimiento</th>
            <th>Monto cuota</th><th>Mora</th><th>Total a pagar</th>
            <th>Pagado</th><th>Tipo pago</th><th>Estado</th><th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="c in cuotas.data" :key="c.id"
            :style="rowStyle(c)">
            <td class="mono" style="font-size:12px;color:#6366f1;">#{{ c.venta_id }}</td>
            <td style="font-size:13px;font-weight:500;">
              <div class="d-flex align-center gap-2">
                <v-avatar :color="c.estado==='Mora'?'error':'indigo'" size="26">
                  <span style="font-size:10px;color:#fff;font-weight:700;">{{ initials(c.cliente) }}</span>
                </v-avatar>
                {{ c.cliente }}
              </div>
            </td>
            <td style="font-size:12px;">{{ c.num }}/{{ c.total_cuotas }}</td>
            <td style="font-size:12px;">{{ c.vencimiento }}</td>
            <td class="mono" style="font-size:12px;">L. {{ Number(c.monto_cuota).toLocaleString('es-HN') }}</td>
            <td class="mono" style="font-size:12px;" :style="c.recargo_mora > 0 ? 'color:#ef4444;font-weight:600;' : 'color:#94a3b8;'">
              {{ c.recargo_mora > 0 ? 'L. ' + Number(c.recargo_mora).toLocaleString('es-HN') : '—' }}
            </td>
            <td class="mono" style="font-size:13px;font-weight:700;">
              L. {{ Number(c.total_a_pagar).toLocaleString('es-HN') }}
            </td>
            <td class="mono" style="font-size:12px;color:#22c55e;">
              {{ c.monto_pagado > 0 ? 'L. ' + Number(c.monto_pagado).toLocaleString('es-HN') : '—' }}
            </td>
            <td>
              <span v-if="c.tipo_pago === 'anticipado'" class="badge-anticipado">Anticipado</span>
              <span v-else-if="c.tipo_pago === 'parcial'" class="badge-parcial">Parcial</span>
              <span v-else-if="c.tipo_pago === 'completo'" class="badge-pagada">Completo</span>
              <span v-else style="color:#94a3b8;font-size:12px;">—</span>
            </td>
            <td>
              <span :class="'badge-' + c.estado.toLowerCase()">{{ c.estado }}</span>
            </td>
            <td>
              <v-btn v-if="c.estado !== 'Pagada'" size="small" color="success" variant="tonal"
                prepend-icon="mdi-cash" rounded @click="abrirPago(c)">
                Pagar
              </v-btn>
              <v-icon v-else color="success" size="20">mdi-check-circle</v-icon>
            </td>
          </tr>
          <tr v-if="!cuotas.data.length && !hasFilter">
            <td colspan="11" style="text-align:center;color:#94a3b8;padding:44px 24px;font-size:13px;">
              <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                <v-icon size="36" color="#cbd5e1">mdi-filter-search-outline</v-icon>
                Usa el buscador o selecciona un estado para ver las cuotas
              </div>
            </td>
          </tr>
          <tr v-else-if="!cuotas.data.length">
            <td colspan="11" style="text-align:center;color:#94a3b8;padding:32px;font-size:13px;">
              No se encontraron cuotas con ese criterio.
            </td>
          </tr>
        </tbody>
      </v-table>
    </v-card>

    <div class="d-flex justify-end mt-3">
      <v-pagination v-if="cuotas.last_page > 1"
        :model-value="cuotas.current_page" :length="cuotas.last_page"
        density="compact" @update:model-value="paginar" />
    </div>

    <v-dialog v-model="dialogs.pago" max-width="480">
      <v-card class="modern-card" variant="flat" v-if="cuotaActiva">

        <div style="background:linear-gradient(135deg,#0a1628,#1e3a5f);padding:20px 24px;border-radius:16px 16px 0 0;">
          <div class="d-flex align-center gap-3">
            <div style="width:40px;height:40px;background:rgba(34,197,94,0.2);border-radius:10px;display:flex;align-items:center;justify-content:center;">
              <v-icon color="success" size="22">mdi-cash-register</v-icon>
            </div>
            <div>
              <div style="font-size:16px;font-weight:700;color:#f8fafc;">Registrar Pago</div>
              <div style="font-size:12px;color:#64748b;">{{ cuotaActiva.cliente }} · Venta #{{ cuotaActiva.venta_id }}</div>
            </div>
          </div>
        </div>

        <v-card-text class="pa-6">
          <div class="cuota-info-box mb-4">
            <div class="info-row">
              <span>Cuota N°</span>
              <strong>{{ cuotaActiva.num }} / {{ cuotaActiva.total_cuotas }}</strong>
            </div>
            <div class="info-row">
              <span>Vencimiento</span>
              <strong>{{ cuotaActiva.vencimiento }}</strong>
            </div>
            <div class="info-row">
              <span>Monto cuota</span>
              <strong class="mono">L. {{ Number(cuotaActiva.monto_cuota).toLocaleString('es-HN') }}</strong>
            </div>
            <div v-if="cuotaActiva.recargo_mora > 0" class="info-row" style="color:#ef4444;">
              <span>Recargo mora</span>
              <strong class="mono">L. {{ Number(cuotaActiva.recargo_mora).toLocaleString('es-HN') }}</strong>
            </div>
            <div class="info-row total-row">
              <span>Total a cobrar</span>
              <strong class="mono" style="font-size:16px;">L. {{ Number(cuotaActiva.total_a_pagar).toLocaleString('es-HN') }}</strong>
            </div>
          </div>


          <div style="font-size:12px;color:#64748b;font-weight:600;margin-bottom:8px;">Método de pago</div>
          <div class="d-flex gap-3 mb-4">
            <div v-for="m in metodosPago" :key="m.value"
              @click="metodoPago = m.value"
              :style="`
                flex:1; border:2px solid ${metodoPago === m.value ? m.color : '#e2e8f0'};
                border-radius:10px; padding:10px 8px; cursor:pointer; text-align:center;
                background:${metodoPago === m.value ? m.bg : '#fff'};
                transition:all 0.15s;
              `">
              <v-icon :color="metodoPago === m.value ? m.color : '#94a3b8'" size="22">{{ m.icon }}</v-icon>
              <div :style="`font-size:12px;font-weight:600;margin-top:4px;color:${metodoPago === m.value ? m.color : '#94a3b8'}`">
                {{ m.label }}
              </div>
            </div>
          </div>

          <transition name="slide-down">
            <div v-if="metodoPago === 'tarjeta'" class="tarjeta-form mb-4">
              <div class="tarjeta-preview mb-3">
                <div style="font-size:11px;opacity:0.7;letter-spacing:1px;">NÚMERO DE TARJETA</div>
                <div style="font-size:18px;font-weight:700;letter-spacing:3px;margin:4px 0;">
                  {{ tarjeta.numero ? tarjeta.numero.replace(/(.{4})/g,'$1 ').trim() : '•••• •••• •••• ••••' }}
                </div>
                <div class="d-flex justify-space-between mt-2">
                  <div>
                    <div style="font-size:10px;opacity:0.7;">TITULAR</div>
                    <div style="font-size:13px;font-weight:600;">{{ tarjeta.titular || '———' }}</div>
                  </div>
                  <div>
                    <div style="font-size:10px;opacity:0.7;">EXPIRA</div>
                    <div style="font-size:13px;font-weight:600;">{{ tarjeta.expiracion || 'MM/AA' }}</div>
                  </div>
                </div>
              </div>
              <v-row dense>
                <v-col cols="12">
                  <v-text-field v-model="tarjeta.numero" label="Número de tarjeta"
                    variant="outlined" density="comfortable"
                    prepend-icon="mdi-credit-card-outline"
                    maxlength="16" placeholder="1234567890123456"
                    @keypress="soloNumerosKey"
                    :error-messages="errors.numero_tarjeta" />
                </v-col>
                <v-col cols="12">
                  <v-text-field v-model="tarjeta.titular" label="Nombre del titular"
                    variant="outlined" density="comfortable"
                    prepend-icon="mdi-account-outline"
                    placeholder="Como aparece en la tarjeta"
                    @keypress="soloLetrasKey"
                    :error-messages="errors.titular_tarjeta" />
                </v-col>
                <v-col cols="6">
                  <v-text-field v-model="tarjeta.expiracion" label="Fecha expiración"
                    variant="outlined" density="comfortable"
                    prepend-icon="mdi-calendar-outline"
                    placeholder="MM/AA" maxlength="5"
                    @input="formatearExpiracion"
                    :error-messages="errors.expiracion_tarjeta" />
                </v-col>
                <v-col cols="6">
                  <v-text-field v-model="tarjeta.cvv" label="CVV"
                    variant="outlined" density="comfortable"
                    prepend-icon="mdi-lock-outline"
                    :type="mostrarCvv ? 'text' : 'password'"
                    :append-inner-icon="mostrarCvv ? 'mdi-eye-off' : 'mdi-eye'"
                    @click:append-inner="mostrarCvv = !mostrarCvv"
                    @keypress="soloNumerosKey"
                    maxlength="3" placeholder="•••"
                    :error-messages="errors.cvv_tarjeta" />
                </v-col>
              </v-row>
            </div>
          </transition>

          <v-text-field v-model="montoPago"
            label="Monto recibido (L.)"
            type="number"
            variant="outlined" density="comfortable"
            prepend-icon="mdi-currency-usd"
            :hint="cuotaActiva.total_deuda_venta > cuotaActiva.total_a_pagar
              ? 'Máx: L. ' + Number(cuotaActiva.total_deuda_venta).toLocaleString('es-HN') + ' (deuda total venta)'
              : 'Máx: L. ' + Number(cuotaActiva.total_a_pagar).toLocaleString('es-HN')"
            persistent-hint
            :error-messages="errors.monto_recibido"
            @input="calcularPreview"
            class="mb-3"
          />

          <transition name="slide-down">
            <div v-if="preview" class="preview-box" :class="preview.tipo">
              <div class="preview-icon">
                <v-icon size="18" :color="preview.color">{{ preview.icon }}</v-icon>
              </div>
              <div style="flex:1;">
                <div style="font-size:13px;font-weight:600;" :style="`color:${preview.textColor}`">
                  {{ preview.titulo }}
                </div>
                <div style="font-size:11px;color:#64748b;margin-top:2px;line-height:1.5;">
                  {{ preview.detalle }}
                </div>
              </div>
            </div>
          </transition>
        </v-card-text>

        <v-card-actions class="pa-4 pt-0">
          <v-spacer />
          <v-btn variant="text" @click="dialogs.pago = false">Cancelar</v-btn>
          <v-btn color="indigo"  variant="flat" rounded="lg" prepend-icon="mdi-check"
            :loading="loading" @click="confirmarPago">
            Confirmar pago
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
  cuotas:  { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const search       = ref(props.filters.search ?? '')
const filtroEstado = ref(props.filters.estado ?? 'Todos')
const loading      = ref(false)
const errors       = ref({})
const dialogs      = reactive({ pago: false })
const cuotaActiva  = ref(null)
const montoPago    = ref(0)
const metodoPago   = ref('efectivo')
const preview      = ref(null)
const mostrarCvv   = ref(false)
const tarjeta      = reactive({ numero: '', titular: '', expiracion: '', cvv: '' })

const metodosPago = [
  { value: 'efectivo', label: 'Efectivo', icon: 'mdi-cash',        color: '#22c55e', bg: '#f0fdf4' },
  { value: 'tarjeta',  label: 'Tarjeta',  icon: 'mdi-credit-card', color: '#3b82f6', bg: '#eff6ff' },
]

const estados    = ['Todos', 'Pendiente', 'Pagada', 'Mora']
const initials   = (n) => n?.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase() ?? 'XX'
const hasFilter  = computed(() => search.value.trim() !== '' || filtroEstado.value !== 'Todos')

const rowStyle = (c) => {
  if (c.estado === 'Mora')   return 'background:rgba(239,68,68,0.03);'
  if (c.estado === 'Pagada') return 'background:rgba(34,197,94,0.03);'
  if (c.tipo_pago === 'parcial') return 'background:rgba(139,92,246,0.03);'
  return ''
}

const buscar  = () => router.get(route('cuotas.index'), { search: search.value, estado: filtroEstado.value }, { preserveState: true, replace: true })
const paginar = (p) => router.get(route('cuotas.index'), { search: search.value, estado: filtroEstado.value, page: p }, { preserveState: true })

const abrirPago = (c) => {
  cuotaActiva.value = c
  montoPago.value   = c.total_a_pagar
  metodoPago.value  = 'efectivo'
  errors.value      = {}
  preview.value     = null
  mostrarCvv.value  = false
  Object.assign(tarjeta, { numero: '', titular: '', expiracion: '', cvv: '' })
  dialogs.pago      = true
  setTimeout(() => calcularPreview(), 50)
}

const calcularPreview = () => {
  if (!cuotaActiva.value) return

  const deudaTotal = parseFloat(cuotaActiva.value.total_deuda_venta) || parseFloat(cuotaActiva.value.total_a_pagar) || 0
  let monto = parseFloat(montoPago.value) || 0

  if (monto > deudaTotal) {
    montoPago.value = deudaTotal
    monto = deudaTotal
  }

  const totalCuota = parseFloat(cuotaActiva.value.total_a_pagar) || 0

  if (monto <= 0) { preview.value = null; return }

  if (monto < totalCuota) {
    const saldo = (totalCuota - monto).toFixed(2)
    preview.value = {
      tipo: 'parcial', color: 'purple', textColor: '#7c3aed',
      icon: 'mdi-arrow-down-circle',
      titulo: `Pago parcial — saldo restante en esta cuota: L. ${Number(saldo).toLocaleString('es-HN')}`,
      detalle: 'La cuota quedará con saldo pendiente y se actualizará el monto a pagar.',
    }
  } else if (Math.abs(monto - totalCuota) < 0.01) {
    preview.value = {
      tipo: 'exacto', color: 'success', textColor: '#16a34a',
      icon: 'mdi-check-circle',
      titulo: 'Pago exacto — cuota completada',
      detalle: 'Esta cuota quedará marcada como Pagada completamente.',
    }
  } else {
    const excedente = (monto - totalCuota).toFixed(2)
    preview.value = {
      tipo: 'excedente', color: 'info', textColor: '#1d4ed8',
      icon: 'mdi-lightning-bolt-circle',
      titulo: `Cubre esta cuota + L. ${Number(excedente).toLocaleString('es-HN')} hacia las siguientes`,
      detalle: `El excedente se aplica a las cuotas siguientes. Deuda total de la venta: L. ${Number(deudaTotal).toLocaleString('es-HN')}`,
    }
  }
}

const formatearExpiracion = () => {
  let v = tarjeta.expiracion.replace(/\D/g, '').slice(0, 4)
  if (v.length >= 3) v = v.slice(0, 2) + '/' + v.slice(2)
  tarjeta.expiracion = v
}

const soloNumerosKey = (e) => {
  if (!/\d/.test(e.key)) e.preventDefault()
}
const soloLetrasKey = (e) => {
  if (!/[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]/.test(e.key)) e.preventDefault()
}

const validarTarjeta = () => {
  const errs = {}
  if (!tarjeta.numero || !/^\d{16}$/.test(tarjeta.numero))
    errs.numero_tarjeta = 'Debe tener exactamente 16 dígitos numéricos'
  if (!tarjeta.titular || !/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]+$/.test(tarjeta.titular.trim()))
    errs.titular_tarjeta = 'Solo se permiten letras y espacios, sin números ni caracteres especiales'
  const partes = tarjeta.expiracion.split('/')
  if (partes.length !== 2 || !/^\d{2}$/.test(partes[0]) || !/^\d{2}$/.test(partes[1])) {
    errs.expiracion_tarjeta = 'Formato inválido. Use MM/AA (ej: 05/27)'
  } else {
    const mes = parseInt(partes[0])
    const anio = 2000 + parseInt(partes[1])
    if (mes < 1 || mes > 12) {
      errs.expiracion_tarjeta = 'El mes debe ser entre 01 y 12'
    } else {
      const fechaExpiracion = new Date(anio, mes, 0)
      fechaExpiracion.setHours(23, 59, 59, 999)
      if (fechaExpiracion < new Date()) {
        errs.expiracion_tarjeta = `Tarjeta vencida. Venció en ${partes[0]}/${partes[1]} — no se puede usar.`
      }
    }
  }
  if (!tarjeta.cvv || !/^\d{3}$/.test(tarjeta.cvv))
    errs.cvv_tarjeta = 'El CVV debe tener exactamente 3 dígitos'
  return errs
}

const confirmarPago = () => {
  errors.value = {}
  if (metodoPago.value === 'tarjeta') {
    const errs = validarTarjeta()
    if (Object.keys(errs).length > 0) {
      errors.value = errs
      return
    }
  }
  loading.value = true
  const payload = { monto_recibido: montoPago.value, metodo_pago: metodoPago.value }
  if (metodoPago.value === 'tarjeta') {
    Object.assign(payload, { numero_tarjeta: tarjeta.numero, titular_tarjeta: tarjeta.titular, expiracion_tarjeta: tarjeta.expiracion, cvv_tarjeta: tarjeta.cvv })
  }
  router.post(route('cuotas.pagar', cuotaActiva.value.id), payload, {
    onSuccess: () => { dialogs.pago = false },
    onError: (e) => { errors.value = e },
    onFinish: () => { loading.value = false },
  })
}
</script>

<style scoped>
.cuota-info-box {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 16px;
}
.info-row {
  display: flex; justify-content: space-between;
  font-size: 13px; padding: 4px 0;
  color: #64748b;
}
.info-row span { color: #94a3b8; }
.total-row {
  margin-top: 8px; padding-top: 10px;
  border-top: 1px solid #e2e8f0;
  font-size: 14px; font-weight: 700;
  color: #0f172a;
}
.total-row span { color: #0f172a; }

.preview-box {
  display: flex; gap: 12px; align-items: flex-start;
  border-radius: 12px; padding: 14px;
  border: 1px solid;
}
.preview-icon {
  width:32px; height:32px; border-radius:8px;
  display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.preview-box.parcial   { background:#f5f3ff; border-color:#ddd6fe; }
.preview-box.parcial .preview-icon { background:#ede9fe; }
.preview-box.exacto    { background:#f0fdf4; border-color:#bbf7d0; }
.preview-box.exacto .preview-icon { background:#dcfce7; }
.preview-box.excedente { background:#eff6ff; border-color:#bfdbfe; }
.preview-box.excedente .preview-icon { background:#dbeafe; }

.slide-down-enter-active { transition: all 0.2s ease; }
.slide-down-enter-from   { opacity:0; transform:translateY(-6px); }

.tarjeta-form { }
.tarjeta-preview {
  background: linear-gradient(135deg, #1e3a5f, #3b82f6);
  border-radius: 12px;
  padding: 16px;
  color: #fff;
}
</style>
