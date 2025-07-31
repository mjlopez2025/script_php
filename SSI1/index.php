<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Docentes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="app-container">
        <header class="app-header">
            <img class="logo" src="logo.png">
        </header>
        <nav class="navbar navbar-expand-lg custom-navbar">
            <div class="container-fluid">
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="index.php">Home</a>
                        </li>
                        <li class="nav-item dropdown nav-color">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                Docentes
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" data-value="guarani">Docentes Guaraní</a></li>
                                <li><a class="dropdown-item" data-value="mapuche">Docentes Mapuche</a></li>
                                <li><a class="dropdown-item" data-value="combinados">Docentes Combinados</a></li>
                            </ul>
                        </li>
                    </ul>

                    <!-- Contenedor del filtro -->
                    <div class="filter-container">
                        <label for="filterInput" class="filter-label">Filtrar:</label>
                        <input type="text" id="filterInput" class="form-control filter-input"
                            placeholder="Nombre/Apellido">
                        <button type="button" id="filterBtn" class="btn btn-outline-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <p type="button" id="refreshBtn">
                </div>
            </div>
        </nav>

        <!-- Titulo -->
        <div id="selectionTitle" class="selection-title">
        </div>

        <main class="app-main">
            <div class="query-panel container-fluid px-0">
                <!-- Botones PDF Y EXCEL -->
                <div id="exportButtons" class="export-buttons" style="display:none;">
                    <button id="excelBtn" class="btn btn-success btn-sm"><i class="fas fa-file-excel"></i> Excel</button>
                    <button id="pdfBtn" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf"></i> PDF</button>
                </div>

                <!-- Sección de resultados -->
                <div id="resultsContainer" class="results-container"></div>
                <div id="paginationContainer" class="pagination-container"></div>
            </div>
        </main>

        <footer class="app-footer">
            <p>TINKUY v.1.0 &copy; 2025 - Desarrollado por el Área de Sistemas de la UNDAV.</p>
        </footer>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"></script>

    <!-- SheetJS para Excel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <!-- jsPDF y autoTable para PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

    <script>
    const baseURL = "http://172.16.1.58/Tincuy/consultas.php";
    let currentPage = 1;
    const perPage = 10;
    let currentQueryType = '';
    let currentSelectionText = 'Seleccione un grupo de docentes del menú desplegable';
    let currentSearchTerm = '';

    async function cargarResultados() {
        const resultsContainer = document.getElementById('resultsContainer');
        const paginationContainer = document.getElementById('paginationContainer');
        const selectionTitle = document.getElementById('selectionTitle');
        const exportButtons = document.getElementById('exportButtons');
        exportButtons.style.display = 'none';

        if (!currentQueryType) {
            resultsContainer.innerHTML = '<div class="error">Seleccione un tipo de docentes del menú</div>';
            paginationContainer.innerHTML = '';
            selectionTitle.textContent = currentSelectionText;
            return;
        }

        resultsContainer.innerHTML = '<div class="loading">Cargando datos...</div>';
        paginationContainer.innerHTML = '';
        selectionTitle.textContent = `${currentSelectionText}`;

        try {
            const response = await fetch(`${baseURL}?action=getData&type=${currentQueryType}&page=${currentPage}&search=${encodeURIComponent(currentSearchTerm)}`);
            if (!response.ok) throw new Error('Error en la respuesta del servidor');

            const data = await response.json();
            if (!data.success) throw new Error(data.error || 'Error desconocido');

            let html = `<h3>Resultados (Página ${data.pagination.current_page} de ${data.pagination.total_pages})</h3>`;

            if (currentSearchTerm) {
                html += `<p class="search-info">Filtrado por: <strong>${currentSearchTerm}</strong></p>`;
            }

            html += '<div class="table-responsive"><table class="table table-striped table-bordered" style="width:100%; margin:0">';

            if (data.data.length > 0) {
                html += '<thead><tr>';
                Object.keys(data.data[0]).forEach(key => {
                    html += `<th style="white-space: nowrap">${key}</th>`;
                });
                html += '</tr></thead><tbody>';
                data.data.forEach(row => {
                    html += '<tr>';
                    Object.values(row).forEach(value => {
                        html += `<td style="white-space: nowrap">${value ?? ''}</td>`;
                    });
                    html += '</tr>';
                });

                html += '</tbody></table></div>';
                resultsContainer.innerHTML = html;
                exportButtons.style.display = 'flex';
                exportButtons.style.gap = '10px';
            } else {
                resultsContainer.innerHTML = '<div class="alert alert-info">No se encontraron resultados.</div>';
            }

            // Paginación
            const { current_page, total_pages } = data.pagination;
            let pagHtml = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
            if (current_page > 1) {
                pagHtml += `<li class="page-item"><a class="page-link" href="#" onclick="irPagina(${current_page - 1}); return false;">&laquo;</a></li>`;
            } else {
                pagHtml += `<li class="page-item disabled"><span class="page-link">&laquo;</span></li>`;
            }

            const maxPagesToShow = 5;
            let startPage = Math.max(1, current_page - Math.floor(maxPagesToShow / 2));
            let endPage = startPage + maxPagesToShow - 1;

            if (endPage > total_pages) {
                endPage = total_pages;
                startPage = Math.max(1, endPage - maxPagesToShow + 1);
            }

            for (let i = startPage; i <= endPage; i++) {
                pagHtml += `<li class="page-item ${i === current_page ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="irPagina(${i}); return false;">${i}</a>
                </li>`;
            }

            if (current_page < total_pages) {
                pagHtml += `<li class="page-item"><a class="page-link" href="#" onclick="irPagina(${current_page + 1}); return false;">&raquo;</a></li>`;
            } else {
                pagHtml += `<li class="page-item disabled"><span class="page-link">&raquo;</span></li>`;
            }

            pagHtml += '</ul></nav>';
            paginationContainer.innerHTML = pagHtml;

        } catch (error) {
            console.error('Error:', error);
            resultsContainer.innerHTML = `<div class="error"><strong>Error:</strong> ${error.message}</div>`;
        }
    }

    function irPagina(pagina) {
        currentPage = pagina;
        cargarResultados();
        document.querySelector('.query-panel').scrollIntoView({ behavior: 'smooth' });
    }

    async function obtenerTodosLosDatos() {
        try {
            const response = await fetch(`${baseURL}?action=getData&type=${currentQueryType}&search=${encodeURIComponent(currentSearchTerm)}&export=true`);
            const data = await response.json();
            return data.resultados || data.data || [];
        } catch (error) {
            console.error("Error al obtener todos los datos:", error);
            return [];
        }
    }

    async function exportarAExcel() {
        const datos = await obtenerTodosLosDatos();
        if (datos.length === 0) {
            alert("No hay datos para exportar.");
            return;
        }

        const wsData = [Object.keys(datos[0]), ...datos.map(row => Object.values(row))];
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(wsData);
        XLSX.utils.book_append_sheet(wb, ws, "Resultados");
        XLSX.writeFile(wb, "resultados.xlsx");
    }

    async function exportarAPDF() {
        const datos = await obtenerTodosLosDatos();
        if (datos.length === 0) {
            alert("No hay datos para exportar.");
            return;
        }

        const jsPDF = window.jspdf?.jsPDF || window.jspdf;
        const doc = new jsPDF({ orientation: "landscape", unit: "pt", format: "a4" });

        const fecha = new Date().toLocaleString('es-AR');
        doc.setFontSize(14);
        doc.text("Listado de Docentes - Completo", 40, 40);
        doc.setFontSize(10);
        doc.text(`Exportado el ${fecha}`, 40, 60);

        const headers = [Object.keys(datos[0])];
        const rows = datos.map(row => Object.values(row));

        doc.autoTable({
            head: headers,
            body: rows,
            startY: 80,
            margin: { top: 40, left: 40, right: 40 },
            styles: { fontSize: 9, cellPadding: 4 },
            headStyles: { fillColor: [41, 128, 185], textColor: 255, halign: 'center', fontStyle: 'bold' },
            alternateRowStyles: { fillColor: [240, 240, 240] },
            theme: 'striped'
        });

        doc.save("resultados.pdf");
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('refreshBtn').addEventListener('click', () => {
            currentPage = 1;
            cargarResultados();
        });

        document.getElementById('filterBtn').addEventListener('click', function () {
            currentSearchTerm = document.getElementById('filterInput').value.trim();
            currentPage = 1;
            cargarResultados();
        });

        document.getElementById('filterInput').addEventListener('keyup', function (e) {
            if (e.key === 'Enter') {
                currentSearchTerm = this.value.trim();
                currentPage = 1;
                cargarResultados();
            }
        });

        document.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('click', function (e) {
                e.preventDefault();
                currentQueryType = this.dataset.value;
                currentSelectionText = this.textContent;
                currentPage = 1;
                currentSearchTerm = '';
                document.getElementById('filterInput').value = '';
                cargarResultados();
            });
        });

        // Botones de exportación
        document.getElementById('excelBtn').addEventListener('click', exportarAExcel);
        document.getElementById('pdfBtn').addEventListener('click', exportarAPDF);
    });
    </script>
</body>
</html>
