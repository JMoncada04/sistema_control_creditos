<script setup>
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
})

const activeTab = ref('login')

const loginForm = useForm({ email: '', password: '', remember: false })
const showLoginPwd = ref(false)
const submitLogin = () => {
    loginForm.post(route('login'), { onFinish: () => loginForm.reset('password') })
}

const regForm = useForm({ name: '', email: '', password: '', password_confirmation: '' })
const showRegPwd     = ref(false)
const showRegPwdConf = ref(false)
const submitRegister = () => {
    regForm.post(route('register'), { onFinish: () => regForm.reset('password', 'password_confirmation') })
}
</script>

<template>
    <Head :title="activeTab === 'login' ? 'Iniciar sesión' : 'Crear cuenta'" />

    <v-app theme="light">
        <div class="page-root">

            <div class="side-left">

                <div class="top-tabs">
                    <button class="top-tab" :class="{ active: activeTab === 'login' }"    @click="activeTab = 'login'">
                        Ingresar con mi cuenta
                    </button>
                    <button class="top-tab" :class="{ active: activeTab === 'register' }" @click="activeTab = 'register'">
                        Crear cuenta
                    </button>
                </div>

                <div class="form-area">
                    <transition name="slide" mode="out-in">

                        <div v-if="activeTab === 'login'" key="login">
                            <h1 class="form-heading">Inicio de Sesión</h1>
                            <p class="form-subheading">¡Te Damos la Bienvenida de Nuevo!</p>

                            <div v-if="status" class="status-ok mb-4">
                                {{ status }}
                            </div>

                            <form @submit.prevent="submitLogin">

                                <div class="field-wrap">
                                    <label class="f-label">Correo electrónico</label>
                                    <div class="input-pill" :class="{ 'input-pill-error': loginForm.errors.email }">
                                        <v-icon size="18" color="#94a3b8">mdi-email-outline</v-icon>
                                        <input
                                            v-model="loginForm.email"
                                            type="email"
                                            placeholder="Ingresar"
                                            autocomplete="username"
                                            autofocus
                                            class="pill-input"
                                        />
                                    </div>
                                    <span v-if="loginForm.errors.email" class="f-error">{{ loginForm.errors.email }}</span>
                                </div>

                                <div class="field-wrap">
                                    <label class="f-label">Contraseña</label>
                                    <div class="input-pill" :class="{ 'input-pill-error': loginForm.errors.password }">
                                        <v-icon size="18" color="#94a3b8">mdi-lock-outline</v-icon>
                                        <input
                                            v-model="loginForm.password"
                                            :type="showLoginPwd ? 'text' : 'password'"
                                            placeholder="Ingresar"
                                            autocomplete="current-password"
                                            class="pill-input"
                                        />
                                        <button type="button" class="eye-btn" @click="showLoginPwd = !showLoginPwd" tabindex="-1">
                                            <v-icon size="18" color="#94a3b8">
                                                {{ showLoginPwd ? 'mdi-eye-off-outline' : 'mdi-eye-outline' }}
                                            </v-icon>
                                        </button>
                                    </div>
                                    <span v-if="loginForm.errors.password" class="f-error">{{ loginForm.errors.password }}</span>
                                </div>

                                <button type="submit" class="btn-primary" :disabled="loginForm.processing"
                                        style="background:linear-gradient(180deg,#060e1f 0%,#0d1f3c 60%,#060e1f 100%); border-right:1px solid rgba(59,130,246,0.10);">
                                    <span v-if="loginForm.processing" class="btn-spinner" />
                                    <span v-else>Iniciar Sesión</span>
                                </button>

                                <div class="forgot-line">
                                    ¿Has Olvidado tu Contraseña?
                                    <a v-if="canResetPassword" :href="route('password.request')" class="forgot-lnk">
                                        Reiniciar Contraseña
                                    </a>
                                </div>

                                <div class="register-line">
                                    Si aún no tienes cuenta
                                </div>
                                <button type="button" class="btn-register" @click="activeTab = 'register'" style="background:linear-gradient(180deg,#060e1f 0%,#0d1f3c 60%,#060e1f 100%); border-right:1px solid rgba(59,130,246,0.10);">
                                    Registrarme
                                </button>

                            </form>
                        </div>

                        <div v-else key="register">
                            <h1 class="form-heading">Crear Cuenta</h1>
                            <p class="form-subheading">Completa los datos para registrarte</p>

                            <form @submit.prevent="submitRegister">

                                <div class="field-wrap">
                                    <label class="f-label">Nombre completo</label>
                                    <div class="input-pill" :class="{ 'input-pill-error': regForm.errors.name }">
                                        <v-icon size="18" color="#94a3b8">mdi-account-outline</v-icon>
                                        <input v-model="regForm.name" type="text" placeholder="Ingresar"
                                               autocomplete="name" autofocus class="pill-input" />
                                    </div>
                                    <span v-if="regForm.errors.name" class="f-error">{{ regForm.errors.name }}</span>
                                </div>

                                <div class="field-wrap">
                                    <label class="f-label">Correo electrónico</label>
                                    <div class="input-pill" :class="{ 'input-pill-error': regForm.errors.email }">
                                        <v-icon size="18" color="#94a3b8">mdi-email-outline</v-icon>
                                        <input v-model="regForm.email" type="email" placeholder="Ingresar"
                                               autocomplete="username" class="pill-input" />
                                    </div>
                                    <span v-if="regForm.errors.email" class="f-error">{{ regForm.errors.email }}</span>
                                </div>

                                <div class="field-wrap">
                                    <label class="f-label">Contraseña</label>
                                    <div class="input-pill" :class="{ 'input-pill-error': regForm.errors.password }">
                                        <v-icon size="18" color="#94a3b8">mdi-lock-outline</v-icon>
                                        <input v-model="regForm.password" :type="showRegPwd ? 'text' : 'password'"
                                               placeholder="Mínimo 8 caracteres" autocomplete="new-password" class="pill-input" />
                                        <button type="button" class="eye-btn" @click="showRegPwd = !showRegPwd" tabindex="-1">
                                            <v-icon size="18" color="#94a3b8">{{ showRegPwd ? 'mdi-eye-off-outline' : 'mdi-eye-outline' }}</v-icon>
                                        </button>
                                    </div>
                                    <span v-if="regForm.errors.password" class="f-error">{{ regForm.errors.password }}</span>
                                </div>

                                <div class="field-wrap">
                                    <label class="f-label">Confirmar contraseña</label>
                                    <div class="input-pill" :class="{ 'input-pill-error': regForm.errors.password_confirmation }">
                                        <v-icon size="18" color="#94a3b8">mdi-lock-check-outline</v-icon>
                                        <input v-model="regForm.password_confirmation" :type="showRegPwdConf ? 'text' : 'password'"
                                               placeholder="Repetir contraseña" autocomplete="new-password" class="pill-input" />
                                        <button type="button" class="eye-btn" @click="showRegPwdConf = !showRegPwdConf" tabindex="-1">
                                            <v-icon size="18" color="#94a3b8">{{ showRegPwdConf ? 'mdi-eye-off-outline' : 'mdi-eye-outline' }}</v-icon>
                                        </button>
                                    </div>
                                    <span v-if="regForm.errors.password_confirmation" class="f-error">{{ regForm.errors.password_confirmation }}</span>
                                </div>

                                <button type="submit" class="btn-primary" :disabled="regForm.processing">
                                    <span v-if="regForm.processing" class="btn-spinner" />
                                    <span v-else>Crear Cuenta</span>
                                </button>

                                <div class="register-line" style="margin-top:18px;">
                                    ¿Ya tienes cuenta?
                                </div>
                                <button type="button" class="btn-register" @click="activeTab = 'login'">
                                    Iniciar Sesión
                                </button>

                            </form>
                        </div>

                    </transition>
                </div>
            </div>

            <div class="side-right">
                <div class="brand-center">
                    <div class="brand-logo-wrap" >
                        <div class="brand-logo-ring" />
                        <div class="brand-logo-inner" >
                            <v-icon color="white" size="52">mdi-credit-card-multiple</v-icon>
                        </div>
                    </div>

                    <div class="brand-name" >FastCredit</div>
                    <div class="brand-tagline">Lo mejor siempre</div>

                    <div class="brand-stats">
                        <div class="brand-stat">
                            <div class="bstat-val">+1,240</div>
                            <div class="bstat-lbl">Créditos activos</div>
                        </div>
                        <div class="bstat-divider"></div>
                        <div class="brand-stat">
                            <div class="bstat-val">98%</div>
                            <div class="bstat-lbl">Satisfacción</div>
                        </div>
                        <div class="bstat-divider" />
                        <div class="brand-stat">
                            <div class="bstat-val">24/7</div>
                            <div class="bstat-lbl">Disponibilidad</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </v-app>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
* { font-family: 'Inter', sans-serif !important; box-sizing: border-box; }

.page-root {
    display: flex;
    min-height: 100vh;
}

.side-left {
    width: 52%;
    min-height: 100vh;
    background: #ffffff;
    display: flex;
    flex-direction: column;
    border-right: 1px solid #e8edf2;
}

.top-tabs {
    display: flex;
    border-bottom: 1px solid #e8edf2;
}
.top-tab {
    flex: 1;
    padding: 18px 24px;
    border: none;
    background: #f0f4f8;
    font-size: 14px;
    font-weight: 500;
    color: #64748b;
    cursor: pointer;
    font-family: 'Inter', sans-serif !important;
    transition: background 0.2s, color 0.2s;
}
.top-tab:first-child { border-right: 1px solid #e8edf2; }
.top-tab.active {
    background: #1d6fe0;
    color: #ffffff;
    font-weight: 600;
}
.top-tab:not(.active):hover { background: #e2e8f0; color: #334155; }

.form-area {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 48px 64px;
}
.form-area > div { width: 100%; max-width: 400px; }

.form-heading {
    font-size: 28px;
    font-weight: 700;
    color: #1d6fe0;
    margin: 0 0 6px;
    letter-spacing: -0.4px;
}
.form-subheading {
    font-size: 14px;
    font-weight: 600;
    color: #0f172a;
    margin: 0 0 32px;
}

.field-wrap { margin-bottom: 20px; }
.f-label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: #334155;
    margin-bottom: 7px;
}
.input-pill {
    display: flex;
    align-items: center;
    gap: 10px;
    border: 1.5px solid #cbd5e1;
    border-radius: 9999px;
    padding: 0 18px;
    height: 50px;
    background: #ffffff;
    transition: border-color 0.18s, box-shadow 0.18s;
}
.input-pill:focus-within {
    border-color: #1d6fe0;
    box-shadow: 0 0 0 3px rgba(29,111,224,0.10);
}
.input-pill-error { border-color: #ef4444; }
.input-pill-error:focus-within { box-shadow: 0 0 0 3px rgba(239,68,68,0.10); }

.pill-input {
    flex: 1;
    border: none;
    outline: none;
    border-color: transparent;
    background: transparent;
    font-size: 14px;
    color: #0f172a;
    font-family: 'Inter', sans-serif !important;
}
.pill-input::placeholder { color: #94a3b8; }

.eye-btn {
    border: none;
    background: transparent;
    cursor: pointer;
    padding: 0;
    display: flex;
    align-items: center;
}
.f-error {
    display: block;
    font-size: 11px;
    color: #ef4444;
    margin-top: 4px;
    padding-left: 18px;
}

.btn-primary {
    width: 100%;
    height: 50px;
    background: #1d6fe0;
    color: #ffffff;
    border: none;
    border-radius: 9999px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    font-family: 'Inter', sans-serif !important;
    transition: background 0.18s, transform 0.1s;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 8px;
}
.btn-primary:hover:not(:disabled) { background: #1558c0; }
.btn-primary:active:not(:disabled) { transform: scale(0.985); }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

.btn-register {
    width: 100%;
    height: 50px;
    background: #7c3aed;
    color: #ffffff;
    border: none;
    border-radius: 9999px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    font-family: 'Inter', sans-serif !important;
    transition: background 0.18s, transform 0.1s;
}
.btn-register:hover { background: #6d28d9; }
.btn-register:active { transform: scale(0.985); }

.forgot-line {
    text-align: center;
    font-size: 13px;
    color: #64748b;
    margin: 16px 0 10px;
}
.forgot-lnk {
    color: #f97316;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.15s;
}
.forgot-lnk:hover { color: #ea6c08; text-decoration: underline; }

.register-line {
    text-align: center;
    font-size: 14px;
    color: #475569;
    margin-bottom: 10px;
}

.status-ok {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #166534;
    border-radius: 10px;
    padding: 10px 16px;
    font-size: 13px;
    margin-bottom: 16px;
}

.btn-spinner {
    width: 18px; height: 18px;
    border: 2.5px solid rgba(255,255,255,0.35);
    border-top-color: #ffffff;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
    display: inline-block;
}
@keyframes spin { to { transform: rotate(360deg); } }

.slide-enter-active, .slide-leave-active { transition: opacity 0.18s ease, transform 0.18s ease; }
.slide-enter-from { opacity: 0; transform: translateY(8px); }
.slide-leave-to   { opacity: 0; transform: translateY(-8px); }

.side-right {
    flex: 1;
    background: linear-gradient(145deg, #f8faff 0%, #eef3ff 50%, #f0f7ff 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.side-right::before {
    content: '';
    position: absolute;
    width: 500px; height: 500px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(29,111,224,0.07) 0%, transparent 70%);
    top: -100px; right: -100px;
}
.side-right::after {
    content: '';
    position: absolute;
    width: 350px; height: 350px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(124,58,237,0.06) 0%, transparent 70%);
    bottom: -80px; left: -60px;
}

.brand-center {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    position: relative;
    z-index: 1;
}

.brand-logo-wrap {
    position: relative;
    width: 140px; height: 140px;
    margin-bottom: 28px;
}
.brand-logo-ring {
    position: absolute; inset: 0;
    border-radius: 50%;
    border: 2px solid transparent;
    border-top-color: #1d6fe0;
    border-right-color: rgba(29,111,224,0.3);
    animation: rotate-ring 3s linear infinite;
}
@keyframes rotate-ring { to { transform: rotate(360deg); } }

.brand-logo-inner {
    position: absolute;
    inset: 12px;
    border-radius: 50%;
    background: linear-gradient(135deg, #1d6fe0 0%, #7c3aed 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 12px 40px rgba(29,111,224,0.35);
}

.brand-name {
    font-size: 44px;
    font-weight: 800;
    color: #1d6fe0;
    letter-spacing: -1px;
    line-height: 1;
    margin-bottom: 8px;
}
.brand-tagline {
    font-size: 17px;
    font-weight: 500;
    color: #64748b;
    margin-bottom: 40px;
    letter-spacing: 0.2px;
}

.brand-stats {
    display: flex;
    align-items: center;
    gap: 0;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 20px 32px;
    box-shadow: 0 4px 24px rgba(29,111,224,0.08);
}
.brand-stat { text-align: center; padding: 0 24px; }
.bstat-val {
    font-size: 22px;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: -0.5px;
}
.bstat-lbl {
    font-size: 11px;
    color: #94a3b8;
    font-weight: 500;
    margin-top: 3px;
    text-transform: uppercase;
    letter-spacing: 0.6px;
}
.bstat-divider {
    width: 1px; height: 36px;
    background: #e2e8f0;
}

@media (max-width: 860px) {
    .side-right { display: none; }
    .side-left  { width: 100%; }
    .form-area  { padding: 32px 28px; }
}
</style>
