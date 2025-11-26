# Sistema de Gestión de Trabajos (Job Shop)

Este proyecto es un sistema de gestión de trabajos a medida, desarrollado con **Laravel**, **Inertia.js**, **Vue.js** y **PostgreSQL**. Permite a los clientes solicitar trabajos personalizados y a los propietarios gestionar presupuestos, producción e inventario.

## 🚀 Instalación y Configuración

1.  **Clonar el repositorio**
2.  **Instalar dependencias de PHP:**
    ```bash
    composer install
    ```
3.  **Instalar dependencias de Node.js:**
    ```bash
    npm install
    ```
4.  **Configurar entorno:**
    - Copiar `.env.example` a `.env`
    - Configurar las credenciales de base de datos en `.env` (DB_CONNECTION=pgsql, etc.)
5.  **Generar clave de aplicación:**
    ```bash
    php artisan key:generate
    ```
6.  **Ejecutar migraciones y seeders (Datos de Prueba):**
    ```bash
    php artisan migrate:fresh --seed
    ```
7.  **Iniciar servidores:**
    - Terminal 1: `php artisan serve`
    - Terminal 2: `npm run dev`

---

## 🧪 Credenciales de Prueba

El sistema viene precargado con los siguientes usuarios para pruebas:

| Rol | Nombre | Email | Contraseña |
| :--- | :--- | :--- | :--- |
| **Propietario** | Carlos Propietario | `admin@tecnoweb.com` | `password` |
| **Cliente** | Juan Cliente | `cliente1@test.com` | `password` |
| **Cliente** | Maria Cliente | `cliente2@test.com` | `password` |

---

## 🔄 Guía de Pruebas de Flujo

### Flujo 1: Solicitud de Trabajo (Cliente)
1.  Inicia sesión como **Cliente** (`cliente1@test.com`).
2.  En el Dashboard, haz clic en **"Solicitar Nuevo Trabajo"**.
3.  Llena el formulario con un título (ej. "Mesa de Centro") y una descripción.
4.  Al guardar, serás redirigido al Dashboard donde verás tu trabajo con estado **SOLICITADO**.

### Flujo 2: Gestión y Presupuesto (Propietario)
1.  Inicia sesión como **Propietario** (`admin@tecnoweb.com`) en otro navegador o ventana de incógnito.
2.  En el Dashboard de Admin, verás la lista de todos los trabajos. Busca el trabajo "Mesa de Centro" (estado **SOLICITADO**).
3.  Haz clic en **"Gestionar"**.
4.  Revisa los detalles del cliente y la descripción.
5.  En la sección **"Crear Presupuesto"**, ingresa los costos estimados (Materiales, Mano de Obra, Otros).
6.  Haz clic en **"Enviar Presupuesto"**. El estado del trabajo cambiará a **PRESUPUESTADO**.

### Flujo 3: Aprobación y Pago (Cliente)
1.  Vuelve a la sesión del **Cliente**.
2.  Entra al detalle del trabajo "Mesa de Centro".
3.  Verás el presupuesto enviado. Haz clic en **"Aprobar Presupuesto"**.
4.  Serás redirigido a la página de **selección de método de pago**.
5.  **Pago de Contado:** 
    - Elige "Pago de Contado" y confirma.
    - Se generará un código QR para pagar el monto completo.
    - Escanea el QR con tu app bancaria.
6.  **Pago a Crédito (Cuotas):**
    - Elige "Pago a Crédito" y selecciona el número de cuotas (2-12 meses).
    - Se generará un código QR para pagar la **primera cuota**.
    - Escanea el QR para pagar la primera cuota.
    - Puedes ver el plan completo de cuotas haciendo clic en "Ver Plan de Cuotas".
    - Las cuotas restantes se generarán mensualmente y podrás pagarlas desde el plan de cuotas.
7.  **Confirmación:**
    - Una vez confirmado el pago (callback de PagoFácil), el trabajo cambiará a **EN_PRODUCCION** automáticamente.
    - Los materiales se descontarán del inventario.
    - Puedes descargar tu comprobante de pago en PDF.

### Flujo 4: Producción y Seguimiento (Propietario)
1.  Vuelve a la sesión del **Propietario**.
2.  El trabajo ahora está en estado **EN_PRODUCCION** (después del pago del cliente).
3.  Entra a gestionar el trabajo.
4.  **Registrar Gastos:** Usa el formulario para registrar gastos reales de materiales o transporte.
5.  **Registrar Avance:** Usa el formulario de seguimiento para actualizar el porcentaje de avance (ej. 50% "Corte de madera").
6.  Cuando el trabajo esté listo, registra un avance del **100%**. Esto cambiará automáticamente el estado a **FINALIZADO**.

### Flujo 5: Gestión de Inventario (Propietario)
1.  En el Dashboard de Admin, haz clic en **"Gestionar Inventario"**.
2.  Puedes agregar nuevos materiales, editar el stock existente o eliminar ítems.
