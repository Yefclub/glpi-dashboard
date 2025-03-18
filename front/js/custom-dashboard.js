/**
 * GLPI Dashboard - Custom Dashboard Builder
 * 
 * Este arquivo contém funções para o construtor de dashboards personalizados
 * permitindo que os usuários criem e gerenciem seus próprios dashboards.
 */

// Função para inicializar os controladores de widgets
function initializeWidgetControllers() {
    // Bind para botão de remover widget
    $('.widget-remove').off('click').on('click', function(e) {
        e.preventDefault();
        var gridItem = $(this).closest('.grid-stack-item');
        var grid = $('.grid-stack').data('gridstack');
        grid.removeWidget(gridItem);
    });
    
    // Bind para botão de atualizar widget
    $('.widget-refresh').off('click').on('click', function(e) {
        e.preventDefault();
        var widgetContent = $(this).closest('.grid-stack-item-content').find('.widget-content');
        var widgetType = widgetContent.data('type');
        
        widgetContent.html('<div class="widget-loading"><i class="fa fa-spinner fa-spin"></i> ' + __('Loading...', 'dashboard') + '</div>');
        loadWidgetData(widgetType, widgetContent);
    });
}

// Função para carregar dados do widget
function loadWidgetData(widgetType, container) {
    // Aqui você faria uma chamada AJAX para buscar os dados reais
    // Por enquanto, vamos simular um carregamento
    var url = 'ajax/get_widget_data.php';
    
    $.ajax({
        url: url,
        type: 'POST',
        data: {
            widget: widgetType
        },
        success: function(response) {
            // Como não temos o endpoint real, vamos simular a resposta
            setTimeout(function() {
                renderWidgetContent(widgetType, container);
            }, 500);
        },
        error: function() {
            container.html('<div class="alert alert-error">' + __('Error loading data', 'dashboard') + '</div>');
        }
    });
}

// Função para renderizar o conteúdo do widget com base no tipo
function renderWidgetContent(widgetType, container) {
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
        default:
            container.html('<div class="alert">Widget não implementado</div>');
    }
}

// Renderiza o resumo de tickets
function renderTicketsSummary(container) {
    // Dados de exemplo - idealmente viriam do servidor
    var data = {
        open: 45,
        in_progress: 28,
        closed: 215
    };
    
    var html = '<div class="row-fluid">';
    html += '<div class="span4 summary-box">';
    html += '<h4>' + __('Open', 'dashboard') + '</h4>';
    html += '<div class="number">' + data.open + '</div>';
    html += '</div>';
    html += '<div class="span4 summary-box">';
    html += '<h4>' + __('In Progress', 'dashboard') + '</h4>';
    html += '<div class="number">' + data.in_progress + '</div>';
    html += '</div>';
    html += '<div class="span4 summary-box">';
    html += '<h4>' + __('Closed', 'dashboard') + '</h4>';
    html += '<div class="number">' + data.closed + '</div>';
    html += '</div>';
    html += '</div>';
    
    container.html(html);
}

// Renderiza o gráfico de status de tickets
function renderTicketsStatus(container) {
    var chartId = 'chart-tickets-status-' + new Date().getTime();
    container.html('<div id="' + chartId + '" style="width: 100%; height: 90%;"></div>');
    
    // Dados de exemplo - idealmente viriam do servidor
    var data = [
        {name: __('New', 'dashboard'), y: 45},
        {name: __('In Progress', 'dashboard'), y: 28},
        {name: __('Waiting', 'dashboard'), y: 12},
        {name: __('Solved', 'dashboard'), y: 187},
        {name: __('Closed', 'dashboard'), y: 215}
    ];
    
    drawPieChart(chartId, __('Tickets by Status', 'dashboard'), data);
}

// Renderiza o gráfico de evolução de tickets
function renderTicketsEvolution(container) {
    var chartId = 'chart-tickets-evolution-' + new Date().getTime();
    container.html('<div id="' + chartId + '" style="width: 100%; height: 90%;"></div>');
    
    // Dados de exemplo - idealmente viriam do servidor
    var categories = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
    var series = [
        {name: __('Opened', 'dashboard'), data: [15, 20, 25, 18, 22, 30, 28, 35, 25, 32, 28, 30]},
        {name: __('Closed', 'dashboard'), data: [12, 18, 22, 15, 25, 28, 30, 32, 22, 30, 25, 35]}
    ];
    
    drawLineChart(chartId, __('Tickets Evolution', 'dashboard'), categories, series);
}

// Renderiza o resumo de assets
function renderAssetsSummary(container) {
    // Dados de exemplo - idealmente viriam do servidor
    var data = {
        computers: 128,
        monitors: 95,
        printers: 42
    };
    
    var html = '<div class="row-fluid">';
    html += '<div class="span4 summary-box">';
    html += '<h4>' + __('Computers', 'dashboard') + '</h4>';
    html += '<div class="number">' + data.computers + '</div>';
    html += '</div>';
    html += '<div class="span4 summary-box">';
    html += '<h4>' + __('Monitors', 'dashboard') + '</h4>';
    html += '<div class="number">' + data.monitors + '</div>';
    html += '</div>';
    html += '<div class="span4 summary-box">';
    html += '<h4>' + __('Printers', 'dashboard') + '</h4>';
    html += '<div class="number">' + data.printers + '</div>';
    html += '</div>';
    html += '</div>';
    
    container.html(html);
}

// Renderiza o gráfico de distribuição de assets
function renderAssetsDistribution(container) {
    var chartId = 'chart-assets-distribution-' + new Date().getTime();
    container.html('<div id="' + chartId + '" style="width: 100%; height: 90%;"></div>');
    
    // Dados de exemplo - idealmente viriam do servidor
    var categories = [
        __('Computers', 'dashboard'),
        __('Monitors', 'dashboard'),
        __('Printers', 'dashboard'),
        __('Phones', 'dashboard'),
        __('Software', 'dashboard')
    ];
    
    var series = [
        {name: __('Assets', 'dashboard'), data: [128, 95, 42, 35, 76]}
    ];
    
    drawColumnChart(chartId, __('Assets Distribution', 'dashboard'), categories, series);
}

// Função auxiliar para desenhar gráfico de pizza
function drawPieChart(containerId, title, data) {
    Highcharts.chart(containerId, {
        chart: {
            type: 'pie',
            options3d: {
                enabled: true,
                alpha: 45,
                beta: 0
            }
        },
        title: {
            text: title
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
            name: __('Tickets', 'dashboard'),
            data: data
        }]
    });
}

// Função auxiliar para desenhar gráfico de linha
function drawLineChart(containerId, title, categories, series) {
    Highcharts.chart(containerId, {
        chart: {
            type: 'line'
        },
        title: {
            text: title
        },
        xAxis: {
            categories: categories
        },
        yAxis: {
            title: {
                text: __('Number of Tickets', 'dashboard')
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
        series: series
    });
}

// Função auxiliar para desenhar gráfico de colunas
function drawColumnChart(containerId, title, categories, series) {
    Highcharts.chart(containerId, {
        chart: {
            type: 'column'
        },
        title: {
            text: title
        },
        xAxis: {
            categories: categories,
            crosshair: true
        },
        yAxis: {
            min: 0,
            title: {
                text: __('Quantity', 'dashboard')
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
        series: series
    });
}

// Função de suporte para tradução
function __(text, domain) {
    // Esta é uma função simplificada - em produção você usaria o sistema de tradução real do GLPI
    return text;
} 