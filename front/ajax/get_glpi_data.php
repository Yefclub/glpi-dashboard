<?php
// Incluir arquivos necessários do GLPI
include("../../../../inc/includes.php");
include("../../../../inc/config.php");

global $DB;

// Verificar se o usuário está autenticado
Session::checkLoginUser();

// Obter ID do usuário atual
$userID = $_SESSION['glpiID'];

// Recuperar entidades do usuário
$sql_e = "SELECT value FROM glpi_plugin_dashboard_config WHERE name = 'entity' AND users_id = ".$userID."";
$result_e = $DB->query($sql_e);
$sel_ent = $DB->result($result_e, 0, 'value');

if($sel_ent == '') {
    $entities = $_SESSION['glpiactiveentities'];
    $sel_ent = implode(",", $entities);
}

// Verificar ação solicitada
if(isset($_POST['action'])) {
    $action = $_POST['action'];
    
    // Verificar o tipo de ação
    if($action == 'get_real_data') {
        $widget_type = $_POST['widget_type'];
        
        // Preparar array para dados
        $data = array();
        
        // Se solicitou todos os dados
        if($widget_type == 'all_data') {
            // Buscar dados de tickets
            $data['tickets'] = getTicketsSummary($DB, $sel_ent);
            $data['tickets_by_status'] = getTicketsByStatus($DB, $sel_ent);
            $data['tickets_evolution'] = getTicketsEvolution($DB, $sel_ent);
            $data['tickets_by_category'] = getTicketsByCategory($DB, $sel_ent);
            $data['tickets_by_requester'] = getTicketsByRequester($DB, $sel_ent);
            $data['satisfaction'] = getTicketSatisfaction($DB, $sel_ent);
            
            // Buscar dados de ativos
            $data['assets'] = getAssetsSummary($DB, $sel_ent);
            $data['assets_distribution'] = getAssetsDistribution($DB, $sel_ent);
        } else {
            // Buscar dados específicos para o widget solicitado
            switch($widget_type) {
                case 'tickets_summary':
                    $data = getTicketsSummary($DB, $sel_ent);
                    break;
                case 'tickets_status':
                    $data = getTicketsByStatus($DB, $sel_ent);
                    break;
                case 'tickets_evolution':
                    $data = getTicketsEvolution($DB, $sel_ent);
                    break;
                case 'assets_summary':
                    $data = getAssetsSummary($DB, $sel_ent);
                    break;
                case 'assets_distribution':
                    $data = getAssetsDistribution($DB, $sel_ent);
                    break;
                case 'tickets_by_category':
                    $data = getTicketsByCategory($DB, $sel_ent);
                    break;
                case 'tickets_by_requester':
                    $data = getTicketsByRequester($DB, $sel_ent);
                    break;
                case 'ticket_satisfaction':
                    $data = getTicketSatisfaction($DB, $sel_ent);
                    break;
            }
        }
        
        // Retornar dados como JSON
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}

/**
 * Função para obter resumo de chamados
 */
function getTicketsSummary($DB, $entities) {
    // Chamados novos/abertos
    $query_open = "SELECT COUNT(*) AS total
                  FROM glpi_tickets
                  WHERE glpi_tickets.is_deleted = 0
                  AND glpi_tickets.status IN (1, 2)
                  AND glpi_tickets.entities_id IN (".$entities.")";
    $result_open = $DB->query($query_open);
    $open = $DB->result($result_open, 0, 'total');
    
    // Chamados em andamento
    $query_process = "SELECT COUNT(*) AS total
                     FROM glpi_tickets
                     WHERE glpi_tickets.is_deleted = 0
                     AND glpi_tickets.status IN (3, 4)
                     AND glpi_tickets.entities_id IN (".$entities.")";
    $result_process = $DB->query($query_process);
    $processing = $DB->result($result_process, 0, 'total');
    
    // Chamados fechados
    $query_closed = "SELECT COUNT(*) AS total
                    FROM glpi_tickets
                    WHERE glpi_tickets.is_deleted = 0
                    AND glpi_tickets.status IN (5, 6)
                    AND glpi_tickets.entities_id IN (".$entities.")";
    $result_closed = $DB->query($query_closed);
    $closed = $DB->result($result_closed, 0, 'total');
    
    return array(
        'open' => $open,
        'processing' => $processing,
        'closed' => $closed
    );
}

/**
 * Função para obter chamados por status
 */
function getTicketsByStatus($DB, $entities) {
    $status_map = array(
        1 => __('Novo', 'dashboard'),
        2 => __('Em atendimento (atribuído)', 'dashboard'),
        3 => __('Em atendimento (planejado)', 'dashboard'),
        4 => __('Pendente', 'dashboard'),
        5 => __('Resolvido', 'dashboard'),
        6 => __('Fechado', 'dashboard')
    );
    
    $query = "SELECT status, COUNT(*) AS total
             FROM glpi_tickets
             WHERE glpi_tickets.is_deleted = 0
             AND glpi_tickets.entities_id IN (".$entities.")
             GROUP BY status";
    $result = $DB->query($query);
    
    $data = array();
    while($row = $DB->fetchAssoc($result)) {
        $status_name = isset($status_map[$row['status']]) ? $status_map[$row['status']] : 'Status '.$row['status'];
        $data[$status_name] = (int)$row['total'];
    }
    
    return $data;
}

/**
 * Função para obter evolução de chamados
 */
function getTicketsEvolution($DB, $entities) {
    $data = array();
    
    // Últimos 12 meses
    for($i = 11; $i >= 0; $i--) {
        $month_start = date('Y-m-01', strtotime("-$i months"));
        $month_end = date('Y-m-t', strtotime("-$i months"));
        $month_name = strftime('%b/%Y', strtotime($month_start));
        
        // Chamados abertos no mês
        $query_open = "SELECT COUNT(*) AS total
                      FROM glpi_tickets
                      WHERE glpi_tickets.is_deleted = 0
                      AND glpi_tickets.entities_id IN (".$entities.")
                      AND date_format(glpi_tickets.date, '%Y-%m-%d') BETWEEN '".$month_start."' AND '".$month_end."'";
        $result_open = $DB->query($query_open);
        $opened = $DB->result($result_open, 0, 'total');
        
        // Chamados fechados no mês
        $query_closed = "SELECT COUNT(*) AS total
                        FROM glpi_tickets
                        WHERE glpi_tickets.is_deleted = 0
                        AND glpi_tickets.entities_id IN (".$entities.")
                        AND glpi_tickets.status IN (5, 6)
                        AND date_format(glpi_tickets.closedate, '%Y-%m-%d') BETWEEN '".$month_start."' AND '".$month_end."'";
        $result_closed = $DB->query($query_closed);
        $closed = $DB->result($result_closed, 0, 'total');
        
        $data[] = array(
            'month_name' => $month_name,
            'opened' => (int)$opened,
            'closed' => (int)$closed
        );
    }
    
    return $data;
}

/**
 * Função para obter chamados por categoria
 */
function getTicketsByCategory($DB, $entities) {
    $query = "SELECT glpi_itilcategories.name, COUNT(*) AS total
             FROM glpi_tickets
             LEFT JOIN glpi_itilcategories ON (glpi_tickets.itilcategories_id = glpi_itilcategories.id)
             WHERE glpi_tickets.is_deleted = 0
             AND glpi_tickets.entities_id IN (".$entities.")
             GROUP BY glpi_itilcategories.name
             ORDER BY total DESC
             LIMIT 10";
    $result = $DB->query($query);
    
    $data = array();
    while($row = $DB->fetchAssoc($result)) {
        $category = !empty($row['name']) ? $row['name'] : __('Sem categoria', 'dashboard');
        $data[$category] = (int)$row['total'];
    }
    
    return $data;
}

/**
 * Função para obter chamados por solicitante
 */
function getTicketsByRequester($DB, $entities) {
    $query = "SELECT concat(glpi_users.firstname, ' ', glpi_users.realname) AS fullname, COUNT(DISTINCT glpi_tickets.id) AS total
             FROM glpi_tickets_users
             INNER JOIN glpi_tickets ON (glpi_tickets_users.tickets_id = glpi_tickets.id)
             INNER JOIN glpi_users ON (glpi_tickets_users.users_id = glpi_users.id)
             WHERE glpi_tickets.is_deleted = 0
             AND glpi_tickets.entities_id IN (".$entities.")
             AND glpi_tickets_users.type = 1
             GROUP BY fullname
             ORDER BY total DESC
             LIMIT 10";
    $result = $DB->query($query);
    
    $data = array();
    while($row = $DB->fetchAssoc($result)) {
        $requester = !empty($row['fullname']) ? $row['fullname'] : __('Usuário desconhecido', 'dashboard');
        $data[$requester] = (int)$row['total'];
    }
    
    return $data;
}

/**
 * Função para obter dados de satisfação de chamados
 */
function getTicketSatisfaction($DB, $entities) {
    // Total de avaliações respondidas
    $query_count = "SELECT COUNT(*) AS total
                   FROM glpi_ticketsatisfactions
                   INNER JOIN glpi_tickets ON (glpi_ticketsatisfactions.tickets_id = glpi_tickets.id)
                   WHERE glpi_tickets.entities_id IN (".$entities.")
                   AND glpi_ticketsatisfactions.satisfaction IS NOT NULL";
    $result_count = $DB->query($query_count);
    $count = $DB->result($result_count, 0, 'total');
    
    // Média de satisfação
    $query_avg = "SELECT AVG(satisfaction) AS average
                 FROM glpi_ticketsatisfactions
                 INNER JOIN glpi_tickets ON (glpi_ticketsatisfactions.tickets_id = glpi_tickets.id)
                 WHERE glpi_tickets.entities_id IN (".$entities.")
                 AND glpi_ticketsatisfactions.satisfaction IS NOT NULL";
    $result_avg = $DB->query($query_avg);
    $average = $DB->result($result_avg, 0, 'average');
    
    if($average === null) {
        $average = 0;
    }
    
    return array(
        'count' => (int)$count,
        'average' => (float)$average
    );
}

/**
 * Função para obter resumo de ativos
 */
function getAssetsSummary($DB, $entities) {
    // Computadores
    $query_comp = "SELECT COUNT(*) AS total
                  FROM glpi_computers
                  WHERE glpi_computers.is_deleted = 0
                  AND glpi_computers.entities_id IN (".$entities.")";
    $result_comp = $DB->query($query_comp);
    $computers = $DB->result($result_comp, 0, 'total');
    
    // Monitores
    $query_mon = "SELECT COUNT(*) AS total
                 FROM glpi_monitors
                 WHERE glpi_monitors.is_deleted = 0
                 AND glpi_monitors.entities_id IN (".$entities.")";
    $result_mon = $DB->query($query_mon);
    $monitors = $DB->result($result_mon, 0, 'total');
    
    // Impressoras
    $query_print = "SELECT COUNT(*) AS total
                   FROM glpi_printers
                   WHERE glpi_printers.is_deleted = 0
                   AND glpi_printers.entities_id IN (".$entities.")";
    $result_print = $DB->query($query_print);
    $printers = $DB->result($result_print, 0, 'total');
    
    return array(
        'computers' => (int)$computers,
        'monitors' => (int)$monitors,
        'printers' => (int)$printers
    );
}

/**
 * Função para obter distribuição de ativos
 */
function getAssetsDistribution($DB, $entities) {
    $data = array();
    
    // Computadores
    $query_comp = "SELECT COUNT(*) AS total
                  FROM glpi_computers
                  WHERE glpi_computers.is_deleted = 0
                  AND glpi_computers.entities_id IN (".$entities.")";
    $result_comp = $DB->query($query_comp);
    $data[__('Computadores', 'dashboard')] = (int)$DB->result($result_comp, 0, 'total');
    
    // Monitores
    $query_mon = "SELECT COUNT(*) AS total
                 FROM glpi_monitors
                 WHERE glpi_monitors.is_deleted = 0
                 AND glpi_monitors.entities_id IN (".$entities.")";
    $result_mon = $DB->query($query_mon);
    $data[__('Monitores', 'dashboard')] = (int)$DB->result($result_mon, 0, 'total');
    
    // Impressoras
    $query_print = "SELECT COUNT(*) AS total
                   FROM glpi_printers
                   WHERE glpi_printers.is_deleted = 0
                   AND glpi_printers.entities_id IN (".$entities.")";
    $result_print = $DB->query($query_print);
    $data[__('Impressoras', 'dashboard')] = (int)$DB->result($result_print, 0, 'total');
    
    // Telefones
    $query_phone = "SELECT COUNT(*) AS total
                   FROM glpi_phones
                   WHERE glpi_phones.is_deleted = 0
                   AND glpi_phones.entities_id IN (".$entities.")";
    $result_phone = $DB->query($query_phone);
    $data[__('Telefones', 'dashboard')] = (int)$DB->result($result_phone, 0, 'total');
    
    // Software
    $query_soft = "SELECT COUNT(*) AS total
                  FROM glpi_softwares
                  WHERE glpi_softwares.is_deleted = 0
                  AND glpi_softwares.entities_id IN (".$entities.")";
    $result_soft = $DB->query($query_soft);
    $data[__('Software', 'dashboard')] = (int)$DB->result($result_soft, 0, 'total');
    
    return $data;
} 