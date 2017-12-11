<?php
require_once('../config.php');
require_once($CFG->libdir . '/datalib.php');
require_once($CFG->libdir . '/authlib.php');
require_once $CFG->libdir . '/coursecatlib.php';
require_once($CFG->dirroot . '/mod/inea/inealib.php');

//print_object($_POST);
//exit;

$id_usuario = required_param('id_user', PARAM_INT);//$_GET['id_user'];
$id_rol = required_param('id_rol', PARAM_INT);//$_GET['id_rol'];

if (!$authplugin = signup_is_enabled()) {
    print_error('notlocalisederrormessage', 'error', '', 'Sorry, you may not use this page.');
}

//$categorias = coursecat::make_categories_list();
//print_object($categorias);
//exit;
	
$PAGE->set_url('/login/enrol.php', array('id_rol'=>$id_rol, 'id_user'=>$id_usuario));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('embedded');

switch($id_rol) {
	case ASESOR : $es_educando = false; break;
	case EDUCANDO : $es_educando = true; break;
	default: break;
}

if(!isset($es_educando))	{
	print_error("No es posible identificar el rol que tienes dentro del sistema. <br/> Por favor intenta entrar nuevamente.");
}

//TODO mandar error si el rol no es el correcto
if(!$user = inea_get_user_from_id($id_usuario)) {
    print_error("El usuario no esta registrado en el sistema");
}

$mensaje = "";
$titulo = "";
$rol = "";

if($id_rol == ASESOR) {
  $rol = "Asesor";
  $titulo = "asesores";
  $funcionalidad = "";
  $funcionalidadboton = "onclick=\"activarDesactivarBoton(this,1);registrarCurso($id_usuario,$id_rol,document.getElementById('cursos').value,-1);activarDesactivarSeccion(this,0);activarDesactivarBoton(this,0);\"";
  $maxcurses = 100;
} else {
  $rol = "Educando";
  $titulo = "educandos";
  $mensaje = ", por ultimo un <strong><i>Asesor</i></strong>";
  $funcionalidad = "onchange=\"generarAsesores(".$id_usuario.",this.value,'getasesores');\"";
  $funcionalidadboton = "onclick=\"activarDesactivarBoton(this,1);registrarCurso($id_usuario,$id_rol,document.getElementById('cursos').value,document.getElementById('asesores').value);activarDesactivarSeccion(this,0);activarDesactivarBoton(this,0);\"";
  $maxcurses = 2;
}

$ajax_url = $CFG->wwwroot . '/login/funcionesAjax.js';

//$url = new moodle_url($ajax_url);
//print_object($url);
//exit;
		
$PAGE->set_title($titulo);
$PAGE->set_heading($SITE->fullname);
if (file_exists('funcionesAjax.js')) {
	//echo "entro aqui?";
	$PAGE->requires->js('/login/funcionesAjax.js');
	//$PAGE->requires->js('/login/funcionesAjax.js')->in_head();
	//$PAGE->requires->js_function_call('generarCursosRegistrados', array($id_usuario, $id_rol))->on_dom_ready();
	$PAGE->requires->js_function_call('generarCursosRegistrados', array($id_usuario, $id_rol));
}
echo $OUTPUT->header();
//echo $OUTPUT->footer();
//exit;
/*print_header("Cursos", $heading='', $navigation='', $focus='',
                       $meta='<script language="javascript" src="funcionesAjax.js"></script>', $cache=true, $button='&nbsp;', $menu='',
                       $usexml=false, $bodytags='onload="generarCursosRegistrados('.$id_usuario.','.$id_rol.');"', $return=false);*/
?>
<table width="800" border="0" cellpadding="0" cellspacing="0" align="center">
	<tr>
      <td valign="top" align="center"><?php   $user = $DB->get_record('user', array('id'=>$id_usuario), 'id, firstname, lastname, lastaccess, skype as plaza, lastnamephonetic, firstnamephonetic, middlename, alternatename');
		$user->fullname = fullname($user, true); ?><br/><br/><br/><?php echo $rol; ?>: <?php echo $user->fullname; ?><br /><br />
	  </td>
    </tr>
</table>

<form  class="mform" >
<fieldset class="clearfix"><legend class="ftoggler">
Registro de <?php echo $titulo; ?>
</legend>
<div class="fcontainer clearfix">
<div align="center"><input type="button" name="inicioregistrar" id="inicioregistrar" value="Registrar" onclick="activarDesactivarSeccion(this,1);" /></div>
<div id="_registro" style="visibility:hidden">
<div class="fitemtitle fstaticlabel">
    Selecciona el <strong><i>Tipo de curso</i></strong> al que deseas registrarte (b&aacute;sicos / diversificados). Enseguida selecciona el <br />nombre del <strong><i>Curso</i></strong><?php echo $mensaje; ?> y finalmente presiona el bot&oacute;n <strong><i>Registrar</i></strong>.
</div>
<table width="50%" border="0" >
  <tr>
    <td valign="top" width="40%" align="right">&nbsp;&nbsp;&nbsp;Tipo de curso:&nbsp;&nbsp;&nbsp;</td>
    <td valign="top" width="60%" align="left">
      <select name="id_categoria" id="id_categoria" onchange="generarCursos(this.value,'getcursos',<?php echo $id_usuario; ?>);">
        <option value="-1" selected>------Seleccionar------</option>
        <?php
			if($categorias = coursecat::make_categories_list()) {
				foreach ($categorias as $idcat=>$nombrecat) {
					print("<option value='".$idcat."'>".$nombrecat."</option>");
				}
			}
			
        ?>
      </select>
	</td>
  </tr>
  <tr>
    <td valign="top" align="right">&nbsp;&nbsp;&nbsp;Curso:&nbsp;&nbsp;&nbsp;</td>
    <td valign="top" align="left"><select name="cursos" id="cursos" <?php echo $funcionalidad; ?>><option value="-1">------Seleccionar------</option></select></td>
  </tr>
  <?php
    if($id_rol == EDUCANDO) {
  ?>
  <tr>
    <td valign="top" align="right">&nbsp;&nbsp;&nbsp;Asesores:&nbsp;&nbsp;&nbsp;</td>
    <td valign="top" align="left"><select name="asesores" id="asesores"><option value="-1">------Seleccionar------</option></select></td>
  </tr>
  <?php
    }
  ?>
  <tr>
    <td><input type="hidden" name="maxcursos" id="maxcursos" value="<?php echo $maxcurses; ?>" /></td>
    <td valign="top" align="left"><input type="button" value="Registrar" <?php echo $funcionalidadboton; ?> name="registra" id="registra" /></td>
  </tr>
</table>
</div>
<div style="visibility:hidden" class="cuerpo_mevyt" id="mensaje" align="center"><br />Solo tienes permitido estar registrado en <?php echo $maxcurses; ?> cursos como m&aacute;ximo.</div>
</div>
</fieldset>
</form>
<br/><br/>&nbsp;
<form class="mform">
<div id="datos"><div align="center"><img src="cargando.gif" title="Cargando" /><br />Cargando Informaci&oacute;n</div></div>
</form>
<?php
echo $OUTPUT->footer();
?>
