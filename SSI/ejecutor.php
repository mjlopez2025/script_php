<?php
// Ejecutar primer script
chdir(__DIR__ . '/docentes_mapuche');
require 'index.php';

// Ejecutar segundo script
chdir(__DIR__ . '/docentes_guarani');
require 'importar.php';

// Ejecutar segundo script
chdir(__DIR__ . '/consulta');
require 'index.php';
echo "Ambos scripts se ejecutaron correctamente.\n";
?>


    <link rel="stylesheet" href="styles.css">
