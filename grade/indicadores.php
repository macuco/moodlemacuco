<?php
    if($pinta_header==true){
        $heade_indicadores = '
        <style type="text/css">
ul {
    list-style-type:none; /*con esto quitamos las vi�etas*/
    margin:0; /*le quitamos el margen a la lista*/
    padding:0; /*y el relleno*/
}
li.hecho, li.porhacer {
    margin:0; /*le quito el margen al elemento de la lista*/
    float:left; /*y los floto a la izquierda para eliminar el salto de linea*/

    display:block; /*con esto el vinculo tendra un area rectangular, asi no sera solo el texto*/
    color:#FFFFFF; /*mas*/


    text-decoration:none; /*elimino el subrayado del v�nculo*/
    text-transform:uppercase; /*decorare los vinculos en mayusculas*/

    height: 100%;


}

li.porhacer {
	width:0px; /*defino el ancho del rectangulo del vinculo*/
	background-color:#FF3300; /*algo de color*/
}

li.hecho {
	width:0px; /*defino el ancho del rectangulo del vinculo*/
	background-color:#00FF00; /*algo de color*/

}

.retro {
	position:absolute;
	text-align:center;
	display:block;
	border:none;
}

</style>
<script  src="'.$CFG->wwwroot.'/grade/jquery-1.4.2.js" type="text/javascript" language="javascript"></script>
<script  type="text/javascript" language="javascript">
<!--
var unidades = {"unidad1":{"contestadas":70,"total":100},"unidad2":{"contestadas":99,"total":100}};
(function($) {
        $.sizeBarra = 120;
        $(function(){
            $(".retro").css({"width":$.sizeBarra});
            $.each(unidades,function(obj){
                var contestadas = unidades[obj].contestadas*$.sizeBarra/unidades[obj].total;
                var porhacer = (unidades[obj].total-unidades[obj].contestadas)*$.sizeBarra/unidades[obj].total;
                (porhacer<2)?$(".porhacer",$("#"+obj)).html(""):$(".porhacer",$("#"+obj)).html("&nbsp;");
                $(".porhacer",$("#"+obj)).css({"width":porhacer});
                $(".hecho",$("#"+obj)).css({"width":contestadas});
                var xhecho = Math.round(contestadas*100/$.sizeBarra);
                $(".retro",$("#"+obj).parent()).html(xhecho+"%");
            });
        });
    })(jQuery);
-->
</script>
';

    }else {
?>


<table align="center" border="0" cellspacing="5" cellpadding="0">
        <thead bgcolor="#FF9900">
            <tr>
                <th>Nombre del educando</th>
                <?php
                foreach($unidades as $unidad){
                    echo '<th >Unidad '.$unidad->unidad.'</th>';
                }
                ?>
                <th>Porcentaje 
				total del curso</th>
                <th>Total de ejercicios contestados</th>
            </tr>
        </thead>
        <tbody>
            <?php

                
            $datos_unidades = "";
                foreach($groupmembers as $id=>$ouser){
            ?>
            <tr>
                <td><?=$ouser->firstname." ".$ouser->lastname." ".$ouser->icq?></td>
                 <?php
                 $total_ejercicios_contestados = 0;
                 $total_ejercicios = 0;
                foreach($unidades as $unidad){

                    $ejercicios = $DB->get_records_select('inea_ejercicios','courseid='.$course->id.' AND unidad='.$unidad->unidad,array(),'','id');
                    $ejercicios = array_keys($ejercicios);
                    $ejercicios = implode(",",$ejercicios);

                    $respuestas = $DB->get_records_select('inea_respuestas','userid='.$ouser->id.' AND ejercicios_id in('.$ejercicios.') group by ejercicios_id');
                    $contestadas = empty($respuestas)?0:count($respuestas);

                    $idunidad = $unidad->unidad."_".$ouser->id;
                    $total = 100*$unidad->nactividades/$unidad->porcentaje;
                    $avance = round(($contestadas*100/$total)*100)/100;
                    $datos_unidades = $datos_unidades.'"unidad'.$idunidad.'":{"contestadas":'.$contestadas.',"total":'.$total.'},';

                    $total_ejercicios += $total;
                    $total_ejercicios_contestados +=  $contestadas;

                ?>
                <td width="122"><ul id="unidad<?=$idunidad?>">
                        <li class="hecho">&nbsp;</li>
                        <li class="porhacer">&nbsp;</li>
                    </ul>
                    <div class="retro"><?=$avance?>%</div>
                </td>
                <?php }
                $porcentaje_total = round($total_ejercicios_contestados*100/$total_ejercicios);
                ?>

                <td align="center"><?=$porcentaje_total?>%</td>
                <td align="center"><?=$total_ejercicios_contestados?> de <?=$total_ejercicios?><!-- de <?=$total_ejercicios?>--></td>
            </tr>
            <?php
            }
            ?>
        </tbody>
    </table>

<script  type="text/javascript" language="javascript">
<!--
var unidades = {<?=substr($datos_unidades,0,-1)?>};
-->
</script>

<?php } ?>
