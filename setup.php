<?php


function plugin_init_dashboard() {

   global $PLUGIN_HOOKS, $LANG ;
	
	$PLUGIN_HOOKS['csrf_compliant']['dashboard'] = true;
	
   Plugin::registerClass('PluginDashboardConfig', array(
         'classname'           => 'PluginDashboardConfig'
   ));   
   
   $PLUGIN_HOOKS["menu_toadd"]['dashboard'] = array('plugins'  => 'PluginDashboardConfig');
   $PLUGIN_HOOKS['config_page']['dashboard'] = 'front/index.php';
   
   $PLUGIN_HOOKS['change_profile']['dashboard'] = array('PluginDashboardProfile','changeprofile');
   
   $PLUGIN_HOOKS['add_javascript']['dashboard'][] = 'js/highcharts.js';  
   $PLUGIN_HOOKS['add_javascript']['dashboard'][] = 'js/highcharts-3d.js';
   $PLUGIN_HOOKS['add_javascript']['dashboard'][] = 'js/highcharts-more.js';
   $PLUGIN_HOOKS['add_javascript']['dashboard'][] = 'js/modules/exporting.js';
   
   $PLUGIN_HOOKS['add_javascript']['dashboard'][] = 'js/modules/no-data-to-display.js';
    
   // Adicionar link para o Dashboard Personalizado no menu
   $PLUGIN_HOOKS['menu_entry']['dashboard'] = 'front/index.php';
   $PLUGIN_HOOKS['submenu_entry']['dashboard']['options']['custom_dashboard'] = [
       'title' => 'Dashboard Personalizado',
       'page'  => 'front/custom_dashboard.php'
   ];
   
   $PLUGIN_HOOKS['add_css']['dashboard'][] = 'css/style-'.$_SESSION['style'].".css";  
}


function plugin_version_dashboard(){
	global $DB, $LANG;

	return array('name'			=> __('Dashboard','dashboard'),
					'version' 			=> '1.0.3',
                    'author'	        => '<a href="mailto:contato@servicetic.com.br">Stevenes Donato/99NET/Service TIC</b></a>',					
					'license'		 	=> 'GPLv2+',
					'homepage'			=> 'https://github.com/serviceticst/glpi-plugin-dashboard/releases',
					'minGlpiVersion'	=> '10.0'
					);
}


function plugin_dashboard_check_prerequisites(){
     if (version_compare(GLPI_VERSION, '10.0', '>=')){
         return true;
     } else {
         echo "GLPI version NOT compatible. Requires GLPI >= 10.0";
     }
}


function plugin_dashboard_check_config($verbose=false){
	if ($verbose) {
		echo 'Installed / not configured';
	}
	return true;
}


?>
