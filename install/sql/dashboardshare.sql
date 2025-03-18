-- Tabela para armazenar os compartilhamentos de widgets
CREATE TABLE IF NOT EXISTS `glpi_plugin_dashboard_share` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `users_id` int(11) NOT NULL,
  `shared_by` int(11) NOT NULL,
  `widget_type` varchar(255) NOT NULL,
  `widget_id` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `is_accepted` tinyint(1) NOT NULL DEFAULT '0',
  `date_creation` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `users_id` (`users_id`),
  KEY `shared_by` (`shared_by`),
  KEY `widget_type` (`widget_type`),
  KEY `is_accepted` (`is_accepted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; 