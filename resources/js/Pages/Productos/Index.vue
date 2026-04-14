<template>
  <AppLayout title="Productos">

    <v-row class="mb-4" align="center">
      <v-col>
        <v-text-field
          v-model="search"
          prepend-icon="mdi-magnify"
          placeholder="Buscar por nombre o código..."
          variant="outlined" density="comfortable" hide-details
          style="max-width:340px;"
          @update:model-value="buscar"
        />
      </v-col>
      <v-col class="text-right">
        <v-btn color="indigo" prepend-icon="mdi-plus" rounded="lg" @click="dialogs.crear = true">
          Nuevo producto
        </v-btn>
      </v-col>
    </v-row>

    <v-card class="modern-card" variant="flat">
      <v-table>
        <thead>
          <tr>
            <th>Código</th><th>Producto</th><th>Precio de venta</th>
            <th>Stock actual</th><th>Stock mínimo</th><th>Estado</th><th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in productos.data" :key="p.id">
            <td class="mono" style="font-size:13px;">{{ p.codigo }}</td>
            <td style="font-size:13px;font-weight:500;">{{ p.nombre }}</td>
            <td class="mono" style="font-size:13px;">L. {{ Number(p.precio_venta).toLocaleString('es-HN') }}</td>
            <td>
              <span class="mono" :style="p.stock_bajo ? 'color:#ef4444;font-weight:700;font-size:13px;' : 'font-size:13px;'">
                {{ p.stock_actual }}
              </span>
            </td>
            <td class="mono" style="font-size:13px;">{{ p.stock_minimo }}</td>
            <td>
              <v-chip :color="p.stock_bajo ? 'error' : 'success'" size="small" variant="tonal">
                {{ p.stock_bajo ? 'Stock bajo' : 'Disponible' }}
              </v-chip>
            </td>
            <td>
              <v-btn icon="mdi-pencil-outline" size="small" variant="text" color="primary" @click="editarProducto(p)" />
              <v-btn icon="mdi-delete-outline"  size="small" variant="text" color="error"   @click="confirmarEliminar(p)" />
            </td>
          </tr>
          <tr v-if="!productos.data.length">
            <td colspan="7" style="text-align:center;color:#94a3b8;padding:24px;font-size:13px;">
              No se encontraron productos.
            </td>
          </tr>
        </tbody>
      </v-table>
    </v-card>

    <div class="d-flex justify-end mt-3">
      <v-pagination
        v-if="productos.last_page > 1"
        :model-value="productos.current_page"
        :length="productos.last_page"
        density="compact"
        @update:model-value="paginar"
      />
    </div>

    <!-- Dialog: Crear -->
    <v-dialog v-model="dialogs.crear" max-width="540">
      <v-card rounded="lg" class="modern-card">
        <v-card-title class="pa-5" style="font-size:17px;font-weight:600;border-bottom:1px solid rgba(0,0,0,0.08);">
          <v-icon class="mr-2" color="indigo">mdi-package-variant-plus</v-icon>Registrar producto
        </v-card-title>
        <v-card-text class="pa-5">
          <v-row>
            <v-col cols="12" sm="5">
              <v-text-field v-model="form.codigo" label="Código" variant="outlined" density="comfortable" prepend-icon="mdi-barcode" :error-messages="errors.codigo" />
            </v-col>
            <v-col cols="12" sm="7">
              <v-text-field v-model="form.nombre" label="Nombre del producto" variant="outlined" density="comfortable" prepend-icon="mdi-package-variant" :error-messages="errors.nombre" />
            </v-col>
            <v-col cols="12" sm="4">
              <v-text-field v-model="form.precio_venta" label="Precio (L.)" type="number" variant="outlined" density="comfortable" prepend-icon="mdi-cash" :error-messages="errors.precio_venta" />
            </v-col>
            <v-col cols="12" sm="4">
              <v-text-field v-model="form.stock_actual" label="Stock actual" type="number" variant="outlined" density="comfortable" prepend-icon="mdi-archive-outline" :error-messages="errors.stock_actual" />
            </v-col>
            <v-col cols="12" sm="4">
              <v-text-field v-model="form.stock_minimo" label="Stock mínimo" type="number" variant="outlined" density="comfortable" prepend-icon="mdi-alert-circle-outline" :error-messages="errors.stock_minimo" />
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="pa-4 pt-0">
          <v-spacer />
          <v-btn variant="text" @click="cerrarCrear">Cancelar</v-btn>
          <v-btn color="indigo" variant="flat" rounded="lg" :loading="loading" @click="guardarProducto">Guardar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Dialog: Editar -->
    <v-dialog v-model="dialogs.editar" max-width="480">
      <v-card rounded="lg" class="modern-card">
        <v-card-title class="pa-5" style="font-size:17px;font-weight:600;border-bottom:1px solid rgba(0,0,0,0.08);">
          <v-icon class="mr-2" color="primary">mdi-pencil-outline</v-icon>Editar producto
        </v-card-title>
        <v-card-text class="pa-5">
          <v-row>
            <v-col cols="12">
              <v-text-field v-model="formEditar.nombre"       label="Nombre"        variant="outlined" density="comfortable" prepend-icon="mdi-package-variant" :error-messages="errors.nombre" />
            </v-col>
            <v-col cols="12" sm="4">
              <v-text-field v-model="formEditar.precio_venta" label="Precio (L.)"   type="number" variant="outlined" density="comfortable" prepend-icon="mdi-cash" />
            </v-col>
            <v-col cols="12" sm="4">
              <v-text-field v-model="formEditar.stock_actual" label="Stock actual"  type="number" variant="outlined" density="comfortable" prepend-icon="mdi-archive-outline" />
            </v-col>
            <v-col cols="12" sm="4">
              <v-text-field v-model="formEditar.stock_minimo" label="Stock mínimo"  type="number" variant="outlined" density="comfortable" prepend-icon="mdi-alert-circle-outline" />
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="pa-4 pt-0">
          <v-spacer />
          <v-btn variant="text" @click="dialogs.editar = false">Cancelar</v-btn>
          <v-btn color="primary" variant="flat" rounded="lg" :loading="loading" @click="actualizarProducto">Guardar cambios</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Dialog: Eliminar -->
    <v-dialog v-model="dialogs.eliminar" max-width="400">
      <v-card rounded="lg" class="modern-card">
        <v-card-title class="pa-5" style="font-size:16px;font-weight:600;">Eliminar producto</v-card-title>
        <v-card-text style="padding:0 20px 16px;">
          ¿Eliminar <strong>{{ productoSeleccionado?.nombre }}</strong>? Esta acción no se puede deshacer.
        </v-card-text>
        <v-card-actions class="pa-4 pt-0">
          <v-spacer />
          <v-btn variant="text" @click="dialogs.eliminar = false">Cancelar</v-btn>
          <v-btn color="error" variant="flat" rounded="lg" :loading="loading" @click="eliminarProducto">Eliminar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  productos: { type: Object, required: true },
  filters:   { type: Object, default: () => ({}) },
})

const search   = ref(props.filters.search ?? '')
const loading  = ref(false)
const errors   = ref({})
const dialogs  = reactive({ crear: false, editar: false, eliminar: false })
const productoSeleccionado = ref(null)

const form       = reactive({ codigo: '', nombre: '', precio_venta: '', stock_actual: 0, stock_minimo: 0 })
const formEditar = reactive({ nombre: '', precio_venta: '', stock_actual: 0, stock_minimo: 0 })

const buscar  = () => router.get(route('productos.index'), { search: search.value }, { preserveState: true, replace: true })
const paginar = (p) => router.get(route('productos.index'), { search: search.value, page: p }, { preserveState: true })

const cerrarCrear = () => {
  dialogs.crear = false
  Object.assign(form, { codigo: '', nombre: '', precio_venta: '', stock_actual: 0, stock_minimo: 0 })
  errors.value = {}
}

const guardarProducto = () => {
  loading.value = true
  router.post(route('productos.store'), form, {
    onSuccess: () => cerrarCrear(),
    onError: (e) => { errors.value = e },
    onFinish: () => { loading.value = false },
  })
}

const editarProducto = (p) => {
  productoSeleccionado.value = p
  Object.assign(formEditar, { nombre: p.nombre, precio_venta: p.precio_venta, stock_actual: p.stock_actual, stock_minimo: p.stock_minimo })
  dialogs.editar = true
}

const actualizarProducto = () => {
  loading.value = true
  router.put(route('productos.update', productoSeleccionado.value.id), formEditar, {
    onSuccess: () => { dialogs.editar = false },
    onError: (e) => { errors.value = e },
    onFinish: () => { loading.value = false },
  })
}

const confirmarEliminar = (p) => { productoSeleccionado.value = p; dialogs.eliminar = true }

const eliminarProducto = () => {
  loading.value = true
  router.delete(route('productos.destroy', productoSeleccionado.value.id), {
    onSuccess: () => { dialogs.eliminar = false },
    onFinish:  () => { loading.value = false },
  })
}
</script>
