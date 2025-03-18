<?php
include ("../../../inc/includes.php");
include ("../../../inc/config.php");

global $DB;

Session::checkLoginUser();

$userID = $_SESSION['glpiID'];

// Função para salvar layout
function saveLayout($data) {
    global $DB, $userID;
    
    $layout_data = json_encode($data);
    $name = $data['name'] ?? 'Meu Dashboard';
    
    $query = "INSERT INTO glpi_plugin_dashboard_layouts (users_id, name, layout_data) 
              VALUES ($userID, '$name', '$layout_data')
              ON DUPLICATE KEY UPDATE layout_data = '$layout_data'";
              
    return $DB->query($query);
}

// Função para carregar layout
function loadLayout($layout_id) {
    global $DB, $userID;
    
    $query = "SELECT * FROM glpi_plugin_dashboard_layouts 
              WHERE id = $layout_id AND users_id = $userID";
              
    $result = $DB->query($query);
    if($row = $DB->fetchAssoc($result)) {
        return json_decode($row['layout_data'], true);
    }
    return null;
}

// Função para listar layouts do usuário
function listLayouts() {
    global $DB, $userID;
    
    $query = "SELECT * FROM glpi_plugin_dashboard_layouts 
              WHERE users_id = $userID 
              ORDER BY created_at DESC";
              
    $result = $DB->query($query);
    $layouts = array();
    
    while($row = $DB->fetchAssoc($result)) {
        $layouts[] = $row;
    }
    
    return $layouts;
}

// Widgets disponíveis
$available_widgets = array(
    'tickets_chart' => array(
        'name' => 'Gráfico de Chamados',
        'type' => 'chart',
        'query' => 'SELECT COUNT(*) as total, status FROM glpi_tickets WHERE date >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY status'
    ),
    'tickets_table' => array(
        'name' => 'Tabela de Chamados',
        'type' => 'table',
        'query' => 'SELECT id, name, status, date FROM glpi_tickets ORDER BY date DESC LIMIT 10'
    ),
    'stats_card' => array(
        'name' => 'Card de Estatísticas',
        'type' => 'card',
        'query' => 'SELECT COUNT(*) as total FROM glpi_tickets WHERE status IN (1,2,3,4)'
    )
);

// Processar requisições AJAX
if(isset($_POST['action'])) {
    switch($_POST['action']) {
        case 'save':
            echo json_encode(saveLayout($_POST['data']));
            break;
            
        case 'load':
            echo json_encode(loadLayout($_POST['layout_id']));
            break;
            
        case 'list':
            echo json_encode(listLayouts());
            break;
    }
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>GLPI - Dashboard Personalizado</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- CSS -->
    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
    <link href="css/skin-default1.css" rel="stylesheet">
    
    <!-- JavaScript -->
    <script src="js/jquery.js"></script>
    <script src="js/bootstrap.js"></script>
    <script src="js/gridstack.js"></script>
    <script src="js/highcharts.js"></script>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Dashboard Personalizado</h3>
                </div>
                <div class="panel-body">
                    <!-- Barra de ferramentas -->
                    <div class="toolbar">
                        <button class="btn btn-primary" onclick="saveCurrentLayout()">Salvar Layout</button>
                        <select class="form-control" id="layoutSelect" onchange="loadSelectedLayout()">
                            <option value="">Selecione um layout...</option>
                        </select>
                    </div>
                    
                    <!-- Área de widgets -->
                    <div class="grid-stack">
                        <!-- Widgets serão inseridos aqui dinamicamente -->
                    </div>
                    
                    <!-- Painel de widgets disponíveis -->
                    <div class="widget-panel">
                        <h4>Widgets Disponíveis</h4>
                        <div class="widget-list">
                            <?php foreach($available_widgets as $id => $widget): ?>
                            <div class="widget-item" draggable="true" data-widget-id="<?php echo $id; ?>">
                                <?php echo $widget['name']; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Inicialização do GridStack
var grid = GridStack.init({
    float: true,
    animate: true,
    resizable: {
        handles: 'e,se,s,sw,w'
    }
});

// Função para salvar layout
function saveCurrentLayout() {
    var layout = {
        name: prompt('Nome do layout:'),
        widgets: []
    };
    
    grid.engine.nodes.forEach(function(node) {
        layout.widgets.push({
            id: node.el.getAttribute('data-widget-id'),
            x: node.x,
            y: node.y,
            w: node.w,
            h: node.h
        });
    });
    
    $.post('custom_layout.php', {
        action: 'save',
        data: layout
    }, function(response) {
        alert('Layout salvo com sucesso!');
        loadLayouts();
    });
}

// Função para carregar layouts
function loadLayouts() {
    $.post('custom_layout.php', {
        action: 'list'
    }, function(layouts) {
        var select = $('#layoutSelect');
        select.empty();
        select.append('<option value="">Selecione um layout...</option>');
        
        layouts.forEach(function(layout) {
            select.append(`<option value="${layout.id}">${layout.name}</option>`);
        });
    });
}

// Função para carregar layout selecionado
function loadSelectedLayout() {
    var layoutId = $('#layoutSelect').val();
    if(!layoutId) return;
    
    $.post('custom_layout.php', {
        action: 'load',
        layout_id: layoutId
    }, function(layout) {
        grid.removeAll();
        
        layout.widgets.forEach(function(widget) {
            addWidget(widget.id, widget.x, widget.y, widget.w, widget.h);
        });
    });
}

// Função para adicionar widget
function addWidget(widgetId, x, y, w, h) {
    var widget = <?php echo json_encode($available_widgets); ?>[widgetId];
    if(!widget) return;
    
    var content = '';
    switch(widget.type) {
        case 'chart':
            content = `<div class="chart-container"></div>`;
            break;
        case 'table':
            content = `<div class="table-container"></div>`;
            break;
        case 'card':
            content = `<div class="card-container"></div>`;
            break;
    }
    
    grid.addWidget({
        x: x,
        y: y,
        w: w,
        h: h,
        content: content,
        data: {widgetId: widgetId}
    });
    
    // Carregar dados do widget
    loadWidgetData(widgetId, widget.query);
}

// Função para carregar dados do widget
function loadWidgetData(widgetId, query) {
    $.post('widget_data.php', {
        query: query
    }, function(data) {
        var widget = grid.engine.nodes.find(n => n.el.getAttribute('data-widget-id') === widgetId);
        if(!widget) return;
        
        var container = widget.el.querySelector('.chart-container, .table-container, .card-container');
        if(!container) return;
        
        // Renderizar dados baseado no tipo do widget
        renderWidgetData(container, data);
    });
}

// Função para renderizar dados do widget
function renderWidgetData(container, data) {
    if(container.classList.contains('chart-container')) {
        renderChart(container, data);
    } else if(container.classList.contains('table-container')) {
        renderTable(container, data);
    } else if(container.classList.contains('card-container')) {
        renderCard(container, data);
    }
}

// Inicialização
$(document).ready(function() {
    loadLayouts();
    
    // Configurar drag and drop de widgets
    $('.widget-item').on('dragstart', function(e) {
        e.originalEvent.dataTransfer.setData('widgetId', $(this).data('widget-id'));
    });
    
    $('.grid-stack').on('dragover', function(e) {
        e.preventDefault();
    }).on('drop', function(e) {
        e.preventDefault();
        var widgetId = e.originalEvent.dataTransfer.getData('widgetId');
        var position = grid.engine.getCellFromPixel({
            top: e.originalEvent.clientY,
            left: e.originalEvent.clientX
        });
        
        addWidget(widgetId, position.x, position.y, 3, 2);
    });
});
</script>

</body>
</html> 