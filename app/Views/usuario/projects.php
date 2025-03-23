<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: /index.php");
    exit();
}

// Obtener el ID del proyecto desde la URL
$proyecto_id = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : '';

// Verificar que el ID del proyecto no esté vacío
if (empty($proyecto_id)) {
    echo "<script>alert('ID del proyecto no válido.'); window.history.back();</script>";
    exit();
}

// Obtener las tareas del proyecto
$tareas = $this->usuarioModel->obtenerTareas($proyecto_id);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Proyecto - TaskHive</title>
    <link rel="stylesheet" href="/css/usuario.css">
</head>
<body>
    <div class="proyecto-container">
        <h1>Detalles del Proyecto</h1>

        <h2>Tareas</h2>
        <?php if (empty($tareas)): ?>
            <p>No hay tareas en este proyecto.</p>
        <?php else: ?>
            <ul class="tareas-lista">
                <?php foreach ($tareas as $tarea): ?>
                    <li>
                        <h3><?php echo htmlspecialchars($tarea['nombre']); ?></h3>
                        <p><?php echo htmlspecialchars($tarea['descripcion']); ?></p>
                        <!-- Botón para eliminar la tarea -->
                        <form action="/index.php?action=eliminarTarea" method="POST" style="display: inline;">
                            <input type="hidden" name="tarea_id" value="<?php echo $tarea['id']; ?>">
                            <input type="hidden" name="proyecto_id" value="<?php echo $proyecto_id; ?>">
                            <button type="submit" class="btn-eliminar">Eliminar</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <!-- Formulario para crear una nueva tarea -->
        <div class="create-task-container">
            <h2>Crear Nueva Tarea</h2>
            <form action="/index.php?action=crearTarea" method="POST">
                <input type="hidden" name="action" value="crearTarea">
                <input type="hidden" name="proyecto_id" value="<?php echo $proyecto_id; ?>"> <!-- ID del proyecto -->

                <div class="form-group">
                    <label for="nombre">Nombre de la Tarea</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Introduce el nombre de la tarea" required>
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" placeholder="Introduce la descripción de la tarea" required></textarea>
                </div>

                <button type="submit" class="btn-crear-tarea">Crear Tarea</button>
            </form>
        </div>

        <!-- Enlace para volver al dashboard -->
        <div class="back-link">
            <a href="/index.php?action=dashboard">Volver al dashboard</a>
        </div>
    </div>
</body>
</html>