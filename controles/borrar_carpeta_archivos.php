<?php
/*
$directorio_antiguo = '../archivos/seguro_historico';
$directorio_nuevo = '../archivos/seguro';
if (rename($directorio_antiguo, $directorio_nuevo)) {
  echo "Carpeta renombrada con éxito.";
  //mkdir("../archivos/seguro/2024", 0700);
} else {
  echo "Error al renombrar la carpeta.";
}
*/

//mkdir('../archivos/lotes/a_procesar', 0777, true);

$borrado = 0;
//rmdir('../archivos/a_procesar');
unlink('../archivos/lotes/a_procesar/845-05-06-2026.zip');
unlink('../archivos/lotes/a_procesar/PGHR0603.rch');
unlink('../archivos/lotes/a_procesar/PGHR0603.log');

/*
foreach(glob("../archivos/lotes/a_procesar/") as $archivos_carpeta){             
    print_r($archivos_carpeta);
    echo '<br>';

    if (is_dir($archivos_carpeta)){
        foreach(glob($archivos_carpeta."/*") as $archivos_carpeta2){             
            print_r($archivos_carpeta2);
            echo '<br>';

            if (is_dir($archivos_carpeta2)){
                foreach(glob($archivos_carpeta2."/*") as $archivos_carpeta3){
                    print_r($archivos_carpeta3);
                    echo '<br>';
                    if (is_dir($archivos_carpeta3)){
                        foreach(glob($archivos_carpeta3."/*") as $archivos_carpeta4){
                            print_r($archivos_carpeta4);
                            echo '<br>';
                            if (is_dir($archivos_carpeta4)){
                                rmdir($archivos_carpeta4);
                            } else {
                                unlink($archivos_carpeta4);
                            }
                            $borrado++;
                        }            
                        rmdir($archivos_carpeta3);
                    } else {
                        unlink($archivos_carpeta3);
                    }
                    $borrado++;
                }            
                rmdir($archivos_carpeta2);
            } else {
                unlink($archivos_carpeta2);
            }
            $borrado++;
        }            
        rmdir($archivos_carpeta);
    } else {
        unlink($archivos_carpeta);
    }
    $borrado++;
}            
*/
echo 'borrados->'.$borrado.'<br>';
