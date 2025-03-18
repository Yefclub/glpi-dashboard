<?php

include ("../../../inc/includes.php");
include ("../../../inc/config.php");

global $DB;

error_reporting(E_ERROR | E_PARSE);

Session::checkLoginUser();

$userID = $_SESSION['glpiID'];

# entity in index
$sql_e = "SELECT value FROM glpi_plugin_dashboard_config WHERE name = 'entity' AND users_id = ".$userID."";
$result_e = $DB->query($sql_e);
$sel_ent = $DB->result($result_e,0,'value');

if($sel_ent == '') { 	
	$entities = $_SESSION['glpiactiveentities'];
	$sel_ent = implode(",",$entities);		
	$query = "SELECT name FROM glpi_entities WHERE id IN (".$sel_ent.")";
	$result = $DB->query($query);
	$ent_name1 = $DB->result($result,0,'name');
	if(count($entities)>1) {
		$ent_name = __('Dashboard Personalizado','dashboard');
	} else {
		$ent_name = __('Dashboard Personalizado','dashboard')." :  ". $ent_name1 ;
	}		
}
elseif(strstr($sel_ent,",")) { 	
	$ent_name = __('Dashboard Personalizado','dashboard');
}
else {
	$query = "SELECT name FROM glpi_entities WHERE id IN (".$sel_ent.")";
	$result = $DB->query($query);
	$ent_name1 = $DB->result($result,0,'name');
	$ent_name = __('Dashboard Personalizado','dashboard')." :  ". $ent_name1 ;
}

# years in index
$sql_y = "SELECT value FROM glpi_plugin_dashboard_config WHERE name = 'num_years' AND users_id = ".$userID."";
$result_y = $DB->query($sql_y);
$num_years = $DB->result($result_y,0,'value');

if($num_years == '') {
	$num_years = 0;
}

# color theme
$sql_theme = "SELECT value FROM glpi_plugin_dashboard_config WHERE name = 'theme' AND users_id = ".$userID."";
$result_theme = $DB->query($sql_theme);
$theme = $DB->result($result_theme,0,'value');
$style = $theme;

if($theme == '' || substr($theme,0,5) == 'skin-' ) {
	$theme = 'material.css';
	$style = 'material.css';
}

$_SESSION['theme'] = $theme;
$_SESSION['style'] = $theme;

# background
$sql_back = "SELECT value FROM glpi_plugin_dashboard_config WHERE name = 'back' AND users_id = ".$userID."";
$result_back = $DB->query($sql_back);
$back = $DB->result($result_back,0,'value');

if($back == '') {
	$back = 'bg1.jpg';	
}
$_SESSION['back'] = $back;

# charts colors 
$sql_colors = "SELECT value FROM glpi_plugin_dashboard_config WHERE name = 'charts_colors' AND users_id = ".$userID."";
$result_colors = $DB->query($sql_colors);
$colors = $DB->result($result_colors,0,'value');

if($colors == '') {
	$colors = 'default.js';	
}

$_SESSION['charts_colors'] = $colors;

?>

<!DOCTYPE html>
<html>
<head>
    <title>GLPI - Dashboard - Dashboard Personalizado</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Pragma" content="public">    
    
    <link rel="icon" href="img/dash.ico" type="image/x-icon" />
    <link rel="shortcut icon" href="img/dash.ico" type="image/x-icon" />    
    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/bootstrap-responsive.css" rel="stylesheet">
    <link href="css/font-awesome.css" type="text/css" rel="stylesheet" />
    
    <script src="js/jquery.min.js" type="text/javascript"></script>
    <script src="js/bootstrap.min.js" type="text/javascript"></script>
    <script src="js/jquery-ui.min.js" type="text/javascript"></script>

    <?php echo '<link rel="stylesheet" type="text/css" href="./css/style-'.$_SESSION['style'].'">';  ?> 
    <link href="css/custom-dashboard.css" rel="stylesheet" type="text/css">
    
    <script src="js/highcharts.js" type="text/javascript"></script>
    <script src="js/highcharts-more.js" type="text/javascript"></script>
    <script src="js/highcharts-3d.js" type="text/javascript"></script>
</head>

<body style="background-color: #e5e5e5;">

<div id="container-fluid">
    <div id="head-lg" class="fluid" style="width: 100%; margin-top: -20px; z-index: 100;">
        <a href="./index.php" style="margin-top: 13px;">
            <i class="fa fa-home" style="font-size:14pt; margin-left:30px; margin-top:15px; color:#FFF;"></i>
            <span style="font-size:10pt; color:#FFF; font-weight:bold; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">Home</span>
        </a>
        
        <div id="titulo" style="margin-top: 15px; color: #FFF; width: 800px; margin-left: 35px;">
            <?php echo $ent_name; ?>  
        </div>
        
        <div id="titulo2" style="margin-top: -25px; color: #FFF; width: 800px; margin-left: 35px;">
            <?php echo __('Construtor de Dashboard','dashboard'); ?>  
        </div>
        
        <div id="options" class="dropdown" style="margin-top: -25px; margin-right: 25px; float: right;">
            <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">
                <?php echo __('Opções','dashboard'); ?>
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-right">
                <li><a href="#" id="save-dashboard"><?php echo __('Salvar Dashboard','dashboard'); ?></a></li>
                <li><a href="#" id="load-dashboard"><?php echo __('Carregar Dashboard','dashboard'); ?></a></li>
                <li><a href="#" id="new-dashboard"><?php echo __('Novo Dashboard','dashboard'); ?></a></li>
                <li class="divider"></li>
                <li><a href="#" id="add-widget"><?php echo __('Adicionar Widget','dashboard'); ?></a></li>
            </ul>
        </div>
    </div>

    <div class="container-fluid" style="margin-top: 40px;">
        <div class="row-fluid">
            <div class="span12">
                <!-- Área de trabalho do dashboard -->
                <div id="dashboard-widgets" class="widgets-container">
                    <!-- Widgets serão adicionados aqui -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal para adicionar widgets -->
    <div class="modal fade" id="addWidgetModal" tabindex="-1" role="dialog" aria-labelledby="addWidgetModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="addWidgetModalLabel"><?php echo __('Adicionar Widget','dashboard'); ?></h4>
                </div>
                <div class="modal-body">
                    <div class="widget-list">
                        <h5><?php echo __('Chamados','dashboard'); ?></h5>
                        <div class="row-fluid">
                            <div class="span4 widget-item" data-type="tickets_summary" data-width="6" data-height="4">
                                <div class="widget-preview">
                                    <i class="fa fa-ticket fa-3x"></i>
                                    <p><?php echo __('Resumo de Chamados','dashboard'); ?></p>
                                </div>
                            </div>
                            <div class="span4 widget-item" data-type="tickets_status" data-width="6" data-height="8">
                                <div class="widget-preview">
                                    <i class="fa fa-pie-chart fa-3x"></i>
                                    <p><?php echo __('Chamados por Status','dashboard'); ?></p>
                                </div>
                            </div>
                            <div class="span4 widget-item" data-type="tickets_evolution" data-width="12" data-height="8">
                                <div class="widget-preview">
                                    <i class="fa fa-line-chart fa-3x"></i>
                                    <p><?php echo __('Evolução de Chamados','dashboard'); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row-fluid">
                            <div class="span4 widget-item" data-type="tickets_by_category" data-width="6" data-height="8">
                                <div class="widget-preview">
                                    <i class="fa fa-bar-chart fa-3x"></i>
                                    <p><?php echo __('Chamados por Categoria','dashboard'); ?></p>
                                </div>
                            </div>
                            <div class="span4 widget-item" data-type="tickets_by_requester" data-width="6" data-height="8">
                                <div class="widget-preview">
                                    <i class="fa fa-users fa-3x"></i>
                                    <p><?php echo __('Chamados por Solicitante','dashboard'); ?></p>
                                </div>
                            </div>
                            <div class="span4 widget-item" data-type="ticket_satisfaction" data-width="6" data-height="8">
                                <div class="widget-preview">
                                    <i class="fa fa-smile-o fa-3x"></i>
                                    <p><?php echo __('Satisfação de Chamados','dashboard'); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <h5><?php echo __('Ativos','dashboard'); ?></h5>
                        <div class="row-fluid">
                            <div class="span4 widget-item" data-type="assets_summary" data-width="6" data-height="4">
                                <div class="widget-preview">
                                    <i class="fa fa-desktop fa-3x"></i>
                                    <p><?php echo __('Resumo de Ativos','dashboard'); ?></p>
                                </div>
                            </div>
                            <div class="span4 widget-item" data-type="assets_distribution" data-width="6" data-height="8">
                                <div class="widget-preview">
                                    <i class="fa fa-bar-chart fa-3x"></i>
                                    <p><?php echo __('Distribuição de Ativos','dashboard'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Fechar','dashboard'); ?></button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal para salvar dashboard -->
    <div class="modal fade" id="saveDashboardModal" tabindex="-1" role="dialog" aria-labelledby="saveDashboardModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="saveDashboardModalLabel"><?php echo __('Salvar Dashboard','dashboard'); ?></h4>
                </div>
                <div class="modal-body">
                    <form id="save-dashboard-form">
                        <div class="form-group">
                            <label for="dashboard-name"><?php echo __('Nome do Dashboard','dashboard'); ?></label>
                            <input type="text" class="form-control" id="dashboard-name" required>
                        </div>
                        <div class="form-group">
                            <label for="dashboard-description"><?php echo __('Descrição','dashboard'); ?></label>
                            <textarea class="form-control" id="dashboard-description"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Cancelar','dashboard'); ?></button>
                    <button type="button" class="btn btn-primary" id="save-dashboard-btn"><?php echo __('Salvar','dashboard'); ?></button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal para carregar dashboard -->
    <div class="modal fade" id="loadDashboardModal" tabindex="-1" role="dialog" aria-labelledby="loadDashboardModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="loadDashboardModalLabel"><?php echo __('Carregar Dashboard','dashboard'); ?></h4>
                </div>
                <div class="modal-body">
                    <div class="dashboard-list">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo __('Nome','dashboard'); ?></th>
                                    <th><?php echo __('Descrição','dashboard'); ?></th>
                                    <th><?php echo __('Criado em','dashboard'); ?></th>
                                    <th><?php echo __('Ações','dashboard'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="dashboard-list-body">
                                <!-- Lista de dashboards será carregada aqui -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Fechar','dashboard'); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function () {
        // Inicializa o dashboard sortable
        $("#dashboard-widgets").sortable({
            placeholder: "widget-placeholder",
            handle: ".widget-header",
            cursor: "move",
            opacity: 0.7,
            tolerance: "pointer",
            start: function(event, ui) {
                $(ui.item).addClass('widget-dragging');
            },
            stop: function(event, ui) {
                $(ui.item).removeClass('widget-dragging');
                // Salvar a ordem dos widgets
                saveWidgetsOrder();
            }
        });
        
        // Adicionar widget ao clicar em um item da lista
        $('.widget-item').on('click', function() {
            var widgetType = $(this).data('type');
            var widgetWidth = $(this).data('width');
            var widgetHeight = $(this).data('height');
            
            addWidget(widgetType, widgetWidth, widgetHeight);
            $('#addWidgetModal').modal('hide');
        });
        
        // Abrir modal para adicionar widget
        $('#add-widget').on('click', function() {
            $('#addWidgetModal').modal('show');
        });
        
        // Abrir modal para salvar dashboard
        $('#save-dashboard').on('click', function() {
            $('#saveDashboardModal').modal('show');
        });
        
        // Salvar dashboard
        $('#save-dashboard-btn').on('click', function() {
            var dashboardName = $('#dashboard-name').val();
            var dashboardDescription = $('#dashboard-description').val();
            
            if (!dashboardName) {
                alert('<?php echo __("Por favor, insira um nome para o dashboard","dashboard"); ?>');
                return;
            }
            
            // Serializa o layout para salvar
            var widgetsData = [];
            $('.widget').each(function() {
                var widget = $(this);
                widgetsData.push({
                    id: widget.attr('id'),
                    type: widget.data('type'),
                    width: widget.data('width'),
                    height: widget.data('height')
                });
            });
            
            // Aqui você enviaria os dados para salvar no banco de dados via AJAX
            // Por enquanto, vamos apenas armazenar no localStorage para exemplo
            var dashboards = JSON.parse(localStorage.getItem('custom_dashboards') || '[]');
            
            dashboards.push({
                name: dashboardName,
                description: dashboardDescription,
                widgets: widgetsData,
                created: new Date().toISOString()
            });
            
            localStorage.setItem('custom_dashboards', JSON.stringify(dashboards));
            
            $('#saveDashboardModal').modal('hide');
            alert('<?php echo __("Dashboard salvo com sucesso","dashboard"); ?>');
        });
        
        // Abrir modal para carregar dashboard
        $('#load-dashboard').on('click', function() {
            // Carregar a lista de dashboards
            var dashboards = JSON.parse(localStorage.getItem('custom_dashboards') || '[]');
            var listHtml = '';
            
            if (dashboards.length === 0) {
                listHtml = '<tr><td colspan="4"><?php echo __("Nenhum dashboard encontrado","dashboard"); ?></td></tr>';
            } else {
                dashboards.forEach(function(dashboard, index) {
                    var date = new Date(dashboard.created);
                    var formattedDate = date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
                    
                    listHtml += '<tr>' +
                        '<td>' + dashboard.name + '</td>' +
                        '<td>' + (dashboard.description || '') + '</td>' +
                        '<td>' + formattedDate + '</td>' +
                        '<td>' +
                        '<button class="btn btn-small btn-primary load-dashboard-btn" data-index="' + index + '"><?php echo __("Carregar","dashboard"); ?></button> ' +
                        '<button class="btn btn-small btn-danger delete-dashboard-btn" data-index="' + index + '"><?php echo __("Excluir","dashboard"); ?></button>' +
                        '</td>' +
                        '</tr>';
                });
            }
            
            $('#dashboard-list-body').html(listHtml);
            
            // Bind dos botões de ação
            $('.load-dashboard-btn').on('click', function() {
                var index = $(this).data('index');
                var dashboards = JSON.parse(localStorage.getItem('custom_dashboards') || '[]');
                var dashboard = dashboards[index];
                
                // Limpar dashboard atual
                $('#dashboard-widgets').empty();
                
                // Adicionar widgets do dashboard salvo
                if (dashboard.widgets && dashboard.widgets.length > 0) {
                    dashboard.widgets.forEach(function(widgetData) {
                        addWidget(widgetData.type, widgetData.width, widgetData.height);
                    });
                }
                
                $('#loadDashboardModal').modal('hide');
            });
            
            $('.delete-dashboard-btn').on('click', function() {
                if (confirm('<?php echo __("Tem certeza que deseja excluir este dashboard?","dashboard"); ?>')) {
                    var index = $(this).data('index');
                    var dashboards = JSON.parse(localStorage.getItem('custom_dashboards') || '[]');
                    
                    dashboards.splice(index, 1);
                    localStorage.setItem('custom_dashboards', JSON.stringify(dashboards));
                    
                    // Recarregar a lista
                    $('#load-dashboard').click();
                }
            });
            
            $('#loadDashboardModal').modal('show');
        });
        
        // Botão para novo dashboard
        $('#new-dashboard').on('click', function() {
            if (confirm('<?php echo __("Isso irá limpar o dashboard atual. Continuar?","dashboard"); ?>')) {
                $('#dashboard-widgets').empty();
            }
        });
        
        // Função para salvar a ordem dos widgets
        function saveWidgetsOrder() {
            var widgetIds = [];
            $('.widget').each(function() {
                widgetIds.push($(this).attr('id'));
            });
            localStorage.setItem('widget_order', JSON.stringify(widgetIds));
        }
        
        // Função para adicionar um widget
        function addWidget(widgetType, widgetWidth, widgetHeight) {
            var widgetId = 'widget-' + new Date().getTime();
            var widgetTitle = getWidgetTitle(widgetType);
            
            var widgetHtml = 
                '<div id="' + widgetId + '" class="widget span' + widgetWidth + '" data-type="' + widgetType + '" data-width="' + widgetWidth + '" data-height="' + widgetHeight + '">' +
                '   <div class="widget-header">' +
                '       <h3>' + widgetTitle + '</h3>' +
                '       <div class="widget-toolbar">' +
                '           <a href="#" class="widget-export" title="<?php echo __("Exportar","dashboard"); ?>"><i class="fa fa-download"></i></a>' +
                '           <a href="#" class="widget-share" title="<?php echo __("Compartilhar","dashboard"); ?>"><i class="fa fa-share-alt"></i></a>' +
                '           <a href="#" class="widget-refresh" title="<?php echo __("Atualizar","dashboard"); ?>"><i class="fa fa-refresh"></i></a>' +
                '           <a href="#" class="widget-remove" title="<?php echo __("Remover","dashboard"); ?>"><i class="fa fa-times"></i></a>' +
                '       </div>' +
                '   </div>' +
                '   <div class="widget-content" style="height: ' + (widgetHeight * 50) + 'px;">' +
                '       <div class="widget-loading"><i class="fa fa-spinner fa-spin"></i> <?php echo __("Carregando...","dashboard"); ?></div>' +
                '   </div>' +
                '</div>';
            
            $('#dashboard-widgets').append(widgetHtml);
            
            // Inicializar resizable para permitir redimensionamento
            $('#' + widgetId).resizable({
                handles: 'se',
                minHeight: 100,
                minWidth: 200,
                grid: [50, 50],
                start: function(event, ui) {
                    $(this).addClass('widget-resizing');
                },
                stop: function(event, ui) {
                    $(this).removeClass('widget-resizing');
                    // Atualizar tamanho widget após redimensionamento
                    var newHeight = Math.round(ui.size.height / 50);
                    var newWidth = Math.round(ui.size.width / ($(this).parent().width() / 12));
                    
                    $(this).data('height', newHeight).data('width', newWidth);
                    $(this).attr('data-height', newHeight).attr('data-width', newWidth);
                    $(this).removeClass().addClass('widget span' + newWidth);
                }
            });
            
            // Bind para botão de remover widget
            $('#' + widgetId + ' .widget-remove').on('click', function(e) {
                e.preventDefault();
                $(this).closest('.widget').remove();
            });
            
            // Bind para botão de atualizar widget
            $('#' + widgetId + ' .widget-refresh').on('click', function(e) {
                e.preventDefault();
                var widget = $(this).closest('.widget');
                var widgetContent = widget.find('.widget-content');
                var widgetType = widget.data('type');
                
                widgetContent.html('<div class="widget-loading"><i class="fa fa-spinner fa-spin"></i> <?php echo __("Carregando...","dashboard"); ?></div>');
                
                // Carregar conteúdo do widget
                setTimeout(function() {
                    loadWidgetContent(widgetType, widgetContent);
                }, 500);
            });
            
            // Bind para botão de exportar widget
            $('#' + widgetId + ' .widget-export').on('click', function(e) {
                e.preventDefault();
                var widget = $(this).closest('.widget');
                var widgetType = widget.data('type');
                
                // Mostrar modal de exportação
                showExportModal(widgetType);
            });
            
            // Bind para botão de compartilhar widget
            $('#' + widgetId + ' .widget-share').on('click', function(e) {
                e.preventDefault();
                var widget = $(this).closest('.widget');
                var widgetType = widget.data('type');
                
                // Mostrar modal de compartilhamento
                showShareModal(widgetType);
            });
            
            // Carregar conteúdo do widget
            loadWidgetContent(widgetType, $('#' + widgetId + ' .widget-content'));
        }
        
        // Obter título do widget com base no tipo
        function getWidgetTitle(widgetType) {
            switch(widgetType) {
                case 'tickets_summary':
                    return '<?php echo __("Resumo de Chamados","dashboard"); ?>';
                case 'tickets_status':
                    return '<?php echo __("Chamados por Status","dashboard"); ?>';
                case 'tickets_evolution':
                    return '<?php echo __("Evolução de Chamados","dashboard"); ?>';
                case 'assets_summary':
                    return '<?php echo __("Resumo de Ativos","dashboard"); ?>';
                case 'assets_distribution':
                    return '<?php echo __("Distribuição de Ativos","dashboard"); ?>';
                default:
                    return '<?php echo __("Widget","dashboard"); ?>';
            }
        }
        
        // Renderiza o gráfico de distribuição de assets
        function renderAssetsDistribution(container) {
            var chartId = 'chart-' + new Date().getTime();
            container.html('<div id="' + chartId + '" style="width: 100%; height: 90%;"></div>');
            
            Highcharts.chart(chartId, {
                chart: {
                    type: 'column'
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: [
                        '<?php echo __("Computadores","dashboard"); ?>',
                        '<?php echo __("Monitores","dashboard"); ?>',
                        '<?php echo __("Impressoras","dashboard"); ?>',
                        '<?php echo __("Telefones","dashboard"); ?>',
                        '<?php echo __("Software","dashboard"); ?>'
                    ],
                    crosshair: true
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: '<?php echo __("Quantidade","dashboard"); ?>'
                    }
                },
                tooltip: {
                    headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                    pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                        '<td style="padding:0"><b>{point.y}</b></td></tr>',
                    footerFormat: '</table>',
                    shared: true,
                    useHTML: true
                },
                plotOptions: {
                    column: {
                        pointPadding: 0.2,
                        borderWidth: 0
                    }
                },
                series: [{
                    name: '<?php echo __("Ativos","dashboard"); ?>',
                    data: [128, 95, 42, 35, 76]
                }]
            });
        }
        
        // ==========================================
        // FUNÇÕES PARA BUSCAR DADOS REAIS DO GLPI
        // ==========================================
        
        // Função para buscar dados reais de chamados
        function carregarDadosReais() {
            $.ajax({
                url: 'ajax/get_glpi_data.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'get_real_data',
                    widget_type: 'all_data'
                },
                success: function(response) {
                    // Armazenar dados globalmente para uso em todos os widgets
                    window.glpiDadosReais = response;
                    
                    // Atualizar todos os widgets com dados reais
                    $('.widget').each(function() {
                        var widgetType = $(this).data('type');
                        var widgetContent = $(this).find('.widget-content');
                        
                        // Recarregar widget com dados reais
                        loadWidgetContent(widgetType, widgetContent);
                    });
                },
                error: function(xhr, status, error) {
                    console.error("Erro ao carregar dados reais:", error);
                    // Em caso de erro, usar dados de exemplo
                    alert('<?php echo __("Erro ao carregar dados reais. Usando dados de exemplo.","dashboard"); ?>');
                }
            });
        }
        
        // Reescrever função de carregar conteúdo para usar dados reais quando disponíveis
        function loadWidgetContent(widgetType, container) {
            // Verificar se temos dados reais disponíveis
            if (window.glpiDadosReais) {
                switch(widgetType) {
                    case 'tickets_summary':
                        renderRealTicketsSummary(container, window.glpiDadosReais.tickets);
                        break;
                    case 'tickets_status':
                        renderRealTicketsStatus(container, window.glpiDadosReais.tickets_by_status);
                        break;
                    case 'tickets_evolution':
                        renderRealTicketsEvolution(container, window.glpiDadosReais.tickets_evolution);
                        break;
                    case 'assets_summary':
                        renderRealAssetsSummary(container, window.glpiDadosReais.assets);
                        break;
                    case 'assets_distribution':
                        renderRealAssetsDistribution(container, window.glpiDadosReais.assets_distribution);
                        break;
                    // Novos tipos de widgets
                    case 'tickets_by_category':
                        renderTicketsByCategory(container, window.glpiDadosReais.tickets_by_category);
                        break;
                    case 'tickets_by_requester':
                        renderTicketsByRequester(container, window.glpiDadosReais.tickets_by_requester);
                        break;
                    case 'ticket_satisfaction':
                        renderTicketSatisfaction(container, window.glpiDadosReais.satisfaction);
                        break;
                    default:
                        container.html('<div class="alert">Widget não implementado</div>');
                }
            } else {
                // Se não temos dados reais, usamos as funções de exemplo originais
                switch(widgetType) {
                    case 'tickets_summary':
                        renderTicketsSummary(container);
                        break;
                    case 'tickets_status':
                        renderTicketsStatus(container);
                        break;
                    case 'tickets_evolution':
                        renderTicketsEvolution(container);
                        break;
                    case 'assets_summary':
                        renderAssetsSummary(container);
                        break;
                    case 'assets_distribution':
                        renderAssetsDistribution(container);
                        break;
                    // Novos tipos de widgets com dados de exemplo
                    case 'tickets_by_category':
                        renderTicketsByCategoryDemo(container);
                        break;
                    case 'tickets_by_requester':
                        renderTicketsByRequesterDemo(container);
                        break;
                    case 'ticket_satisfaction':
                        renderTicketSatisfactionDemo(container);
                        break;
                    default:
                        container.html('<div class="alert">Widget não implementado</div>');
                }
                
                // Tentar carregar dados reais após renderizar a versão de demonstração
                if (!window.dataRequestInProgress) {
                    window.dataRequestInProgress = true;
                    carregarDadosReais();
                }
            }
        }
        
        // Funções para renderizar widgets com dados reais
        function renderRealTicketsSummary(container, data) {
            var html = '<div class="row-fluid">';
            html += '<div class="span4 summary-box">';
            html += '<h4><?php echo __("Abertos","dashboard"); ?></h4>';
            html += '<div class="number">' + data.open + '</div>';
            html += '</div>';
            html += '<div class="span4 summary-box">';
            html += '<h4><?php echo __("Em andamento","dashboard"); ?></h4>';
            html += '<div class="number">' + data.processing + '</div>';
            html += '</div>';
            html += '<div class="span4 summary-box">';
            html += '<h4><?php echo __("Fechados","dashboard"); ?></h4>';
            html += '<div class="number">' + data.closed + '</div>';
            html += '</div>';
            html += '</div>';
            
            container.html(html);
        }
        
        function renderRealTicketsStatus(container, data) {
            var chartId = 'chart-' + new Date().getTime();
            container.html('<div id="' + chartId + '" style="width: 100%; height: 90%;"></div>');
            
            var chartData = [];
            $.each(data, function(status, count) {
                chartData.push([status, count]);
            });
            
            Highcharts.chart(chartId, {
                chart: {
                    type: 'pie',
                    options3d: {
                        enabled: true,
                        alpha: 45,
                        beta: 0
                    }
                },
                title: {
                    text: null
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        depth: 35,
                        dataLabels: {
                            enabled: true,
                            format: '{point.name}: {point.y}'
                        }
                    }
                },
                series: [{
                    type: 'pie',
                    name: '<?php echo __("Chamados","dashboard"); ?>',
                    data: chartData
                }]
            });
        }
        
        function renderRealTicketsEvolution(container, data) {
            var chartId = 'chart-' + new Date().getTime();
            container.html('<div id="' + chartId + '" style="width: 100%; height: 90%;"></div>');
            
            var categories = [];
            var openData = [];
            var closedData = [];
            
            $.each(data, function(index, month) {
                categories.push(month.month_name);
                openData.push(month.opened);
                closedData.push(month.closed);
            });
            
            Highcharts.chart(chartId, {
                chart: {
                    type: 'line'
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: categories
                },
                yAxis: {
                    title: {
                        text: '<?php echo __("Número de Chamados","dashboard"); ?>'
                    }
                },
                plotOptions: {
                    line: {
                        dataLabels: {
                            enabled: true
                        },
                        enableMouseTracking: true
                    }
                },
                series: [{
                    name: '<?php echo __("Abertos","dashboard"); ?>',
                    data: openData
                }, {
                    name: '<?php echo __("Fechados","dashboard"); ?>',
                    data: closedData
                }]
            });
        }
        
        function renderRealAssetsSummary(container, data) {
            var html = '<div class="row-fluid">';
            html += '<div class="span4 summary-box">';
            html += '<h4><?php echo __("Computadores","dashboard"); ?></h4>';
            html += '<div class="number">' + data.computers + '</div>';
            html += '</div>';
            html += '<div class="span4 summary-box">';
            html += '<h4><?php echo __("Monitores","dashboard"); ?></h4>';
            html += '<div class="number">' + data.monitors + '</div>';
            html += '</div>';
            html += '<div class="span4 summary-box">';
            html += '<h4><?php echo __("Impressoras","dashboard"); ?></h4>';
            html += '<div class="number">' + data.printers + '</div>';
            html += '</div>';
            html += '</div>';
            
            container.html(html);
        }
        
        function renderRealAssetsDistribution(container, data) {
            var chartId = 'chart-' + new Date().getTime();
            container.html('<div id="' + chartId + '" style="width: 100%; height: 90%;"></div>');
            
            var categories = [];
            var assetData = [];
            
            $.each(data, function(asset_type, count) {
                categories.push(asset_type);
                assetData.push(count);
            });
            
            Highcharts.chart(chartId, {
                chart: {
                    type: 'column'
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: categories,
                    crosshair: true
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: '<?php echo __("Quantidade","dashboard"); ?>'
                    }
                },
                tooltip: {
                    headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                    pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                        '<td style="padding:0"><b>{point.y}</b></td></tr>',
                    footerFormat: '</table>',
                    shared: true,
                    useHTML: true
                },
                plotOptions: {
                    column: {
                        pointPadding: 0.2,
                        borderWidth: 0
                    }
                },
                series: [{
                    name: '<?php echo __("Ativos","dashboard"); ?>',
                    data: assetData
                }]
            });
        }
        
        // Funções para novos tipos de widgets com dados reais
        function renderTicketsByCategory(container, data) {
            var chartId = 'chart-' + new Date().getTime();
            container.html('<div id="' + chartId + '" style="width: 100%; height: 90%;"></div>');
            
            var categories = [];
            var ticketData = [];
            
            $.each(data, function(category, count) {
                categories.push(category);
                ticketData.push(count);
            });
            
            Highcharts.chart(chartId, {
                chart: {
                    type: 'bar'
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: categories
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: '<?php echo __("Número de Chamados","dashboard"); ?>'
                    }
                },
                legend: {
                    enabled: false
                },
                tooltip: {
                    pointFormat: '{point.y} chamados'
                },
                series: [{
                    name: '<?php echo __("Chamados","dashboard"); ?>',
                    data: ticketData,
                    colorByPoint: true
                }]
            });
        }
        
        function renderTicketsByRequester(container, data) {
            var chartId = 'chart-' + new Date().getTime();
            container.html('<div id="' + chartId + '" style="width: 100%; height: 90%;"></div>');
            
            var chartData = [];
            var i = 0;
            $.each(data, function(requester, count) {
                if (i < 10) { // Limitar a 10 solicitantes para melhor visualização
                    chartData.push([requester, count]);
                    i++;
                }
            });
            
            Highcharts.chart(chartId, {
                chart: {
                    type: 'pie'
                },
                title: {
                    text: null
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.y}</b>'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>: {point.y}'
                        }
                    }
                },
                series: [{
                    name: '<?php echo __("Chamados","dashboard"); ?>',
                    data: chartData
                }]
            });
        }
        
        function renderTicketSatisfaction(container, data) {
            var chartId = 'chart-' + new Date().getTime();
            container.html('<div id="' + chartId + '" style="width: 100%; height: 90%;"></div>');
            
            Highcharts.chart(chartId, {
                chart: {
                    type: 'gauge',
                    plotBackgroundColor: null,
                    plotBackgroundImage: null,
                    plotBorderWidth: 0,
                    plotShadow: false
                },
                title: {
                    text: null
                },
                pane: {
                    startAngle: -150,
                    endAngle: 150,
                    background: [{
                        backgroundColor: {
                            linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                            stops: [
                                [0, '#FFF'],
                                [1, '#333']
                            ]
                        },
                        borderWidth: 0,
                        outerRadius: '109%'
                    }, {
                        backgroundColor: {
                            linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                            stops: [
                                [0, '#333'],
                                [1, '#FFF']
                            ]
                        },
                        borderWidth: 1,
                        outerRadius: '107%'
                    }, {
                        backgroundColor: '#DDD',
                        borderWidth: 0,
                        outerRadius: '105%',
                        innerRadius: '103%'
                    }]
                },
                yAxis: {
                    min: 0,
                    max: 5,
                    minorTickInterval: 'auto',
                    minorTickWidth: 1,
                    minorTickLength: 10,
                    minorTickPosition: 'inside',
                    minorTickColor: '#666',
                    tickPixelInterval: 30,
                    tickWidth: 2,
                    tickPosition: 'inside',
                    tickLength: 10,
                    tickColor: '#666',
                    labels: {
                        step: 1,
                        rotation: 'auto'
                    },
                    title: {
                        text: '<?php echo __("Satisfação","dashboard"); ?>'
                    },
                    plotBands: [{
                        from: 0,
                        to: 1,
                        color: '#e74c3c' // red
                    }, {
                        from: 1,
                        to: 2,
                        color: '#f39c12' // orange
                    }, {
                        from: 2,
                        to: 3,
                        color: '#f1c40f' // yellow
                    }, {
                        from: 3,
                        to: 4,
                        color: '#2ecc71' // light green
                    }, {
                        from: 4,
                        to: 5,
                        color: '#27ae60' // green
                    }]
                },
                series: [{
                    name: '<?php echo __("Satisfação","dashboard"); ?>',
                    data: [data.average],
                    tooltip: {
                        valueSuffix: ' / 5'
                    }
                }]
            });
            
            // Adicionar detalhes da satisfação
            var html = '<div class="satisfaction-details" style="margin-top: 20px; text-align: center;">';
            html += '<p><strong><?php echo __("Avaliações respondidas","dashboard"); ?>:</strong> ' + data.count + '</p>';
            html += '<p><strong><?php echo __("Média de satisfação","dashboard"); ?>:</strong> ' + data.average.toFixed(1) + '/5</p>';
            html += '</div>';
            
            $(container).append(html);
        }
        
        // Funções de demonstração para novos widgets quando dados reais não estão disponíveis
        function renderTicketsByCategoryDemo(container) {
            var chartId = 'chart-' + new Date().getTime();
            container.html('<div id="' + chartId + '" style="width: 100%; height: 90%;"></div>');
            
            Highcharts.chart(chartId, {
                chart: {
                    type: 'bar'
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: [
                        '<?php echo __("Hardware","dashboard"); ?>',
                        '<?php echo __("Software","dashboard"); ?>',
                        '<?php echo __("Rede","dashboard"); ?>',
                        '<?php echo __("Telefonia","dashboard"); ?>',
                        '<?php echo __("Infraestrutura","dashboard"); ?>'
                    ]
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: '<?php echo __("Número de Chamados","dashboard"); ?>'
                    }
                },
                legend: {
                    enabled: false
                },
                tooltip: {
                    pointFormat: '{point.y} chamados'
                },
                series: [{
                    name: '<?php echo __("Chamados","dashboard"); ?>',
                    data: [45, 35, 28, 18, 12],
                    colorByPoint: true
                }]
            });
        }
        
        function renderTicketsByRequesterDemo(container) {
            var chartId = 'chart-' + new Date().getTime();
            container.html('<div id="' + chartId + '" style="width: 100%; height: 90%;"></div>');
            
            Highcharts.chart(chartId, {
                chart: {
                    type: 'pie'
                },
                title: {
                    text: null
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.y}</b>'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>: {point.y}'
                        }
                    }
                },
                series: [{
                    name: '<?php echo __("Chamados","dashboard"); ?>',
                    data: [
                        ['Departamento TI', 25],
                        ['Departamento RH', 18],
                        ['Departamento Comercial', 15],
                        ['Departamento Financeiro', 12],
                        ['Departamento Administrativo', 8]
                    ]
                }]
            });
        }
        
        function renderTicketSatisfactionDemo(container) {
            var chartId = 'chart-' + new Date().getTime();
            container.html('<div id="' + chartId + '" style="width: 100%; height: 90%;"></div>');
            
            Highcharts.chart(chartId, {
                chart: {
                    type: 'gauge',
                    plotBackgroundColor: null,
                    plotBackgroundImage: null,
                    plotBorderWidth: 0,
                    plotShadow: false
                },
                title: {
                    text: null
                },
                pane: {
                    startAngle: -150,
                    endAngle: 150,
                    background: [{
                        backgroundColor: {
                            linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                            stops: [
                                [0, '#FFF'],
                                [1, '#333']
                            ]
                        },
                        borderWidth: 0,
                        outerRadius: '109%'
                    }, {
                        backgroundColor: {
                            linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                            stops: [
                                [0, '#333'],
                                [1, '#FFF']
                            ]
                        },
                        borderWidth: 1,
                        outerRadius: '107%'
                    }, {
                        backgroundColor: '#DDD',
                        borderWidth: 0,
                        outerRadius: '105%',
                        innerRadius: '103%'
                    }]
                },
                yAxis: {
                    min: 0,
                    max: 5,
                    minorTickInterval: 'auto',
                    minorTickWidth: 1,
                    minorTickLength: 10,
                    minorTickPosition: 'inside',
                    minorTickColor: '#666',
                    tickPixelInterval: 30,
                    tickWidth: 2,
                    tickPosition: 'inside',
                    tickLength: 10,
                    tickColor: '#666',
                    labels: {
                        step: 1,
                        rotation: 'auto'
                    },
                    title: {
                        text: '<?php echo __("Satisfação","dashboard"); ?>'
                    },
                    plotBands: [{
                        from: 0,
                        to: 1,
                        color: '#e74c3c' // red
                    }, {
                        from: 1,
                        to: 2,
                        color: '#f39c12' // orange
                    }, {
                        from: 2,
                        to: 3,
                        color: '#f1c40f' // yellow
                    }, {
                        from: 3,
                        to: 4,
                        color: '#2ecc71' // light green
                    }, {
                        from: 4,
                        to: 5,
                        color: '#27ae60' // green
                    }]
                },
                series: [{
                    name: '<?php echo __("Satisfação","dashboard"); ?>',
                    data: [4.2],
                    tooltip: {
                        valueSuffix: ' / 5'
                    }
                }]
            });
            
            // Adicionar detalhes da satisfação
            var html = '<div class="satisfaction-details" style="margin-top: 20px; text-align: center;">';
            html += '<p><strong><?php echo __("Avaliações respondidas","dashboard"); ?>:</strong> 128</p>';
            html += '<p><strong><?php echo __("Média de satisfação","dashboard"); ?>:</strong> 4.2/5</p>';
            html += '</div>';
            
            $(container).append(html);
        }
        
        // Carregar dados ao iniciar o dashboard
        $(document).ready(function() {
            // Iniciar buscando dados reais
            carregarDadosReais();
            
            // Atualizar dados a cada 5 minutos
            setInterval(function() {
                carregarDadosReais();
            }, 300000); // 5 minutos
        });
        
        // Modal para exportação de widgets
        function showExportModal(widgetType) {
            // Criar modal dinamicamente
            var modal = $('<div class="modal fade" id="exportWidgetModal" tabindex="-1" role="dialog">' +
                '<div class="modal-dialog" role="document">' +
                '<div class="modal-content">' +
                '<div class="modal-header">' +
                '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                '<h4 class="modal-title"><?php echo __("Exportar Widget","dashboard"); ?></h4>' +
                '</div>' +
                '<div class="modal-body">' +
                '<p><?php echo __("Selecione o formato para exportação:","dashboard"); ?></p>' +
                '<div class="export-options">' +
                '<button class="btn btn-default export-csv" data-type="' + widgetType + '" data-format="csv">' +
                '<i class="fa fa-file-text-o"></i> CSV</button> ' +
                '<button class="btn btn-default export-excel" data-type="' + widgetType + '" data-format="excel">' +
                '<i class="fa fa-file-excel-o"></i> Excel</button> ' +
                '<button class="btn btn-default export-pdf" data-type="' + widgetType + '" data-format="pdf">' +
                '<i class="fa fa-file-pdf-o"></i> PDF</button>' +
                '</div>' +
                '</div>' +
                '<div class="modal-footer">' +
                '<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __("Fechar","dashboard"); ?></button>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>');
            
            // Adicionar ao body
            $('body').append(modal);
            
            // Exibir modal
            $('#exportWidgetModal').modal('show');
            
            // Listener para botões de exportação
            $('.export-csv, .export-excel, .export-pdf').on('click', function() {
                var type = $(this).data('type');
                var format = $(this).data('format');
                
                // Fazer solicitação de exportação
                exportWidget(type, format);
                
                // Fechar modal
                $('#exportWidgetModal').modal('hide');
            });
            
            // Remover modal ao fechar
            $('#exportWidgetModal').on('hidden.bs.modal', function() {
                $(this).remove();
            });
        }
        
        // Função para exportar widget
        function exportWidget(widgetType, format) {
            // Criar formulário dinâmico para submissão
            var form = $('<form action="ajax/export_dashboard.php" method="post" target="_blank"></form>');
            form.append('<input type="hidden" name="action" value="export">');
            form.append('<input type="hidden" name="widget_type" value="' + widgetType + '">');
            form.append('<input type="hidden" name="format" value="' + format + '">');
            
            // Adicionar ao body, submeter e remover
            $('body').append(form);
            form.submit();
            form.remove();
        }
        
        // Modal para compartilhamento de widgets
        function showShareModal(widgetType) {
            // Obter título do widget
            var widgetTitle = getWidgetTitle(widgetType);
            
            // Criar ID único para compartilhamento
            var widgetId = 'widget_' + new Date().getTime();
            
            // URL de compartilhamento
            var shareUrl = window.location.origin + window.location.pathname + '?share=' + widgetType + '&id=' + widgetId;
            
            // Carregar a lista de usuários
            $.ajax({
                url: 'ajax/share_widget.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'get_users'
                },
                success: function(response) {
                    if (response.status === 'success') {
                        // Criar opções para dropdown
                        var userOptions = '';
                        $.each(response.users, function(index, user) {
                            userOptions += '<option value="' + user.id + '">' + user.name + '</option>';
                        });
                        
                        // Criar modal dinamicamente
                        var modal = $('<div class="modal fade" id="shareWidgetModal" tabindex="-1" role="dialog">' +
                            '<div class="modal-dialog" role="document">' +
                            '<div class="modal-content">' +
                            '<div class="modal-header">' +
                            '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                            '<h4 class="modal-title"><?php echo __("Compartilhar Widget","dashboard"); ?></h4>' +
                            '</div>' +
                            '<div class="modal-body">' +
                            '<p><strong><?php echo __("Widget:","dashboard"); ?></strong> ' + widgetTitle + '</p>' +
                            '<p><?php echo __("Compartilhe este widget com outros usuários do GLPI:","dashboard"); ?></p>' +
                            '<div class="form-group">' +
                            '<label for="share-users"><?php echo __("Selecione os usuários:","dashboard"); ?></label>' +
                            '<select id="share-users" multiple class="form-control">' +
                            userOptions +
                            '</select>' +
                            '</div>' +
                            '<div class="form-group">' +
                            '<label><?php echo __("Link para compartilhamento:","dashboard"); ?></label>' +
                            '<div class="input-group">' +
                            '<input type="text" class="form-control" id="share-url" value="' + shareUrl + '" readonly>' +
                            '<span class="input-group-btn">' +
                            '<button class="btn btn-default" id="copy-url" type="button" title="<?php echo __("Copiar","dashboard"); ?>">' +
                            '<i class="fa fa-copy"></i></button>' +
                            '</span>' +
                            '</div>' +
                            '</div>' +
                            '</div>' +
                            '<div class="modal-footer">' +
                            '<button type="button" class="btn btn-primary" id="share-widget-btn"><?php echo __("Compartilhar","dashboard"); ?></button>' +
                            '<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __("Cancelar","dashboard"); ?></button>' +
                            '</div>' +
                            '</div>' +
                            '</div>' +
                            '</div>');
                        
                        // Adicionar ao body
                        $('body').append(modal);
                        
                        // Exibir modal
                        $('#shareWidgetModal').modal('show');
                        
                        // Listener para botão de copiar link
                        $('#copy-url').on('click', function() {
                            var copyText = document.getElementById("share-url");
                            copyText.select();
                            document.execCommand("copy");
                            
                            // Mudar texto do botão para indicar cópia
                            $(this).html('<i class="fa fa-check"></i>');
                            setTimeout(function() {
                                $('#copy-url').html('<i class="fa fa-copy"></i>');
                            }, 2000);
                        });
                        
                        // Listener para botão de compartilhar
                        $('#share-widget-btn').on('click', function() {
                            var selectedUsers = $('#share-users').val();
                            
                            if (selectedUsers && selectedUsers.length > 0) {
                                // Fazer a solicitação de compartilhamento
                                $.ajax({
                                    url: 'ajax/share_widget.php',
                                    type: 'POST',
                                    dataType: 'json',
                                    data: {
                                        action: 'share_widget',
                                        widget_type: widgetType,
                                        widget_id: widgetId,
                                        widget_title: widgetTitle,
                                        users: selectedUsers
                                    },
                                    success: function(shareResponse) {
                                        if (shareResponse.status === 'success') {
                                            alert('<?php echo __("Widget compartilhado com sucesso!","dashboard"); ?>');
                                        } else {
                                            alert(shareResponse.message || '<?php echo __("Erro ao compartilhar widget.","dashboard"); ?>');
                                        }
                                        $('#shareWidgetModal').modal('hide');
                                    },
                                    error: function() {
                                        alert('<?php echo __("Erro ao compartilhar widget.","dashboard"); ?>');
                                        $('#shareWidgetModal').modal('hide');
                                    }
                                });
                            } else {
                                alert('<?php echo __("Por favor, selecione pelo menos um usuário para compartilhar.","dashboard"); ?>');
                            }
                        });
                        
                        // Remover modal ao fechar
                        $('#shareWidgetModal').on('hidden.bs.modal', function() {
                            $(this).remove();
                        });
                    } else {
                        alert('<?php echo __("Erro ao carregar usuários.","dashboard"); ?>');
                    }
                },
                error: function() {
                    alert('<?php echo __("Erro ao carregar usuários.","dashboard"); ?>');
                }
            });
        }
        
        // Verificar compartilhamento via URL e notificações
        $(document).ready(function() {
            // Processar parâmetros da URL
            var urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('share') && urlParams.has('id')) {
                var widgetType = urlParams.get('share');
                var shareId = urlParams.get('id');
                
                // Adicionar o widget compartilhado
                addWidget(widgetType, 6, 8);
                
                // Exibir notificação
                var notification = $('<div class="alert alert-success" style="position: fixed; top: 70px; right: 20px; z-index: 9999;">' +
                    '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                    '<strong><?php echo __("Widget Compartilhado!","dashboard"); ?></strong> ' +
                    '<?php echo __("O widget foi adicionado ao seu dashboard.","dashboard"); ?>' +
                    '</div>');
                
                $('body').append(notification);
                setTimeout(function() {
                    notification.fadeOut('slow', function() {
                        $(this).remove();
                    });
                }, 5000);
            }
            
            // Verificar widgets compartilhados com o usuário
            checkSharedWidgets();
        });
        
        // Função para verificar widgets compartilhados
        function checkSharedWidgets() {
            $.ajax({
                url: 'ajax/share_widget.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'get_shared_widgets'
                },
                success: function(response) {
                    if (response.status === 'success' && response.shared_widgets.length > 0) {
                        // Adicionar botão de notificação
                        var notificationBtn = $('<div class="notification-badge" title="<?php echo __("Widgets compartilhados","dashboard"); ?>">' +
                            '<i class="fa fa-bell"></i>' +
                            '<span class="badge">' + response.shared_widgets.length + '</span>' +
                            '</div>');
                        
                        // Estilizar botão
                        notificationBtn.css({
                            'position': 'fixed',
                            'top': '70px',
                            'right': '20px',
                            'background-color': '#3498db',
                            'color': 'white',
                            'border-radius': '50%',
                            'width': '40px',
                            'height': '40px',
                            'text-align': 'center',
                            'line-height': '40px',
                            'font-size': '20px',
                            'cursor': 'pointer',
                            'z-index': '9999',
                            'box-shadow': '0 2px 5px rgba(0, 0, 0, 0.2)'
                        });
                        
                        // Estilizar badge
                        notificationBtn.find('.badge').css({
                            'position': 'absolute',
                            'top': '-5px',
                            'right': '-5px',
                            'background-color': '#e74c3c',
                            'color': 'white',
                            'border-radius': '50%',
                            'width': '20px',
                            'height': '20px',
                            'font-size': '12px',
                            'line-height': '20px'
                        });
                        
                        // Adicionar ao body
                        $('body').append(notificationBtn);
                        
                        // Ao clicar no botão de notificação
                        notificationBtn.on('click', function() {
                            showSharedWidgetsModal(response.shared_widgets);
                        });
                    }
                }
            });
        }
        
        // Função para mostrar modal de widgets compartilhados
        function showSharedWidgetsModal(sharedWidgets) {
            // Criar conteúdo da lista
            var widgetListHtml = '';
            
            $.each(sharedWidgets, function(index, widget) {
                widgetListHtml += '<div class="shared-widget-item">' +
                    '<h4>' + widget.title + '</h4>' +
                    '<p><strong><?php echo __("Compartilhado por","dashboard"); ?>:</strong> ' + widget.shared_by + '</p>' +
                    '<p><strong><?php echo __("Data","dashboard"); ?>:</strong> ' + widget.date_creation + '</p>' +
                    '<div class="btn-group">' +
                    '<button class="btn btn-small btn-success add-shared-widget" data-type="' + widget.widget_type + '" data-id="' + widget.widget_id + '">' +
                    '<i class="fa fa-plus"></i> <?php echo __("Adicionar","dashboard"); ?></button> ' +
                    '<button class="btn btn-small btn-danger reject-shared-widget" data-id="' + widget.id + '">' +
                    '<i class="fa fa-times"></i> <?php echo __("Rejeitar","dashboard"); ?></button>' +
                    '</div>' +
                    '</div>';
            });
            
            // Criar modal
            var modal = $('<div class="modal fade" id="sharedWidgetsModal" tabindex="-1" role="dialog">' +
                '<div class="modal-dialog" role="document">' +
                '<div class="modal-content">' +
                '<div class="modal-header">' +
                '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                '<h4 class="modal-title"><?php echo __("Widgets Compartilhados","dashboard"); ?></h4>' +
                '</div>' +
                '<div class="modal-body">' +
                (widgetListHtml || '<p><?php echo __("Nenhum widget compartilhado.","dashboard"); ?></p>') +
                '</div>' +
                '<div class="modal-footer">' +
                '<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __("Fechar","dashboard"); ?></button>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>');
            
            // Estilizar itens de widget
            modal.find('.shared-widget-item').css({
                'background-color': '#f9f9f9',
                'border-radius': '5px',
                'padding': '15px',
                'margin-bottom': '15px',
                'border-left': '4px solid #3498db'
            });
            
            // Adicionar ao body
            $('body').append(modal);
            
            // Exibir modal
            $('#sharedWidgetsModal').modal('show');
            
            // Listener para botão de adicionar widget compartilhado
            $('.add-shared-widget').on('click', function() {
                var widgetType = $(this).data('type');
                var widgetId = $(this).data('id');
                
                // Adicionar widget ao dashboard
                addWidget(widgetType, 6, 8);
                
                // Aceitar compartilhamento
                var shareId = $(this).closest('.shared-widget-item').find('.reject-shared-widget').data('id');
                $.ajax({
                    url: 'ajax/share_widget.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'accept_widget',
                        share_id: shareId
                    }
                });
                
                // Remover item da lista
                $(this).closest('.shared-widget-item').fadeOut(300, function() {
                    $(this).remove();
                    
                    // Se não houver mais itens, mostrar mensagem
                    if ($('.shared-widget-item').length === 0) {
                        $('#sharedWidgetsModal .modal-body').html('<p><?php echo __("Nenhum widget compartilhado.","dashboard"); ?></p>');
                        
                        // Remover botão de notificação
                        $('.notification-badge').remove();
                    }
                });
            });
            
            // Listener para botão de rejeitar widget compartilhado
            $('.reject-shared-widget').on('click', function() {
                var shareId = $(this).data('id');
                var widgetItem = $(this).closest('.shared-widget-item');
                
                if (confirm('<?php echo __("Tem certeza que deseja rejeitar este widget?","dashboard"); ?>')) {
                    // Rejeitar compartilhamento
                    $.ajax({
                        url: 'ajax/share_widget.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'reject_widget',
                            share_id: shareId
                        }
                    });
                    
                    // Remover item da lista
                    widgetItem.fadeOut(300, function() {
                        $(this).remove();
                        
                        // Se não houver mais itens, mostrar mensagem
                        if ($('.shared-widget-item').length === 0) {
                            $('#sharedWidgetsModal .modal-body').html('<p><?php echo __("Nenhum widget compartilhado.","dashboard"); ?></p>');
                            
                            // Remover botão de notificação
                            $('.notification-badge').remove();
                        }
                    });
                }
            });
            
            // Remover modal ao fechar
            $('#sharedWidgetsModal').on('hidden.bs.modal', function() {
                $(this).remove();
            });
        }
    });
</script>

</body>
</html> 