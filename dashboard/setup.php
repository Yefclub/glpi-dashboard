<?php


function plugin_init_dashboard() {

   global $PLUGIN_HOOKS, $LANG ;
	
	$PLUGIN_HOOKS['csrf_compliant']['dashboard'] = true;
	
   Plugin::registerClass('PluginDashboardConfig', [
      'addtabon' => ['Entity']
   ]);  
          
    $PLUGIN_HOOKS["menu_toadd"]['dashboard'] = array('plugins'  => 'PluginDashboardConfig');
    $PLUGIN_HOOKS['config_page']['dashboard'] = 'front/index.php';
                
    // Adicionar suporte a notificações
    $PLUGIN_HOOKS['item_add']['dashboard'] = [
       'Ticket' => ['PluginDashboardConfig', 'itemAdd'],
       'Change' => ['PluginDashboardConfig', 'itemAdd'],
       'Problem' => ['PluginDashboardConfig', 'itemAdd']
    ];
}


function plugin_version_dashboard(){
	global $DB, $LANG;

	return [
		'name'           => __('Dashboard', 'dashboard'),
		'version'        => '2.0.0',
		'author'         => '<a href="https://forge.glpi-project.org/projects/dashboard">Stevenes Donato</a>',
		'license'        => 'GPLv2+',
		'homepage'       => 'https://forge.glpi-project.org/projects/dashboard',
		'minGlpiVersion' => '10.0',
		'requirements'   => [
			'glpi' => [
				'min' => '10.0',
				'max' => '11.0'
			]
		]
	];
}


function plugin_dashboard_check_prerequisites(){
     if (version_compare(GLPI_VERSION, '10.0', '>=')) {
         return true;
     } else {
         echo "GLPI version NOT compatible. Requires GLPI >= 10.0";
         return false;
     }
}


function plugin_dashboard_check_config($verbose=false){
	if ($verbose) {
		echo 'Installed / not configured';
	}
	return true;
}


?>
