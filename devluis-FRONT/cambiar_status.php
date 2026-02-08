<?php
$db_file = 'ubam_master_v2.db';
if (isset($_GET['id']) && isset($_GET['accion'])) {
    try {
        $pdo = new PDO("sqlite:" . $db_file);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        if ($_GET['accion'] == 'activo' || $_GET['accion'] == 'baja') {
            $pdo->prepare("UPDATE alumnos SET estatus = ? WHERE id_alumno = ?")->execute([$_GET['accion'], $_GET['id']]);
        }
        header("Location: index.php");
    } catch (Exception $e) { die("Error: " . $e->getMessage()); }
}


?>