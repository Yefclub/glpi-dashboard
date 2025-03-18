-- Criar tabela para layouts personalizados
CREATE TABLE IF NOT EXISTS glpi_plugin_dashboard_layouts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    users_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    layout_data TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (users_id) REFERENCES glpi_users(id)
);

-- Adicionar permissão para acessar dashboard personalizado
INSERT INTO glpi_profilerights (profiles_id, name, rights)
SELECT id, 'plugin_dashboard_custom', 1
FROM glpi_profiles
WHERE id NOT IN (
    SELECT profiles_id 
    FROM glpi_profilerights 
    WHERE name = 'plugin_dashboard_custom'
);

-- Adicionar link no menu
INSERT INTO glpi_plugin_dashboard_menu (name, link, icon, order)
VALUES ('Dashboard Personalizado', 'custom_layout.php', 'fa fa-puzzle-piece', 100); 