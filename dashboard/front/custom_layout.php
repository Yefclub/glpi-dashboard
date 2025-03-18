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
    
    $input = [
        'users_id' => $userID,
        'name' => $name,
        'layout_data' => $layout_data
    ];
    
    $layout = new PluginDashboardLayout();
    return $layout->add($input);
}

// Função para carregar layout
function loadLayout($layout_id) {
    global $DB, $userID;
    
    $layout = new PluginDashboardLayout();
    $found = $layout->getFromDB($layout_id);
    
    if ($found && $layout->fields['users_id'] == $userID) {
        return json_decode($layout->fields['layout_data'], true);
    }
    return null;
}

// Função para listar layouts do usuário
function listLayouts() {
    global $DB, $userID;
    
    $layout = new PluginDashboardLayout();
    $layouts = $layout->find(['users_id' => $userID], ['created_at DESC']);
    
    return array_values($layouts);
}

// Widgets disponíveis
$available_widgets = [
    'tickets_chart' => [
        'name' => __('Gráfico de Chamados', 'dashboard'),
        'type' => 'chart',
        'query' => 'SELECT COUNT(*) as total, status FROM glpi_tickets WHERE date >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY status'
    ],
    'tickets_table' => [
        'name' => __('Tabela de Chamados', 'dashboard'),
        'type' => 'table',
        'query' => 'SELECT id, name, status, date FROM glpi_tickets ORDER BY date DESC LIMIT 10'
    ],
    'stats_card' => [
        'name' => __('Card de Estatísticas', 'dashboard'),
        'type' => 'card',
        'query' => 'SELECT COUNT(*) as total FROM glpi_tickets WHERE status IN (1,2,3,4)'
    ]
];

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

// Renderizar interface
Html::header(__('Dashboard Personalizado', 'dashboard'), $_SERVER['PHP_SELF'], 'plugins', 'dashboard', 'custom_layout');

echo '<div class="container-fluid">';
echo '<div class="row">';
echo '<div class="col-md-12">';
echo '<div class="panel panel-default">';
echo '<div class="panel-heading">';
echo '<h3 class="panel-title">' . __('Dashboard Personalizado', 'dashboard') . '</h3>';
echo '</div>';
echo '<div class="panel-body">';

// Barra de ferramentas
echo '<div class="toolbar">';
echo '<button class="btn btn-primary" onclick="saveCurrentLayout()">' . __('Salvar Layout', 'dashboard') . '</button>';
echo '<select class="form-control" id="layoutSelect" onchange="loadSelectedLayout()">';
echo '<option value="">' . __('Selecione um layout...', 'dashboard') . '</option>';
echo '</select>';
echo '</div>';

// Área de widgets
echo '<div class="grid-stack">';
// Widgets serão inseridos aqui dinamicamente
echo '</div>';

// Painel de widgets disponíveis
echo '<div class="widget-panel">';
echo '<h4>' . __('Widgets Disponíveis', 'dashboard') . '</h4>';
echo '<div class="widget-list">';
foreach($available_widgets as $id => $widget) {
    echo '<div class="widget-item" draggable="true" data-widget-id="' . $id . '">';
    echo $widget['name'];
    echo '</div>';
}
echo '</div>';
echo '</div>';

echo '</div>'; // panel-body
echo '</div>'; // panel
echo '</div>'; // col-md-12
echo '</div>'; // row
echo '</div>'; // container-fluid

// JavaScript
echo '<script>';
echo '// Inicialização do GridStack
var grid = GridStack.init({
    float: true,
    animate: true,
    resizable: {
        handles: "e,se,s,sw,w"
    }
});

// Função para salvar layout
function saveCurrentLayout() {
    var layout = {
        name: prompt("' . __('Nome do layout:', 'dashboard') . '"),
        widgets: []
    };
    
    grid.engine.nodes.forEach(function(node) {
        layout.widgets.push({
            id: node.el.getAttribute("data-widget-id"),
            x: node.x,
            y: node.y,
            w: node.w,
            h: node.h
        });
    });
    
    $.post("custom_layout.php", {
        action: "save",
        data: layout
    }, function(response) {
        alert("' . __('Layout salvo com sucesso!', 'dashboard') . '");
        loadLayouts();
    });
}

// Função para carregar layouts
function loadLayouts() {
    $.post("custom_layout.php", {
        action: "list"
    }, function(layouts) {
        var select = $("#layoutSelect");
        select.empty();
        select.append("<option value=\'\'>' . __('Selecione um layout...', 'dashboard') . '</option>");
        
        layouts.forEach(function(layout) {
            select.append(`<option value="${layout.id}">${layout.name}</option>`);
        });
    });
}

// Função para carregar layout selecionado
function loadSelectedLayout() {
    var layoutId = $("#layoutSelect").val();
    if(!layoutId) return;
    
    $.post("custom_layout.php", {
        action: "load",
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
    var widget = ' . json_encode($available_widgets) . '[widgetId];
    if(!widget) return;
    
    var content = "";
    switch(widget.type) {
        case "chart":
            content = `<div class="chart-container"></div>`;
            break;
        case "table":
            content = `<div class="table-container"></div>`;
            break;
        case "card":
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
    $.post("widget_data.php", {
        query: query
    }, function(data) {
        var widget = grid.engine.nodes.find(n => n.el.getAttribute("data-widget-id") === widgetId);
        if(!widget) return;
        
        var container = widget.el.querySelector(".chart-container, .table-container, .card-container");
        if(!container) return;
        
        // Renderizar dados baseado no tipo do widget
        renderWidgetData(container, data);
    });
}

// Função para renderizar dados do widget
function renderWidgetData(container, data) {
    if(container.classList.contains("chart-container")) {
        renderChart(container, data);
    } else if(container.classList.contains("table-container")) {
        renderTable(container, data);
    } else if(container.classList.contains("card-container")) {
        renderCard(container, data);
    }
}

// Inicialização
$(document).ready(function() {
    loadLayouts();
    
    // Configurar drag and drop de widgets
    $(".widget-item").on("dragstart", function(e) {
        e.originalEvent.dataTransfer.setData("widgetId", $(this).data("widget-id"));
    });
    
    $(".grid-stack").on("dragover", function(e) {
        e.preventDefault();
    }).on("drop", function(e) {
        e.preventDefault();
        var widgetId = e.originalEvent.dataTransfer.getData("widgetId");
        var position = grid.engine.getCellFromPixel({
            top: e.originalEvent.clientY,
            left: e.originalEvent.clientX
        });
        
        addWidget(widgetId, position.x, position.y, 3, 2);
    });
});';
echo '</script>';

Html::footer(); 