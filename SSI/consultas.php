<?php
class ConsultasDocentes {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // ================================
    // 1. DOCENTES COMBINADOS
    // ================================
    public function contarDocentesCombinados() {
        $sql = "SELECT COUNT(*) as total
                FROM docentes_mapuche AS m
                LEFT JOIN docentes_guarani AS g 
                ON m.nro_documento::VARCHAR = g.num_documento  
                WHERE g.num_documento IS NULL OR 
                (m.categoria_desc <> g.propuesta_formativa_guarani OR
                 m.dedicacion_desc <> g.actividad_guarani)";
        
        return $this->conn->query($sql)->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function docentesCombinados($limit = 10, $offset = 0) {
        $sql = "SELECT 
            m.apellidonombre_desc AS \"Apellido y Nombre\",
            m.nro_documento AS \"Documento\",
            m.categoria_desc AS \"Categoria\", 
            m.nro_cargo AS \"Num. Cargo\",
            m.dedicacion_desc AS \"Dedicación\",
            m.estadodelcargo_desc AS \"Estado del Cargo\", 
            m.dependenciadesign_desc AS \"Dependencia Designada\",
            g.responsabilidad_academica_guarani AS \"Responsabilidad Académica\",
            g.propuesta_formativa_guarani AS \"Propuesta Formativa\", 
            g.comision_guarani AS \"Comisión\",
            g.anio_guarani AS \"Año\",
            g.periodo_guarani AS \"Periodo\",
            g.actividad_guarani AS \"Actividad\", 
            g.codigo_guarani AS \"Codigo\",
            g.cursados_guarani AS \"Cursados\"
        FROM 
            docentes_mapuche AS m
        LEFT JOIN 
            docentes_guarani AS g 
            ON m.nro_documento::VARCHAR = g.num_documento  
        WHERE 
            g.num_documento IS NULL OR 
            (m.categoria_desc <> g.propuesta_formativa_guarani OR
             m.dedicacion_desc <> g.actividad_guarani)
        ORDER BY 
            m.apellidonombre_desc
        LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ================================
    // 2. DOCENTES MAPUCHE
    // ================================
    public function obtenerDocentesMapuche() {
        $sql = "SELECT 
            apellidonombre_desc AS \"Apellido y Nombre\",
            nro_documento AS \"Documento\",
            categoria_desc AS \"Categoría\",
            nro_cargo AS \"Nro Cargo\",
            dedicacion_desc AS \"Dedicación\",
            estadodelcargo_desc AS \"Estado del Cargo\",
            dependenciadesign_desc AS \"Dependencia\",
            anio_id AS \"Año\"
        FROM 
            docentes_mapuche
        ORDER BY 
            apellidonombre_desc";
        
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarDocentesMapuche() {
        $sql = "SELECT COUNT(*) as total
                FROM docentes_mapuche";
        
        return $this->conn->query($sql)->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // ================================
    // 3. DOCENTES GUARANI
    // ================================
    public function obtenerDocentesGuarani() {
        $sql = "SELECT 
            responsabilidad_academica_guarani AS \"Responsabilidad Académica\",
            propuesta_formativa_guarani AS \"Propuesta Formativa\",
            comision_guarani AS \"Comisión\",
            anio_guarani AS \"Año\",
            periodo_guarani AS \"Periodo\",
            actividad_guarani AS \"Actividad\",
            codigo_guarani AS \"Código\",
            cursados_guarani AS \"Cursados\",
            num_documento AS \"Documento\"
        FROM 
            docentes_guarani
        ORDER BY 
            propuesta_formativa_guarani";
        
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarDocentesGuarani() {
        $sql = "SELECT COUNT(*) as total
                FROM docentes_guarani";
        
        return $this->conn->query($sql)->fetch(PDO::FETCH_ASSOC)['total'];
    }
}
?>
