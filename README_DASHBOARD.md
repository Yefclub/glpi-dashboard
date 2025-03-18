# Dashboard Personalizado para GLPI

Este módulo adiciona um dashboard personalizado e moderno ao GLPI, permitindo visualização de dados em tempo real, personalização completa da interface, e compartilhamento de widgets entre usuários.

## Funcionalidades Principais

### 1. Consultas SQL Reais
- Integração direta com o banco de dados do GLPI
- Visualização de dados reais e atualizados automaticamente a cada 5 minutos
- Filtros inteligentes por entidade e outros parâmetros do GLPI

### 2. Widgets Específicos
O dashboard inclui diversos widgets prontos para uso:

#### Chamados
- **Resumo de Chamados**: Estatísticas de chamados abertos, em andamento e fechados
- **Chamados por Status**: Gráfico com a distribuição por status (novo, atribuído, planejado, pendente, etc.)
- **Evolução de Chamados**: Tendências de abertura e fechamento de chamados ao longo do tempo
- **Chamados por Categoria**: Distribuição de chamados por categoria
- **Chamados por Solicitante**: Análise dos usuários que mais abrem chamados
- **Satisfação de Chamados**: Métricas de satisfação dos usuários com o suporte

#### Ativos
- **Resumo de Ativos**: Números totais de computadores, monitores e impressoras
- **Distribuição de Ativos**: Gráfico de barras com todos os tipos de ativos

### 3. Exportação de Dados
Qualquer widget pode ser exportado nos seguintes formatos:
- CSV: Para importação em planilhas
- Excel: Formatado para Microsoft Excel
- PDF: Relatórios em formato de documento

### 4. Compartilhamento de Dashboards
- **Compartilhamento de Widgets**: Permite enviar widgets específicos para outros usuários
- **Notificações de Compartilhamento**: Sistema de notificação quando um widget é compartilhado
- **Aceitação/Rejeição**: Os usuários podem aceitar ou rejeitar widgets compartilhados

## Como Usar

### Acessando o Dashboard
1. Navegue até o menu "Dashboard"
2. Selecione a opção "Dashboard Personalizado"

### Personalizando o Dashboard
1. Clique em "Adicionar Widget" para incluir novos componentes
2. Arraste e solte os widgets para reorganizá-los
3. Redimensione os widgets conforme necessário

### Salvando e Carregando Dashboards
1. Clique em "Salvar Dashboard" para armazenar sua configuração
2. Dê um nome e descrição para facilitar a identificação
3. Use "Carregar Dashboard" para restaurar configurações salvas

### Exportando Dados
1. Clique no ícone de download em qualquer widget
2. Escolha o formato desejado (CSV, Excel ou PDF)
3. O arquivo será automaticamente baixado pelo navegador

### Compartilhando Widgets
1. Clique no ícone de compartilhamento no widget desejado
2. Selecione os usuários que receberão o widget
3. Você também pode copiar um link de compartilhamento direto

## Requisitos
- GLPI 10.0 ou superior
- Permissões adequadas configuradas no GLPI

## Configuração Avançada
- Todos os widgets podem ser personalizados editando os arquivos PHP na pasta `/front/ajax/`
- Novas consultas SQL podem ser adicionadas no arquivo `get_glpi_data.php`
- Estilos adicionais podem ser definidos em `custom-dashboard.css`

## Solução de Problemas
Se os dados reais não estiverem carregando, verifique:
1. Permissões de banco de dados do usuário GLPI
2. Configuração correta das entidades e grupos
3. Logs de erro no console do navegador

---

Desenvolvido como parte do plugin GLPI Dashboard. 