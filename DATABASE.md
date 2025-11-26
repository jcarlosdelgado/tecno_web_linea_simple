# Base de Datos - ERP TecnoWeb

## Esquema Completo de Base de Datos

Sistema de gestión ERP para taller de tecnología con PostgreSQL.

---

## 📋 Tablas del Sistema (14 tablas)

### 1. **usuarios** (Autenticación y Usuarios)
```sql
CREATE TABLE usuarios (
    id_usuario BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    direccion TEXT,
    rol VARCHAR(20) CHECK (rol IN ('PROPIETARIO', 'CLIENTE')),
    email_verified_at TIMESTAMP,
    remember_token VARCHAR(100),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```
**Roles:**
- `PROPIETARIO`: Administrador del sistema
- `CLIENTE`: Usuario cliente

---

### 2. **servicios** (Catálogo de Servicios)
```sql
CREATE TABLE servicios (
    id_servicio BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    precio_base DECIMAL(10,2),
    imagen VARCHAR(255),
    categoria VARCHAR(100),
    activo BOOLEAN DEFAULT true,
    orden INTEGER DEFAULT 0,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```
**Descripción:** Servicios que ofrece el taller (reparaciones, mantenimiento, etc.)

---

### 3. **trabajos** (Trabajos/Órdenes de Servicio)
```sql
CREATE TABLE trabajos (
    id_trabajo BIGSERIAL PRIMARY KEY,
    id_cliente BIGINT REFERENCES usuarios(id_usuario),
    id_servicio BIGINT REFERENCES servicios(id_servicio),
    titulo VARCHAR(150),
    descripcion TEXT,
    imagenes_referencia JSON,
    estado VARCHAR(20) CHECK (estado IN ('SOLICITADO', 'PRESUPUESTADO', 'EN_PRODUCCION', 'FINALIZADO', 'CANCELADO')),
    fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_inicio TIMESTAMP,
    fecha_fin TIMESTAMP,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```
**Estados:**
- `SOLICITADO`: Cliente solicita el trabajo
- `PRESUPUESTADO`: Presupuesto enviado
- `EN_PRODUCCION`: Trabajo en proceso
- `FINALIZADO`: Trabajo completado
- `CANCELADO`: Trabajo cancelado

---

### 4. **presupuestos** (Cotizaciones)
```sql
CREATE TABLE presupuestos (
    id_presupuesto BIGSERIAL PRIMARY KEY,
    id_trabajo BIGINT REFERENCES trabajos(id_trabajo),
    descripcion TEXT,
    mano_obra DECIMAL(10,2) DEFAULT 0,
    otros_gastos DECIMAL(10,2) DEFAULT 0,
    monto_total DECIMAL(10,2) DEFAULT 0,
    fecha_emision DATE,
    fecha_validez DATE,
    notas TEXT,
    estado VARCHAR(20) CHECK (estado IN ('PENDIENTE', 'APROBADO', 'RECHAZADO')),
    fecha_respuesta TIMESTAMP,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```
**Cálculo:** `monto_total = materiales + mano_obra + otros_gastos`

---

### 5. **proveedores** (Proveedores de Materiales)
```sql
CREATE TABLE proveedores (
    id_proveedor BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    contacto VARCHAR(255),
    telefono VARCHAR(20),
    email VARCHAR(255),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

### 6. **materiales** (Inventario de Materiales)
```sql
CREATE TABLE materiales (
    id_material BIGSERIAL PRIMARY KEY,
    id_proveedor BIGINT REFERENCES proveedores(id_proveedor),
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    unidad_medida VARCHAR(50),
    precio_unitario DECIMAL(10,2) DEFAULT 0,
    cantidad_disponible DECIMAL(10,2) DEFAULT 0,
    stock_actual DECIMAL(10,2) DEFAULT 0,
    stock_minimo DECIMAL(10,2) DEFAULT 0,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```
**Unidades:** pza, kg, m, l, etc.

---

### 7. **presupuesto_detalle_material** (Materiales en Presupuesto)
```sql
CREATE TABLE presupuesto_detalle_material (
    id_detalle BIGSERIAL PRIMARY KEY,
    id_presupuesto BIGINT REFERENCES presupuestos(id_presupuesto),
    id_material BIGINT REFERENCES materiales(id_material),
    cantidad DECIMAL(10,2) NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL
);
```
**Cálculo:** `subtotal = cantidad * precio_unitario`

---

### 8. **movimientos_material** (Control de Inventario)
```sql
CREATE TABLE movimientos_material (
    id_movimiento BIGSERIAL PRIMARY KEY,
    id_material BIGINT REFERENCES materiales(id_material),
    id_trabajo BIGINT REFERENCES trabajos(id_trabajo),
    id_presupuesto BIGINT REFERENCES presupuestos(id_presupuesto),
    tipo_movimiento VARCHAR(20) CHECK (tipo_movimiento IN ('ENTRADA', 'SALIDA', 'AJUSTE')),
    cantidad DECIMAL(10,2) NOT NULL,
    precio_unitario DECIMAL(10,2),
    motivo TEXT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```
**Tipos:**
- `ENTRADA`: Compra de material
- `SALIDA`: Uso en trabajo (automático al aprobar presupuesto)
- `AJUSTE`: Corrección de inventario

---

### 9. **seguimiento_trabajo** (Progreso de Trabajos)
```sql
CREATE TABLE seguimiento_trabajo (
    id_seguimiento BIGSERIAL PRIMARY KEY,
    id_trabajo BIGINT REFERENCES trabajos(id_trabajo),
    descripcion TEXT NOT NULL,
    porcentaje_avance INTEGER DEFAULT 0 CHECK (porcentaje_avance >= 0 AND porcentaje_avance <= 100),
    horas_trabajadas DECIMAL(5,2),
    fotos JSON,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```
**Nota:** Al llegar a 100%, el trabajo se marca como FINALIZADO automáticamente.

---

### 10. **gastos** (Gastos de Trabajos)
```sql
CREATE TABLE gastos (
    id_gasto BIGSERIAL PRIMARY KEY,
    id_trabajo BIGINT REFERENCES trabajos(id_trabajo),
    descripcion TEXT NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    fecha DATE NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```
**Uso:** Gastos adicionales no contemplados en presupuesto

---

### 11. **pagos** (Pagos de Trabajos)
```sql
CREATE TABLE pagos (
    id_pago BIGSERIAL PRIMARY KEY,
    id_trabajo BIGINT REFERENCES trabajos(id_trabajo),
    monto DECIMAL(10,2) NOT NULL,
    tipo_pago VARCHAR(20) CHECK (tipo_pago IN ('CONTADO', 'CREDITO')),
    metodo_pago VARCHAR(50),
    estado VARCHAR(20) CHECK (estado IN ('PENDIENTE', 'COMPLETADO', 'CANCELADO')),
    fecha_pago TIMESTAMP,
    fecha_vencimiento DATE,
    referencia_transaccion VARCHAR(255),
    notas TEXT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

### 12. **pagos_credito** (Configuración de Crédito)
```sql
CREATE TABLE pagos_credito (
    id_pago_credito BIGSERIAL PRIMARY KEY,
    id_pago BIGINT REFERENCES pagos(id_pago),
    numero_cuotas INTEGER NOT NULL,
    monto_cuota DECIMAL(10,2) NOT NULL,
    interes DECIMAL(5,2) DEFAULT 0,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

### 13. **cuotas_pago** (Detalle de Cuotas)
```sql
CREATE TABLE cuotas_pago (
    id_cuota BIGSERIAL PRIMARY KEY,
    id_pago BIGINT REFERENCES pagos(id_pago),
    numero_cuota INTEGER NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    fecha_vencimiento DATE NOT NULL,
    fecha_pago TIMESTAMP,
    estado VARCHAR(20) CHECK (estado IN ('PENDIENTE', 'PAGADA', 'VENCIDA')),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

### 14. **pagos_pasarela** (Integración Pago Fácil)
```sql
CREATE TABLE pagos_pasarela (
    id_pago_pasarela BIGSERIAL PRIMARY KEY,
    id_pago BIGINT REFERENCES pagos(id_pago),
    pasarela VARCHAR(50) DEFAULT 'PagoFacil',
    transaction_id VARCHAR(255),
    qr_code TEXT,
    estado VARCHAR(20) CHECK (estado IN ('PENDIENTE', 'PROCESANDO', 'COMPLETADO', 'FALLIDO')),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 🔗 Diagrama de Relaciones

```
┌─────────────┐
│  usuarios   │
└──────┬──────┘
       │
       │ (1:N)
       ▼
┌─────────────┐      ┌─────────────┐
│  trabajos   │◄─────┤  servicios  │
└──────┬──────┘      └─────────────┘
       │
       ├─────────────────┬─────────────────┬──────────────┬──────────────┐
       │                 │                 │              │              │
       │ (1:N)           │ (1:N)           │ (1:N)        │ (1:N)        │ (1:N)
       ▼                 ▼                 ▼              ▼              ▼
┌──────────────┐  ┌──────────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐
│presupuestos  │  │ seguimiento  │  │  gastos  │  │  pagos   │  │movimientos│
└──────┬───────┘  └──────────────┘  └──────────┘  └────┬─────┘  └──────────┘
       │                                                 │
       │ (1:N)                                          │ (1:1)
       ▼                                                 ▼
┌──────────────┐                                  ┌──────────────┐
│   detalle    │                                  │pagos_credito │
│  materiales  │                                  └──────┬───────┘
└──────┬───────┘                                         │
       │                                                 │ (1:N)
       │ (N:1)                                           ▼
       ▼                                          ┌──────────────┐
┌──────────────┐      ┌──────────────┐           │cuotas_pago   │
│  materiales  │◄─────┤ proveedores  │           └──────────────┘
└──────────────┘      └──────────────┘
                                                  ┌──────────────┐
                                                  │pagos_pasarela│
                                                  └──────────────┘
```

---

## 📊 Resumen de Tablas por Módulo

### Autenticación (1)
- `usuarios`

### Servicios (1)
- `servicios`

### Trabajos (2)
- `trabajos`
- `seguimiento_trabajo`

### Presupuestos (2)
- `presupuestos`
- `presupuesto_detalle_material`

### Inventario (3)
- `proveedores`
- `materiales`
- `movimientos_material`

### Finanzas (5)
- `gastos`
- `pagos`
- `pagos_credito`
- `cuotas_pago`
- `pagos_pasarela`

**Total: 14 tablas principales**

---

## 🔑 Índices Recomendados

```sql
-- Búsquedas frecuentes
CREATE INDEX idx_trabajos_cliente ON trabajos(id_cliente);
CREATE INDEX idx_trabajos_estado ON trabajos(estado);
CREATE INDEX idx_trabajos_servicio ON trabajos(id_servicio);

CREATE INDEX idx_presupuestos_trabajo ON presupuestos(id_trabajo);
CREATE INDEX idx_presupuestos_estado ON presupuestos(estado);

CREATE INDEX idx_pagos_trabajo ON pagos(id_trabajo);
CREATE INDEX idx_pagos_estado ON pagos(estado);

CREATE INDEX idx_materiales_proveedor ON materiales(id_proveedor);
CREATE INDEX idx_movimientos_material ON movimientos_material(id_material);
CREATE INDEX idx_movimientos_trabajo ON movimientos_material(id_trabajo);
```

---

## 📈 Estadísticas de la Base de Datos

- **14 tablas principales**
- **3 tablas de Laravel** (cache, jobs, failed_jobs)
- **~80 columnas totales**
- **15 relaciones Foreign Key**
- **8 enumeraciones (CHECK constraints)**
- **Soporte completo para JSON** (fotos, imágenes)

---

## 🚀 Comandos de Instalación

```bash
# 1. Crear base de datos PostgreSQL
createdb tecno_web

# 2. Configurar .env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=tecno_web
DB_USERNAME=postgres
DB_PASSWORD=tu_password

# 3. Ejecutar migraciones
php artisan migrate

# 4. Ejecutar seeders (opcional)
php artisan db:seed
```

---

## 📝 Notas Importantes

1. **Timestamps:** Todas las tablas usan `creado_en` y `actualizado_en`
2. **Primary Keys:** Formato `id_tabla` (ej: `id_trabajo`)
3. **Foreign Keys:** Con ON DELETE CASCADE donde aplica
4. **Decimales:** Precisión (10,2) para montos y cantidades
5. **JSON:** Para arrays de imágenes y fotos
6. **CHECK Constraints:** Para validar estados y tipos
7. **Índices:** Optimizados para consultas frecuentes

---

## 🔒 Seguridad

- Contraseñas hasheadas con bcrypt
- Validación de roles en middleware
- Protección CSRF en formularios
- Sanitización de inputs
- Prepared statements (Eloquent ORM)

---

**Versión:** 1.0
**Última actualización:** 2025-11-22
**Motor:** PostgreSQL 14+
**Framework:** Laravel 11
