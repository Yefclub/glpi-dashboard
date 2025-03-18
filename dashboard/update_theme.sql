-- Atualiza o tema padrão para todos os usuários
UPDATE glpi_plugin_dashboard_config 
SET value = 'skin-default1.css' 
WHERE name = 'theme' AND (value = '' OR value IS NULL);

-- Insere o tema padrão para usuários que não têm configuração
INSERT INTO glpi_plugin_dashboard_config (users_id, name, value)
SELECT DISTINCT u.id, 'theme', 'skin-default1.css'
FROM glpi_users u
LEFT JOIN glpi_plugin_dashboard_config c ON u.id = c.users_id AND c.name = 'theme'
WHERE c.id IS NULL; 