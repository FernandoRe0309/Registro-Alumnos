<?php
$db_file = 'ubam_master_v2.db';

// FUNCIÓN ALERTA
function alerta($titulo, $txt, $icon) {
    echo "<!DOCTYPE html><html><head><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head><body>
    <script>Swal.fire({title:'$titulo',text:'$txt',icon:'$icon',confirmButtonColor:'#003366'}).then(()=>{window.location='index.php'});</script></body></html>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $pdo = new PDO("sqlite:" . $db_file);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Datos Auxiliares
        $s1 = $pdo->prepare("SELECT siglas FROM carreras WHERE id_carrera = ?");
        $s1->execute([$_POST['id_carrera']]);
        $siglas = $s1->fetchColumn();

        $s2 = $pdo->prepare("SELECT numero FROM cat_grados WHERE id_grado = ?");
        $s2->execute([$_POST['id_grado']]);
        $num_grado = $s2->fetchColumn();

        // Consecutivo
        $patron = $siglas . $num_grado . "%";
        $s3 = $pdo->prepare("SELECT COUNT(*) FROM grupos WHERE codigo_grupo LIKE ?");
        $s3->execute([$patron]);
        $total = $s3->fetchColumn();

        $consecutivo = str_pad($total + 1, 2, "0", STR_PAD_LEFT);
        $codigo = $siglas . $num_grado . $consecutivo;

        // Insertar
        $sql = "INSERT INTO grupos (id_carrera, id_grado, id_turno, codigo_grupo) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_POST['id_carrera'], $_POST['id_grado'], $_POST['id_turno'], $codigo]);

        alerta("¡Grupo Creado!", "Código Generado: $codigo", "success");

    } catch (Exception $e) { alerta("Error", $e->getMessage(), "error"); }
}

?>