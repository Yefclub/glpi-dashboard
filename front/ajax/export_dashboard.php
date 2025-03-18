<?php
// Incluir arquivos necessários do GLPI
include("../../../../inc/includes.php");
include("../../../../inc/config.php");

global $DB;

// Verificar se o usuário está autenticado
Session::checkLoginUser();

// Obter ID do usuário atual
$userID = $_SESSION['glpiID'];

// Verificar ação solicitada
if(isset($_POST['action']) && $_POST['action'] == 'export') {
    $widget_type = $_POST['widget_type'];
    $export_format = $_POST['format'];
    
    // Recuperar entidades do usuário
    $sql_e = "SELECT value FROM glpi_plugin_dashboard_config WHERE name = 'entity' AND users_id = ".$userID."";
    $result_e = $DB->query($sql_e);
    $sel_ent = $DB->result($result_e, 0, 'value');
    
    if($sel_ent == '') {
        $entities = $_SESSION['glpiactiveentities'];
        $sel_ent = implode(",", $entities);
    }
    
    // Incluir arquivo com funções de dados
    include_once("get_glpi_data.php");
    
    switch($widget_type) {
        case 'tickets_summary':
            $data = getTicketsSummary($DB, $sel_ent);
            $title = __('Resumo de Chamados', 'dashboard');
            $headers = array(__('Status', 'dashboard'), __('Quantidade', 'dashboard'));
            $rows = array(
                array(__('Abertos', 'dashboard'), $data['open']),
                array(__('Em andamento', 'dashboard'), $data['processing']),
                array(__('Fechados', 'dashboard'), $data['closed'])
            );
            break;
            
        case 'tickets_status':
            $data = getTicketsByStatus($DB, $sel_ent);
            $title = __('Chamados por Status', 'dashboard');
            $headers = array(__('Status', 'dashboard'), __('Quantidade', 'dashboard'));
            $rows = array();
            foreach($data as $status => $count) {
                $rows[] = array($status, $count);
            }
            break;
            
        case 'tickets_evolution':
            $data = getTicketsEvolution($DB, $sel_ent);
            $title = __('Evolução de Chamados', 'dashboard');
            $headers = array(__('Mês', 'dashboard'), __('Abertos', 'dashboard'), __('Fechados', 'dashboard'));
            $rows = array();
            foreach($data as $month) {
                $rows[] = array($month['month_name'], $month['opened'], $month['closed']);
            }
            break;
            
        case 'tickets_by_category':
            $data = getTicketsByCategory($DB, $sel_ent);
            $title = __('Chamados por Categoria', 'dashboard');
            $headers = array(__('Categoria', 'dashboard'), __('Quantidade', 'dashboard'));
            $rows = array();
            foreach($data as $category => $count) {
                $rows[] = array($category, $count);
            }
            break;
            
        case 'tickets_by_requester':
            $data = getTicketsByRequester($DB, $sel_ent);
            $title = __('Chamados por Solicitante', 'dashboard');
            $headers = array(__('Solicitante', 'dashboard'), __('Quantidade', 'dashboard'));
            $rows = array();
            foreach($data as $requester => $count) {
                $rows[] = array($requester, $count);
            }
            break;
            
        case 'ticket_satisfaction':
            $data = getTicketSatisfaction($DB, $sel_ent);
            $title = __('Satisfação de Chamados', 'dashboard');
            $headers = array(__('Métrica', 'dashboard'), __('Valor', 'dashboard'));
            $rows = array(
                array(__('Total de avaliações', 'dashboard'), $data['count']),
                array(__('Média de satisfação', 'dashboard'), number_format($data['average'], 1))
            );
            break;
            
        case 'assets_summary':
            $data = getAssetsSummary($DB, $sel_ent);
            $title = __('Resumo de Ativos', 'dashboard');
            $headers = array(__('Tipo de Ativo', 'dashboard'), __('Quantidade', 'dashboard'));
            $rows = array(
                array(__('Computadores', 'dashboard'), $data['computers']),
                array(__('Monitores', 'dashboard'), $data['monitors']),
                array(__('Impressoras', 'dashboard'), $data['printers'])
            );
            break;
            
        case 'assets_distribution':
            $data = getAssetsDistribution($DB, $sel_ent);
            $title = __('Distribuição de Ativos', 'dashboard');
            $headers = array(__('Tipo de Ativo', 'dashboard'), __('Quantidade', 'dashboard'));
            $rows = array();
            foreach($data as $asset_type => $count) {
                $rows[] = array($asset_type, $count);
            }
            break;
            
        default:
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(array('error' => 'Invalid widget type'));
            exit;
    }
    
    // Gerar a exportação no formato solicitado
    switch($export_format) {
        case 'csv':
            exportToCSV($title, $headers, $rows);
            break;
            
        case 'excel':
            exportToExcel($title, $headers, $rows);
            break;
            
        case 'pdf':
            exportToPDF($title, $headers, $rows);
            break;
            
        default:
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(array('error' => 'Invalid export format'));
            exit;
    }
} else {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(array('error' => 'Invalid request'));
    exit;
}

/**
 * Exportar dados para CSV
 */
function exportToCSV($title, $headers, $rows) {
    // Definir headers para download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . str_replace(' ', '_', $title) . '_' . date('Y-m-d') . '.csv"');
    
    // Criar arquivo CSV
    $output = fopen('php://output', 'w');
    
    // UTF-8 BOM para Excel reconhecer corretamente os caracteres especiais
    fputs($output, "\xEF\xBB\xBF");
    
    // Adicionar cabeçalhos
    fputcsv($output, $headers);
    
    // Adicionar linhas de dados
    foreach($rows as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}

/**
 * Exportar dados para Excel (XLSX)
 */
function exportToExcel($title, $headers, $rows) {
    // Fallback para CSV se não tiver biblioteca para Excel
    exportToCSV($title, $headers, $rows);
}

/**
 * Exportar dados para PDF
 */
function exportToPDF($title, $headers, $rows) {
    // HTML para o PDF
    $html = '<html><head><meta charset="utf-8"><title>' . $title . '</title>';
    $html .= '<style>
        body { font-family: Arial, sans-serif; }
        h1 { text-align: center; font-size: 16pt; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #2c3e50; color: white; font-weight: bold; text-align: left; padding: 8px; }
        td { border: 1px solid #ddd; padding: 8px; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .footer { text-align: center; font-size: 9pt; margin-top: 20px; color: #666; }
    </style>';
    $html .= '</head><body>';
    $html .= '<h1>' . $title . '</h1>';
    
    // Tabela de dados
    $html .= '<table>';
    
    // Cabeçalhos
    $html .= '<tr>';
    foreach($headers as $header) {
        $html .= '<th>' . $header . '</th>';
    }
    $html .= '</tr>';
    
    // Linhas de dados
    foreach($rows as $row) {
        $html .= '<tr>';
        foreach($row as $cell) {
            $html .= '<td>' . $cell . '</td>';
        }
        $html .= '</tr>';
    }
    
    $html .= '</table>';
    
    // Rodapé
    $html .= '<div class="footer">Gerado em ' . date('d/m/Y H:i:s') . ' - GLPI Dashboard</div>';
    $html .= '</body></html>';
    
    // Usar HTML2PDF ou outra biblioteca
    // Como fallback, vamos apenas exibir o HTML
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . str_replace(' ', '_', $title) . '_' . date('Y-m-d') . '.html"');
    echo $html;
    exit;
} 