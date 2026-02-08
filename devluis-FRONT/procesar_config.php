<?php
$db_file = 'ubam_master_v2.db';

// FUNCIÓN PARA MOSTRAR ALERTA BONITA
function alertaBonita($titulo, $mensaje, $icono) {
    echo "<!DOCTYPE html><html><head><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <style>body{font-family:sans-serif;background:#f4f6f9;}</style></head><body>
    <script>
        Swal.fire({
            title: '$titulo',
            text: '$mensaje',
            icon: '$icono',
            confirmButtonColor: '#003366',
            confirmButtonText: 'Entendido',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) { window.location.href = 'index.php'; }
        });
    </script></body></html>";
    exit();
}

// LÓGICA GET (ACTIVAR / ELIMINAR)
if (isset($_GET['accion'])) {
    try {
        $pdo = new PDO("sqlite:" . $db_file);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $id = $_GET['id'];
        $tipo = $_GET['tipo'];
        $accion = $_GET['accion'];

        if ($tipo == 'carrera') {
            if ($accion == 'eliminar') {
                $pdo->prepare("UPDATE carreras SET estatus = 'inactivo' WHERE id_carrera = ?")->execute([$id]);
                alertaBonita("Desactivada", "La carrera ha sido marcada como inactiva.", "success");
            } elseif ($accion == 'activar') {
                $pdo->prepare("UPDATE carreras SET estatus = 'activo' WHERE id_carrera = ?")->execute([$id]);
                alertaBonita("Activada", "La carrera está activa nuevamente.", "success");
            }
        }
    } catch (Exception $e) { alertaBonita("Error", $e->getMessage(), "error"); }
}

// LÓGICA POST (REGISTRAR)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $pdo = new PDO("sqlite:" . $db_file);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $tipo = $_POST['tipo'];

        if ($tipo == 'carrera') {
            $stmt = $pdo->prepare("INSERT INTO carreras (nombre, siglas, estatus) VALUES (?, ?, 'activo')");
            $stmt->execute([$_POST['nombre'], $_POST['siglas']]);
            alertaBonita("¡Excelente!", "Nueva carrera registrada correctamente.", "success");
        
        } elseif ($tipo == 'turno') {
            $stmt = $pdo->prepare("INSERT INTO cat_turnos (nombre) VALUES (?)");
            $stmt->execute([$_POST['nombre_turno']]);
            alertaBonita("¡Listo!", "Turno agregado al catálogo.", "success");
        
        } elseif ($tipo == 'grado') {
            $stmt = $pdo->prepare("INSERT INTO cat_grados (numero) VALUES (?)");
            $stmt->execute([$_POST['numero_grado']]);
            alertaBonita("¡Listo!", "Nuevo grado escolar agregado.", "success");
        }

    } catch (Exception $e) { alertaBonita("Ocurrió un error", $e->getMessage(), "error"); }
}

?>