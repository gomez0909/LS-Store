-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 01-10-2026 a las 20:49:39
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `ls_store`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `estado` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `estado`) VALUES
(1, 'Camisetas', 'activo'),
(2, 'Hoodies', 'activo'),
(3, 'Pantalones', 'activo'),
(4, 'Accesorios', 'activo'),
(6, 'Zapatos', 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedido`
--

CREATE TABLE `detalle_pedido` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `talla` varchar(10) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `detalle_pedido`
--

INSERT INTO `detalle_pedido` (`id`, `pedido_id`, `producto_id`, `talla`, `cantidad`, `precio_unitario`, `subtotal`) VALUES
(1, 1, 2, 'XL', 1, 69900.00, 69900.00),
(2, 2, 2, 'XL', 1, 69900.00, 69900.00),
(3, 3, 2, 'S', 5, 69900.00, 349500.00),
(4, 4, 2, 'L', 1, 69900.00, 69900.00),
(5, 4, 3, 'XL', 1, 109900.00, 109900.00),
(6, 4, 4, 'L', 1, 149900.00, 149900.00),
(7, 4, 5, 'L', 1, 49900.00, 49900.00),
(8, 5, 6, 'S', 2, 69900.00, 139800.00),
(9, 5, 6, 'M', 4, 69900.00, 279600.00),
(10, 5, 6, 'L', 5, 69900.00, 349500.00),
(11, 6, 2, 'XL', 1, 69900.00, 69900.00),
(12, 7, 7, 'XL', 1, 67900.00, 67900.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `ciudad` varchar(100) NOT NULL,
  `departamento` varchar(100) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `estado` varchar(30) NOT NULL,
  `fecha_pedido` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `usuario_id`, `total`, `direccion`, `ciudad`, `departamento`, `telefono`, `estado`, `fecha_pedido`) VALUES
(1, 1, 69900.00, 'Calle 96BB sur #50AA - 22', 'La estrella', 'Antioquia', '3135509690', 'cancelado', '2026-09-18 05:47:33'),
(2, 1, 69900.00, 'mi cas', 'asasd', 'asdsad', '3135509690', 'cancelado', '2026-09-18 06:01:08'),
(3, 1, 349500.00, 'Calle 127 sur #38A', 'Caldas', 'Antioquia', '3135509690', 'cancelado', '2026-09-18 06:05:05'),
(4, 1, 379600.00, 'Calle 96BB sur #50AA - 22', 'La estrella', 'Antioquia', '3135509690', 'cancelado', '2026-09-18 06:14:08'),
(5, 2, 768900.00, 'tvyb', 'gvbhj', 'drtfy', '3158765433', 'entregado', '2026-09-18 07:15:19'),
(6, 1, 69900.00, 'rtjhgwthwerth', 'tikujyhegfgwteh', 'ujhwy64jbgwe', '3135509690', 'entregado', '2026-09-18 18:54:13'),
(7, 2, 67900.00, 'zesxdrcfvgbhnj', 'wexrctfvygbhunjm', 'wzexrctfvghbjn', '3158765433', 'cancelado', '2026-09-18 19:02:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `estado` varchar(20) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `categoria_id`, `nombre`, `descripcion`, `precio`, `imagen`, `color`, `estado`, `fecha_creacion`) VALUES
(2, 1, 'Camiseta Negra Oversize - Edición Fast Love', 'Camiseta urbana negra de corte oversize', 69900.00, 'camisa-negra.png', 'negro', 'activo', '2026-09-18 04:01:00'),
(3, 2, 'Hoodie Negro Oversize Obsidian', 'Hoodie negro urbano de corte oversize', 109900.00, 'hoodie-negro.png', 'negro', 'activo', '2026-09-18 06:10:00'),
(4, 3, 'Jeans Baggy Distressed Black', 'Jean negro urbano de corte baggy', 149900.00, 'baggy-negro.png', 'negro', 'activo', '2026-09-18 06:10:57'),
(5, 4, 'Gorra Negra Soft Core', 'Gorra negra de estilo urbano', 49900.00, 'gorra-negra.png', 'negro', 'activo', '2026-09-18 06:11:57'),
(6, 1, 'Camiseta Blanca Oversize - Edición Fast Love', 'Tela firme que se siente pesada, no barata\r\nDiseños con actitud que imponen presencia\r\nAguanta uso, lavadas y calle sin deformarse\r\nHechas para darle duro, no para guardarla', 69900.00, 'producto_f9d946ffb7686d3f.png', 'blanca', 'activo', '2026-09-18 06:32:31'),
(7, 1, 'Camiseta Negra Oversize Notorius', 'Tela firme que se siente pesada, no barata\r\nDiseños con actitud que imponen presencia\r\nAguanta uso, lavadas y calle sin deformarse\r\nHechas para darle duro, no para guardarla', 67900.00, 'producto_be83397573c3ea8e.png', 'negra', 'activo', '2026-09-18 18:59:12'),
(8, 6, 'DC Shoes - Court Graffik', 'Zapatillas de piel Negro Hombre', 149900.00, 'producto_9e5b4006b930ebe5.png', 'negros', 'activo', '2026-09-18 19:07:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_tallas`
--

CREATE TABLE `producto_tallas` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `talla` varchar(10) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `producto_tallas`
--

INSERT INTO `producto_tallas` (`id`, `producto_id`, `talla`, `stock`) VALUES
(1, 2, 'S', 6),
(2, 2, 'M', 8),
(3, 2, 'L', 4),
(4, 2, 'XL', 0),
(5, 4, 'L', 2),
(6, 4, 'XL', 3),
(7, 3, 'XL', 3),
(8, 3, 'L', 3),
(9, 5, 'L', 2),
(10, 6, 'S', 0),
(11, 6, 'M', 0),
(12, 6, 'L', 0),
(13, 6, 'XL', 0),
(14, 4, 'S', 1),
(15, 4, 'M', 1),
(30, 3, 'S', 0),
(31, 3, 'M', 0),
(34, 5, 'S', 2),
(35, 5, 'M', 1),
(37, 5, 'XL', 1),
(38, 7, 'S', 5),
(39, 7, 'M', 5),
(40, 7, 'L', 5),
(41, 7, 'XL', 5),
(42, 8, 'S', 1),
(43, 8, 'M', 1),
(44, 8, 'L', 1),
(45, 8, 'XL', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `rol` varchar(20) NOT NULL,
  `estado` varchar(20) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombres`, `apellidos`, `correo`, `password`, `telefono`, `rol`, `estado`, `fecha_registro`) VALUES
(1, 'Luis Mateo', 'Gómez Alzate', 'mateo@gmail.com', '$2y$10$ZRyNORnvaOZOihzZE/evZOBaOGcNG8v9FmaBdLjHaliwjD6t1JHfK', '3135509690', 'cliente', 'activo', '2026-09-18 05:17:26'),
(2, 'Jose David', 'Agudelo Montoya', 'lonso@gmail.com', '$2y$10$jfcK38lc11jz6UScCQPx8.tA9p3qxbxWC6I66ip90BO5Emp7zllf6', '3158765433', 'administrador', 'activo', '2026-09-18 06:17:56');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedido_id` (`pedido_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Categorias` (`categoria_id`);

--
-- Indices de la tabla `producto_tallas`
--
ALTER TABLE `producto_tallas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `producto_id` (`producto_id`,`talla`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `producto_tallas`
--
ALTER TABLE `producto_tallas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD CONSTRAINT `detalle_pedido_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detalle_pedido_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `Categorias` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);

--
-- Filtros para la tabla `producto_tallas`
--
ALTER TABLE `producto_tallas`
  ADD CONSTRAINT `Tallas` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
