<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Verificaci&oacute;n de cursos conclu&iacute;dos MOL v.12</title>
<style type="text/css">
<!--
.Estilo1 {color: #FF0000}
.Estilo5 {font-size: small}
.Estilo6 {color: #0000FF}
-->
</style>
</head>

<body>
<?php 
    require_once("../config.php");

	if(!$_POST['rfe']){
?>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<form id="form1" name="form1" method="post" action="">
		  <table width="200" align="center">
            <tr>
              <td colspan="2" nowrap="nowrap"><span class="Estilo6">Para verificar los registros de cursos concluidos ingrese el RFE del educando </span></td>
            </tr>
            <tr>
              <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
              <td width="128"><div align="right">RFE:</div></td>
              <td width="129"><input name="rfe" type="text" id="rfe" size="15" maxlength="13" onKeyUp="this.value = this.value.toUpperCase();" /></td>
            </tr>
            <tr>
              <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
              <td colspan="2"><div align="center">
                  <input type="submit" name="Submit" value="Verificar" />
              </div></td>
            </tr>
            <tr>
              <td colspan="2">&nbsp;</td>
            </tr>
          </table>
</form>
		<p>&nbsp;</p>
<?php 
	}else if($object_concluido = get_record('inea_concluidos','idnumber',$_POST['rfe'])){
?>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<form id="form1" name="form1" method="post" action="">
		  <table width="200" align="center">
            <tr>
              <td colspan="2" nowrap="nowrap"><span class="Estilo6">Para verificar los registros de cursos concluidos ingrese el RFE del educando</span> </td>
            </tr>
            <tr>
              <td colspan="2" nowrap="nowrap">&nbsp;</td>
            </tr>
            <tr>
              <td width="128"><div align="right">RFE:</div></td>
              <td width="129"><input name="rfe" type="text" id="rfe" value="<?php echo $_POST['rfe']?>" size="15" maxlength="13" onKeyUp="this.value = this.value.toUpperCase();" /></td>
            </tr>
            <tr>
              <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
              <td colspan="2"><div align="center">
                  <input type="submit" name="Submit" value="Verificar" />
              </div></td>
            </tr>
            <tr>
              <td colspan="2">&nbsp;</td>
            </tr>
          </table>
		</form>
				
		<table align="center" border="0" cellspacing="5" cellpadding="0">
        <thead bgcolor="#FF9900">
            <tr>
                <th>RFE</th>
                <th>Nombre</th>
                <th>Curso</th>
                <?php
                for($i = 1; $i <= 8; $i++){
                    echo '<th >AE U'.$i.'</th>';
                    echo '<th >Acts U'.$i.'</th>';
                }
                ?>
                <th><span class="Estilo5">Fecha de conclusi&oacute;n</span></th>
                <th>Folio</th>
            </tr>
        </thead>
        <tbody>
            <?php                
			//$object_concluido = get_record('inea_concluidos','idnumber',$_POST['rfe']);
            ?>
            <tr>
                <td><span class="Estilo5"><?=$object_concluido->idnumber?></span></td>
                <td><span class="Estilo5"><?=$object_concluido->nombre?></span></td>
                <td><span class="Estilo5"><?=$object_concluido->curso?></span></td>
                 <?php
				 
                for($i = 1; $i <= 8; $i++){

					$quizes = get_record_select('inea_grades',"id_concluidos = $object_concluido->id AND type = 'ae' AND unidad = $i");
					$acts = get_record_select('inea_grades',"id_concluidos = $object_concluido->id AND type = 'act' AND unidad = $i");

                	echo '<td width="20" align="center"><span class="Estilo5">'.$quizes->value.'</span></td>';
                	echo '<td width="20" align="center"><span class="Estilo5">'.$acts->value.'</span></td>';
				}

                	echo '<td align="center"><span class="Estilo5">'.date("d-m-Y H:i", $object_concluido->fecha_concluido).'</span></td>';
                	echo '<td align="center"><span class="Estilo5">'.$object_concluido->id.'</span></td>';
				?>

            </tr>
        </tbody>
    </table>

		<p>&nbsp;</p>
		<p>&nbsp;</p>
<?php
		}else{
?>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<form id="form1" name="form1" method="post" action="">
		  <table width="200" align="center">
            <tr>
              <td colspan="2" nowrap="nowrap"><span class="Estilo6">Para verificar los registros de cursos concluidos ingrese el RFE del educando</span> </td>
            </tr>
            <tr>
              <td colspan="2" nowrap="nowrap">&nbsp;</td>
            </tr>
            <tr>
              <td width="128"><div align="right">RFE:</div></td>
              <td width="129"><input name="rfe" type="text" id="rfe" value="<?php echo $_POST['rfe']?>" size="15" maxlength="13" onKeyUp="this.value = this.value.toUpperCase();" /></td>
            </tr>
            <tr>
              <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
              <td colspan="2"><div align="center">
                  <input type="submit" name="Submit" value="Verificar" />
              </div></td>
            </tr>
            <tr>
              <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
              <td colspan="2" nowrap="nowrap"><div align="center" class="Estilo1">NO SE ENCONTRARON REGISTROS PARA EL RFE <?php echo $_POST['rfe']?></div></td>
            </tr>
          </table>
		</form>
<?php }?>
</body>
</html>
