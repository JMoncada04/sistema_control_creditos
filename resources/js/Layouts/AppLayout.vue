<template>
  <v-app theme="light">

    <!-- ─── NAVIGATION DRAWER ─────────────────────────────────────────── -->
    <v-navigation-drawer v-model="drawer" :rail="rail" permanent width="260"
      style="background:linear-gradient(180deg,#060e1f 0%,#0d1f3c 60%,#060e1f 100%); border-right:1px solid rgba(59,130,246,0.10);">

      <!-- Logo (expandido) -->
      <div v-if="!rail" class="sidebar-logo">
        <div class="logo-icon-wrap">
          <v-icon color="white" size="20">mdi-credit-card-multiple</v-icon>
        </div>
        <div class="logo-text-wrap">
          <span class="logo-brand">FastCredit</span>
          <span class="logo-sub">Sistema de Créditos</span>
        </div>
        <v-btn icon="mdi-chevron-left" variant="text" size="small"
          class="rail-btn" @click="rail = true" />
      </div>

      <!-- Logo (colapsado) -->
      <div v-else class="sidebar-logo-rail">
        <div class="logo-icon-wrap" style="cursor:pointer;" @click="rail = false">
          <v-icon color="white" size="20">mdi-credit-card-multiple</v-icon>
        </div>
      </div>

      <!-- Etiqueta sección -->
      <div v-if="!rail" class="nav-section-label px-4 mt-3 mb-1">Principal</div>

      <!-- Nav items -->
      <v-list density="compact" nav class="px-2 mt-1">
        <v-list-item
          v-for="item in navItems" :key="item.route"
          :prepend-icon="item.icon"
          :title="!rail ? item.label : ''"
          :class="['nav-item', isActive(item.route) ? 'nav-item-active' : '']"
          rounded="lg"
          @click="$inertia.visit(route(item.route))"
        >
          <template v-if="!rail && item.badge" #append>
            <v-chip color="error" size="x-small" variant="flat" class="nav-badge">{{ item.badge }}</v-chip>
          </template>
        </v-list-item>
      </v-list>

      <template #append>
        <div class="sidebar-bottom pa-2">
          <v-divider style="border-color:rgba(255,255,255,0.07); margin-bottom:8px;" />
          <v-list density="compact" nav>
            <v-list-item prepend-icon="mdi-percent-box-outline"
              :title="!rail ? 'Tasas de interés' : ''"
              :class="['nav-item', isActive('tasas.index') ? 'nav-item-active' : '']"
              rounded="lg" @click="$inertia.visit(route('tasas.index'))" />
            <v-list-item prepend-icon="mdi-logout"
              :title="!rail ? 'Cerrar sesión' : ''"
              class="nav-item nav-item-logout" rounded="lg" @click="logout" />
          </v-list>

          <!-- Info usuario (solo expandido) -->
          <div v-if="!rail" class="user-info-sidebar mx-2 mt-2">
            <div class="user-avatar-sidebar">{{ userInitials }}</div>
            <div class="user-info-text">
              <div class="user-info-name">{{ userName }}</div>
              <div class="user-info-role">Administrador</div>
            </div>
          </div>
        </div>
      </template>
    </v-navigation-drawer>

    <!-- ─── APP BAR ────────────────────────────────────────────────────── -->
    <v-app-bar elevation="0" height="64"
      style="border-bottom:1px solid rgba(0,0,0,0.06); backdrop-filter:blur(8px); background:rgba(255,255,255,0.95);">

      <div class="appbar-left px-4">
        <div class="page-breadcrumb">
          <v-icon size="16" color="primary" class="mr-2">{{ currentIcon }}</v-icon>
          <span class="page-title-bar">{{ title }}</span>
        </div>
      </div>

      <template #append>
        <div class="appbar-right pr-4 d-flex align-center gap-3">
          <v-chip prepend-icon="mdi-circle" color="success" variant="tonal" size="small">
            Sistema activo
          </v-chip>
          <v-menu v-model="bellMenu" location="bottom end" :close-on-content-click="false" max-width="320">
            <template #activator="{ props: menuProps }">
              <v-btn icon variant="text" size="small" v-bind="menuProps">
                <v-icon>mdi-bell-outline</v-icon>
                <v-badge v-if="notificaciones.length" color="error" :content="String(notificaciones.length)" floating />
              </v-btn>
            </template>
            <v-card rounded="lg" elevation="8">
              <div style="padding:14px 16px 10px;border-bottom:1px solid rgba(0,0,0,0.06);">
                <div style="font-size:14px;font-weight:600;">Nuevos productos</div>
                <div style="font-size:11px;color:#94a3b8;margin-top:1px;">Últimos 7 días</div>
              </div>
              <div v-if="!notificaciones.length" style="padding:20px 16px;text-align:center;color:#94a3b8;font-size:13px;">
                <v-icon size="28" color="#e2e8f0" class="mb-1">mdi-bell-sleep-outline</v-icon><br>
                Sin nuevos productos recientemente.
              </div>
              <div v-else style="max-height:280px;overflow-y:auto;">
                <div v-for="n in notificaciones" :key="n.id"
                  style="display:flex;align-items:flex-start;gap:10px;padding:12px 16px;border-bottom:1px solid rgba(0,0,0,0.04);">
                  <div style="width:32px;height:32px;background:#eef2ff;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <v-icon color="indigo" size="16">mdi-package-variant-closed</v-icon>
                  </div>
                  <div>
                    <div style="font-size:12px;font-weight:600;color:#0f172a;">{{ n.mensaje }}</div>
                    <div style="font-size:11px;color:#94a3b8;margin-top:2px;">Código: {{ n.codigo }} · {{ n.tiempo }}</div>
                  </div>
                </div>
              </div>
            </v-card>
          </v-menu>
          <div class="user-avatar-bar" @click="logout" title="Cerrar sesión">
            <span class="user-initials-bar">{{ userInitials }}</span>
          </div>
        </div>
      </template>
    </v-app-bar>

    <!-- ─── MAIN ───────────────────────────────────────────────────────── -->
    <v-main>
      <v-container fluid class="main-container">

        <!-- Flash snackbar -->
        <v-snackbar v-model="snack.show" :color="snack.color" location="top right"
          rounded="lg" :timeout="3800" elevation="2">
          <div class="d-flex align-center gap-2">
            <v-icon size="18">{{ snack.icon }}</v-icon>
            <span>{{ snack.msg }}</span>
          </div>
          <template #actions>
            <v-btn variant="text" size="small" @click="snack.show = false">✕</v-btn>
          </template>
        </v-snackbar>

        <slot />
      </v-container>
    </v-main>
  </v-app>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { usePage, router } from '@inertiajs/vue3'

const props = defineProps({ title: { type: String, default: 'FastCredit' } })

const page   = usePage()
const drawer = ref(true)
const rail   = ref(false)
const bellMenu = ref(false)
const notificaciones = computed(() => page.props.notificaciones ?? [])

const userName     = computed(() => page.props.auth?.user?.name ?? 'Usuario')
const userInitials = computed(() =>
  userName.value.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase()
)

const navItems = [
  { route: 'dashboard',       icon: 'mdi-view-dashboard-outline', label: 'Dashboard'       },
  { route: 'clientes.index',  icon: 'mdi-account-group-outline',  label: 'Clientes'        },
  { route: 'productos.index', icon: 'mdi-package-variant-closed', label: 'Productos'       },
  { route: 'ventas.index',    icon: 'mdi-cart-outline',           label: 'Ventas'          },
  { route: 'cuotas.index',    icon: 'mdi-calendar-check-outline', label: 'Plan de cuotas'  },
  { route: 'historial.index', icon: 'mdi-history',                label: 'Historial'       },
]

const iconMap = {
  'Dashboard': 'mdi-view-dashboard-outline',
  'Clientes': 'mdi-account-group-outline',
  'Productos': 'mdi-package-variant-closed',
  'Ventas': 'mdi-cart-outline',
  'Plan de cuotas': 'mdi-calendar-check-outline',
  'Historial de créditos': 'mdi-history',
  'Tasas de interés': 'mdi-percent-box-outline',
}
const currentIcon = computed(() => iconMap[props.title] ?? 'mdi-circle-small')
const isActive    = (r) => route().current(r)
const logout      = () => router.post(route('logout'))

const snack = ref({ show: false, msg: '', color: 'success', icon: 'mdi-check-circle' })
const showFlash = (msg, color = 'success') => {
  snack.value = { show: true, msg, color, icon: color === 'success' ? 'mdi-check-circle' : 'mdi-alert-circle' }
}
watch(() => page.props.flash, (flash) => {
  if (flash?.success) showFlash(flash.success, 'success')
  if (flash?.error)   showFlash(flash.error, 'error')
}, { deep: true, immediate: true })
</script>

<style>
/* ─── Fuentes ─────────────────────────────────────────────────────────── */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap');
* { font-family: 'Inter', sans-serif !important; }
.mono, .mono * { font-family: 'JetBrains Mono', monospace !important; }

/* ─── Sidebar logo ────────────────────────────────────────────────────── */
.sidebar-logo {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 20px 14px 16px;
  border-bottom: 1px solid rgba(59,130,246,0.10);
}
.sidebar-logo-rail {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px 0 16px;
  border-bottom: 1px solid rgba(59,130,246,0.10);
}
.logo-icon-wrap {
  width: 36px; height: 36px; min-width: 36px;
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 4px 12px rgba(59,130,246,0.4);
  flex-shrink: 0;
}
.logo-text-wrap { flex: 1; min-width: 0; }
.logo-brand { display: block; font-size: 15px; font-weight: 700; color: #f8fafc; letter-spacing: -0.3px; white-space: nowrap; }
.logo-sub   { display: block; font-size: 10px; color: #94a3b8; margin-top: 1px; white-space: nowrap; }
.rail-btn   { color: #475569 !important; flex-shrink: 0; }

/* ─── Nav section label ───────────────────────────────────────────────── */
.nav-section-label {
  font-size: 10px; text-transform: uppercase; letter-spacing: 1.2px;
  color: #64748b; font-weight: 600;
}

/* ─── Nav items ───────────────────────────────────────────────────────── */
.nav-item {
  color: #64748b !important;
  margin-bottom: 2px !important;
  transition: all 0.15s ease !important;
  min-height: 40px !important;
}
.nav-item:hover {
  background: rgba(59,130,246,0.08) !important;
  color: #93c5fd !important;
}
.nav-item-active {
  background: linear-gradient(90deg, rgba(59,130,246,0.18), rgba(59,130,246,0.06)) !important;
  color: #60a5fa !important;
  border-left: 2px solid #3b82f6 !important;
}
.nav-item-active .v-icon { color: #60a5fa !important; }
.nav-item-logout { color: #f87171 !important; }
.nav-item-logout:hover { background: rgba(239,68,68,0.08) !important; color: #fca5a5 !important; }
.nav-badge { font-size: 9px !important; }

/* ─── User info sidebar ───────────────────────────────────────────────── */
.user-info-sidebar {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 12px;
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.07);
  border-radius: 10px;
  margin-bottom: 4px;
}
.user-avatar-sidebar {
  width: 30px; height: 30px; border-radius: 8px;
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  display: flex; align-items: center; justify-content: center;
  font-size: 11px; font-weight: 700; color: #fff;
  flex-shrink: 0;
}
.user-info-text { min-width: 0; }
.user-info-name { font-size: 12px; font-weight: 600; color: #f1f5f9; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.user-info-role { font-size: 10px; color: #64748b; }

/* ─── Section label (pasos de formularios) ────────────────────────────── */
.section-label {
  font-size: 12px; font-weight: 600; color: #64748b;
  text-transform: uppercase; letter-spacing: 0.6px;
}

/* ─── App Bar ─────────────────────────────────────────────────────────── */
.appbar-left { flex: 1; }
.page-breadcrumb { display: flex; align-items: center; }
.page-title-bar { font-size: 17px; font-weight: 600; letter-spacing: -0.3px; }
.appbar-right { gap: 12px; }
.user-avatar-bar {
  width: 34px; height: 34px; border-radius: 50%;
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  display: flex; align-items: center; justify-content: center;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(59,130,246,0.3);
  transition: opacity 0.15s;
}
.user-avatar-bar:hover { opacity: 0.85; }
.user-initials-bar { font-size: 12px; font-weight: 700; color: #fff; }

/* ─── Main container ──────────────────────────────────────────────────── */
.main-container { padding: 28px 32px !important; max-width: 100% !important; }

/* ─── Badges de estado ────────────────────────────────────────────────── */
.badge-activo    { background:#dcfce7; color:#166534; border-radius:20px; padding:3px 12px; font-size:11px; font-weight:600; }
.badge-mora      { background:#fee2e2; color:#991b1b; border-radius:20px; padding:3px 12px; font-size:11px; font-weight:600; }
.badge-pendiente { background:#fef3c7; color:#92400e; border-radius:20px; padding:3px 12px; font-size:11px; font-weight:600; }
.badge-pagada    { background:#dcfce7; color:#166534; border-radius:20px; padding:3px 12px; font-size:11px; font-weight:600; }
.badge-parcial   { background:#ede9fe; color:#5b21b6; border-radius:20px; padding:3px 12px; font-size:11px; font-weight:600; }
.badge-anticipado{ background:#dbeafe; color:#1e40af; border-radius:20px; padding:3px 12px; font-size:11px; font-weight:600; }

/* ─── Stat cards ──────────────────────────────────────────────────────── */
.stat-card-modern {
  border-radius: 16px !important;
  border: 1px solid rgba(0,0,0,0.06) !important;
  overflow: hidden;
  transition: transform 0.2s, box-shadow 0.2s;
}
.stat-card-modern:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.1) !important; }
.stat-icon-wrap { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; }
.stat-label-text { font-size: 12px; color: #64748b; font-weight: 500; margin-bottom: 4px; }
.stat-value-text { font-size: 26px; font-weight: 700; letter-spacing: -0.5px; line-height: 1.1; }
.stat-value-text.money { font-size: 20px; }

/* ─── Tablas ──────────────────────────────────────────────────────────── */
.v-table thead th {
  font-size: 11px !important; text-transform: uppercase; letter-spacing: 0.6px;
  color: #64748b !important; font-weight: 600 !important;
  border-bottom: 2px solid rgba(0,0,0,0.06) !important;
  padding: 10px 16px !important;
}
.v-table tbody td { padding: 12px 16px !important; }
.v-table tbody tr:hover { background: rgba(59,130,246,0.03) !important; }

/* ─── Progress bars ───────────────────────────────────────────────────── */
.progress-track { background: #e2e8f0; border-radius: 99px; height: 6px; overflow: hidden; }
.progress-fill  { height: 100%; border-radius: 99px; background: linear-gradient(90deg,#3b82f6,#6366f1); transition: width .5s ease; }

/* ─── Cards ───────────────────────────────────────────────────────────── */
.modern-card { border-radius: 16px !important; border: 1px solid rgba(0,0,0,0.06) !important; }
.modern-card-header {
  padding: 20px 24px 14px;
  border-bottom: 1px solid rgba(0,0,0,0.06);
  display: flex; align-items: center; justify-content: space-between;
}
.modern-card-title { font-size: 15px; font-weight: 600; }
.modern-card-sub   { font-size: 12px; color: #94a3b8; margin-top: 2px; }


/* ─── Inputs outlined — esquinas cuadradas ─────────────────────────────── */
.v-field--variant-outlined {
  border-radius: 8px !important;
}
.v-field--variant-outlined .v-field__outline__start {
  border-radius: 8px 0 0 8px !important;
  min-width: 8px !important;
}
.v-field--variant-outlined .v-field__outline__end {
  border-radius: 0 8px 8px 0 !important;
}
.v-field--variant-outlined .v-field__outline__notch {
  border-top: none;
}
.v-field--variant-outlined.v-field--focused .v-field__outline__notch {
  border-top: none;
}

</style>
