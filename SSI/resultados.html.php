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
<div class="results-container">
    <div class="results-header">
        <h2><?= htmlspecialchars($titulo) ?></h2>
        <?php if ($selected_query === 'combinados'): ?>
            <div class="results-meta">
                <span class="meta-item"><i class="fas fa-database"></i> <?= number_format($total_registros) ?> registros</span>
                <span class="meta-item"><i class="fas fa-clock"></i> <?= $tiempo_ejecucion ?> segundos</span>
                <span class="meta-item"><i class="fas fa-table"></i> Página <?= $pagina_actual ?> de <?= $total_paginas ?></span>
            </div>
        <?php endif; ?>
    </div>

    
    <!-- Paginación mejorada -->
            <?php if ($total_paginas > 1): ?>
            <div class="pagination-container">
                <div class="pagination-info">
                    <a href="index.html" class="btn-volver">Volver</a>
                    Mostrando <?= count($resultados) ?> de <?= number_format($total_registros) ?> registros
                </div>
                <div class="pagination-controls">
                    <?php if ($pagina_actual > 1): ?>
                        <a href="?query_type=combinados&pagina=<?= $pagina_actual - 1 ?>" class="pagination-button prev">
                            <i class="fas fa-chevron-left"></i> Anterior
                        </a>
                    <?php endif; ?>
                    <div class="page-numbers">
                        <?php 
                        $start_page = max(1, $pagina_actual - 2);
                        $end_page = min($total_paginas, $pagina_actual + 2);
                        
                        if ($start_page > 1): ?>
                            <span class="page-dots">...</span>
                        <?php endif;
                        
                        for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <a href="?query_type=combinados&pagina=<?= $i ?>" class="pagination-number <?= $i == $pagina_actual ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor;
                        
                        if ($end_page < $total_paginas): ?>
                            <span class="page-dots">...</span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($pagina_actual < $total_paginas): ?>
                        <a href="?query_type=combinados&pagina=<?= $pagina_actual + 1 ?>" class="pagination-button next">
                            Siguiente <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
    
    <?php if (!empty($error_message)): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_message) ?>
        </div>
    <?php elseif (empty($resultados) && $selected_query === 'combinados'): ?>
        <div class="no-results">
            <div class="no-results-content">
                <i class="fas fa-search"></i>
                <h3>No se encontraron resultados</h3>
                <p>La consulta no devolvió ningún registro</p>
            </div>
        </div>
    <?php elseif (!empty($resultados)): ?>
        <div class="table-responsive">
            <table class="professional-table">
                <thead>
                    <tr>
                        <?php foreach (array_keys($resultados[0]) as $columna): ?>
                            <th class="<?= strpos($columna, 'Documento') !== false ? 'text-center' : '' ?>">
                                <div class="th-content">
                                    <?= htmlspecialchars($columna) ?>
                                    <?php if (!in_array($columna, ['Documento', 'Num. Cargo', 'Año', 'Año'])): ?>
                                        <span class="sort-indicator"><i class="fas fa-sort"></i></span>
                                    <?php endif; ?>
                                </div>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultados as $fila): ?>
                            <?php foreach ($fila as $campo => $valor): ?>
                                <td class="<?= strpos($campo, 'Documento') !== false ? 'text-center' : '' ?>"
                                    data-title="<?= htmlspecialchars($campo) ?>">
                                    <?php if (in_array($campo, ['Responsabilidad Académica', 'Propuesta Formativa', 'Actividad']) && strlen($valor) > 30): ?>
                                        <span class="text-truncate" title="<?= htmlspecialchars($valor) ?>">
                                            <?= htmlspecialchars(substr($valor, 0, 30)) ?>...
                                        </span>
                                    <?php else: ?>
                                        <?= htmlspecialchars($valor) ?>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Paginación mejorada -->
            <?php if ($total_paginas > 1): ?>
            <div class="pagination-container">
                <div class="pagination-info">
                    Mostrando <?= count($resultados) ?> de <?= number_format($total_registros) ?> registros
                </div>
                <div class="pagination-controls">
                    <?php if ($pagina_actual > 1): ?>
                        <a href="?query_type=combinados&pagina=1" class="pagination-button first">
                            <i class="fas fa-step-backward"></i>
                        </a>
                        <a href="?query_type=combinados&pagina=<?= $pagina_actual - 1 ?>" class="pagination-button prev">
                            <i class="fas fa-chevron-left"></i> Anterior
                        </a>
                    <?php endif; ?>
                    
                    <div class="page-numbers">
                        <?php 
                        $start_page = max(1, $pagina_actual - 2);
                        $end_page = min($total_paginas, $pagina_actual + 2);
                        
                        if ($start_page > 1): ?>
                            <span class="page-dots">...</span>
                        <?php endif;
                        
                        for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <a href="?query_type=combinados&pagina=<?= $i ?>" class="pagination-number <?= $i == $pagina_actual ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor;
                        
                        if ($end_page < $total_paginas): ?>
                            <span class="page-dots">...</span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($pagina_actual < $total_paginas): ?>
                        <a href="?query_type=combinados&pagina=<?= $pagina_actual + 1 ?>" class="pagination-button next">
                            Siguiente <i class="fas fa-chevron-right"></i>
                        </a>
                        <a href="?query_type=combinados&pagina=<?= $total_paginas ?>" class="pagination-button last">
                            <i class="fas fa-step-forward"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="no-results">
            <div class="no-results-content">
                <i class="fas fa-users"></i>
                <h3>Seleccione una consulta</h3>
                <p>Elija "Docentes Combinados" para visualizar los datos</p>
            </div>
        </div>
    <?php endif; ?>
</div>
</body>
</html>