<script setup>
import { Head, useForm } from '@inertiajs/vue3'

defineProps({ status: { type: String } })

const form = useForm({ email: '' })
const submit = () => { form.post(route('password.email')) }
</script>

<template>
    <Head title="Restablecer contraseña" />
    <v-app theme="light">
        <div class="page-root">

            <!-- ══════════ LADO IZQUIERDO — Formulario ══════════ -->
            <div class="side-left">
                <div class="top-brand">
                    <div class="brand-logo-mini">
                        <v-icon color="white" size="18">mdi-credit-card-multiple</v-icon>
                    </div>
                    <span class="brand-name-mini">FastCredit</span>
                </div>

                <div class="form-area">
                    <div class="form-inner">
                        <a :href="route('login')" class="back-link">
                            <v-icon size="16" style="margin-right:4px;">mdi-arrow-left</v-icon>
                            Volver al inicio de sesión
                        </a>

                        <h1 class="form-heading">Restablecer contraseña</h1>
                        <p class="form-subheading">Te enviaremos un enlace seguro a tu correo para crear una nueva contraseña.</p>

                        <div v-if="status" class="status-ok">
                            <v-icon size="18" color="success" style="margin-right:8px;flex-shrink:0;">mdi-check-circle</v-icon>
                            {{ status }}
                        </div>

                        <form @submit.prevent="submit">
                            <div class="field-wrap">
                                <label class="f-label">Correo electrónico</label>
                                <div class="input-box" :class="{ 'input-box-error': form.errors.email }">
                                    <v-icon size="18" color="#94a3b8">mdi-email-outline</v-icon>
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        placeholder="tucorreo@ejemplo.com"
                                        autofocus
                                        autocomplete="username"
                                        class="box-input"
                                    />
                                </div>
                                <span v-if="form.errors.email" class="f-error">{{ form.errors.email }}</span>
                            </div>

                            <button type="submit" class="btn-primary" :disabled="form.processing">
                                <span v-if="form.processing" class="btn-spinner" />
                                <template v-else>
                                    <v-icon size="16" style="margin-right:6px;">mdi-email-fast-outline</v-icon>
                                    Enviar enlace de restablecimiento
                                </template>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ══════════ LADO DERECHO — Marca ══════════ -->
            <div class="side-right">
                <div class="brand-center">
                    <div class="brand-logo-wrap">
                        <div class="brand-logo-ring" />
                        <div class="brand-logo-inner">
                            <v-icon color="white" size="52">mdi-credit-card-multiple</v-icon>
                        </div>
                    </div>
                    <div class="brand-name">FastCredit</div>
                    <div class="brand-tagline">Lo mejor siempre</div>
                    <div class="info-card">
                        <v-icon color="primary" size="26" style="margin-bottom:10px;">mdi-shield-key-outline</v-icon>
                        <div class="info-title">¿Olvidaste tu contraseña?</div>
                        <div class="info-desc">No te preocupes. Ingresa tu correo y recibirás un enlace seguro para crear una nueva contraseña en minutos.</div>
                    </div>
                </div>
            </div>

        </div>
    </v-app>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
* { font-family: 'Inter', sans-serif !important; box-sizing: border-box; }

.page-root { display: flex; min-height: 100vh; }

/* ── Lado izquierdo ──────────────────────────────────────────────────────── */
.side-left {
    width: 52%; min-height: 100vh; background: #ffffff;
    display: flex; flex-direction: column; border-right: 1px solid #e8edf2;
}
.top-brand {
    display: flex; align-items: center; gap: 10px;
    padding: 20px 32px; border-bottom: 1px solid #e8edf2;
}
.brand-logo-mini {
    width: 32px; height: 32px; border-radius: 8px;
    background: linear-gradient(135deg, #1d6fe0, #7c3aed);
    display: flex; align-items: center; justify-content: center;
}
.brand-name-mini { font-size: 16px; font-weight: 700; color: #1d6fe0; }

.form-area {
    flex: 1; display: flex; align-items: center; justify-content: center;
    padding: 48px 64px;
}
.form-inner { width: 100%; max-width: 400px; }

.back-link {
    display: inline-flex; align-items: center;
    font-size: 13px; color: #64748b; text-decoration: none;
    margin-bottom: 28px; transition: color 0.15s;
}
.back-link:hover { color: #1d6fe0; }

.form-heading { font-size: 28px; font-weight: 700; color: #1d6fe0; margin: 0 0 6px; letter-spacing: -0.4px; }
.form-subheading { font-size: 14px; color: #64748b; margin: 0 0 28px; line-height: 1.6; }

/* Campos ─────────────────────────────────────────────────────────────────── */
.field-wrap { margin-bottom: 20px; }
.f-label { display: block; font-size: 13px; font-weight: 500; color: #334155; margin-bottom: 7px; }
.input-box {
    display: flex; align-items: center; gap: 10px;
    border: 1.5px solid #cbd5e1; border-radius: 8px;
    padding: 0 16px; height: 50px; background: #ffffff;
    transition: border-color 0.18s, box-shadow 0.18s;
}
.input-box:focus-within { border-color: #1d6fe0; box-shadow: 0 0 0 3px rgba(29,111,224,0.10); }
.input-box-error { border-color: #ef4444; }
.input-box-error:focus-within { box-shadow: 0 0 0 3px rgba(239,68,68,0.10); }
.box-input {
    flex: 1; border: none; outline: none; background: transparent;
    font-size: 14px; color: #0f172a; font-family: 'Inter', sans-serif !important;
}
.box-input::placeholder { color: #94a3b8; }
.f-error { display: block; font-size: 11px; color: #ef4444; margin-top: 4px; padding-left: 4px; }

.status-ok {
    display: flex; align-items: center;
    background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534;
    border-radius: 8px; padding: 12px 16px; font-size: 13px; margin-bottom: 20px;
}

/* Botón ──────────────────────────────────────────────────────────────────── */
.btn-primary {
    width: 100%; height: 50px;
    background: linear-gradient(180deg, #060e1f 0%, #0d1f3c 60%, #060e1f 100%);
    color: #ffffff; border: none; border-radius: 8px;
    font-size: 15px; font-weight: 600; cursor: pointer;
    font-family: 'Inter', sans-serif !important;
    transition: opacity 0.18s, transform 0.1s;
    display: flex; align-items: center; justify-content: center; gap: 4px;
    margin-top: 8px;
}
.btn-primary:hover:not(:disabled) { opacity: 0.88; }
.btn-primary:active:not(:disabled) { transform: scale(0.985); }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

.btn-spinner {
    width: 18px; height: 18px;
    border: 2.5px solid rgba(255,255,255,0.35); border-top-color: #ffffff;
    border-radius: 50%; animation: spin 0.7s linear infinite; display: inline-block;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Lado derecho ─────────────────────────────────────────────────────────── */
.side-right {
    flex: 1;
    background: linear-gradient(145deg, #f8faff 0%, #eef3ff 50%, #f0f7ff 100%);
    display: flex; align-items: center; justify-content: center;
    position: relative; overflow: hidden;
}
.side-right::before {
    content: ''; position: absolute; width: 500px; height: 500px; border-radius: 50%;
    background: radial-gradient(circle, rgba(29,111,224,0.07) 0%, transparent 70%);
    top: -100px; right: -100px;
}
.side-right::after {
    content: ''; position: absolute; width: 350px; height: 350px; border-radius: 50%;
    background: radial-gradient(circle, rgba(124,58,237,0.06) 0%, transparent 70%);
    bottom: -80px; left: -60px;
}
.brand-center {
    display: flex; flex-direction: column; align-items: center;
    text-align: center; position: relative; z-index: 1;
}
.brand-logo-wrap { position: relative; width: 140px; height: 140px; margin-bottom: 28px; }
.brand-logo-ring {
    position: absolute; inset: 0; border-radius: 50%;
    border: 2px solid transparent;
    border-top-color: #1d6fe0; border-right-color: rgba(29,111,224,0.3);
    animation: rotate-ring 3s linear infinite;
}
@keyframes rotate-ring { to { transform: rotate(360deg); } }
.brand-logo-inner {
    position: absolute; inset: 12px; border-radius: 50%;
    background: linear-gradient(135deg, #1d6fe0 0%, #7c3aed 100%);
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 12px 40px rgba(29,111,224,0.35);
}
.brand-name { font-size: 44px; font-weight: 800; color: #1d6fe0; letter-spacing: -1px; line-height: 1; margin-bottom: 8px; }
.brand-tagline { font-size: 17px; font-weight: 500; color: #64748b; margin-bottom: 40px; }
.info-card {
    display: flex; flex-direction: column; align-items: center;
    background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px;
    padding: 24px 32px; box-shadow: 0 4px 24px rgba(29,111,224,0.08); max-width: 280px;
}
.info-title { font-size: 15px; font-weight: 600; color: #0f172a; margin-bottom: 8px; }
.info-desc { font-size: 13px; color: #64748b; line-height: 1.6; }

@media (max-width: 860px) {
    .side-right { display: none; }
    .side-left  { width: 100%; }
    .form-area  { padding: 32px 28px; }
}
</style>
