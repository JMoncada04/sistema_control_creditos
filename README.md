# 🚀 Instalación y Configuración del Proyecto

Sigue los pasos a continuación para clonar y configurar correctamente el proyecto en tu entorno local.

---

## 📋 Requisitos Previos

Asegúrate de tener instalado:
- PHP >= 8.x
- Composer
- Node.js & NPM
- Una base de datos compatible (MySQL, PostgreSQL, etc.)

---

## ⚙️ Pasos de Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/usuario/nombre-del-repositorio.git
cd nombre-del-repositorio
```

---

### 2. Configurar las variables de entorno

Crea una copia del archivo `.env.example` y renómbrala como `.env`:

```bash
cp .env.example .env
```

> ✏️ Abre el archivo `.env` y configura los valores según tu entorno local (base de datos, credenciales, etc.).

---

### 3. Instalar dependencias de PHP

```bash
composer install
```

---

### 4. Instalar dependencias de Node.js

```bash
npm install
```

> ⚠️ Si ocurre algún error de compatibilidad, fuerza la instalación con:
> ```bash
> npm install --force
> ```

---

### 5. Generar la clave de la aplicación

```bash
php artisan key:generate
```

---

### 6. Ejecutar las migraciones

Crea las tablas de la base de datos ejecutando:

```bash
php artisan migrate
```

---

## ✅ ¡Listo!

El proyecto debería estar configurado y listo para usarse en tu entorno local.

Si encuentras algún problema, revisa que las variables en tu archivo `.env` estén correctamente configuradas.
