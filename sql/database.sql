-- =========================================================
-- Base de datos: Sillas y Mesas Hernández
-- Fecha: 2025-10-07
-- =========================================================

CREATE TABLE Cliente (
    idCliente INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    direccion VARCHAR(150),
    correo VARCHAR(100)
);

CREATE TABLE ClienteParticular (
    idCliente INT PRIMARY KEY,
    fechaNacimiento DATE,
    CURP VARCHAR(20),
    FOREIGN KEY (idCliente) REFERENCES Cliente(idCliente)
        ON DELETE CASCADE
);


CREATE TABLE ClienteEmpresa (
    idCliente INT PRIMARY KEY,
    razonSocial VARCHAR(100),
    RFC VARCHAR(15),
    contactoEmpresa VARCHAR(100),
    FOREIGN KEY (idCliente) REFERENCES Cliente(idCliente)
        ON DELETE CASCADE
);


CREATE TABLE Telefono (
    idTelefono INT AUTO_INCREMENT PRIMARY KEY,
    idCliente INT,
    numero VARCHAR(15) NOT NULL,
    FOREIGN KEY (idCliente) REFERENCES Cliente(idCliente)
        ON DELETE CASCADE
);


CREATE TABLE Articulo (
    idArticulo INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    estado VARCHAR(20) DEFAULT 'Disponible',
    cantidadTotal INT NOT NULL DEFAULT 0,
    cantidadDisponible INT NOT NULL DEFAULT 0,
    cantidadEnUso INT NOT NULL DEFAULT 0,
    cantidadDanada INT NOT NULL DEFAULT 0,
    costoRenta DECIMAL(10,2) NOT NULL
);


CREATE TABLE Silla (
    idArticulo INT PRIMARY KEY,
    tipoSilla VARCHAR(50),
    material VARCHAR(50),
    FOREIGN KEY (idArticulo) REFERENCES Articulo(idArticulo)
        ON DELETE CASCADE
);


CREATE TABLE Mesa (
    idArticulo INT PRIMARY KEY,
    forma VARCHAR(50),
    capacidadPersonas INT,
    tamaño VARCHAR(50),
    FOREIGN KEY (idArticulo) REFERENCES Articulo(idArticulo)
        ON DELETE CASCADE
);


CREATE TABLE Accesorio (
    idArticulo INT PRIMARY KEY,
    descripcion VARCHAR(100),
    fragilidad VARCHAR(30),
    FOREIGN KEY (idArticulo) REFERENCES Articulo(idArticulo)
        ON DELETE CASCADE
);


CREATE TABLE Pedido (
    idPedido INT AUTO_INCREMENT PRIMARY KEY,
    idCliente INT,
    fechaPedido DATE,
    fechaEvento DATE NOT NULL,
    fechaEntrega DATE,
    fechaDevolucion DATE,
    montoTotal DECIMAL(10,2),
    FOREIGN KEY (idCliente) REFERENCES Cliente(idCliente)
        ON DELETE CASCADE
);


CREATE TABLE DetallePedido (
    idPedido INT,
    idArticulo INT,
    cantidad INT NOT NULL,
    PRIMARY KEY (idPedido, idArticulo),
    FOREIGN KEY (idPedido) REFERENCES Pedido(idPedido)
        ON DELETE CASCADE,
    FOREIGN KEY (idArticulo) REFERENCES Articulo(idArticulo)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS DetallePedidoPaquete (
    idPedido INT NOT NULL,
    idPaquete INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    PRIMARY KEY (idPedido, idPaquete),
    INDEX (idPaquete),
    CONSTRAINT FK_DetallePedidoPaquete_Pedido FOREIGN KEY (idPedido) REFERENCES Pedido(idPedido) ON DELETE CASCADE,
    CONSTRAINT FK_DetallePedidoPaquete_Paquete FOREIGN KEY (idPaquete) REFERENCES Paquete(idPaquete) ON DELETE CASCADE
);


CREATE TABLE Pago (
    idPago INT AUTO_INCREMENT PRIMARY KEY,
    idPedido INT,
    fechaPago DATE,
    monto DECIMAL(10,2),
    estadoPago VARCHAR(20) DEFAULT 'Pendiente',
    FOREIGN KEY (idPedido) REFERENCES Pedido(idPedido)
        ON DELETE CASCADE
);


CREATE TABLE Paquete (
    idPaquete INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    precioEspecial DECIMAL(10,2)
);


CREATE TABLE PaqueteArticulo (
    idPaquete INT,
    idArticulo INT,
    cantidad INT NOT NULL,
    PRIMARY KEY (idPaquete, idArticulo),
    FOREIGN KEY (idPaquete) REFERENCES Paquete(idPaquete)
        ON DELETE CASCADE,
    FOREIGN KEY (idArticulo) REFERENCES Articulo(idArticulo)
        ON DELETE CASCADE
);


CREATE TABLE PedidoPaquete (
    idPedido INT,
    idPaquete INT,
    cantidad INT NOT NULL,
    PRIMARY KEY (idPedido, idPaquete),
    FOREIGN KEY (idPedido) REFERENCES Pedido(idPedido)
        ON DELETE CASCADE,
    FOREIGN KEY (idPaquete) REFERENCES Paquete(idPaquete)
        ON DELETE CASCADE
);

CREATE TABLE Usuario (
    idUsuario INT AUTO_INCREMENT PRIMARY KEY,
    nombreUsuario VARCHAR(50) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    rol VARCHAR(20) DEFAULT 'admin'
);
