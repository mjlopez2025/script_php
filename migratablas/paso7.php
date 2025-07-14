<?php
//Limpieza de los dnis . y espacios en blanco

     $limpiarDnis ="UPDATE doc_de_guarani 
SET num_doc1 = REGEXP_REPLACE(num_doc1, '[^0-9]', '')
WHERE num_doc1 ~ '[^0-9]'";                 

                $stmt = $conn->prepare($limpiarDnis);
                $stmt->execute();
                $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($stmt) {
                    echo "dnis limpios\n";
                } else {
                    echo "No se encontraron datos.\n";
                }


                // Actualizar Dedicación en doc_de_guarani con datos de doc_de_mapuche
    $updateGuarani = "UPDATE doc_de_guarani g
                        SET Dedicacion = COALESCE(
                            (SELECT m.Dedicacion 
                             FROM doc_de_mapuche m 
                             WHERE m.nro_de_documento = g.num_doc1 LIMIT 1),
                            '--'
                        )
                        WHERE g.Dedicacion IS NULL OR g.Dedicacion = '--'";
    $stmt = $conn->prepare($updateGuarani);

                $stmt->execute();
                $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($stmt) {
                    echo "Datos actualizados exitosamente.\n";
                } else {
                    echo "No se encontraron datos.\n";
                }