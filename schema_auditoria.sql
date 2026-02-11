-- Tabla de auditoría para registrar cambios
CREATE TABLE IF NOT EXISTS `auditoria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `accion` varchar(50) NOT NULL COMMENT 'CREAR, EDITAR, ELIMINAR, etc',
  `tabla` varchar(100) NOT NULL COMMENT 'Tabla afectada',
  `registro_id` int(11) NOT NULL COMMENT 'ID del registro afectado',
  `detalles` longtext COMMENT 'Descripción de los cambios',
  `ip_origen` varchar(45) COMMENT 'IP que hizo la acción',
  `user_agent` varchar(255) COMMENT 'User Agent del navegador',
  PRIMARY KEY (`id`),
  KEY `idx_accion` (`accion`),
  KEY `idx_tabla_registro` (`tabla`, `registro_id`),
  KEY `idx_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Agregar campos de timestamp a tabla productos (ignorar si ya existen)
ALTER TABLE `productos` ADD COLUMN `created_at` datetime DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `productos` ADD COLUMN `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
