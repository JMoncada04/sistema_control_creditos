<template>
    <AppLayout title="Tasas de interés">

        <v-row>
            <v-col cols="12" md="4">
                <v-card class="modern-card pa-5" variant="flat">
                    <div style="font-size:16px;font-weight:600;margin-bottom:16px;">
                        <v-icon class="mr-2" color="indigo">mdi-percent</v-icon>
                        {{ editando ? 'Editar tasa' : 'Nueva tasa de interés' }}
                    </div>

                    <v-select
                        v-model="form.plazo_meses"
                        :items="plazosDisponibles"
                        :item-title="(i) => i + ' meses'"
                        :item-value="(i) => i"
                        label="Plazo (meses)"
                        variant="outlined" density="comfortable" rounded="lg"
                        class="mb-3"
                        :disabled="editando"
                        :error-messages="errors.plazo_meses"
                    />

                    <v-text-field
                        v-model="form.porcentaje"
                        label="Porcentaje de interés (%)"
                        type="number" step="0.01" min="0.01" max="100"
                        variant="outlined" density="comfortable"
                        prepend-icon="mdi-percent"
                        class="mb-4"
                        :error-messages="errors.porcentaje"
                        hint="Ejemplo: 5 meses al 5%, 6 meses al 10%"
                        persistent-hint
                    />

                    <v-btn color="indigo" variant="flat" rounded="lg" block prepend-icon="mdi-content-save"
                           :loading="loading" @click="guardar">
                        {{ editando ? 'Actualizar tasa' : 'Guardar tasa' }}
                    </v-btn>
                    <v-btn v-if="editando" variant="text" block class="mt-2" @click="cancelarEdicion">
                        Cancelar edición
                    </v-btn>
                </v-card>

                <v-card variant="tonal" color="indigo" rounded="lg" class="pa-4 mt-4" style="font-size:13px;">
                    <div style="font-weight:600;margin-bottom:8px;">¿Cómo funciona?</div>
                    <p style="color:#4f46e5;line-height:1.6;">
                        El sistema usará automáticamente la tasa correspondiente al plazo elegido
                        al momento de registrar una venta. Si no existe una tasa para el plazo
                        seleccionado, la venta no se podrá registrar.
                    </p>
                </v-card>
            </v-col>

            <v-col cols="12" md="8">
                <v-card class="modern-card" variant="flat">
                    <v-card-title style="font-size:15px;font-weight:600;padding:16px 20px 12px;">
                        Tasas configuradas<span class="mono" style="font-size:13px;color:#6366f1;"></span>
                    </v-card-title>
                    <v-table>
                        <thead>
                        <tr>
                            <th>Plazo</th><th>Porcentaje</th>
                            <th>Ejemplo L. 10,000</th><th>Cuota mensual ejemplo</th><th>Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="t in tasas" :key="t.id">
                            <td style="font-size:14px;font-weight:600;">{{ t.plazo_meses }} meses</td>
                            <td>
                                <v-chip color="indigo" variant="tonal" size="small">{{ t.porcentaje }}%</v-chip>
                            </td>
                            <td class="mono" style="font-size:13px;">
                                L. {{ (10000 * (1 + t.porcentaje / 100)).toLocaleString('es-HN', { minimumFractionDigits: 2 }) }}
                            </td>
                            <td class="mono" style="font-size:13px;color:#64748b;">
                                L. {{ Math.round(10000 * (1 + t.porcentaje / 100) / t.plazo_meses).toLocaleString('es-HN') }}
                            </td>
                            <td>
                                <v-btn icon="mdi-pencil-outline" size="small" variant="text" color="primary" @click="editarTasa(t)" />
                                <v-btn icon="mdi-delete-outline"  size="small" variant="text" color="error"   @click="confirmarEliminar(t)" />
                            </td>
                        </tr>
                        <tr v-if="!tasas.length">
                            <td colspan="5" style="text-align:center;color:#94a3b8;padding:24px;font-size:13px;">
                                No hay tasas configuradas. Agrega la primera.
                            </td>
                        </tr>
                        </tbody>
                    </v-table>
                </v-card>
            </v-col>
        </v-row>

        <v-dialog v-model="dialogs.eliminar" max-width="400">
            <v-card rounded="lg" class="modern-card">
                <v-card-title class="pa-5" style="font-size:16px;font-weight:600;">Eliminar tasa</v-card-title>
                <v-card-text style="padding:0 20px 16px;">
                    ¿Eliminar la tasa de <strong>{{ tasaSeleccionada?.plazo_meses }} meses ({{ tasaSeleccionada?.porcentaje }}%)</strong>?
                    No se puede eliminar si ya fue usada en ventas.
                </v-card-text>
                <v-card-actions class="pa-4 pt-0">
                    <v-spacer />
                    <v-btn variant="text" @click="dialogs.eliminar = false">Cancelar</v-btn>
                    <v-btn color="error" variant="flat" rounded="lg" :loading="loading" @click="eliminarTasa">Eliminar</v-btn>
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
    tasas: { type: Array, default: () => [] },
})

const loading          = ref(false)
const errors           = ref({})
const editando         = ref(false)
const tasaSeleccionada = ref(null)
const dialogs          = reactive({ eliminar: false })

const form = reactive({ plazo_meses: null, porcentaje: '' })

const plazosDisponibles = [3, 6, 12, 18, 24, 36]

const plazosUsados = computed(() => props.tasas.map(t => t.plazo_meses))

const guardar = () => {
    loading.value = true
    if (editando.value) {
        router.put(route('tasas.update', tasaSeleccionada.value.id), { porcentaje: form.porcentaje }, {
            onSuccess: () => cancelarEdicion(),
            onError: (e) => { errors.value = e },
            onFinish: () => { loading.value = false },
        })
    } else {
        router.post(route('tasas.store'), form, {
            onSuccess: () => { Object.assign(form, { plazo_meses: null, porcentaje: '' }); errors.value = {} },
            onError: (e) => { errors.value = e },
            onFinish: () => { loading.value = false },
        })
    }
}

const editarTasa = (t) => {
    tasaSeleccionada.value = t
    form.plazo_meses = t.plazo_meses
    form.porcentaje  = t.porcentaje
    editando.value   = true
}

const cancelarEdicion = () => {
    editando.value = false
    tasaSeleccionada.value = null
    Object.assign(form, { plazo_meses: null, porcentaje: '' })
    errors.value = {}
}

const confirmarEliminar = (t) => { tasaSeleccionada.value = t; dialogs.eliminar = true }

const eliminarTasa = () => {
    loading.value = true
    router.delete(route('tasas.destroy', tasaSeleccionada.value.id), {
        onSuccess: () => { dialogs.eliminar = false },
        onFinish: () => { loading.value = false },
    })
}
</script>
