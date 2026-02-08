<?php
// ==========================================
// CONFIGURACIÓN Y BASE DE DATOS (SQLite)
// ==========================================
$db_file = 'ubam_master_v2.db';

try {
    $pdo = new PDO("sqlite:" . $db_file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. TABLA CARRERAS
    $pdo->exec("CREATE TABLE IF NOT EXISTS carreras (
        id_carrera INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre TEXT NOT NULL,
        siglas TEXT NOT NULL,
        estatus TEXT DEFAULT 'activo'
    )");

    // 2. TABLAS CONFIGURACIÓN
    $pdo->exec("CREATE TABLE IF NOT EXISTS cat_grados (
        id_grado INTEGER PRIMARY KEY AUTOINCREMENT,
        numero INTEGER NOT NULL
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS cat_turnos (
        id_turno INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre TEXT NOT NULL
    )");

    // 3. TABLA GRUPOS
    $pdo->exec("CREATE TABLE IF NOT EXISTS grupos (
        id_grupo INTEGER PRIMARY KEY AUTOINCREMENT,
        id_carrera INTEGER,
        id_grado INTEGER,
        id_turno INTEGER,
        codigo_grupo TEXT,
        FOREIGN KEY (id_carrera) REFERENCES carreras(id_carrera),
        FOREIGN KEY (id_grado) REFERENCES cat_grados(id_grado),
        FOREIGN KEY (id_turno) REFERENCES cat_turnos(id_turno)
    )");

    // 4. TABLA ALUMNOS
    $pdo->exec("CREATE TABLE IF NOT EXISTS alumnos (
        id_alumno INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre TEXT,
        ap_paterno TEXT,
        ap_materno TEXT,
        id_grupo INTEGER,
        estatus TEXT DEFAULT 'pendiente',
        FOREIGN KEY (id_grupo) REFERENCES grupos(id_grupo)
    )");

    // INYECCIÓN DE DATOS (Solo si vacía)
    $chk = $pdo->query("SELECT COUNT(*) FROM carreras");
    if ($chk->fetchColumn() == 0) {
        $pdo->beginTransaction();

       $carreras = [
    ['Administración de Empresas', 'LAE'],
    ['Administración de Empresas Turísticas', 'LAET'],
    ['Relaciones Internacionales', 'LRI'],
    ['Contaduría Pública y Finanzas', 'LCPF'],
    ['Derecho', 'DER'],
    ['Mercadotecnia y Publicidad', 'MYP'],
    ['Gastronomía', 'GAS'],
    ['Periodismo y Ciencias de la Comunicación', 'PCC'],
    ['Diseño de Modas', 'LDM'],
    ['Pedagogía', 'PED'],
    ['Cultura Física y Educación del Deporte', 'CFED'],
    ['Idiomas (Inglés y Francés)', 'IDI'],
    ['Psicología', 'PSI'],
    ['Diseño de Interiores', 'LDI'],
    ['Diseño Gráfico', 'LDG'],
    ['Ingeniería en Logística y Transporte', 'ILT'],
    ['Ingeniero Arquitecto', 'ARQ'],
    ['Informática Administrativa y Fiscal', 'IAF'],
    ['Ingeniería en Sistemas Computacionales', 'ISC'],
    ['Ingeniería Mecánica Automotriz', 'IMA']
];
        $stmt_c = $pdo->prepare("INSERT INTO carreras (nombre, siglas) VALUES (?, ?)");
        foreach ($carreras as $c) $stmt_c->execute($c);

        $stmt_g = $pdo->prepare("INSERT INTO cat_grados (numero) VALUES (?)");
        for ($i = 1; $i <= 11; $i++) { $stmt_g->execute([$i]); }

        $turnos = ['Matutino', 'Vespertino', 'Mixto'];
        $stmt_t = $pdo->prepare("INSERT INTO cat_turnos (nombre) VALUES (?)");
        foreach ($turnos as $t) $stmt_t->execute([$t]);

        $pdo->commit();
    }
} catch (PDOException $e) { die("Error Crítico DB: " . $e->getMessage()); }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>UBAM - Sistema Integral</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        /* ESTILO INSTITUCIONAL UBAM */
        :root { --ubam-blue: #003366; --ubam-gold: #D4AF37; --ubam-bg: #f4f6f9; }
        body { background-color: var(--ubam-bg); font-family: 'Segoe UI', sans-serif; }
        
        .navbar-ubam { background-color: var(--ubam-blue); border-bottom: 4px solid var(--ubam-gold); padding: 15px 0; }
        .navbar-brand { font-weight: 800; color: white !important; letter-spacing: 1px; }
        .nav-link { color: rgba(255,255,255,0.9) !important; font-weight: 500; margin-left: 10px; transition: 0.3s; }
        .nav-link:hover { color: var(--ubam-gold) !important; transform: translateY(-2px); }
        
        .card-grupo { border: none; border-top: 5px solid var(--ubam-blue); transition: 0.3s; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .card-grupo:hover { transform: translateY(-5px); border-top-color: var(--ubam-gold); }
        
        .modal-header { background-color: var(--ubam-blue); color: white; border-bottom: 4px solid var(--ubam-gold); }
        .btn-ubam { background-color: var(--ubam-blue); color: white; font-weight: bold; width: 100%; border: none; padding: 10px; }
        .btn-ubam:hover { background-color: #002244; color: var(--ubam-gold); }

        /* Colores forzados tablas */
        .table-success > td, .table-success > th { background-color: #d1e7dd !important; color: #0f5132 !important; }
        .table-danger > td, .table-danger > th { background-color: #f8d7da !important; color: #842029 !important; }
        .badge-codigo { background-color: var(--ubam-blue); font-size: 0.9em; padding: 5px 10px; }
        .btn-circle { width: 32px; height: 32px; padding: 0; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-ubam sticky-top">
    <div class="container">
        <a class="navbar-brand" href="#"><i class="fas fa-university me-2"></i>UBAM GESTIÓN</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
            <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="#" onclick="abrirModalRegistro()"><i class="fas fa-user-plus"></i> Registrar Alumno</a></li>
                <li class="nav-item"><a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#modalGrupo"><i class="fas fa-chalkboard-teacher"></i> Crear Grupo</a></li>
                <li class="nav-item"><a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#modalListaAlumnos"><i class="fas fa-list-alt"></i> Alumnos</a></li>
                <li class="nav-item"><a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#modalConfig"><i class="fas fa-tools"></i> Configuración</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <h3 style="color: var(--ubam-blue); font-weight: 800;">Panel de Grupos Activos</h3>
        <button onclick="location.reload()" class="btn btn-outline-primary btn-sm"><i class="fas fa-sync-alt me-1"></i> Actualizar</button>
    </div>

    <div class="row g-4">
        <?php
        $sql = "SELECT g.codigo_grupo, c.nombre, gr.numero as grado, t.nombre as turno, 
                (SELECT COUNT(*) FROM alumnos WHERE id_grupo = g.id_grupo) as total 
                FROM grupos g 
                JOIN carreras c ON g.id_carrera = c.id_carrera 
                JOIN cat_grados gr ON g.id_grado = gr.id_grado
                JOIN cat_turnos t ON g.id_turno = t.id_turno
                ORDER BY g.id_grupo DESC";
        $stmt = $pdo->query($sql);
        $hay = false;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { $hay = true;
            echo '<div class="col-md-4 col-lg-3"><div class="card card-grupo h-100"><div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3"><span class="badge badge-codigo">'.$row['codigo_grupo'].'</span><span class="badge bg-light text-dark border">'.$row['turno'].'</span></div>
                <h6 class="card-title fw-bold text-dark">'.$row['nombre'].'</h6>
                <small class="text-muted"><i class="fas fa-layer-group me-1"></i> '.$row['grado'].'° Cuatrimestre</small>
                <div class="mt-3 text-end fw-bold text-primary"><i class="fas fa-users me-1"></i> '.$row['total'].' Alumnos</div>
            </div></div></div>';
        }
        if (!$hay) echo '<div class="col-12 text-center py-5 text-muted"><h4>No hay grupos activos</h4><p>Ve a "Crear Grupo" para comenzar.</p></div>';
        ?>
    </div>
</div>

<div class="modal fade" id="modalConfig" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="fas fa-cogs me-2"></i>Configuración de Catálogo</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-4">
                
                <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Gestión de Carreras</h6>
                <form action="procesar_config.php" method="POST" class="row g-2 mb-4 align-items-end">
                    <input type="hidden" name="tipo" value="carrera">
                    <div class="col-md-6"><label class="form-label small">Nombre</label><input type="text" name="nombre" class="form-control form-control-sm" required></div>
                    <div class="col-md-3"><label class="form-label small">Siglas</label><input type="text" name="siglas" class="form-control form-control-sm" required maxlength="5"></div>
                    <div class="col-md-3"><button type="submit" class="btn btn-ubam btn-sm"><i class="fas fa-plus"></i> Registrar</button></div>
                </form>

                <div class="table-responsive mb-4" style="max-height: 250px; overflow-y: auto;">
                    <table class="table table-sm table-hover table-bordered">
                        <thead class="table-light sticky-top"><tr><th>Carrera</th><th class="text-center">Siglas</th><th class="text-center">Estado</th><th class="text-center">Acciones</th></tr></thead>
                        <tbody>
                            <?php
                            $q_c = $pdo->query("SELECT * FROM carreras ORDER BY nombre ASC");
                            while($c = $q_c->fetch(PDO::FETCH_ASSOC)) {
                                $estado = ($c['estatus'] == 'activo') ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
                                echo "<tr><td>{$c['nombre']}</td><td class='text-center'>{$c['siglas']}</td><td class='text-center'>$estado</td><td class='text-center'>
                                    <button onclick=\"confirmarAccion('procesar_config.php?accion=activar&id={$c['id_carrera']}&tipo=carrera', '¿Activar carrera?')\" class='btn btn-success btn-sm p-0 px-1'><i class='fas fa-check'></i></button>
                                    <button onclick=\"confirmarAccion('procesar_config.php?accion=eliminar&id={$c['id_carrera']}&tipo=carrera', '¿Desactivar carrera?')\" class='btn btn-danger btn-sm p-0 px-1'><i class='fas fa-times'></i></button>
                                </td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Registrar Turno</h6>
                        <form action="procesar_config.php" method="POST" class="d-flex gap-2">
                            <input type="hidden" name="tipo" value="turno">
                            <input type="text" name="nombre_turno" class="form-control form-control-sm" placeholder="Ej. Nocturno" required>
                            <button type="submit" class="btn btn-primary btn-sm">Agregar</button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Registrar Grado</h6>
                        <form action="procesar_config.php" method="POST" class="d-flex gap-2">
                            <input type="hidden" name="tipo" value="grado">
                            <input type="number" name="numero_grado" class="form-control form-control-sm" placeholder="Ej. 12" required>
                            <button type="submit" class="btn btn-primary btn-sm">Agregar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAlumno" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="tituloModalAlumno">Registrar Alumno</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <form action="procesar_alumno.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="id_alumno" id="input_id_alumno">
                    <div class="mb-3"><label class="form-label text-primary fw-bold">Nombre(s)</label><input type="text" name="nombre" id="input_nombre" class="form-control" required></div>
                    <div class="row">
                        <div class="col-6 mb-3"><label class="form-label text-primary fw-bold">Apellido Paterno</label><input type="text" name="ap_paterno" id="input_ap_paterno" class="form-control" required></div>
                        <div class="col-6 mb-3"><label class="form-label text-primary fw-bold">Apellido Materno</label><input type="text" name="ap_materno" id="input_ap_materno" class="form-control" required></div>
                    </div>
                    <div class="mb-3"><label class="form-label text-primary fw-bold">Asignar a Grupo</label>
                        <select name="id_grupo" id="input_grupo" class="form-select" required>
                            <option value="">-- Selecciona --</option>
                            <?php
                            try {
                                $q = $pdo->query("SELECT g.id_grupo, g.codigo_grupo, c.nombre, t.nombre as turno FROM grupos g JOIN carreras c ON g.id_carrera = c.id_carrera JOIN cat_turnos t ON g.id_turno = t.id_turno ORDER BY g.id_grupo DESC");
                                while($g = $q->fetch(PDO::FETCH_ASSOC)) echo "<option value='{$g['id_grupo']}'>🟦 [{$g['codigo_grupo']}] - {$g['nombre']} ({$g['turno']})</option>";
                            } catch (Exception $e) {}
                            ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light"><button type="submit" class="btn btn-ubam" id="btnGuardarAlumno">Guardar Alumno</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalGrupo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Abrir Nuevo Grupo</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <form action="procesar_grupo.php" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3"><label class="form-label fw-bold">Carrera</label>
                        <select name="id_carrera" class="form-select" required>
                            <?php
                            $c = $pdo->query("SELECT * FROM carreras WHERE estatus = 'activo' ORDER BY nombre ASC");
                            while($r = $c->fetch(PDO::FETCH_ASSOC)) echo "<option value='{$r['id_carrera']}'>{$r['nombre']}</option>";
                            ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col mb-3"><label class="form-label fw-bold">Grado</label>
                            <select name="id_grado" class="form-select">
                                <?php 
                                $gs = $pdo->query("SELECT * FROM cat_grados ORDER BY numero ASC");
                                while($g = $gs->fetch(PDO::FETCH_ASSOC)) echo "<option value='{$g['id_grado']}'>{$g['numero']}°</option>"; 
                                ?>
                            </select>
                        </div>
                        <div class="col mb-3"><label class="form-label fw-bold">Turno</label>
                            <select name="id_turno" class="form-select">
                                <?php 
                                $ts = $pdo->query("SELECT * FROM cat_turnos ORDER BY id_turno ASC");
                                while($t = $ts->fetch(PDO::FETCH_ASSOC)) echo "<option value='{$t['id_turno']}'>{$t['nombre']}</option>"; 
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="alert alert-info py-2"><small>Código automático (Ej. ISC101).</small></div>
                </div>
                <div class="modal-footer bg-light"><button type="submit" class="btn btn-ubam">Generar Grupo</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalListaAlumnos" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="fas fa-list-alt me-2"></i>Control de Alumnos</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-bordered">
                        <thead class="table-dark text-center"><tr><th>ID</th><th>Nombre Completo</th><th>Grupo</th><th>Estatus</th><th>Acciones</th></tr></thead>
                        <tbody>
                            <?php
                            $sql_l = "SELECT a.id_alumno, a.nombre, a.ap_paterno, a.ap_materno, a.estatus, g.codigo_grupo, c.nombre as carrera, g.id_grupo FROM alumnos a JOIN grupos g ON a.id_grupo = g.id_grupo JOIN carreras c ON g.id_carrera = c.id_carrera ORDER BY a.id_alumno DESC";
                            $stmt_l = $pdo->query($sql_l);
                            while ($alu = $stmt_l->fetch(PDO::FETCH_ASSOC)) {
                                $clase = ''; $texto = 'PENDIENTE';
                                if ($alu['estatus'] == 'activo') { $clase = 'table-success'; $texto = 'ACTIVO'; }
                                elseif ($alu['estatus'] == 'baja') { $clase = 'table-danger'; $texto = 'BAJA'; }

                                echo "<tr class='$clase'><td class='text-center'>".str_pad($alu['id_alumno'],4,'0',STR_PAD_LEFT)."</td>
                                <td class='fw-bold'>{$alu['nombre']} {$alu['ap_paterno']} {$alu['ap_materno']}</td>
                                <td><span class='badge bg-secondary'>{$alu['codigo_grupo']}</span> <small class='d-block text-muted'>{$alu['carrera']}</small></td>
                                <td class='text-center fw-bold small'>$texto</td><td class='text-center'>
                                <a href='cambiar_status.php?id={$alu['id_alumno']}&accion=activo' class='btn btn-success btn-circle me-1'><i class='fas fa-check'></i></a>
                                <a href='cambiar_status.php?id={$alu['id_alumno']}&accion=baja' class='btn btn-danger btn-circle me-1'><i class='fas fa-times'></i></a>
                                <button onclick='cargarEdicion(".htmlspecialchars(json_encode($alu), ENT_QUOTES, 'UTF-8').")' class='btn btn-primary btn-circle'><i class='fas fa-sync-alt'></i></button>
                                </td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function abrirModalRegistro() {
        document.getElementById('input_id_alumno').value = '';
        document.getElementById('input_nombre').value = '';
        document.getElementById('input_ap_paterno').value = '';
        document.getElementById('input_ap_materno').value = '';
        document.getElementById('input_grupo').value = '';
        document.getElementById('tituloModalAlumno').innerText = 'Registrar Nuevo Alumno';
        document.getElementById('btnGuardarAlumno').innerText = 'Guardar Alumno';
        document.getElementById('btnGuardarAlumno').className = 'btn btn-ubam';
        new bootstrap.Modal(document.getElementById('modalAlumno')).show();
    }
    function cargarEdicion(alumno) {
        bootstrap.Modal.getInstance(document.getElementById('modalListaAlumnos')).hide();
        document.getElementById('input_id_alumno').value = alumno.id_alumno;
        document.getElementById('input_nombre').value = alumno.nombre;
        document.getElementById('input_ap_paterno').value = alumno.ap_paterno;
        document.getElementById('input_ap_materno').value = alumno.ap_materno;
        document.getElementById('input_grupo').value = alumno.id_grupo;
        document.getElementById('tituloModalAlumno').innerText = 'Actualizar Datos: ' + alumno.nombre;
        document.getElementById('btnGuardarAlumno').innerText = 'Actualizar Datos';
        document.getElementById('btnGuardarAlumno').className = 'btn btn-warning fw-bold text-dark';
        new bootstrap.Modal(document.getElementById('modalAlumno')).show();
    }
    // SweetAlert para confirmaciones de link
    function confirmarAccion(url, mensaje) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: mensaje,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#003366',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, ejecutar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        })
    }
</script>
</body>
</html>