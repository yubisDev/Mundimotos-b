-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 27-11-2025 a las 22:57:11
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
-- Base de datos: `mundimotos`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `ProductoID` int(11) NOT NULL,
  `TipoProductoID` int(11) NOT NULL,
  `NombreProducto` varchar(255) NOT NULL,
  `Marca` varchar(100) NOT NULL,
  `Modelo` varchar(100) DEFAULT NULL,
  `PrecioVenta` decimal(10,2) NOT NULL,
  `Stock` int(11) NOT NULL,
  `Descripcion` text DEFAULT NULL,
  `Estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`ProductoID`, `TipoProductoID`, `NombreProducto`, `Marca`, `Modelo`, `PrecioVenta`, `Stock`, `Descripcion`, `Estado`) VALUES
(1, 1, 'YZF-R1', 'Yamaha', 'R1', 18999.00, 3, 'Motocicleta Superbike de 1000cc para circuito y alto rendimiento.', 'Activo'),
(2, 1, 'Ninja ZX-6R', 'Kawasaki', 'ZX-6R', 11900.00, 5, 'Deportiva de media cilindrada, equilibrio perfecto entre calle y pista.', 'Activo'),
(3, 1, 'CBR1000RR-R', 'Honda', 'Fireblade', 25500.00, 2, 'La Fireblade de última generación, máxima tecnología y potencia.', 'Activo'),
(4, 1, 'GSX-R750', 'Suzuki', 'Gixxer', 13500.00, 4, 'La única 750cc deportiva, maneja como una 600, empuja como una 1000.', 'Activo'),
(5, 1, 'RS 660', 'Aprilia', 'RS 660', 10899.00, 6, 'Deportiva bicilíndrica ligera y ágil, excelente para carretera.', 'Activo'),
(6, 1, 'RC 390', 'KTM', 'RC 390', 6000.00, 8, 'Deportiva monocilíndrica ligera, ideal para iniciarse en la pista.', 'Activo'),
(7, 2, 'Street Triple RS', 'Triumph', '765', 13200.00, 4, 'Naked de alto rendimiento con motor tricilíndrico único y componentes premium.', 'Activo'),
(8, 2, 'MT-07', 'Yamaha', 'MT-07', 7999.00, 10, 'Naked bicilíndrica, gran éxito de ventas por su par y facilidad de manejo.', 'Activo'),
(9, 2, '1290 Super Duke R', 'KTM', 'Beast', 19999.00, 3, 'La \"Bestia\" Naked. Potencia extrema y electrónica avanzada.', 'Activo'),
(10, 2, 'Z900', 'Kawasaki', 'Z900', 9800.00, 7, 'Naked tetracilíndrica, equilibrio entre potencia y confort.', 'Activo'),
(11, 2, 'CB650R', 'Honda', 'CB650R', 9500.00, 5, 'Neo Sports Café, motor tetracilíndrico en línea, estilo moderno y clásico.', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tiposdeproducto`
--

CREATE TABLE `tiposdeproducto` (
  `TipoProductoID` int(11) NOT NULL,
  `NombreTipo` varchar(100) NOT NULL,
  `Estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tiposdeproducto`
--

INSERT INTO `tiposdeproducto` (`TipoProductoID`, `NombreTipo`, `Estado`) VALUES
(1, 'Motos Deportivas (Sport)', 'Activo'),
(2, 'Motos Naked (Estándar)', 'Activo');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`ProductoID`),
  ADD KEY `TipoProductoID` (`TipoProductoID`);

--
-- Indices de la tabla `tiposdeproducto`
--
ALTER TABLE `tiposdeproducto`
  ADD PRIMARY KEY (`TipoProductoID`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `ProductoID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `tiposdeproducto`
--
ALTER TABLE `tiposdeproducto`
  MODIFY `TipoProductoID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`TipoProductoID`) REFERENCES `tiposdeproducto` (`TipoProductoID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
