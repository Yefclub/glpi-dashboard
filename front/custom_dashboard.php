<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 Dashboard - Plugin para GLPI
 Copyright (C) 2022 by the Dashboard Development Team.
 https://github.com/plugins/dashboard
 -------------------------------------------------------------------------
 */

include ("../../../inc/includes.php");
include ("../../../inc/config.php");

global $DB, $CFG_GLPI;

Session::checkLoginUser();
Session::checkRight("profile", READ);

// Verifica se o plugin está ativado
$plugin = new Plugin();
if (!$plugin->isActivated('dashboard')) {
    Html::displayNotFoundError();
}

$theme = $_SESSION['glpipalette'];

Html::header(__('Dashboard Personalizado', 'dashboard'), $_SERVER['PHP_SELF'], "plugins", "dashboard", "custom");
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo __('Dashboard Personalizado', 'dashboard'); ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    
    <!-- Importação de estilos -->
    <link rel="stylesheet" type="text/css" href="../css/style-<?php echo $theme; ?>.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/fonts.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/modern-dashboard.css">
    
    <!-- jQuery UI (já incluído no GLPI) -->
    <script type="text/javascript" src="../../../lib/jquery/js/jquery.js"></script>
    <script type="text/javascript" src="../../../lib/jquery/js/jquery-ui.js"></script>
    
    <!-- Highcharts para gráficos -->
    <script type="text/javascript" src="../js/highcharts.js"></script>
    <script type="text/javascript" src="../js/highcharts-more.js"></script>
    <script type="text/javascript" src="../js/modules/exporting.js"></script>
    
    <!-- JavaScript do Dashboard -->
    <script type="text/javascript" src="../assets/js/modern-dashboard.js"></script>
</head>
<body>
    <div class="dashboard-content">
        <!-- Cabeçalho do Dashboard -->
        <div class="dashboard-header">
            <h1><?php echo __('Dashboard Personalizado', 'dashboard'); ?></h1>
            <p><?php echo __('Arraste e solte os widgets para personalizar seu painel', 'dashboard'); ?></p>
        </div>
        
        <!-- Barra de ferramentas do dashboard -->
        <div class="dashboard-toolbar">
            <button type="button" id="add-widget-btn" class="btn btn-primary">
                <i class="fas fa-plus-circle btn-icon"></i> <?php echo __('Adicionar Widget', 'dashboard'); ?>
            </button>
            
            <button type="button" id="save-dashboard-btn" class="btn btn-success">
                <i class="fas fa-save btn-icon"></i> <?php echo __('Salvar Dashboard', 'dashboard'); ?>
            </button>
            
            <button type="button" id="reset-dashboard-btn" class="btn btn-outline">
                <i class="fas fa-undo btn-icon"></i> <?php echo __('Restaurar Padrão', 'dashboard'); ?>
            </button>
            
            <button type="button" id="share-dashboard-btn" class="btn btn-secondary">
                <i class="fas fa-share-alt btn-icon"></i> <?php echo __('Compartilhar', 'dashboard'); ?>
            </button>
        </div>
        
        <!-- Container de widgets -->
        <div class="widgets-container">
            <!-- Os widgets serão inseridos aqui dinamicamente pelo JavaScript -->
            <div class="no-widgets">
                <p><?php echo __('Nenhum widget adicionado. Clique no botão "Adicionar Widget" para começar.', 'dashboard'); ?></p>
            </div>
        </div>
    </div>
    
    <!-- Lógica para carregar dados do dashboard do usuário atual -->
    <script type="text/javascript">
        $(document).ready(function() {
            // Configurações iniciais
            const userId = <?php echo $_SESSION['glpiID']; ?>;
            
            // Salvar dashboard
            $('#save-dashboard-btn').on('click', function() {
                const positions = [];
                
                $('.widgets-container .widget').each(function(index) {
                    const widgetId = $(this).data('widget-id');
                    const widgetType = $(this).data('widget-type');
                    const widgetTitle = $(this).find('.widget-title').text();
                    const theme = $(this).attr('class').split(' ').find(c => c.startsWith('theme-'));
                    
                    // Coletando dimensões
                    const width = $(this).width();
                    const height = $(this).height();
                    
                    positions.push({
                        id: widgetId,
                        type: widgetType,
                        title: widgetTitle,
                        theme: theme ? theme.replace('theme-', '') : '',
                        position: index,
                        width: width,
                        height: height
                    });
                });
                
                // Aqui você implementaria a chamada AJAX para salvar
                console.log('Salvando dashboard para o usuário:', userId);
                console.log('Configuração dos widgets:', positions);
                
                // Simulando chamada AJAX de salvamento
                /*
                $.ajax({
                    url: '../ajax/save_dashboard.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        user_id: userId,
                        widgets: positions
                    },
                    success: function(response) {
                        if (response.success) {
                            showNotification('success', 'Dashboard salvo', 'Seu dashboard foi salvo com sucesso.');
                        } else {
                            showNotification('error', 'Erro ao salvar', response.message);
                        }
                    },
                    error: function() {
                        showNotification('error', 'Erro de conexão', 'Não foi possível salvar seu dashboard.');
                    }
                });
                */
                
                // Para fins de demonstração:
                showNotification('success', 'Dashboard salvo', 'Seu dashboard foi salvo com sucesso.');
            });
            
            // Restaurar padrão
            $('#reset-dashboard-btn').on('click', function() {
                if (confirm('<?php echo __('Tem certeza que deseja restaurar o dashboard para o padrão? Todos os widgets personalizados serão perdidos.', 'dashboard'); ?>')) {
                    // Aqui você implementaria a chamada AJAX para restaurar o padrão
                    console.log('Restaurando dashboard padrão para o usuário:', userId);
                    
                    // Limpa os widgets atuais
                    $('.widgets-container').empty().append('<div class="no-widgets"><p><?php echo __('Nenhum widget adicionado. Clique no botão "Adicionar Widget" para começar.', 'dashboard'); ?></p></div>');
                    
                    // Mostra notificação
                    showNotification('success', 'Dashboard restaurado', 'Seu dashboard foi restaurado para o padrão.');
                }
            });
            
            // Compartilhar dashboard
            $('#share-dashboard-btn').on('click', function() {
                const modal = `
                <div class="modal-overlay" id="share-dashboard-modal">
                    <div class="modal-container">
                        <div class="modal-header">
                            <h3 class="modal-title"><?php echo __('Compartilhar Dashboard', 'dashboard'); ?></h3>
                            <button type="button" class="modal-close">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="form-label"><?php echo __('Compartilhar com', 'dashboard'); ?></label>
                                <select class="form-control form-select" id="share-type">
                                    <option value="user"><?php echo __('Usuários específicos', 'dashboard'); ?></option>
                                    <option value="group"><?php echo __('Grupos', 'dashboard'); ?></option>
                                    <option value="profile"><?php echo __('Perfis', 'dashboard'); ?></option>
                                </select>
                            </div>
                            <div class="form-group" id="share-users-container">
                                <label class="form-label"><?php echo __('Selecione os usuários', 'dashboard'); ?></label>
                                <select class="form-control form-select" id="share-users" multiple>
                                    <?php
                                    // Em produção, você buscaria os usuários do banco de dados
                                    // Simulando alguns usuários para demonstração
                                    $users = [
                                        1 => 'Administrador',
                                        2 => 'Técnico',
                                        3 => 'Usuário Normal',
                                        4 => 'Gerente'
                                    ];
                                    
                                    foreach ($users as $id => $name) {
                                        if ($id != $_SESSION['glpiID']) {
                                            echo "<option value='$id'>$name</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label"><?php echo __('Permissões', 'dashboard'); ?></label>
                                <select class="form-control form-select" id="share-permission">
                                    <option value="view"><?php echo __('Visualizar apenas', 'dashboard'); ?></option>
                                    <option value="edit"><?php echo __('Visualizar e editar', 'dashboard'); ?></option>
                                </select>
                            </div>
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="share-as-template">
                                    <label class="form-check-label" for="share-as-template">
                                        <?php echo __('Compartilhar como modelo (os destinatários receberão uma cópia)', 'dashboard'); ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary modal-cancel"><?php echo __('Cancelar', 'dashboard'); ?></button>
                            <button type="button" class="btn btn-primary" id="share-confirm"><?php echo __('Compartilhar', 'dashboard'); ?></button>
                        </div>
                    </div>
                </div>
                `;
                
                $('body').append(modal);
                
                // Configura o botão de confirmação
                $(document).on('click', '#share-confirm', function() {
                    // Aqui você implementaria a lógica de compartilhamento
                    showNotification('success', 'Dashboard compartilhado', 'Seu dashboard foi compartilhado com sucesso.');
                    closeModals();
                });
                
                setTimeout(() => {
                    $('#share-dashboard-modal').addClass('active');
                }, 10);
            });
        });
    </script>
</body>
</html>

<?php Html::footer(); ?>
