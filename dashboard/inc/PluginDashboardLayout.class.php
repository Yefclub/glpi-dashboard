<?php

class PluginDashboardLayout extends CommonDBTM {
    static $rightname = 'plugin_dashboard_custom';
    
    static function getTypeName($nb = 0) {
        return _n('Layout Personalizado', 'Layouts Personalizados', $nb, 'dashboard');
    }
    
    static function canCreate() {
        return Session::haveRight(self::$rightname, CREATE);
    }
    
    static function canView() {
        return Session::haveRight(self::$rightname, READ);
    }
    
    static function canUpdate() {
        return Session::haveRight(self::$rightname, UPDATE);
    }
    
    static function canDelete() {
        return Session::haveRight(self::$rightname, DELETE);
    }
    
    function prepareInputForAdd($input) {
        if (!isset($input['users_id'])) {
            $input['users_id'] = Session::getLoginUserID();
        }
        return $input;
    }
    
    function prepareInputForUpdate($input) {
        return $this->prepareInputForAdd($input);
    }
    
    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
        if (!$withtemplate) {
            if ($item->getType() == 'User') {
                return self::getTypeName(2);
            }
        }
        return '';
    }
    
    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        if ($item->getType() == 'User') {
            $layout = new self();
            $found = $layout->find(['users_id' => $item->getID()]);
            
            if (!empty($found)) {
                echo "<table class='tab_cadre_fixehov'>";
                echo "<tr><th>" . __('Nome', 'dashboard') . "</th>";
                echo "<th>" . __('Criado em', 'dashboard') . "</th>";
                echo "<th>" . __('Atualizado em', 'dashboard') . "</th>";
                echo "</tr>";
                
                foreach ($found as $row) {
                    echo "<tr>";
                    echo "<td>" . $row['name'] . "</td>";
                    echo "<td>" . $row['created_at'] . "</td>";
                    echo "<td>" . $row['updated_at'] . "</td>";
                    echo "</tr>";
                }
                
                echo "</table>";
            } else {
                echo "<p class='center b'>" . __('Nenhum layout encontrado', 'dashboard') . "</p>";
            }
        }
    }
} 