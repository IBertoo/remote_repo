<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/middleware.php'; // contiene la función db()
$pdo = db();

$id = (int)($_GET['id'] ?? 0); // ID del producto al que se agregan imágenes
$errors = [];
$success = false;

// CARPETA PARA SUBIR LA IMAGEN.
$uploadDir ='../uploads/';
$filePath = __DIR__ . '/'. $uploadDir ;//direccion Absoluta de carpeta uplod






// --- Obtener      FUNCION PARA IMAGENES EXISTENTES ---
function GetImages($pdo, $id) {
    try {
        // Prepare the SQL query to select image IDs and names
        $stmt = $pdo->prepare("SELECT id_imagen, nombre_img FROM imagenes_producto WHERE id_producto = :id");
        
        // Execute the query with the provided product ID
        $stmt->execute([':id' => $id]);
        
        // Fetch all results; return an empty array if no results
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        
        return $images;
    } catch (PDOException $e) {
        // Handle database errors (you can customize this part)
        error_log("Error fetching images: " . $e->getMessage());
        return [];
    }
}
$images=GetImages($pdo, $id);


// Obtener el NOMBRE DEL PRODUCTO
if ($id > 0) {
    $stmt = $pdo->prepare("SELECT nombre FROM producto WHERE id_producto = :id");
    $stmt->execute([':id' => $id]);
    $nombreProducto = $stmt->fetchColumn();
    if ($nombreProducto === false) {
    die("❌ El producto con ID $id no existe.");
}
} else {
    die("❌ ID inválido.");
}


// 🧹 FUNCIÓN PARA ELIMINAR IMÁGENES

if (isset($_POST['delete_image_id'])) {
    // if (!csrf_validate($_POST['csrf'] ?? '')) {
    //         die("❌ Token CSRF inválido o expirado.");
    //     }

    $deleteId = (int)$_POST['delete_image_id'];

    try {
        // Buscar imagen
        $stmt = $pdo->prepare("SELECT nombre_img FROM imagenes_producto WHERE id_imagen = :id AND id_producto = :pid");
        $stmt->execute([':id' => $deleteId, ':pid' => $id]);
        $image = $stmt->fetch();

        if ($image) {

            $filePath = $filePath. $image['nombre_img'];
            if (is_file($filePath)) {
                unlink($filePath); // eliminar archivo físico
            }

            // Eliminar de la base de datos
            $delStmt = $pdo->prepare("DELETE FROM imagenes_producto WHERE id_imagen = :id");
            $delStmt->execute([':id' => $deleteId]);

            $success = true;
        } else {
            $errors[] = "Imagen no encontrada o no pertenece a este producto.";
        }
    } catch (PDOException $e) {
        $errors[] = "Error al eliminar: " . $e->getMessage();
    }
}


// --- REDIMENSION DE IMAGEN ---
function resizeImageGD($sourcePath, $destPath, $newWidth, $newHeight, $quality = 85) {
    $info = @getimagesize($sourcePath);
    if ($info === false) return false;
    $mime = $info['mime'];

    // Crear imagen desde el tipo MIME
    switch ($mime) {
        case 'image/jpeg':
        case 'image/jpg':
            $srcImg = imagecreatefromjpeg($sourcePath);
            if (!$srcImg) return false;
            break;
        case 'image/png':
            $srcImg = imagecreatefrompng($sourcePath);
            if (!$srcImg) return false;
            break;
        case 'image/gif':
            $srcImg = imagecreatefromgif($sourcePath);
            if (!$srcImg) return false;
            break;
        case 'image/webp':
            $srcImg = imagecreatefromwebp($sourcePath);
            if (!$srcImg) return false;
            break;
        default:
            return false; // Tipo no soportado
    }

    // Crear lienzo para la nueva imagen
    $dstImg = imagecreatetruecolor($newWidth, $newHeight);

    // Mantener transparencia para PNG y GIF
    if ($mime === 'image/png' || $mime === 'image/gif') {
        imagecolortransparent($dstImg, imagecolorallocatealpha($dstImg, 0, 0, 0, 127));
        imagealphablending($dstImg, false);
        imagesavealpha($dstImg, true);
    }

    // Redimensionar
    imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $newWidth, $newHeight, imagesx($srcImg), imagesy($srcImg));

    // Guardar imagen
    switch ($mime) {
        case 'image/jpeg':
        case 'image/jpg':
            imagejpeg($dstImg, $destPath, $quality);
            break;
        case 'image/png':
            imagepng($dstImg, $destPath);
            break;
        case 'image/gif':
            imagegif($dstImg, $destPath);
            break;
        case 'image/webp':
            imagewebp($dstImg, $destPath, $quality);
            break;
    }

    // Liberar memoria
    imagedestroy($srcImg);
    imagedestroy($dstImg);

    return true;
}

// --- Manejo del formulario ---
if (isset($_POST['uploadImages'])) {

        // if (!csrf_validate($_POST['csrf'] ?? '')) {
        //         die("❌ Token CSRF inválido o expirado.");
        //     }


    $maxImages = 5;
    $allowedTypes = ['image/jpeg', 'image/jpg','image/png', 'image/gif', 'image/webp'];
    $maxSize = 10 * 1024 * 1024; // 10MB

    $uploadedFiles = $_FILES['images'] ?? null;
//CONTANDO LAS IMAGENES ADMITIDAS, MAS LAS QUE SE PRETENDEN SUBIR.
    if (!$uploadedFiles || empty($uploadedFiles['name'][0])) {
        $errors[] = "Debes seleccionar al menos una imagen.";
    } else {
        // Contar imágenes ya existentes
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM imagenes_producto WHERE id_producto = :id");
        $countStmt->execute([':id' => $id]);
        $existingCount = (int)$countStmt->fetchColumn();
        $total = $existingCount + count($uploadedFiles['name']);
        if ($total > $maxImages) {
            $errors[] = "No puedes subir más de $maxImages imágenes en total (ya tienes $existingCount).";
        }
    }


    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            foreach ($uploadedFiles['tmp_name'] as $i => $tmpName) {
                if ($uploadedFiles['error'][$i] !== UPLOAD_ERR_OK) continue;

                
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $type = finfo_file($finfo, $tmpName);
                finfo_close($finfo);

                $size = $uploadedFiles['size'][$i];

                if (!in_array($type, $allowedTypes)) {
                    $errors[] = "El archivo {$uploadedFiles['name'][$i]} no es un tipo válido (JPEG, PNG, GIF, WEBP).";
                    continue;
                }
                if ($size > $maxSize) {
                    $errors[] = "El archivo {$uploadedFiles['name'][$i]} excede los 10MB.";
                    continue;
                }

                // Generar nombre único
                $ext = pathinfo($uploadedFiles['name'][$i], PATHINFO_EXTENSION);//Obtiene la extension
                $filename = uniqid('img_') . '.' . strtolower($ext);// genera un numero agregado a img_


                $destination = $filePath . $filename;

                $destinationSmall = rtrim($filePath, '/') . '/small/' . $filename;
                $destinationMedium = $filePath . "medium/" . $filename;
                $destinationBig = $filePath . "big/" . $filename;


                //MOVER ARCHIVO SUBIDO
                if (move_uploaded_file($tmpName, $destination)) {
                    $direccionImagen=$destination;
                    // Redimensionar con GD. Se ve que el Orden importa.
                    resizeImageGD($direccionImagen,$destinationSmall, 400, 300);
                    // Redimensionar con GD. Se ve que el Orden importa.
                    resizeImageGD($direccionImagen,$destinationMedium, 800, 600);
                    // Redimensionar con GD. Se ve que el Orden importa.
                    resizeImageGD($direccionImagen,$destinationBig, 1200, 900);

                    $stmt = $pdo->prepare("
                        INSERT INTO imagenes_producto (id_producto, nombre_img)
                        VALUES (:pid, :fn)
                    ");
                    $stmt->execute([':pid' => (int)$id, ':fn' => $filename]);
                //      BORRANDO EL ARCHIVO SUBIDO
                if (is_file($destination)) {
                unlink($destination); // eliminar archivo físico
            }
                    
                } else {
                    echo "Error al mover el archivo subido.";
                }
            }
            if (empty($errors)) {
                $pdo->commit();
                $success = true;
            } else {
                //Hay que borrar los archivos subidos.
                $pdo->rollBack();
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Error en la base de datos: " . $e->getMessage();
        }
    }
}


// <!-- __________________________________________________________________________________________________ -->
// <!-- __________________________________________________________________________________________________ -->
// <!-- __________________________________________________________________________________________________ -->
// <!-- __________________________________________________________________________________________________ -->
include __DIR__ . '/../partials/header.php'; ?>

<h1 class="mb-3">Agregar imágenes al producto:  </h1>
<h3 class="mb-3"><?= htmlspecialchars($nombreProducto) ?></h3>
<!-- __________________________________________________________________________________________________ -->
<!-- __________________________________________________________________________________________________ -->
<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <input type="hidden" name="uploadImages" value="">


    <div class="mb-3">
        <label class="form-label">Seleccionar imágenes (máx. 5 en total)</label>
        <input type="file" name="images[]" multiple accept="image/*" class="form-control">
    </div>

    <div class="d-grid">
        <button class="btn btn-primary">Subir imágenes</button>
    </div>
</form>
<hr>
<!-- __________________________________________________________________________________________________ -->
<!-- __________________________________________________________________________________________________ -->

<h3>📷 Imágenes actuales</h3>
<div style="display:flex; flex-wrap:wrap; gap:10px;">
        <?php 

        $images=GetImages($pdo, $id);
        
foreach ($images as $img): ?>
    <?php 
        //OBTINE LA RUTA RELATIVA DE LA IMAGEN.

        $imgCamino = '/uploads/small/' . $img['nombre_img'];
 
        //var_dump($imgCamino);
       
    ?>
    <div style="border:1px solid #ccc; padding:5px; text-align:center;">
        <img 
            src="<?= htmlspecialchars($imgCamino, ENT_QUOTES, 'UTF-8') ?>" 
            alt="Imagen del producto" 
            width="120">
        <br>

        <form method="post" style="margin-top:5px;">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="delete_image_id" value="<?= htmlspecialchars($img['id_imagen'], ENT_QUOTES, 'UTF-8') ?>">
            <button 
                type="submit"
                class="btn btn-sm btn-danger"
                onclick="return confirm('¿Eliminar esta imagen?')"
            >
                🗑️ Eliminar
            </button>
        </form>
    </div>
<?php endforeach; ?>

</div>

<hr>
<ul>
  <li><a href="/admin/products.php">Administrar productos</a></li>
  <li><a href="/admin/orders.php">Administrar Pedidos</a></li>
</ul>

<?php include __DIR__ . '/../partials/footer.php'; ?>
