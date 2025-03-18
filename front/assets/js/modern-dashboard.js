/**
 * GLPI Dashboard - JavaScript Moderno
 * 
 * Este arquivo contém as funcionalidades JavaScript para o dashboard moderno
 * Utiliza jQuery UI para drag-and-drop e outras funcionalidades interativas
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializa o dashboard
    initDashboard();
    
    // Carrega os widgets do usuário
    loadUserWidgets();
    
    // Configura os eventos dos botões
    setupEventListeners();
});

/**
 * Inicializa o dashboard e suas funcionalidades
 */
function initDashboard() {
    console.log('Inicializando Dashboard Moderno');
    
    // Inicializa os widgets existentes
    initWidgets();
    
    // Configura o contêiner de widgets para ser ordenável
    $(".widgets-container").sortable({
        handle: '.widget-header',
        placeholder: 'widget-placeholder',
        opacity: 0.7,
        start: function(event, ui) {
            $(ui.item).addClass('widget-dragging');
        },
        stop: function(event, ui) {
            $(ui.item).removeClass('widget-dragging');
            saveWidgetPositions();
        }
    }).disableSelection();
}

/**
 * Inicializa os widgets existentes com funcionalidades
 */
function initWidgets() {
    // Para cada widget no dashboard
    $('.widget').each(function() {
        const widgetId = $(this).data('widget-id');
        const widgetType = $(this).data('widget-type');
        
        // Inicializa widget baseado no tipo
        initializeWidgetByType(widgetId, widgetType);
        
        // Configura os widgets para serem redimensionáveis
        $(this).resizable({
            handles: 'se',
            minHeight: 200,
            minWidth: 250,
            stop: function(event, ui) {
                saveWidgetSize(widgetId, ui.size);
                // Atualiza o conteúdo do widget após redimensionar (para gráficos)
                refreshWidgetContent(widgetId);
            }
        });
    });
}

/**
 * Inicializa um widget específico baseado no seu tipo
 */
function initializeWidgetByType(widgetId, widgetType) {
    const widget = $(`[data-widget-id="${widgetId}"]`);
    
    // Adiciona a classe de carregamento
    widget.find('.widget-content').append('<div class="loading-indicator"><div class="spinner"></div></div>');
    
    // Com base no tipo de widget, inicializa adequadamente
    switch(widgetType) {
        case 'chart-line':
        case 'chart-bar':
        case 'chart-pie':
            initializeChart(widgetId, widgetType);
            break;
        case 'table':
            initializeTable(widgetId);
            break;
        case 'counter':
            initializeCounter(widgetId);
            break;
        case 'ticket-list':
            initializeTicketList(widgetId);
            break;
        default:
            console.log(`Tipo de widget desconhecido: ${widgetType}`);
    }
    
    // Remove a indicação de carregamento após inicialização
    setTimeout(() => {
        widget.find('.loading-indicator').fadeOut(300, function() {
            $(this).remove();
        });
    }, 800);
}

/**
 * Carrega os widgets do usuário atual
 */
function loadUserWidgets() {
    // Simulando carregamento de widgets (em produção, isto seria uma chamada AJAX)
    console.log('Carregando widgets do usuário...');
    
    // Para demonstração, vamos adicionar alguns widgets de exemplo
    const demoWidgets = [
        {
            id: 'widget-1',
            type: 'counter',
            title: 'Resumo de Chamados',
            theme: 'primary'
        },
        {
            id: 'widget-2',
            type: 'chart-line',
            title: 'Chamados por Mês',
            theme: 'info'
        },
        {
            id: 'widget-3',
            type: 'table',
            title: 'Últimos Chamados',
            theme: 'success'
        }
    ];
    
    renderWidgets(demoWidgets);
}

/**
 * Renderiza widgets na interface
 */
function renderWidgets(widgets) {
    const container = $('.widgets-container');
    container.empty();
    
    if (!widgets || widgets.length === 0) {
        container.append('<div class="no-widgets"><p>Nenhum widget adicionado. Clique no botão "Adicionar Widget" para começar.</p></div>');
        return;
    }
    
    // Renderiza cada widget
    widgets.forEach(widget => {
        const widgetHtml = createWidgetHtml(widget);
        container.append(widgetHtml);
        initializeWidgetByType(widget.id, widget.type);
    });
}

/**
 * Cria o HTML para um widget
 */
function createWidgetHtml(widget) {
    const themeClass = widget.theme ? `theme-${widget.theme}` : '';
    
    return `
    <div class="widget ${themeClass}" data-widget-id="${widget.id}" data-widget-type="${widget.type}">
        <div class="widget-header">
            <h3 class="widget-title">${widget.title}</h3>
            <div class="widget-toolbar">
                <button type="button" class="widget-refresh" title="Atualizar"><i class="fas fa-sync-alt"></i></button>
                <button type="button" class="widget-settings" title="Configurações"><i class="fas fa-cog"></i></button>
                <button type="button" class="widget-remove" title="Remover"><i class="fas fa-times"></i></button>
            </div>
        </div>
        <div class="widget-content">
            <!-- O conteúdo será preenchido dinamicamente -->
        </div>
    </div>
    `;
}

/**
 * Inicializa um gráfico de widget
 */
function initializeChart(widgetId, chartType) {
    const widget = $(`[data-widget-id="${widgetId}"]`);
    const content = widget.find('.widget-content');
    
    // Adiciona o contêiner do gráfico
    content.append('<div class="chart-container"></div>');
    const chartContainer = content.find('.chart-container')[0];
    
    // Em produção, aqui você carregaria os dados do gráfico via AJAX
    // Simulando dados para demonstração
    const chartData = getTestChartData();
    
    // Determina o tipo de gráfico Highcharts baseado no tipo de widget
    let highchartsType = 'line';
    if (chartType === 'chart-bar') highchartsType = 'column';
    if (chartType === 'chart-pie') highchartsType = 'pie';
    
    // Inicializa o gráfico Highcharts
    Highcharts.chart(chartContainer, {
        chart: {
            type: highchartsType,
            style: {
                fontFamily: 'Poppins, sans-serif'
            },
            backgroundColor: 'transparent'
        },
        title: {
            text: null
        },
        xAxis: {
            categories: chartData.categories,
            labels: {
                style: {
                    color: '#6c757d'
                }
            }
        },
        yAxis: {
            title: {
                text: null
            },
            labels: {
                style: {
                    color: '#6c757d'
                }
            },
            gridLineColor: 'rgba(222, 226, 230, 0.6)'
        },
        legend: {
            enabled: chartType !== 'chart-pie',
            itemStyle: {
                color: '#6c757d',
                fontWeight: 'normal'
            }
        },
        tooltip: {
            headerFormat: '<b>{point.key}</b><br>',
            pointFormat: '{series.name}: {point.y}'
        },
        plotOptions: {
            series: {
                marker: {
                    enabled: false
                }
            },
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: false
                },
                showInLegend: true
            }
        },
        series: chartData.series,
        credits: {
            enabled: false
        },
        colors: ['#4361ee', '#f72585', '#4cc9f0', '#3a0ca3', '#7209b7']
    });
}

/**
 * Dados de teste para os gráficos
 */
function getTestChartData() {
    return {
        categories: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
        series: [{
            name: 'Chamados abertos',
            data: [29, 35, 42, 51, 46, 33]
        }, {
            name: 'Chamados resolvidos',
            data: [24, 31, 38, 46, 42, 28]
        }]
    };
}

/**
 * Inicializa um widget de tabela
 */
function initializeTable(widgetId) {
    const widget = $(`[data-widget-id="${widgetId}"]`);
    const content = widget.find('.widget-content');
    
    // Em produção, aqui você carregaria os dados da tabela via AJAX
    // Simulando dados para demonstração
    const tableData = {
        headers: ['ID', 'Título', 'Status', 'Prioridade', 'Data'],
        rows: [
            [1025, 'Problema com impressora', 'Aberto', 'Alta', '15/07/2023'],
            [1024, 'Falha no sistema ERP', 'Em andamento', 'Média', '14/07/2023'],
            [1023, 'Solicitação de hardware', 'Fechado', 'Baixa', '12/07/2023'],
            [1022, 'Configuração de email', 'Aberto', 'Baixa', '11/07/2023'],
            [1021, 'Acesso ao sistema', 'Resolvido', 'Alta', '10/07/2023']
        ]
    };
    
    // Construir a tabela HTML
    let tableHtml = '<div class="table-responsive"><table class="table table-hover">';
    
    // Cabeçalho
    tableHtml += '<thead><tr>';
    tableData.headers.forEach(header => {
        tableHtml += `<th>${header}</th>`;
    });
    tableHtml += '</tr></thead>';
    
    // Corpo
    tableHtml += '<tbody>';
    tableData.rows.forEach(row => {
        tableHtml += '<tr>';
        row.forEach(cell => {
            tableHtml += `<td>${cell}</td>`;
        });
        tableHtml += '</tr>';
    });
    tableHtml += '</tbody></table></div>';
    
    // Adiciona a tabela ao conteúdo
    content.append(tableHtml);
}

/**
 * Inicializa um contador de widget
 */
function initializeCounter(widgetId) {
    const widget = $(`[data-widget-id="${widgetId}"]`);
    const content = widget.find('.widget-content');
    
    // Em produção, aqui você carregaria os dados via AJAX
    // Simulando dados para demonstração
    const counters = [
        { label: 'Abertos', value: 42, class: 'open' },
        { label: 'Em andamento', value: 23, class: 'processing' },
        { label: 'Resolvidos', value: 78, class: 'closed' }
    ];
    
    // Criar os cards de contadores
    let countersHtml = '<div class="summary-cards">';
    counters.forEach(counter => {
        countersHtml += `
        <div class="summary-card ${counter.class}">
            <div class="number">${counter.value}</div>
            <div class="label">${counter.label}</div>
        </div>
        `;
    });
    countersHtml += '</div>';
    
    // Adiciona os contadores ao conteúdo
    content.append(countersHtml);
}

/**
 * Inicializa uma lista de chamados
 */
function initializeTicketList(widgetId) {
    const widget = $(`[data-widget-id="${widgetId}"]`);
    const content = widget.find('.widget-content');
    
    // Em produção, aqui você carregaria os dados via AJAX
    // Simulando dados para demonstração
    const tickets = [
        { id: 1025, title: 'Problema com impressora', status: 'Aberto', priority: 'Alta', date: '15/07/2023' },
        { id: 1024, title: 'Falha no sistema ERP', status: 'Em andamento', priority: 'Média', date: '14/07/2023' },
        { id: 1023, title: 'Solicitação de hardware', status: 'Fechado', priority: 'Baixa', date: '12/07/2023' }
    ];
    
    // Criar a lista de chamados
    let ticketsHtml = '<div class="ticket-list">';
    tickets.forEach(ticket => {
        ticketsHtml += `
        <div class="ticket-item">
            <div class="ticket-id">#${ticket.id}</div>
            <div class="ticket-info">
                <div class="ticket-title">${ticket.title}</div>
                <div class="ticket-meta">
                    <span class="ticket-status">${ticket.status}</span>
                    <span class="ticket-priority">${ticket.priority}</span>
                    <span class="ticket-date">${ticket.date}</span>
                </div>
            </div>
        </div>
        `;
    });
    ticketsHtml += '</div>';
    
    // Adiciona a lista ao conteúdo
    content.append(ticketsHtml);
}

/**
 * Configura os listeners de eventos para botões e interações
 */
function setupEventListeners() {
    // Botão de adicionar widget
    $('#add-widget-btn').on('click', function() {
        openAddWidgetModal();
    });
    
    // Botões de cada widget (delegação de eventos)
    $(document).on('click', '.widget-refresh', function() {
        const widget = $(this).closest('.widget');
        const widgetId = widget.data('widget-id');
        refreshWidget(widgetId);
    });
    
    $(document).on('click', '.widget-settings', function() {
        const widget = $(this).closest('.widget');
        const widgetId = widget.data('widget-id');
        openWidgetSettings(widgetId);
    });
    
    $(document).on('click', '.widget-remove', function() {
        const widget = $(this).closest('.widget');
        const widgetId = widget.data('widget-id');
        confirmRemoveWidget(widgetId);
    });
    
    // Fechamento de modais
    $(document).on('click', '.modal-close, .modal-cancel', function() {
        closeModals();
    });
    
    // Evita que cliques dentro do modal fechem o modal
    $(document).on('click', '.modal-container', function(e) {
        e.stopPropagation();
    });
    
    // Fechar modal ao clicar fora dele
    $(document).on('click', '.modal-overlay', function() {
        closeModals();
    });
    
    // Salvar configurações de widget
    $(document).on('click', '#save-widget-settings', function() {
        const widgetId = $(this).data('widget-id');
        saveWidgetSettings(widgetId);
    });
    
    // Adicionar novo widget
    $(document).on('click', '#add-widget-confirm', function() {
        addNewWidget();
    });
}

/**
 * Abre o modal para adicionar um novo widget
 */
function openAddWidgetModal() {
    const modal = `
    <div class="modal-overlay" id="add-widget-modal">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">Adicionar novo widget</h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Tipo de widget</label>
                    <select class="form-control form-select" id="widget-type">
                        <option value="chart-line">Gráfico de linha</option>
                        <option value="chart-bar">Gráfico de barras</option>
                        <option value="chart-pie">Gráfico de pizza</option>
                        <option value="table">Tabela</option>
                        <option value="counter">Contadores</option>
                        <option value="ticket-list">Lista de chamados</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Título do widget</label>
                    <input type="text" class="form-control" id="widget-title" placeholder="Digite um título">
                </div>
                <div class="form-group">
                    <label class="form-label">Tema</label>
                    <select class="form-control form-select" id="widget-theme">
                        <option value="">Padrão</option>
                        <option value="primary">Primário</option>
                        <option value="success">Sucesso</option>
                        <option value="warning">Alerta</option>
                        <option value="info">Informação</option>
                        <option value="dark">Escuro</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Fonte de dados</label>
                    <select class="form-control form-select" id="data-source">
                        <option value="tickets">Chamados</option>
                        <option value="users">Usuários</option>
                        <option value="assets">Ativos</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-cancel">Cancelar</button>
                <button type="button" class="btn btn-primary" id="add-widget-confirm">Adicionar</button>
            </div>
        </div>
    </div>
    `;
    
    $('body').append(modal);
    setTimeout(() => {
        $('#add-widget-modal').addClass('active');
    }, 10);
}

/**
 * Adiciona um novo widget com base nos dados do modal
 */
function addNewWidget() {
    const type = $('#widget-type').val();
    const title = $('#widget-title').val() || 'Novo Widget';
    const theme = $('#widget-theme').val();
    const dataSource = $('#data-source').val();
    
    // Em produção, você enviaria esses dados para o servidor
    // e receberia o ID do novo widget
    const widgetId = 'widget-' + Date.now();
    
    // Cria o widget
    const widget = {
        id: widgetId,
        type: type,
        title: title,
        theme: theme,
        dataSource: dataSource
    };
    
    // Adiciona o widget ao DOM
    const widgetHtml = createWidgetHtml(widget);
    $('.widgets-container').append(widgetHtml);
    
    // Inicializa o widget
    initializeWidgetByType(widgetId, type);
    
    // Adiciona as funcionalidades de arrastar e redimensionar
    $(`[data-widget-id="${widgetId}"]`).resizable({
        handles: 'se',
        minHeight: 200,
        minWidth: 250,
        stop: function(event, ui) {
            saveWidgetSize(widgetId, ui.size);
            refreshWidgetContent(widgetId);
        }
    });
    
    // Atualiza as posições dos widgets
    $(".widgets-container").sortable('refresh');
    
    // Salva a configuração do widget (em produção, enviaria para o servidor)
    saveWidgetPositions();
    
    // Mostra notificação
    showNotification('success', 'Widget adicionado', `Widget "${title}" foi adicionado com sucesso.`);
    
    // Fecha o modal
    closeModals();
}

/**
 * Abre as configurações de um widget
 */
function openWidgetSettings(widgetId) {
    const widget = $(`[data-widget-id="${widgetId}"]`);
    const title = widget.find('.widget-title').text();
    const type = widget.data('widget-type');
    
    const modal = `
    <div class="modal-overlay" id="widget-settings-modal">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">Configurações do widget</h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Título do widget</label>
                    <input type="text" class="form-control" id="edit-widget-title" value="${title}">
                </div>
                <div class="form-group">
                    <label class="form-label">Tema</label>
                    <select class="form-control form-select" id="edit-widget-theme">
                        <option value="">Padrão</option>
                        <option value="primary">Primário</option>
                        <option value="success">Sucesso</option>
                        <option value="warning">Alerta</option>
                        <option value="info">Informação</option>
                        <option value="dark">Escuro</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Atualização automática</label>
                    <select class="form-control form-select" id="edit-widget-refresh">
                        <option value="0">Desativada</option>
                        <option value="60">A cada minuto</option>
                        <option value="300">A cada 5 minutos</option>
                        <option value="900">A cada 15 minutos</option>
                        <option value="1800">A cada 30 minutos</option>
                        <option value="3600">A cada hora</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-cancel">Cancelar</button>
                <button type="button" class="btn btn-primary" id="save-widget-settings" data-widget-id="${widgetId}">Salvar</button>
            </div>
        </div>
    </div>
    `;
    
    $('body').append(modal);
    
    // Seleciona o tema atual
    const currentTheme = widget.attr('class').split(' ').find(c => c.startsWith('theme-'));
    if (currentTheme) {
        const themeValue = currentTheme.replace('theme-', '');
        $('#edit-widget-theme').val(themeValue);
    }
    
    setTimeout(() => {
        $('#widget-settings-modal').addClass('active');
    }, 10);
}

/**
 * Salva as configurações de um widget
 */
function saveWidgetSettings(widgetId) {
    const widget = $(`[data-widget-id="${widgetId}"]`);
    const title = $('#edit-widget-title').val();
    const theme = $('#edit-widget-theme').val();
    const refresh = $('#edit-widget-refresh').val();
    
    // Atualiza o título
    widget.find('.widget-title').text(title);
    
    // Atualiza o tema
    widget.removeClass((index, className) => {
        return (className.match(/(^|\s)theme-\S+/g) || []).join(' ');
    });
    
    if (theme) {
        widget.addClass(`theme-${theme}`);
    }
    
    // Configura a atualização automática
    if (widget.data('refresh-interval')) {
        clearInterval(widget.data('refresh-interval'));
    }
    
    if (refresh > 0) {
        const interval = setInterval(() => {
            refreshWidget(widgetId);
        }, refresh * 1000);
        widget.data('refresh-interval', interval);
    }
    
    // Em produção, você enviaria essas configurações para o servidor
    
    // Mostra notificação
    showNotification('success', 'Configurações salvas', 'As configurações do widget foram atualizadas.');
    
    // Fecha o modal
    closeModals();
}

/**
 * Confirma a remoção de um widget
 */
function confirmRemoveWidget(widgetId) {
    const widget = $(`[data-widget-id="${widgetId}"]`);
    const title = widget.find('.widget-title').text();
    
    const modal = `
    <div class="modal-overlay" id="remove-widget-modal">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">Remover widget</h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja remover o widget "${title}"?</p>
                <p>Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-cancel">Cancelar</button>
                <button type="button" class="btn btn-danger" id="remove-widget-confirm" data-widget-id="${widgetId}">Remover</button>
            </div>
        </div>
    </div>
    `;
    
    $('body').append(modal);
    
    // Configura o botão de confirmação
    $(document).on('click', '#remove-widget-confirm', function() {
        const widgetId = $(this).data('widget-id');
        removeWidget(widgetId);
    });
    
    setTimeout(() => {
        $('#remove-widget-modal').addClass('active');
    }, 10);
}

/**
 * Remove um widget
 */
function removeWidget(widgetId) {
    const widget = $(`[data-widget-id="${widgetId}"]`);
    
    // Animação de remoção
    widget.addClass('fade-out');
    
    setTimeout(() => {
        // Remove o widget do DOM
        widget.remove();
        
        // Em produção, você enviaria esta ação para o servidor
        
        // Atualiza as posições
        saveWidgetPositions();
        
        // Mostra notificação
        showNotification('success', 'Widget removido', 'O widget foi removido com sucesso.');
        
        // Verifica se ainda há widgets
        if ($('.widgets-container').children().length === 0) {
            $('.widgets-container').append('<div class="no-widgets"><p>Nenhum widget adicionado. Clique no botão "Adicionar Widget" para começar.</p></div>');
        }
    }, 300);
    
    // Fecha o modal
    closeModals();
}

/**
 * Atualiza um widget específico
 */
function refreshWidget(widgetId) {
    const widget = $(`[data-widget-id="${widgetId}"]`);
    const widgetType = widget.data('widget-type');
    
    // Adiciona indicador de carregamento
    widget.find('.widget-content').append('<div class="loading-indicator"><div class="spinner"></div></div>');
    
    // Simulando atraso de rede (em produção, seria uma chamada AJAX)
    setTimeout(() => {
        // Inicializa o widget novamente
        widget.find('.widget-content').empty();
        initializeWidgetByType(widgetId, widgetType);
        
        // Mostra notificação
        showNotification('success', 'Widget atualizado', 'Os dados do widget foram atualizados.');
    }, 800);
}

/**
 * Atualiza o conteúdo de um widget após redimensionamento
 */
function refreshWidgetContent(widgetId) {
    const widget = $(`[data-widget-id="${widgetId}"]`);
    const widgetType = widget.data('widget-type');
    
    // Se for um gráfico, precisamos redimensioná-lo
    if (widgetType.startsWith('chart-')) {
        // Em um ambiente real, você poderia redesenhar o gráfico aqui
        // Para simplificar, vamos recarregar o widget
        refreshWidget(widgetId);
    }
}

/**
 * Salva o tamanho de um widget
 */
function saveWidgetSize(widgetId, size) {
    // Em produção, você enviaria essa informação para o servidor
    console.log(`Tamanho do widget ${widgetId} alterado: ${size.width}x${size.height}`);
}

/**
 * Salva as posições dos widgets
 */
function saveWidgetPositions() {
    // Em produção, você enviaria as posições para o servidor
    const positions = [];
    
    $('.widgets-container .widget').each(function(index) {
        const widgetId = $(this).data('widget-id');
        positions.push({
            id: widgetId,
            position: index
        });
    });
    
    console.log('Posições dos widgets salvas:', positions);
}

/**
 * Fecha todos os modais
 */
function closeModals() {
    $('.modal-overlay').removeClass('active');
    
    setTimeout(() => {
        $('.modal-overlay').remove();
    }, 300);
}

/**
 * Exibe uma notificação
 */
function showNotification(type, title, message) {
    // Remove notificações anteriores
    $('.notification').remove();
    
    let icon = 'info-circle';
    if (type === 'success') icon = 'check-circle';
    if (type === 'warning') icon = 'exclamation-triangle';
    if (type === 'error') icon = 'times-circle';
    
    const notification = `
    <div class="notification ${type}">
        <div class="notification-icon">
            <i class="fas fa-${icon}"></i>
        </div>
        <div class="notification-content">
            <div class="notification-title">${title}</div>
            <div class="notification-message">${message}</div>
        </div>
        <button type="button" class="notification-close">&times;</button>
    </div>
    `;
    
    $('body').append(notification);
    
    setTimeout(() => {
        $('.notification').addClass('show');
    }, 10);
    
    // Configura o botão de fechar
    $('.notification-close').on('click', function() {
        $('.notification').removeClass('show');
        
        setTimeout(() => {
            $('.notification').remove();
        }, 300);
    });
    
    // Auto-fecha após 5 segundos
    setTimeout(() => {
        $('.notification').removeClass('show');
        
        setTimeout(() => {
            $('.notification').remove();
        }, 300);
    }, 5000);
}
