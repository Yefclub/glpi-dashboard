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
if(isset($_POST['action'])) {
    $response = array();
    
    switch($_POST['action']) {
        case 'share_widget':
            // Verificar dados necessários
            if(isset($_POST['widget_type']) && isset($_POST['users'])) {
                $widget_type = $_POST['widget_type'];
                $users = $_POST['users'];
                $widget_id = $_POST['widget_id'];
                $widget_title = $_POST['widget_title'];
                
                // ID do widget compartilhado
                $share_id = time() . '_' . $userID;
                
                // Inserir registro de compartilhamento
                foreach($users as $target_user) {
                    $query = "INSERT INTO glpi_plugin_dashboard_share (id, users_id, shared_by, widget_type, widget_id, title, date_creation) 
                              VALUES (NULL, $target_user, $userID, '$widget_type', '$widget_id', '$widget_title', NOW())";
                    $DB->query($query);
                }
                
                $response['status'] = 'success';
                $response['message'] = __('Widget compartilhado com sucesso', 'dashboard');
                $response['share_id'] = $share_id;
            } else {
                $response['status'] = 'error';
                $response['message'] = __('Dados incompletos para compartilhamento', 'dashboard');
            }
            break;
            
        case 'get_shared_widgets':
            // Buscar widgets compartilhados com o usuário atual
            $query = "SELECT s.*, u.firstname, u.realname 
                      FROM glpi_plugin_dashboard_share s
                      LEFT JOIN glpi_users u ON (s.shared_by = u.id)
                      WHERE s.users_id = $userID AND s.date_creation > DATE_SUB(NOW(), INTERVAL 30 DAY)
                      ORDER BY s.date_creation DESC";
            $result = $DB->query($query);
            
            $shared_widgets = array();
            while($row = $DB->fetchAssoc($result)) {
                $shared_widgets[] = array(
                    'id' => $row['id'],
                    'widget_type' => $row['widget_type'],
                    'widget_id' => $row['widget_id'],
                    'title' => $row['title'],
                    'shared_by' => trim($row['firstname'] . ' ' . $row['realname']),
                    'date_creation' => date('d/m/Y H:i', strtotime($row['date_creation']))
                );
            }
            
            $response['status'] = 'success';
            $response['shared_widgets'] = $shared_widgets;
            break;
            
        case 'get_users':
            // Buscar usuários para compartilhamento
            $query = "SELECT id, firstname, realname 
                      FROM glpi_users 
                      WHERE is_active = 1 AND id != $userID
                      ORDER BY realname, firstname";
            $result = $DB->query($query);
            
            $users = array();
            while($row = $DB->fetchAssoc($result)) {
                $users[] = array(
                    'id' => $row['id'],
                    'name' => trim($row['firstname'] . ' ' . $row['realname']),
                );
            }
            
            $response['status'] = 'success';
            $response['users'] = $users;
            break;
            
        case 'accept_widget':
            // Aceitar um widget compartilhado
            if(isset($_POST['share_id'])) {
                $share_id = $_POST['share_id'];
                
                // Atualizar status do compartilhamento
                $query = "UPDATE glpi_plugin_dashboard_share 
                          SET is_accepted = 1 
                          WHERE id = $share_id AND users_id = $userID";
                $DB->query($query);
                
                $response['status'] = 'success';
                $response['message'] = __('Widget aceito com sucesso', 'dashboard');
            } else {
                $response['status'] = 'error';
                $response['message'] = __('ID de compartilhamento não fornecido', 'dashboard');
            }
            break;
            
        case 'reject_widget':
            // Rejeitar um widget compartilhado
            if(isset($_POST['share_id'])) {
                $share_id = $_POST['share_id'];
                
                // Excluir compartilhamento
                $query = "DELETE FROM glpi_plugin_dashboard_share 
                          WHERE id = $share_id AND users_id = $userID";
                $DB->query($query);
                
                $response['status'] = 'success';
                $response['message'] = __('Widget rejeitado', 'dashboard');
            } else {
                $response['status'] = 'error';
                $response['message'] = __('ID de compartilhamento não fornecido', 'dashboard');
            }
            break;
            
        default:
            $response['status'] = 'error';
            $response['message'] = __('Ação desconhecida', 'dashboard');
            break;
    }
    
    // Retornar resposta como JSON
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(array('error' => 'Requisição inválida'));
} 