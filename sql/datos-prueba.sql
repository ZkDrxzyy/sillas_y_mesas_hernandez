-- =========================================================
-- DATOS DE PRUEBA — Sillas y Mesas Hernández
-- Fecha: Octubre 2025
-- =========================================================

-- =========================================================
-- CLIENTES
-- =========================================================
INSERT INTO Cliente (nombre, direccion, correo)
VALUES 
('María López', 'Calle 12 #45, León', 'maria.lopez@gmail.com'),
('Eventos San Juan', 'Blvd. Hidalgo 321, León', 'contacto@eventossanjuan.com'),
('Juan Pérez', 'Av. Universidad 987, Guanajuato', 'juanperez@hotmail.com'),
('Corporativo Fiesta', 'Zona Centro 101, Salamanca', 'ventas@fiesta.mx'),
('Ana Torres', 'Calle Lomas 22, Celaya', 'ana.torres@gmail.com');

-- CLIENTES PARTICULARES
INSERT INTO ClienteParticular (idCliente, fechaNacimiento, CURP)
VALUES 
(1, '1992-03-12', 'LOPM920312GDFRNS09'),
(3, '1987-09-30', 'PEJJ870930HGTRLS05'),
(5, '1995-11-08', 'TOAA951108MDFRNS08');

-- CLIENTES EMPRESA
INSERT INTO ClienteEmpresa (idCliente, razonSocial, RFC, contactoEmpresa)
VALUES 
(2, 'Eventos San Juan S.A. de C.V.', 'ESJ980412MN3', 'Luis Herrera'),
(4, 'Corporativo Fiesta', 'CFI010207PZ1', 'Laura Mendoza');

-- TELÉFONOS
INSERT INTO Telefono (idCliente, numero)
VALUES
(1, '4771234567'),
(2, '4779988776'),
(3, '4734455667'),
(4, '4641138790'),
(5, '4612223344');

-- =========================================================
-- ARTÍCULOS
-- =========================================================
INSERT INTO Articulo (nombre, estado, cantidadTotal, costoRenta)
VALUES
('Silla plegable blanca', 'Disponible', 100, 15.00),
('Silla Tiffany dorada', 'Disponible', 50, 25.00),
('Mesa redonda grande', 'Disponible', 20, 80.00),
('Mesa rectangular mediana', 'Disponible', 15, 70.00),
('Centro de mesa cristal', 'Disponible', 40, 30.00),
('Mantel blanco', 'Disponible', 60, 20.00);

-- SILLAS
INSERT INTO Silla (idArticulo, tipoSilla, material)
VALUES
(1, 'Plegable', 'Plástico'),
(2, 'Tiffany', 'Madera');

-- MESAS
INSERT INTO Mesa (idArticulo, forma, capacidadPersonas, tamaño)
VALUES
(3, 'Redonda', 10, 'Grande'),
(4, 'Rectangular', 8, 'Mediana');

-- ACCESORIOS
INSERT INTO Accesorio (idArticulo, descripcion, fragilidad)
VALUES
(5, 'Centro decorativo de cristal', 'Alta'),
(6, 'Mantel de tela blanca', 'Baja');

-- =========================================================
-- PEDIDOS
-- =========================================================
INSERT INTO Pedido (idCliente, fechaEvento, fechaEntrega, fechaDevolucion, montoTotal)
VALUES
(1, '2025-10-20', '2025-10-19', '2025-10-21', 600.00),
(2, '2025-10-25', '2025-10-24', '2025-10-26', 1250.00),
(3, '2025-11-02', '2025-11-01', '2025-11-03', 900.00);

-- DETALLE PEDIDO
INSERT INTO DetallePedido (idPedido, idArticulo, cantidad)
VALUES
(1, 1, 20),
(1, 3, 2),
(2, 2, 30),
(2, 4, 5),
(3, 5, 10),
(3, 6, 10);

-- =========================================================
-- PAGOS
-- =========================================================
INSERT INTO Pago (idPedido, fechaPago, monto, estadoPago)
VALUES
(1, '2025-10-18', 300.00, 'Anticipo'),
(1, '2025-10-21', 300.00, 'Pagado'),
(2, '2025-10-23', 500.00, 'Anticipo'),
(2, '2025-10-25', 750.00, 'Pagado'),
(3, '2025-10-31', 450.00, 'Anticipo');

-- =========================================================
-- PAQUETES
-- =========================================================
INSERT INTO Paquete (nombre, precioEspecial)
VALUES
('Paquete básico de 10 personas', 200.00),
('Paquete premium de 30 personas', 600.00);

-- PAQUETE - ARTÍCULO
INSERT INTO PaqueteArticulo (idPaquete, idArticulo, cantidad)
VALUES
(1, 1, 10),
(1, 3, 1),
(2, 2, 30),
(2, 4, 3),
(2, 5, 5);

-- PEDIDO - PAQUETE
INSERT INTO PedidoPaquete (idPedido, idPaquete, cantidad)
VALUES
(1, 1, 1),
(2, 2, 1);

-- =========================================================
-- USUARIOS (para el login)
-- =========================================================
INSERT INTO Usuario (nombreUsuario, contrasena, rol)
VALUES
('admin', '$2y$10$yYbI7cxV7.lcUxyQlgpIMuEyxPaL8F8sUMSmOEK7j0LRjaYuxiPcG', 'Administrador'),
('empleado', '$2y$10$kV20AJ/b1utbs8po4YZhXe6b9vnHZpoXHWMLhyb6kxljf0Z3E3u9q', 'Empleado'),
('consultor', '$2y$10$QixDYOfFdpZsElc7XpnxzuO5Sz24h41LpWhX6oOeY6TKf5hZk1g2G', 'Consultor');

-- Contraseñas originales:
-- admin / admin123
-- empleado / empleado123
-- consultor / consultor123
