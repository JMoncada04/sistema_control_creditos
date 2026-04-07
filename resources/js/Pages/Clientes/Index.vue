<template>
  <AppLayout title="Clientes">

    <v-row class="mb-4" align="center">
      <v-col>
        <v-text-field
          v-model="search"
          prepend-icon="mdi-magnify"
          placeholder="Buscar por nombre o identidad..."
          variant="outlined" density="comfortable" hide-details
          style="max-width:360px;"
          @update:model-value="buscar"
        />
      </v-col>
      <v-col class="text-right">
        <v-btn color="indigo" prepend-icon="mdi-account-plus" rounded="lg" @click="dialogs.crear = true">
          Nuevo cliente
        </v-btn>
      </v-col>
    </v-row>

    <v-card class="modern-card" variant="flat">
      <v-table>
        <thead>
          <tr>
            <th>N° Identidad</th><th>Nombre</th><th>Teléfono</th>
            <th>Dirección</th><th>Estado</th><th>Créditos</th><th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="c in clientes.data" :key="c.id">
            <td class="mono" style="font-size:13px;">{{ c.identidad }}</td>
            <td style="font-size:13px;font-weight:500;">
              <div style="display:flex;align-items:center;gap:8px;">
                <v-avatar :color="c.estado === 'Mora' ? 'error' : 'indigo'" size="28">
                  <span style="font-size:11px;color:#fff;font-weight:600;">{{ initials(c.nombre) }}</span>
                </v-avatar>
                {{ c.nombre }}
              </div>
            </td>
            <td class="mono" style="font-size:13px;">{{ c.telefono }}</td>
            <td style="font-size:13px;color:#64748b;">{{ c.direccion }}</td>
            <td><span :class="'badge-' + c.estado.toLowerCase()">{{ c.estado }}</span></td>
            <td class="mono" style="font-size:13px;">{{ c.creditos }}</td>
            <td>
              <v-btn icon="mdi-pencil-outline" size="small" variant="text" color="primary" @click="editarCliente(c)" />
            </td>
          </tr>
          <tr v-if="!clientes.data.length">
            <td colspan="7" style="text-align:center;color:#94a3b8;padding:24px;font-size:13px;">
              No se encontraron clientes.
            </td>
          </tr>
        </tbody>
      </v-table>
    </v-card>

    <!-- Paginación -->
    <div class="d-flex justify-end mt-3">
      <v-pagination
        v-if="clientes.last_page > 1"
        :model-value="clientes.current_page"
        :length="clientes.last_page"
        density="compact"
        @update:model-value="paginar"
      />
    </div>

    <!-- Dialog: Crear cliente -->
    <v-dialog v-model="dialogs.crear" max-width="520">
      <v-card rounded="lg" class="modern-card">
        <v-card-title class="pa-5" style="font-size:17px;font-weight:600;border-bottom:1px solid rgba(0,0,0,0.08);">
          <v-icon class="mr-2" color="indigo">mdi-account-plus-outline</v-icon>Registrar cliente
        </v-card-title>
        <v-card-text class="pa-5">
          <v-row>
            <v-col cols="12">
                <v-text-field
                    v-model="form.identidad"
                    label="Número de identidad"
                    variant="outlined"
                    density="comfortable"
                    :error-messages="errors.identidad"
                    @keypress="soloEnteros"
                    prepend-icon="mdi-card-account-details"
                    maxlength="13"
                    hint="Máximo 13 dígitos"
                    persistent-hint
                />
            </v-col>
            <v-col cols="12">
              <v-text-field
                v-model="form.nombre"
                label="Nombre completo"
                variant="outlined" density="comfortable"
                prepend-icon="mdi-account-outline"
                :error-messages="errors.nombre"
                @keypress="soloLetrasEsp"
              />
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="form.telefono"
                label="Teléfono"
                variant="outlined" density="comfortable"
                prepend-icon="mdi-phone-outline"
              />
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="form.direccion"
                label="Dirección"
                variant="outlined" density="comfortable"
                prepend-icon="mdi-map-marker-outline"
              />
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="pa-4 pt-0">
          <v-spacer />
          <v-btn variant="text" @click="cerrarCrear">Cancelar</v-btn>
          <v-btn color="indigo" variant="flat" rounded="lg" :loading="loading" @click="guardarCliente">
            Registrar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Dialog: Editar cliente -->
    <v-dialog v-model="dialogs.editar" max-width="520">
      <v-card rounded="lg" class="modern-card">
        <v-card-title class="pa-5" style="font-size:17px;font-weight:600;border-bottom:1px solid rgba(0,0,0,0.08);">
          <v-icon class="mr-2" color="primary">mdi-account-edit-outline</v-icon>Editar cliente
        </v-card-title>
        <v-card-text class="pa-5">
          <v-row>
            <v-col cols="12">
              <v-text-field v-model="formEditar.nombre" label="Nombre" variant="outlined" density="comfortable" prepend-icon="mdi-account-outline" :error-messages="errors.nombre" @keypress="soloLetrasEsp" />
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field v-model="formEditar.telefono"  label="Teléfono"  variant="outlined" density="comfortable" prepend-icon="mdi-phone-outline" />
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field v-model="formEditar.direccion" label="Dirección" variant="outlined" density="comfortable" prepend-icon="mdi-map-marker-outline" />
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="pa-4 pt-0">
          <v-spacer />
          <v-btn variant="text" @click="dialogs.editar = false">Cancelar</v-btn>
          <v-btn color="indigo" variant="flat" rounded="lg" :loading="loading" @click="actualizarCliente">
            Guardar cambios
          </v-btn>
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
  clientes: { type: Object, required: true },
  filters:  { type: Object, default: () => ({}) },
})

const search  = ref(props.filters.search ?? '')
const loading = ref(false)
const errors  = ref({})
const dialogs = reactive({ crear: false, editar: false })

const form = reactive({ identidad: '', nombre: '', telefono: '', direccion: '' })
const formEditar = reactive({ nombre: '', telefono: '', direccion: '' })
const clienteSeleccionado = ref(null)

const initials      = (nombre) => nombre.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase()
const soloEnteros   = (e) => { if (!/\d/.test(e.key)) e.preventDefault() }
const soloLetrasEsp = (e) => { if (!/[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]/.test(e.key)) e.preventDefault() }

const buscar = () => {
  router.get(route('clientes.index'), { search: search.value }, { preserveState: true, replace: true })
}

const paginar = (page) => {
  router.get(route('clientes.index'), { search: search.value, page }, { preserveState: true })
}

const cerrarCrear = () => {
  dialogs.crear = false
  Object.assign(form, { identidad: '', nombre: '', telefono: '', direccion: '' })
  errors.value = {}
}

const guardarCliente = () => {
  loading.value = true
  router.post(route('clientes.store'), form, {
    onSuccess: () => { cerrarCrear() },
    onError: (e) => { errors.value = e },
    onFinish: () => { loading.value = false },
  })
}

const editarCliente = (c) => {
  clienteSeleccionado.value = c
  Object.assign(formEditar, { nombre: c.nombre, telefono: c.telefono, direccion: c.direccion })
  dialogs.editar = true
}

const actualizarCliente = () => {
  loading.value = true
  router.put(route('clientes.update', clienteSeleccionado.value.id), formEditar, {
    onSuccess: () => { dialogs.editar = false },
    onError: (e) => { errors.value = e },
    onFinish: () => { loading.value = false },
  })
}

</script>
