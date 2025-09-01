<?php 

//Split primer nivel, para separa por ;
$arrayString1 = explode(';',substr($item->message,0, -1));

//Texto para responder ticket
$textoRespuesta = ""; 
foreach ($arrayString1 as $value) {
    $textoRespuesta .= $value.'<br>';
}
//echo $textoRespuesta;

// Para concatenar el primer split en un string
$dataValija = null;
for ($i=0; $i < count($arrayString1) ; $i++) {                      
    $dataValija .= $arrayString1[$i].",";
}

//Split segundo nivel, separar por ,
$arrayItemValija = explode(',',substr($dataValija, 0, -1));

//Datos a buscar
$searchR = "Remitente:";
$searchD = "Destinatario:";
$searchRef = "Referencia:";
$searchCan = "Cantidad:";

//Para buscar contenido de Remitente, Destinatario, Referencia y Cantidad
$findValija1 = strpos($item->message, $searchR);
$findValija2 = strpos($item->message, $searchD);
$findValija3 = strpos($item->message, $searchRef);
$findValija4 = strpos($item->message, $searchCan);

//var_dump($item->message);
//var_dump($findValija1);

$arrayAllValija = array();
if($findValija1 == false && $findValija2 == true && $findValija3 == true && $findValija4 == true){
    for ($j=0; $j < count($arrayString1) ; $j++) { 
        $newValija = new Valija();
        $arrayItemValija = explode(',',$arrayString1[$j]);

//Variables para almacenar los valores encontrados
        $item1 = "";
        $item2 = "";
        $item3 = "";
        $item4 = "";
        $item5 = "";

//Funcion para buscar en la posicion del array
        $findA = strpos($arrayItemValija[0],$searchR);
        $findD = strpos($arrayItemValija[1],$searchD);
        $findC = strpos($arrayItemValija[2],$searchRef);
        $findCan = strpos($arrayItemValija[3],$searchCan);

        if ($findA === false) {
        } else {                                
            $item1 = ltrim($arrayItemValija[0],$searchR); 

        }

        if ($findD === false) {
        } else {
            $item2 = ltrim($arrayItemValija[1],$searchD);     
        }

        if ($findC === false) {
        } else {
            $item3 = ltrim($arrayItemValija[2], $searchRef);;
        }

        if ($findCan === false) {
        } else {
            $item4 = ltrim($arrayItemValija[3], $searchCan);

        }

        $newValija->asesor = $item1;
        $newValija->destinatario = $item2;
        $newValija->cliente = $item3;
        $newValija->cantidad = $item4;
        $arrayAllValija[]= $newValija;
    }
}

?>

<!-- Verifico si el objeto tiene el valor de asesor para cargar o no la tabla -->                                   
    <?php 
    $asr = "";
    foreach ($arrayAllValija as $key => $value) {
        $asr = $value -> asesor;
    }
    ?>

    <!-- Para mostrar la tabla detalla Valijas-->
    <?php if ( $asr != "" ): ?>
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>Remitente</th>
                        <th>Destinatario</th>
                        <th>Referencia</th>
                        <th>Cantidad</th>
                    </tr>
                </thead>                               
                <tbody>
                    <?php foreach ($arrayAllValija as $key => $value): ?>
                        <tr>
                            <td><?php echo $value -> asesor; ?></td>
                            <td><?php echo $value -> destinatario; ?></td>
                            <td><?php echo $value -> cliente; ?></td>
                            <td class="text-center"><?php echo $value ->cantidad; ?></td>
                        </tr> 
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
        
        <!-- DIV oculto con el texto de la tabla, solo para respuestas -->
        <div id="msg_<?php echo $item->id;?>" class="form-group" style="display: none;"> 
            <table class="table table-hover table-bordered" style="border-collapse: collapse; width: 100%; margin-bottom: 1rem; color: #212529; border: 1px solid #dee2e6;">
                <thead class="thead-dark" style="color: #fff; background-color: #212529; border-color: #32383e;">
                    <tr>
                        <th>Remitente</th>
                        <th>Destinatario</th>
                        <th>Referencia</th>
                        <th>Cantidad</th>
                    </tr>
                </thead>                               
                <tbody>
                    <?php foreach ($arrayAllValija as $key => $value): ?>
                        <tr>
                            <td><?php echo $value -> asesor; ?></td>
                            <td><?php echo $value -> destinatario; ?></td>
                            <td><?php echo $value -> cliente; ?></td>
                            <td><?php echo $value -> cantidad; ?></td>
                        </tr> 
                    <?php endforeach ?>
                </tbody>
            </table>
            <?php //echo ($item->email == 1 ? $item->message : nl2br($textoRespuesta));?>
        </div>
        <!-- Para mostrar mensaje sin tabla detalle Valijas -->
    <?php else: ?>
        <div id="msg_<?php echo $item->id;?>" class="form-group">
            <?php echo ($item->email == 1 ? $item->message : nl2br($item->message));?>
        </div>
    <?php endif ?>