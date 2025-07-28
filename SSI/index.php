<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Docentes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="app-container">
        <header class="app-header">
            <img class="logo" src="logo.png">
        </header>

        <main class="app-main">
            <div class="query-panel">
                <div class="connection-info">
                    <span class="db-status connected">
                        <i class="fas fa-circle"></i> Conectado a SII
                    </span>
                </div>

                <form id="queryForm" class="query-controls">
                    <div class="select-wrapper">
                        <select name="query_type" id="queryType" class="query-select">
                            <option value="" disabled selected>Seleccione un grupo de docentes...</option>
                            <option value="guarani">Docentes Guaraní</option>
                            <option value="mapuche">Docentes Mapuche</option>
                            <option value="combinados">Docentes Combinados</option>
                        </select>
                        <i class="fas fa-chevron-down"></i>
                    </div>

                    <button type="button" id="refreshBtn" class="refresh-btn">
                        <i class="fas fa-sync-alt"></i> Buscar
                    </button>
                    
                    <!-- Añadido el contenedor de búsqueda -->
                    <div class="search-container">
                        <input type="text" id="searchInput" class="search-input" placeholder="Buscar...">
                        <button type="button" id="searchBtn" class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>

                <div id="resultsContainer" class="results-container"></div>
                <div id="paginationContainer" class="pagination-container"></div>

            </div>
        </main>

        <footer class="app-footer">
            <p>TINKUY - Sistema Integral de Información  1.0 &copy; 2025</p>
        </footer>
    </div>


     <script>
let currentPage = 1;
const perPage = 10;

document.getElementById('refreshBtn').addEventListener('click', () => {
    currentPage = 1;
    cargarResultados();
});

document.getElementById('queryType').addEventListener('change', () => {
    currentPage = 1;
});

// Añadido el controlador de eventos para el botón de búsqueda
document.getElementById('searchBtn').addEventListener('click', function() {
    const searchInput = document.getElementById('searchInput');
    searchInput.classList.toggle('active');
    
    // Enfocar el input cuando se muestra
    if (searchInput.classList.contains('active')) {
        searchInput.focus();
    }
});

// Opcional: puedes añadir funcionalidad de búsqueda en tiempo real
document.getElementById('searchInput').addEventListener('keyup', function(e) {
    if (e.key === 'Enter' || this.value === '') {
        currentPage = 1;
        cargarResultados();
    }
});

async function cargarResultados() {
    const queryType = document.getElementById('queryType').value;
    const searchTerm = document.getElementById('searchInput').value.trim();
    const resultsContainer = document.getElementById('resultsContainer');
    const paginationContainer = document.getElementById('paginationContainer');

    if (!queryType) {
        resultsContainer.innerHTML = '<div class="error">Seleccione un tipo de consulta</div>';
        paginationContainer.innerHTML = '';
        return;
    }

    resultsContainer.innerHTML = '<div class="loading">Cargando datos...</div>';
    paginationContainer.innerHTML = '';

    try {
        const response = await fetch(`http://localhost:8000/consultas.php?action=getData&type=${queryType}&page=${currentPage}`);
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('La respuesta no es JSON');
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Error desconocido');
        }

        // Construir tabla
        let html = `<h3>Resultados (Página ${data.pagination.current_page} de ${data.pagination.total_pages})</h3>`;
        html += '<div class="table-container"><table><thead><tr>';

        if (data.data.length > 0) {
            Object.keys(data.data[0]).forEach(key => {
                html += `<th>${key}</th>`;
            });
            html += '</tr></thead><tbody>';

            data.data.forEach(row => {
                html += '<tr>';
                Object.values(row).forEach(value => {
                    html += `<td>${value ?? ''}</td>`;
                });
                html += '</tr>';
            });
        }

        html += '</tbody></table></div>';
        resultsContainer.innerHTML = html;

        // Paginación mejorada
        const { current_page, total_pages } = data.pagination;
        let pagHtml = '';

        if (current_page > 1) {
            pagHtml += `<button onclick="irPagina(1)">«</button>`;
            pagHtml += `<button onclick="irPagina(${current_page - 1})">‹</button>`;
        }

        const maxPagesToShow = 5;
        let startPage = Math.max(1, current_page - Math.floor(maxPagesToShow / 2));
        let endPage = startPage + maxPagesToShow - 1;

        if (endPage > total_pages) {
            endPage = total_pages;
            startPage = Math.max(1, endPage - maxPagesToShow + 1);
        }

        if (startPage > 1) {
            pagHtml += `<span>...</span>`;
        }

        for (let i = startPage; i <= endPage; i++) {
            pagHtml += `<button onclick="irPagina(${i})" ${i === current_page ? 'disabled' : ''}>${i}</button>`;
        }

        if (endPage < total_pages) {
            pagHtml += `<span>...</span>`;
        }

        if (current_page < total_pages) {
            pagHtml += `<button onclick="irPagina(${current_page + 1})">›</button>`;
            pagHtml += `<button onclick="irPagina(${total_pages})">»</button>`;
        }

        paginationContainer.innerHTML = pagHtml;

    } catch (error) {
        console.error('Error:', error);
        resultsContainer.innerHTML = `
            <div class="error">
                <strong>Error al cargar datos:</strong> ${error.message}
                <button onclick="location.reload()">Reintentar</button>
            </div>`;
    }
}

function irPagina(pagina) {
    currentPage = pagina;
    cargarResultados();
}
</script>


</body>
</html>