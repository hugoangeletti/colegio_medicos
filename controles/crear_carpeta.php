<?php
require_once ('../dataAccess/config.php');
permisoLogueado();

$path = "../archivos/lotes/a_procesar";

if (!file_exists($path)) {
    mkdir($path, 0777, true);
}

echo 'carpeta creada ->'.$path.'<br>';

