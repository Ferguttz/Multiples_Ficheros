<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<?php

$codigosErrorSubida= [ 
    UPLOAD_ERR_OK         => 'Subida correcta',  // Valor 0
    UPLOAD_ERR_INI_SIZE   => 'El tamaño del archivo excede el admitido por el servidor',  // directiva upload_max_filesize en php.ini
    UPLOAD_ERR_FORM_SIZE  => 'El tamaño del archivo excede el admitido por el cliente',  // directiva MAX_FILE_SIZE en el formulario HTML
    UPLOAD_ERR_PARTIAL    => 'El archivo no se pudo subir completamente',
    UPLOAD_ERR_NO_FILE    => 'No se seleccionó ningún archivo para ser subido',
    UPLOAD_ERR_NO_TMP_DIR => 'No existe un directorio temporal donde subir el archivo',
    UPLOAD_ERR_CANT_WRITE => 'No se pudo guardar el archivo en disco',  // permisos
    UPLOAD_ERR_EXTENSION  => 'Una extensión PHP evito la subida del archivo',  // extensión PHP

]; 

define('MAX_FILES', 300000); //Maximo de tamaño de archivos entre todos 300Kb
define('MAX_UPLOAD', 200000); //Maximo tamaño un solo archivo
define('DIRECTORIO', getcwd()."/imgusers"); //Ruta Directorio de imgusers

$mensaje = '';
$nombre = $_FILES['archivos']['name'];
$error = $_FILES['archivos']['error'];


if (!isset($nombre)) {
    $mensaje = "ERROR: No se seleccionado ningún archivo";
} else {
    if (ComprobarCodError($error)) {
        $mensaje = MostrarError($_FILES,$codigosErrorSubida);
    }
    if ($mensaje == '') {
        $mensaje = Comprobar($_FILES);
    }
    if ($mensaje == '') {
        $mensaje = MoverArchivos($_FILES);

    }
}

?>

<!-- Parte de HTML -->
<body>
    <?= $mensaje ?>
</body>
<!-- Parte de HTML -->

<?php

//-------------------------Parte de Funciones----------------------

function MoverArchivos(&$archivos) : string {
    $mensaje = '';
    $temporalFichero = $archivos['archivos']['tmp_name'];
    $nombreFichero = $archivos['archivos']['name'];
    $vuelta = 0;

    if ( is_dir(DIRECTORIO) && is_writable (DIRECTORIO)) { 
        foreach ($temporalFichero as $value) {
            if (move_uploaded_file($value,  DIRECTORIO .'/'. $nombreFichero[$vuelta]) == true) {
                $mensaje .= 'Archivo ['.$nombreFichero[$vuelta].'] guardado en: ' . DIRECTORIO .'/'. $nombreFichero[$vuelta] . ' <br />';
            } else {
                $mensaje .= 'ERROR: Archivo ['.$nombreFichero[$vuelta].'] no guardado correctamente <br />';
            }
            $vuelta++;
        }
    } else {
        $mensaje .= 'ERROR: No es un directorio correcto o no se tiene permiso de escrituraaaa <br />';
    }


    return $mensaje;
}

function ComprobarCodError($error) : bool {
    arsort($error);
    return ($error[array_key_first($error)]> 0);
}

function Comprobar(&$archivos): string{
    $msg = '';

    $msg .= ComprobarTamanio($archivos);
    $msg .= ComprobarFormato($archivos);
    $msg .= ComprobarRepeticion($archivos);

    return $msg;
}

function MostrarError($archivos,$codigos) : string {
    $msg = '';
    $vuelta = 0;
    $lista = [];
    foreach ($archivos['archivos']['error'] as $value) {
        if ($value > 0) {
            $lista [$archivos['archivos']['name'][$vuelta]] = $value;
        }
        $vuelta++;
    }

    $msg .= "Los siguientes archivos tienen el cod. ERROR: <br>";
    foreach ($lista as $key => $value) {
        $msg .= "[".$key."] COD. ".$value." : ".$codigos[$value]."<br>";
    }

    return $msg;
}

function ComprobarRepeticion(&$archivos) : string {
    $msg = '';
    $lista = [];

    foreach ($archivos['archivos']['name'] as $valor) {
        if (file_exists(DIRECTORIO.'/'.$valor)) {
            $lista [] = $valor;
        }
    }

    if(count($lista) >= 1) $msg="ERROR: Ya existen los archivos: ".ListarString($lista);

    return $msg;
    
}

function ComprobarTamanio(&$archivos): string {
    $msg = '';
    $limite = [];
    $sum = 0; //variable acumuladora
    $vuelta = 0; //variable para saber arhivo/os mayor al limite



    foreach ($archivos['archivos']['size'] as  $valor) {
        if ($valor > MAX_UPLOAD) {
            $limite [] = $archivos['archivos']['name'][$vuelta];
        }
        $vuelta++;
        $sum += $valor;
    }

    if (count($limite) >= 1) $msg .= "ERROR: Los archivos que superan el límites tamaño son: ".ListarString($limite);

    if ($sum > MAX_FILES) $msg .= "ERROR: Se ha superado el tamaño límite de archivos en conjunto <br>";

    return $msg;
}

//Funcion para listar un Array
function ListarString(&$lista) : string {
    $msg = "<br>".$lista[0]."<br>";

    for ($i=1; $i < count($lista); $i++) { 
        $msg .= $lista[$i]."<br>";
    }

    return $msg."<br>";
}

function ComprobarFormato(&$archivos) {
    $msg = '';
    $lista = [];
    $vuelta = 0;

    foreach ($archivos['archivos']['type'] as $value) {
        if (!($value == "image/jpeg" || $value == "image/png")) {
            $lista [] = $archivos['archivos']['name'][$vuelta];
        }
        $vuelta++;
    }

    if (count($lista) >= 1) $msg = "ERROR: formato de archivo no aceptado en: ".ListarString($lista);

    return $msg;
}

?>
</html>
