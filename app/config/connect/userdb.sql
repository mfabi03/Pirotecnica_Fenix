SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `userdb` DEFAULT CHARACTER SET utf8mb4;
USE `userdb`;

CREATE TABLE `userdb`.`user` (
  `id` varchar(11) NOT NULL,
  `nombre` text NOT NULL,
  `apellido` text NOT NULL,
  `correo` text NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `user` (`id`, `nombre`, `apellido`, `correo`) VALUES
('12345678912', 'Jose', 'Perez', 'jose@gmail.com'),
('29517943210', 'Sabrina', 'Colmenarez', 'sabrina@gmail.com'),
('87654321098', 'Mario', 'Gonzalez', 'mario@gmail.com');


ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);
COMMIT;
