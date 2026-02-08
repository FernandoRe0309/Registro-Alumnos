<?php
// ==========================================
// 1. CONEXIÓN Y BASE DE DATOS (NO TOCAR)
// ==========================================
$db_file = 'ubam_master_v2.db';

try {
    $pdo = new PDO("sqlite:" . $db_file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Tablas (Misma estructura que ya tenías)
    $pdo->exec("CREATE TABLE IF NOT EXISTS carreras (id_carrera INTEGER PRIMARY KEY AUTOINCREMENT, nombre TEXT NOT NULL, siglas TEXT NOT NULL, estatus TEXT DEFAULT 'activo')");
    $pdo->exec("CREATE TABLE IF NOT EXISTS cat_grados (id_grado INTEGER PRIMARY KEY AUTOINCREMENT, numero INTEGER NOT NULL)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS cat_turnos (id_turno INTEGER PRIMARY KEY AUTOINCREMENT, nombre TEXT NOT NULL)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS grupos (id_grupo INTEGER PRIMARY KEY AUTOINCREMENT, id_carrera INTEGER, id_grado INTEGER, id_turno INTEGER, codigo_grupo TEXT, FOREIGN KEY (id_carrera) REFERENCES carreras(id_carrera))");
    $pdo->exec("CREATE TABLE IF NOT EXISTS alumnos (id_alumno INTEGER PRIMARY KEY AUTOINCREMENT, nombre TEXT, ap_paterno TEXT, ap_materno TEXT, id_grupo INTEGER, estatus TEXT DEFAULT 'pendiente')");

} catch (PDOException $e) { die("Error DB: " . $e->getMessage()); }

// ==========================================
// 2. LÓGICA DE VISTAS (EL TRUCO)
// ==========================================
$vista = isset($_GET['vista']) ? $_GET['vista'] : 'alumnos'; // Por defecto mostramos Alumnos
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>UBAM Gestión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        .navbar-ubam { background-color: #003366; border-bottom: 4px solid #D4AF37; }
        .nav-link { color: rgba(255,255,255,0.8) !important; font-weight: 500; }
        .nav-link:hover, .nav-link.active { color: #D4AF37 !important; font-weight: bold; }
        .card-pizarron { border: 2px solid #444; box-shadow: 4px 4px 0px #ccc; border-radius: 10px; }
        .btn-ubam { background-color: #003366; color: white; border: none; }
        .btn-ubam:hover { background-color: #002244; color: #D4AF37; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-ubam">
    <div class="container">
        <a class="navbar-brand text-white fw-bold" href="index.php">UBAM GESTIÓN</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link <?php echo ($vista=='alumnos')?'active':''; ?>" href="index.php?vista=alumnos">Alumnos</a></li>
                <li class="nav-item"><a class="nav-link <?php echo ($vista=='grupos')?'active':''; ?>" href="index.php?vista=grupos">Grupos</a></li>
                <li class="nav-item"><a class="nav-link <?php echo ($vista=='config')?'active':''; ?>" href="index.php?vista=config">Configuración</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">

    <?php if ($vista == 'alumnos') { ?>
    <div class="row">
        <div class="col-md-4">
            <div class="card card-pizarron mb-3">
                <div class="card-header bg-white fw-bold border-bottom border-dark">Registrar Alumno</div>
                <div class="card-body">
                    <form action="procesar_alumno.php" method="POST">
                        <input type="hidden" name="id_alumno">
                        <div class="mb-2">
                            <label class="small fw-bold">Nombre(s)</label>
                            <input type="text" name="nombre" class="form-control border-dark" required>
                        </div>
                        <div class="mb-2">
                            <label class="small fw-bold">Apellido Paterno</label>
                            <input type="text" name="ap_paterno" class="form-control border-dark" required>
                        </div>
                        <div class="mb-2">
                            <label class="small fw-bold">Apellido Materno</label>
                            <input type="text" name="ap_materno" class="form-control border-dark" required>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold">Grupo</label>
                            <select name="id_grupo" class="form-select border-dark" required>
                                <option value="">-- Seleccionar --</option>
                                <?php
                                $q = $pdo->query("SELECT * FROM grupos g JOIN carreras c ON g.id_carrera=c.id_carrera");
                                while($g = $q->fetch()) echo "<option value='{$g['id_grupo']}'>{$g['codigo_grupo']} - {$g['nombre']}</option>";
                                ?>
                            </select>
                        </div>
                        <button class="btn btn-ubam w-100 fw-bold">Guardar Alumno</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-pizarron">
                <div class="card-header bg-white fw-bold border-bottom border-dark">Alumnos Registrados</div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0 align-middle">
                        <thead class="table-light">
                            <tr><th>ID</th><th>Alumno</th><th>Grupo</th><th>Acciones</th></tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("SELECT a.*, g.codigo_grupo FROM alumnos a LEFT JOIN grupos g ON a.id_grupo = g.id_grupo ORDER BY id_alumno DESC");
                            while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $color = ($r['estatus'] == 'baja') ? 'bg-danger text-white' : '';
                                echo "<tr>
                                    <td>{$r['id_alumno']}</td>
                                    <td>{$r['nombre']} {$r['ap_paterno']}</td>
                                    <td><span class='badge bg-secondary'>{$r['codigo_grupo']}</span></td>
                                    <td class='text-center'>
                                        <a href='cambiar_status.php?id={$r['id_alumno']}&accion=activo' class='btn btn-sm btn-outline-success'><i class='fas fa-check'></i></a>
                                        <a href='cambiar_status.php?id={$r['id_alumno']}&accion=baja' class='btn btn-sm btn-outline-danger'><i class='fas fa-times'></i></a>
                                    </td>
                                </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php } elseif ($vista == 'grupos') { ?>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card card-pizarron">
                <div class="card-header bg-white text-center fw-bold fs-5 border-bottom border-dark">Registro de Grupos</div>
                <div class="card-body p-4">
                    <form action="procesar_grupo.php" method="POST">
                        <div class="mb-3 d-flex align-items-center">
                            <label class="w-25 fw-bold">Carrera:</label>
                            <select name="id_carrera" class="form-select border-dark" required>
                                <?php
                                $c = $pdo->query("SELECT * FROM carreras WHERE estatus='activo'");
                                while($r = $c->fetch()) echo "<option value='{$r['id_carrera']}'>{$r['nombre']}</option>";
                                ?>
                            </select>
                        </div>
                        <div class="mb-3 d-flex align-items-center">
                            <label class="w-25 fw-bold">Turno:</label>
                            <select name="id_turno" class="form-select border-dark">
                                <?php $ts=$pdo->query("SELECT * FROM cat_turnos"); while($t=$ts->fetch()) echo "<option value='{$t['id_turno']}'>{$t['nombre']}</option>"; ?>
                            </select>
                        </div>

                        
                        <div class="mb-3 d-flex align-items-center">
                            <label class="w-25 fw-bold">Grado:</label>
                            <select name="id_grado" class="form-select border-dark w-50">
                                <?php $gs=$pdo->query("SELECT * FROM cat_grados"); while($g=$gs->fetch()) echo "<option value='{$g['id_grado']}'>{$g['numero']}°</option>"; ?>
                            </select>
                        </div>
                        <div class="alert alert-secondary text-center py-2 border-dark">
                            El código (ej. ISC101) se generará automático.
                        </div>
                        <button class="btn btn-outline-dark w-100 fw-bold border-2">• Registrar •</button>
                    </form>
                </div>
            </div>
            
            <div class="mt-4">
                <h5 class="fw-bold">Grupos Activos</h5>
                <div class="row g-2">
                     <?php
                     $g_sql = "SELECT codigo_grupo, nombre FROM grupos g JOIN carreras c ON g.id_carrera = c.id_carrera ORDER BY id_grupo DESC LIMIT 6";
                     foreach($pdo->query($g_sql) as $grp) {
                         echo '<div class="col-4"><div class="card p-2 text-center border-dark shadow-sm"><small class="fw-bold text-primary">'.$grp['codigo_grupo'].'</small><span style="font-size:0.7em">'.$grp['nombre'].'</span></div></div>';
                     }
                     ?>
                </div>
            </div>
        </div>
    </div>

    <?php } elseif ($vista == 'config') { ?>
    <div class="row justify-content-center">
        <div class="col-md-7">
            <h3 class="fw-bold mb-4">Conf. Catálogos</h3>
            
            <div class="card card-pizarron mb-4">
                <div class="card-body">
                    <h5 class="fw-bold border-bottom pb-2">Carrera</h5>
                    <form action="procesar_config.php" method="POST" class="d-flex gap-2 mb-3">
                        <input type="hidden" name="tipo" value="carrera">
                        <input type="text" name="nombre" class="form-control border-dark" placeholder="Nombre" required>
                        <input type="text" name="siglas" class="form-control border-dark w-25" placeholder="Siglas" required>
                        <button class="btn btn-outline-dark text-nowrap fw-bold">- Registrar -</button>
                    </form>

                    <table class="table table-bordered border-dark text-center">
                        <thead class="bg-light"><tr><th>Carrera</th><th>Eliminar</th><th>Activar</th></tr></thead>
                        <tbody>
                            <?php
                            $qc = $pdo->query("SELECT * FROM carreras");
                            while($c = $qc->fetch()) {
                                $chk = ($c['estatus']=='activo') ? 'checked' : '';
                                echo "<tr>
                                    <td class='text-start'>{$c['nombre']}</td>
                                    <td><a href='procesar_config.php?accion=eliminar&id={$c['id_carrera']}&tipo=carrera' class='text-danger'><i class='fas fa-times border border-danger p-1 rounded'></i></a></td>
                                    <td><input type='checkbox' $chk disabled></td>
                                </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="card p-3 border-dark opacity-75 mb-2"><div class="d-flex justify-content-between fw-bold"><span>Turno</span><span>:</span></div></div>
             <div class="card p-3 border-dark opacity-75"><div class="d-flex justify-content-between fw-bold"><span>Grado</span><span>:</span></div></div>
        </div>
    </div>
    <?php } ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>