<?php

function plugin_dashboard_install() {
	global $DB;
	
	$migration = new Migration(200);
	
	// Criar tabela de contagem
	if (!$DB->tableExists("glpi_plugin_dashboard_count")) {
		$migration->addField("glpi_plugin_dashboard_count", "type", "integer", [
			'after' => 'id'
		]);
		$migration->addField("glpi_plugin_dashboard_count", "id", "integer", [
			'after' => 'type'
		]);
		$migration->addField("glpi_plugin_dashboard_count", "quant", "integer", [
			'after' => 'id'
		]);
		$migration->addKey("glpi_plugin_dashboard_count", "id", "PRIMARY KEY");
		$migration->addKey("glpi_plugin_dashboard_count", "type", "KEY");
		
		$DB->query("INSERT INTO glpi_plugin_dashboard_count (type,quant) VALUES ('1','1')");
	}
	
	// Criar tabela de mapa
	if (!$DB->tableExists("glpi_plugin_dashboard_map")) {
		$migration->addField("glpi_plugin_dashboard_map", "id", "integer", [
			'auto_increment' => true
		]);
		$migration->addField("glpi_plugin_dashboard_map", "entities_id", "integer", [
			'after' => 'id'
		]);
		$migration->addField("glpi_plugin_dashboard_map", "location", "string", [
			'after' => 'entities_id',
			'length' => 50
		]);
		$migration->addField("glpi_plugin_dashboard_map", "lat", "float", [
			'after' => 'location'
		]);
		$migration->addField("glpi_plugin_dashboard_map", "lng", "float", [
			'after' => 'lat'
		]);
		$migration->addKey("glpi_plugin_dashboard_map", "id", "PRIMARY KEY");
		$migration->addKey("glpi_plugin_dashboard_map", "entities_id", "KEY");
		$migration->addKey("glpi_plugin_dashboard_map", "location", "UNIQUE KEY");
	}
	
	// Criar tabela de configuração
	if (!$DB->tableExists("glpi_plugin_dashboard_config")) {
		$migration->addField("glpi_plugin_dashboard_config", "id", "integer", [
			'auto_increment' => true
		]);
		$migration->addField("glpi_plugin_dashboard_config", "name", "string", [
			'after' => 'id',
			'length' => 50
		]);
		$migration->addField("glpi_plugin_dashboard_config", "value", "string", [
			'after' => 'name',
			'length' => 125
		]);
		$migration->addField("glpi_plugin_dashboard_config", "users_id", "string", [
			'after' => 'value',
			'length' => 25,
			'default' => ''
		]);
		$migration->addKey("glpi_plugin_dashboard_config", "id", "PRIMARY KEY");
		$migration->addKey("glpi_plugin_dashboard_config", ["name", "users_id"], "UNIQUE KEY");
	}
	
	// Atualizar configurações de entidades
	$query_ent = "SELECT users_id FROM glpi_plugin_dashboard_config WHERE name = 'entity' AND value = '-1'";
	$result = $DB->query($query_ent);
	
	while ($row = $DB->fetchAssoc($result)) {
		$DB->update(
			'glpi_plugin_dashboard_config',
			['value' => ''],
			[
				'name' => 'entity',
				'users_id' => $row['users_id']
			]
		);
	}
	
	return true;
}

function plugin_dashboard_uninstall() {
	global $DB;
	
	$tables = [
		"glpi_plugin_dashboard_count",
		"glpi_plugin_dashboard_map",
		"glpi_plugin_dashboard_config"
	];
	
	foreach ($tables as $table) {
		$DB->query("DROP TABLE IF EXISTS `$table`");
	}
	
	return true;
}

?>
