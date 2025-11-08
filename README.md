Sillas y Mesas Hernández — Sistema de Gestión

Autor: González González Erick Emiliano
       De La Rosa Hernández Tania
Curso: Bases de Datos — Prácticas 1–4
Fecha: Octubre 2025

--------------------------------------------------------------------
Índice
- Descripción general
- Estructura del repositorio
- Contenido de cada carpeta (resumido)
- Cómo ejecutar (local)
- Datos de prueba y scripts SQL
- Seguridad y nota sobre sql-injection
- Buenas prácticas y line endings
- Contacto
--------------------------------------------------------------------

Descripción general
Repositorio del proyecto académico Sillas y Mesas Hernández. Contiene el diseño de la base de datos (EER y relacional), scripts SQL de creación y carga de datos, y la implementación web en PHP para gestionar clientes, artículos, pedidos y pagos.

--------------------------------------------------------------------
Estructura del repositorio

sillas-y-mesas-hernandez/
├─ sql/
│  ├─ database.sql                             # Script DDL (creación de tablas, constraints, PK/FK)
│  └─ datos-prueba.sql                         # Datos de prueba coherentes (INSERTs)
│
├─ php/
│  ├─ index.php                                # Página principal (requiere sesión)
│  ├─ login.php                                # Login seguro (prepare + password_verify)
│  ├─ logout.php                               # Cerrar sesión
│  ├─ conexion.php                             # Conexión PDO (configurar credenciales)
│  ├─ clientes.php                             # CRUD clientes (listado)
│  ├─ nuevo_cliente.php                        # Formulario/insert cliente
│  ├─ editar_cliente.php                       # Editar cliente
│  ├─ eliminar_cliente.php                     # Eliminar cliente
│  ├─ articulos.php                            # CRUD artículos (listado)
│  ├─ nuevo_articulo.php
│  ├─ editar_articulo.php
│  ├─ eliminar_articulo.php
│  ├─ pedidos.php                              # Gestión de pedidos
│  ├─ nuevo_pedido.php
│  ├─ editar_pedido.php
│  ├─ eliminar_pedido.php
│  ├─ pagos.php                                # Gestión de pagos
│  ├─ nuevo_pago.php
│  ├─ editar_pago.php
│  ├─ eliminar_pago.php
│  ├─ paquetes.php                             # Gestión de paquetes
│  ├─ nuevo_paquete.php
│  ├─ editar_paquete.php
│  ├─ eliminar_paquete.php
│  ├─ ver_pedido.php                           # Ver detalle de pedido
│  └─ (otros archivos auxiliares)              # ej. test_conexion.php, hash.php (temporal)
│
├─ docs/
│  ├─ diagrama-entidad-relación.png            # Diagrama EER (imagen)
│  ├─ diagrama-entidad-relacion-extendido.png  # Diagrama EER-Extendido (imagen)
│  ├─ modelo-relacional.png                    # Diagrama Relacional Crow's Foot (imagen)
│  ├─ Práctica 1.pdf                           # Modelo Entidad-Relación (documento)
│  ├─ Práctica 2.pdf                           # Modelo Entidad-Relación-Extendido (documento)
│  ├─ Práctica 3.pdf                           # Transformación del modelo Entidad-Relación (Extendido) al modelo relacional (documento)
│  ├─ Práctica 4.pdf                           # Restricciones de dominio y asignación de permisos (documento)
│  └─ Práctica 4.1.pdf                         # SQL Injection (documento)
│
├─ sql-injection/
│  ├─ php/
│  │  ├─ index.php                             # Demo vulnerable (ejemplo)
│  │  ├─ login.php                             # Endpoint vulnerable
│  │  ├─ register.php                          # Demo de registro (ejemplo)
│  │  ├─ success.php                           # Demo de ingreso (ejemplo)
│  │  ├─ dump_users.php                        # texto plano de usuarios y contraseñas (texto plano)
│  │  └─ config.php                            # Conexión (demo)
│  └─ table-users.sql                          # Tabla de usuarios (ejemplo vulnerable)
│
└─ README.md
--------------------------------------------------------------------

Contenido de cada carpeta (resumido)

- sql/: scripts SQL para crear la base de datos en MySQL, insertar datos de prueba y ejecutar consultas de validación.
- php/: implementación PHP de la aplicación. Contiene módulos para clientes, artículos, pedidos, pagos, paquetes y autenticación. Usa PDO y prepared statements.
- docs/: material complementario: diagramas EER/relacional y capturas de pantalla para la documentación y presentación.
- sql-injection/: ambiente de demostración con ejemplos intencionalmente vulnerables para la práctica de inyección SQL.
  ADVERTENCIA: sólo ejecutar en entorno aislado/local y con fines demostrativos; no subir a hosting público.

--------------------------------------------------------------------

Datos de prueba y scripts SQL

- sql/databasePostgres.sql: crea tablas, secuencias y constraints (ejecutar primero).
- sql/datos_prueba.sql: inserta 5–10 registros por tablas principales (ejemplos coherentes).

--------------------------------------------------------------------
Seguridad y nota sobre sql-injection

- El código en php/ utiliza consultas preparadas (PDO) y password_hash() / password_verify() para proteger autenticación y evitar SQL injection.
- La carpeta sql-injection/ contiene código deliberadamente vulnerable para fines desmotrativos.

Recomendaciones:
- Ejecutar sólo en entorno local aislado (no acceso público).
- NO dejar esos archivos en un servidor público o con datos reales.
- Usar sql-injection/usuarios.sql únicamente con datos ficticios y resetear la BD después de las pruebas.

--------------------------------------------------------------------
Licencia y uso

Proyecto con fines académicos. Siéntete libre de revisar y usar el código con propósito educativo, citando al autor. No usar la parte vulnerable (sql-injection/) en producción.

--------------------------------------------------------------------
