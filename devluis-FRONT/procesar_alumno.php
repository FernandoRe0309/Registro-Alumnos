<?php
$db_file = 'ubam_master_v2.db';

function alerta($titulo, $txt, $icon) {
    echo "<!DOCTYPE html><html><head><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head><body>
    <script>Swal.fire({title:'$titulo',text:'$txt',icon:'$icon',confirmButtonColor:'#003366'}).then(()=>{window.location='index.php'});</script></body></html>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $pdo = new PDO("sqlite:" . $db_file);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $id = $_POST['id_alumno'];
        $n = $_POST['nombre']; $p = $_POST['ap_paterno']; $m = $_POST['ap_materno']; $g = $_POST['id_grupo'];

        if (!empty($id)) {
            $pdo->prepare("UPDATE alumnos SET nombre=?, ap_paterno=?, ap_materno=?, id_grupo=? WHERE id_alumno=?")->execute([$n,$p,$m,$g,$id]);
            alerta("Actualización Exitosa", "Los datos del alumno han sido guardados.", "success");
        } else {
            $pdo->prepare("INSERT INTO alumnos (nombre, ap_paterno, ap_materno, id_grupo) VALUES (?,?,?,?)")->execute([$n,$p,$m,$g]);
            alerta("Registro Exitoso", "El alumno ha sido inscrito correctamente.", "success");
        }

    } catch (Exception $e) { alerta("Error", $e->getMessage(), "error"); }
}
?>