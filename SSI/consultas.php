<?php
header('Content-Type: application/json');

class ConsultasDocentes {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // ================================
    // 1. DOCENTES COMBINADOS 
    // ================================
    public function docentesCombinados($page = 1, $perPage = 100) {
    $offset = ($page - 1) * $perPage;

    $sql = "SELECT 
        COALESCE(m.apellidonombre_desc, 'Sin información') AS \"Apellido Nombre\",
        COALESCE(m.nro_documento::TEXT, 'Sin información') AS \"Doc\",
        COALESCE(m.categoria_desc, 'Sin información') AS \"Cat\", 
        COALESCE(m.nro_cargo::TEXT, 'Sin información') AS \"Cargo\",
        COALESCE(m.dedicacion_desc, 'Sin información') AS \"Dedicacion\",
        COALESCE(m.estadodelcargo_desc, 'Sin información') AS \"Estado\", 
        COALESCE(m.dependenciadesign_desc, 'Sin información') AS \"Dpto\",
        COALESCE(g.responsabilidad_academica_guarani, 'Sin información') AS \"Resp Acad\",
        COALESCE(g.propuesta_formativa_guarani, 'Sin información') AS \"Propuesta\", 
        COALESCE(g.comision_guarani, 'Sin información') AS \"Com\",
        COALESCE(g.anio_guarani::TEXT, 'Sin información') AS \"Año\",
        COALESCE(g.periodo_guarani, 'Sin información') AS \"Périodo\",
        COALESCE(g.actividad_guarani, 'Sin información') AS \"Actividad\",
        COALESCE(g.cursados_guarani, 'Sin información') AS \"Est\"
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
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return [
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'total' => $this->contarDocentesCombinados()
    ];
}



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

    // ================================
    // 2. DOCENTES MAPUCHE 
    // ================================
    public function obtenerDocentesMapuche($page = 1, $perPage = 100) {
    $offset = ($page - 1) * $perPage;

    $sql = "SELECT 
        COALESCE(apellidonombre_desc, 'Sin información') AS apellido_nombre,
        COALESCE(nro_documento::TEXT, 'Sin información') AS documento,
        COALESCE(categoria_desc, 'Sin información') AS categoria,
        COALESCE(nro_cargo::TEXT, 'Sin información') AS num_cargo,
        COALESCE(dedicacion_desc, 'Sin información') AS dedicacion,
        COALESCE(estadodelcargo_desc, 'Sin información') AS estado_cargo,
        COALESCE(dependenciadesign_desc, 'Sin información') AS dependencia,
        COALESCE(anio_id::TEXT, 'Sin información') AS anio
    FROM 
        docentes_mapuche
    ORDER BY 
        apellidonombre_desc
    LIMIT :limit OFFSET :offset";

    $stmt = $this->conn->prepare($sql);
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return [
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'total' => $this->contarDocentesMapuche()
    ];
}



    public function contarDocentesMapuche() {
        return $this->conn->query("SELECT COUNT(*) FROM docentes_mapuche")->fetchColumn();
    }

    // ================================
    // 3. DOCENTES GUARANI 
    // ================================
    public function obtenerDocentesGuarani($page = 1, $perPage = 100) {
    $offset = ($page - 1) * $perPage;

    $sql = "SELECT 
        COALESCE(responsabilidad_academica_guarani, 'Sin información') AS responsabilidad_academica,
        COALESCE(propuesta_formativa_guarani, 'Sin información') AS propuesta_formativa,
        COALESCE(comision_guarani, 'Sin información') AS comision,
        COALESCE(anio_guarani::TEXT, 'Sin información') AS anio,
        COALESCE(periodo_guarani, 'Sin información') AS periodo,
        COALESCE(actividad_guarani, 'Sin información') AS actividad,
        COALESCE(codigo_guarani, 'Sin información') AS codigo,
        COALESCE(cursados_guarani, 'Sin información') AS cursados,
        COALESCE(num_documento::TEXT, 'Sin información') AS documento
    FROM 
        docentes_guarani
    ORDER BY 
        propuesta_formativa_guarani
    LIMIT :limit OFFSET :offset";

    $stmt = $this->conn->prepare($sql);
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return [
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'total' => $this->contarDocentesGuarani()
    ];
}



    public function contarDocentesGuarani() {
        return $this->conn->query("SELECT COUNT(*) FROM docentes_guarani")->fetchColumn();
    }
}

// Configuración de conexión
$config = [
    'host' => '172.16.1.58',
    'port' => '5432',
    'dbname' => 'sii',
    'user' => 'postgres',
    'password' => 'postgres'
];

try {
    $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}";
    $conn = new PDO($dsn, $config['user'], $config['password']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $consultas = new ConsultasDocentes($conn);

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
        $response = [];
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 100; // 10 registros por página como solicitaste
        
        switch ($_GET['action']) {
            case 'getData':
                if (!isset($_GET['type'])) {
                    throw new Exception("Tipo de consulta no especificado");
                }
                
                $result = match($_GET['type']) {
                    'guarani' => $consultas->obtenerDocentesGuarani($page, $perPage),
                    'mapuche' => $consultas->obtenerDocentesMapuche($page, $perPage),
                    'combinados' => $consultas->docentesCombinados($page, $perPage),
                    default => throw new Exception("Tipo de consulta no válido")
                };
                
                $response = [
                    'success' => true,
                    'data' => $result['data'],
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $perPage,
                        'total' => $result['total'],
                        'total_pages' => ceil($result['total'] / $perPage)
                    ]
                ];
                break;
                
            default:
                throw new Exception("Acción no válida");
        }
        
        echo json_encode($response);
        exit;
    }
    
    throw new Exception("Solicitud no válida");

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
    exit;
}


    // ==========
    // 3. FILTROS
    // ==========


    
?>