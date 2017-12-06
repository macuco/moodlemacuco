<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * User sign-up form.
 *
 * @package    core
 * @subpackage auth
 * @copyright  1999 onwards Martin Dougiamas  http://dougiamas.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');
require_once($CFG->dirroot . '/user/editlib.php');
require_once($CFG->dirroot . '/mod/inea/inealib.php');

class login_signup_form extends moodleform implements renderable{
	function definition() {
		global $DB, $USER, $CFG, $REG;
		
		print_object($_POST);
		$quitalogin = false;//Macuco Variable que utilizo para saber si voy a quitar los elementos del formulario para registrar
        $no_usuario = false;//Macuco para saber si el RFE que se ingreso es correcto.
		$esasesor = true;
		$readonly = '';
		
		$mform = $this->_form;
		
		if((!isset($_POST['idnumber'])) || ($_POST['idnumber'] == "")) {//Macuco  Si no traigo RFE		
			if(isset($_SESSION['datos_sasa'])) { //Macuco Si traigo los datos del ZIP que manda SASA
				if($_SESSION['datos_sasa']!="") {
					$desencripta = $_SESSION['datos_sasa'];
					// Obtengo todos los datos que manda SASA
					$rfe = $desencripta['crfe'];
					$nombre	= $desencripta['nombre'];
					$apellidos = explode(" ", $desencripta['apellidos']);
					$apellido_paterno = $apellidos[0];
					$apellido_materno = $apellidos[1];
					$fecha_nacimiento = $desencripta['fnacimiento'];
					$clave_municipio = $desencripta['icvemunicipio'];
					$id_sexo	= $desencripta['sexo'];
					$mail = $desencripta['cemail'];
					$clave_ocupacion = $desencripta['icveocupacion'];
					if($rfe == "") {// Si el RFE es vacio, desactivo TODO
						$readonly = '';
					} else {//Si trae datos, activo TODO
						$readonly = "";
					}
					// LIMPIO LOS DATOS
					$_SESSION['datos_sasa'] = "";
					unset($_POST);
				} else {//Si vienen los datos vacios de SASA o la informacion ya fue utilizada limpio la session
					unset($_SESSION['datos_sasa']);
					unset($_POST);
					$readonly = '';
				}
			} else {// Si no hay RFE ni datos SASA, entonces desactivo todos los campos y pido el zip.
				$rfe = "";
				$nombre = "";
				$apellido_paterno = "";
				$apellido_materno = "";
				$fecha_nacimiento = "";
				$id_sexo = '';
				$sexo = "";
				$clave_ocupacion = "";
				$readonly = '';//Desactivo todos los campos
				//unset($_POST);
			}
		} else {
			// MACUCO  Si traigo el RFE y no se va a registrar solo muestro los datos y quito el LOGIN //
			if((!isset($_POST['country'])) && isset($_POST['idnumber']) && $_POST['idnumber'] != ""){
                if(!$DB->record_exists('user', array('idnumber'=>$_POST['idnumber']))){
                    $no_usuario = true;
					$readonly = '';
                } else {
                    $user = $DB->inea_get_user_from_rfe($_POST['idnumber']);
					// $todos_los_grupos = get_records('groups gr', 'substring(g.name,10)',$user->id,'','gr.name');
                    $esasesor = ($user->url == 4)? TRUE : FALSE;
                    if(!$esasesor){
						$rs = $DB->get_recordset_select('groups', 'name LIKE \'%_%_'.$user->id.'\'', null, '', 'id, name' );
						if ($rs->valid()) {
							$esasesor = true;
							print_object($rs->current());
						} else {
							$esasesor=false;
						}
						$rs->close();
                    }

                    foreach ($user as $clave=>$valor){
                        $_POST[$clave] = $valor;
                    }
                    $readonly = ' readonly style="border: 0px;"';
                    $quitalogin = true;
                }
			}
		}
    	
		//Si el usuario ya tiene un registro activo o no es asesor 
        if((!$esasesor) && (!$no_usuario) && (!isset($_POST['idnumber_ant']))){
            $mform->addElement('header', '', 'Atenci&oacute;n:', '');
            $mform->addElement('static', '', "Este RFC ya fue registrado anteriormente.<br/><br/> Comprueba que los datos que se proporcionaron sean los correctos.");
            $mform->addElement('button','',get_string('back'),'onclick="javascript:window.location.href=\'signup.php?id_rol='.$_GET['id_rol'].'&saltar=1\'"');
            return;
        }

		/* Campos del formulario de registro para del asesor */
		$mform->addElement('header', '', get_string('datosasesor','inea'), '');
		
		// Apellido Paterno
		$mform->addElement('text', 'lastname', get_string('apaterno', 'inea'), 'size="25" onblur="generaRFE(document.getElementById(\'id_lastname\').value, document.getElementById(\'id_icq\').value, document.getElementById(\'id_firstname\').value, this.form);"'.$readonly);
		$mform->setType('lastname', PARAM_TEXT);
		$mform->addRule('lastname', get_string('noapaterno','inea'), 'required', null, 'client');
		$mform->addRule('lastname', get_string('noapaterno_','inea'), 'lettersonly', null, 'client');
        if(isset($apellido_paterno)) {
			$mform->setDefault('lastname', $apellido_paterno);
		}

		// Apellido Materno
		$mform->addElement('text', 'icq', get_string('amaterno','inea'), 'size="25" onblur="generaRFE(document.getElementById(\'id_lastname\').value, document.getElementById(\'id_icq\').value, document.getElementById(\'id_firstname\').value, this.form);"'.$readonly);
		$mform->setType('icq', PARAM_TEXT);
		//$mform->addRule('icq', get_string('noamaterno','inea'), 'required', null, 'client');
		$mform->addRule('icq', get_string('noamaterno_','inea'), 'lettersonly', null, 'client');
        if(isset($apellido_materno)){
			$mform->setDefault('icq', $apellido_materno);
		}
		
		// Nombre
		$mform->addElement('text', 'firstname', get_string('nombres','inea'), 'size="25" onblur="generaRFE(document.getElementById(\'id_lastname\').value, document.getElementById(\'id_icq\').value, document.getElementById(\'id_firstname\').value, this.form);"'.$readonly);
		$mform->setType('firstname', PARAM_TEXT);
		$mform->addRule('firstname', get_string('nonombres','inea'), 'required', null, 'client');
		$mform->addRule('firstname', get_string('nonombres_','inea'), 'lettersonly', null, 'client');
        if(isset($nombre)) {
			$mform->setDefault('firstname', $nombre);
		}
		
		// Genero (Sexo)
		if($quitalogin){
			$_POST['sexo_'] = $_POST['yahoo'];
			$mform->addElement('text', 'sexo_', get_string('sexo','inea'), 'size="25" '.$readonly);
			$mform->setType('sexo_', PARAM_TEXT);			
		} else {
			$sexo = array("Masculino"=>"Masculino", "Femenino"=>"Femenino");
			$default_sexo[''] = get_string('sexo','inea');
			$sexo = array_merge($default_sexo, $sexo);
			$mform->addElement('select', 'yahoo', get_string('sexo','inea'), $sexo, ''.$readonly);
			$mform->addRule('yahoo', get_string('nosexo','inea'), 'required', null, 'client');
			if(isset($id_sexo)) {
				$mform->setDefault('yahoo', $id_sexo);
			}
		}

		// Fecha de Nacimiento
		if($quitalogin){
			$mform->addElement('text', 'aim', get_string('fechanacimiento','inea'), 'size="10" onblur="generaRFE(document.getElementById(\'id_lastname\').value, document.getElementById(\'id_icq\').value, document.getElementById(\'id_firstname\').value, document.getElementById(\'id_aim\').value);"'.$readonly);  
			$mform->setType('aim', PARAM_TEXT);
			$mform->addRule('aim', get_string('nofechanacimiento','inea'), 'required', null, 'client');
			//$mform->addRule('aim', get_string('noefechanacimiento','inea'), 'fecha', null, 'client');
            if(isset($fecha_nacimiento)) {
				$mform->setDefault('aim', $fecha_nacimiento);
			}
		} else {
			$element = &$mform->addElement('date_selector', 'aim', get_string('fechanacimiento','inea'), '', 'onchange="generaRFE(document.getElementById(\'id_lastname\').value, document.getElementById(\'id_icq\').value, document.getElementById(\'id_firstname\').value, this.form);"');
			//$element->setAttributes('onchange="generaRFE(document.getElementById(\'id_lastname\').value, document.getElementById(\'id_icq\').value, document.getElementById(\'id_firstname\').value, this.form);"');
			//$mform->setHelpButton('fecha_nacimiento', array('coursestartdate', utf8_encode(get_string('startdate')), true);
			$mform->addRule('aim', get_string('nofechanacimiento','inea'), 'required', null, 'server');
			//$fechaMiliSecond = mktime(0,0,0,12,0,1985);
			//$mform->setDefault('aim', $fechaMiliSecond);
		}

		// RFE/RFC
		if($quitalogin){
			$rfe = $_POST['idnumber'];
			$mform->addElement('text', 'idnumber_', get_string('rfe', 'inea'), 'size="20"'.$readonly);
			$mform->setType('idnumber_', PARAM_TEXT);
			$mform->addRule('idnumber_', get_string('rfeincorrecto', 'inea'), 'alphanumeric', null, 'client');
			$mform->setDefault('idnumber_', $rfe);
		} else {
			//if($no_usuario){
            $mform->addElement('hidden', 'idnumber', get_string('rfe', 'inea'), ' id="id_idnumber" size="20"'.$readonly);
            $rfe = $_POST['idnumber'];
            //}//else
            // $mform->addElement('text', 'idnumber', get_string('rfe', 'inea'), 'size="20"'.$readonly);
			//$mform->addElement('text', 'idnumber', get_string('rfe', 'inea'), 'size="20"'.$readonly);
			$mform->setType('idnumber', PARAM_TEXT);
			$mform->addRule('idnumber', get_string('norfe', 'inea'), 'required', null, 'client');
			$mform->addRule('idnumber', get_string('rfeincorrecto', 'inea'), 'alphanumeric', null, 'client');
			$mform->setDefault('idnumber', $rfe);
            //if($no_usuario){
            $mform->addElement('hidden', 'idnumber_ant', get_string('rfe', 'inea'), 'id="id_idnumber_ant"');
            $mform->setType('idnumber_ant', PARAM_TEXT);
            $mform->addRule('idnumber_ant', get_string('norfe', 'inea'), 'required', null, 'client');
            $mform->addRule('idnumber_ant', get_string('rfeincorrecto', 'inea'), 'alphanumeric', null, 'client');
            $mform->setDefault('idnumber_ant', $rfe);
            //}
		}
	
		/* El Filtrado para el pais, estado, municipio  */
        $select1[0] = get_string('selectpais','inea'); 
        $select1[1] = get_string('mx','inea');
        $select1[2] = get_string('eu','inea');
        $MEXICO = 1;
		$ocupaciones = inea_get_ocupaciones();
		
		// Campos para datos geograficos (pais, entidad, municipio, zona, plaza)
		if(!$quitalogin) { //Si es registro de usuario
			// Entidad
			$mform->addElement('hidden', 'institution', '', 'size="20" id="institution"');
			$mform->setType('institution', PARAM_TEXT);
			$mform->setDefault('institution', 0);
			
			// Instituto
			$mform->addElement('hidden', 'instituto', '', 'size="20" id="instituto"');
			$mform->setType('instituto', PARAM_TEXT);
			$mform->setDefault('instituto', 0);
			
			// País
			$mform->addElement('hidden', 'country', '', 'size="20" id="country"');
			$mform->setType('country', PARAM_TEXT);
			$mform->setDefault('country', $MEXICO);
			
			//Municipio
			$mform->addElement('hidden', 'city', '', 'size="20" id="city"');// City es el municipio
			$mform->setType('city', PARAM_TEXT);
			$mform->setDefault('city', 0);
			
			// Plaza
			$mform->addElement('hidden', 'skype', '', 'size="20" id="skype"');// skype es la plaza
			$mform->setType('skype', PARAM_RAW);
			$mform->setDefault('skype', 0);

			$entidades = inea_get_entidades();
            $municipios = inea_get_municipios();
            $plazas = inea_get_plazas();
				
			//if(isset($_POST['institution'])){
				//$zonas = get_all_zonas($_POST['institution']);//29/01/09
			//}
				
			//get_current_user();
							
			//print_object($entidades);
			//print_object($clave_municipios);
			// Entidades/Estados
			$select2[0] = " -- Seleccionar entidad -- ";
			foreach ($entidades as $entidad) {
				if($entidad->icvepais == $MEXICO){
					$select2[$entidad->icveentfed] = $entidad->cdesentfed;
				}
			}
			
			// Muncipios
			$select3[0][0] = " -- Seleccionar municipio -- ";
			foreach ($municipios as $municipio) {
				if($municipio->icvepais == $MEXICO){
					$select3[$municipio->icveentfed][$municipio->icvemunicipio] = $municipio->cdesmunicipio;
				}
			}
			
			//Plazas			
			$select4[0][0][0] =  " -- Seleccionar plaza -- ";
			foreach ($plazas as $plaza) {
				if($plaza->icvepais == $MEXICO){
					$select4[$plaza->icveentfed][$plaza->icvemunicipio][$plaza->id] = $plaza->cnomplaza." (".$plaza->ccveplaza.")";
				}
			}
		
			// Create the Element
			$url = 'filtro_ajax.php';
			$sel =& $mform->addElement('hierselect', 'location', 'Origen: ( Estado | Municipio | Plaza)','onchange="asignarOrigen(this.form,\''.$url.'\');"');// And add the selection options
			$sel->setOptions(array($select2, $select3, $select4));
			$mform->addRule('location', get_string('faltaorigen', 'inea'), 'required', null, 'client');
				
			/*if(!empty($zonas))
			foreach($zonas as $zona){
				$desczonas[$zona->icvecz] =  $zona->cdescz;
			}
			$default_sexo[''] = " Seleccionar ";
			if(!isset($desczonas)){
				$zons = $default_sexo;
			}else{
				$zons = array_merge($default_sexo, $desczonas);
			}
			$mform->addElement('select', 'zona', 'Zona', $zons);
			$mform->addRule('zona', 'Zona requerida', 'required', null, 'server');
			if(isset($_POST['zona']))
				$mform->setDefault('zona',$_POST['zona']);
			*/
		} else {//Solo es consulta y la ubicacion se mostraran en campos de texto.
			//echo "holaaaaa ".$_POST['city']." -- ".$_POST['institution']." -- ".$_POST['country'];
			// Obtener plaza
			$plaza = inea_get_plaza($_POST['skype']);
            $_POST['skype'] = $plaza->cnomplaza;
            
			// Obtener municipio
			$municipio = inea_get_municipio($_POST['country'], $_POST['institution'], $_POST['city']);
            $_POST['city'] = $municipio->cdesmunicipio;
            
			// Obtener entidad
			$entidad = inea_get_entidad($_POST['country'], $_POST['institution']);
            $_POST['institution'] = $entidad->cdesentfed;
			$_POST['country'] = $select1[$_POST['country']];
			
			// Obtener zona
            $clave_zona = inea_get_zona($_POST['instituto'], $_POST['zona']);
            $_POST['zona'] = $clave_zona->cdescz;

			//$mform->addElement('text', 'country', get_string('pais','inea'), 'size="20" id="country" readonly');
			//$mform->setType('country', PARAM_TEXT);
			
			// Entidad
			$mform->addElement('text', 'institution', get_string('estado','inea'), 'size="20" id="institution"'.$readonly);
			$mform->setType('institution', PARAM_TEXT);

			// Campo municipio
			$mform->addElement('text', 'city', 'Municipio', 'size="20" id="city"'.$readonly);// City es el municipio
			$mform->setType('city', PARAM_TEXT);

			// Campo plaza
			$mform->addElement('text', 'skype', 'Plaza', 'size="20" id="skype"'.$readonly);// City es el municipio
			$mform->setType('skype', PARAM_TEXT);
			
			// Campo zona
			//$mform->addElement('text', 'zona', 'Zona', 'size="25" '.$readonly);
			//$mform->setType('zona', PARAM_TEXT);
		}
		
		/*$mform->addElement('text', 'phone1', get_string('telefono','inea'), 'size="25"onkeypress="return IsNumber(event);" '.$readonly);
		$mform->setType('phone1', PARAM_TEXT);
		$mform->addRule('phone1', get_string('notelefono','inea'), 'required', null, 'client');*/
		
		/*$mform->addElement('text', 'address', get_string('calleno','inea'), 'size="80" '.$readonly);
		$mform->setType('address', PARAM_TEXT);
		$mform->addRule('address', get_string('nocalleno','inea'), 'required', null, 'client');*/
		
		/*$mform->addElement('text', 'timezone', 'Código postal', 'size="10" onkeypress="return IsNumber(event);" '.$readonly);
		$mform->setType('timezone', PARAM_TEXT);
		$mform->addRule('timezone', 'Falta especificar el código postal', 'required', null, 'client');*/
		
		// Campos para el correo electronico (email)
		if(!$quitalogin){
			$mform->addElement('text', 'email', get_string('email'), 'maxlength="100" size="25" ');
			$mform->setType('email', core_user::get_property_type('email'));
			$mform->addRule('email', get_string('missingemail'), 'required', null, 'client');
			$mform->setForceLtr('email');
			
			$mform->addElement('text', 'email2', get_string('emailagain'), 'maxlength="100" size="25" ');
            $mform->setType('email2', core_user::get_property_type('email'));
            $mform->addRule('email2', get_string('missingemail'), 'required', null, 'client');
			$mform->setForceLtr('email2');
		} else {
			$_POST["email_"] = $_POST['email'];
            $mform->addElement('text', 'email_', get_string('email'), 'maxlength="100" size="40" '.$readonly);
            $mform->setType('email_', core_user::get_property_type('email'));	
		}
		
		// Campo para determinar la ocupacion
		if(!$quitalogin){
			$listaocupaciones[] = "--- Seleccionar ocupacion ---";
            foreach($ocupaciones as $id => $op_ocupacion){
                $listaocupaciones[$id] = $op_ocupacion->cdesocupacion;
            }
			$mform->addElement('select', 'msn', get_string('ocupacion','inea'), $listaocupaciones);
			$mform->addRule('msn', get_string('noocupacion','inea'), 'required', null, 'client');
			if(isset($clave_ocupacion)) {
				$mform->setDefault('msn', $clave_ocupacion);
			}
		}else {
			$_POST['msn'] = $ocupaciones[$_POST['msn']]->cdesocupacion;
			$mform->addElement('text', 'msn', get_string('ocupacion','inea'), 'size="25" '.$readonly);
			$mform->setType('msn', PARAM_TEXT);
			$mform->addRule('msn', get_string('noocupacion','inea'), 'required', null, 'client');	
			//$mform->setDefault('msn', $id_sexo);
		}
		
		//$courses = get_courses($category->id, 'c.sortorder ASC', 'c.id,c.sortorder,c.visible,c.fullname,c.shortname,c.password,c.summary,c.guest,c.cost,c.currency');
		//$cursos = print_object($courses);
		//foreach ($courses as $curso){
		//$mi_curso = array('uno'=>'dos');
		//$mform->addElement('advcheckbox', 'mysql', '',$curso->fullname,$curso->id,$curso->id,$curso->id);
		//}
		
		// Campos de usuario y password
		if(!$quitalogin) {//Si se va a registrar un usuario, muestro los campos de usuario y passwd
			$mform->addElement('header', '', get_string('createuserandpass'), '');
			
			// Campo para el nombre de usuario
			$mform->addElement('text', 'username', get_string('username'), 'size="12"');
			$mform->setType('username', PARAM_RAW);
			$mform->addRule('username', get_string('missingusername'), 'required', null, 'client');
			if(isset($rfe)) {
				$mform->setDefault('username', $rfe);
			}

			// Campo para el password
			$mform->addElement('text', 'password', get_string('password'), 'size="12"');
			$mform->setType('password', core_user::get_property_type('password'));
			$mform->addRule('password', get_string('missingpassword'), 'required', null, 'client');
			if(isset($rfe)) {
				$mform->setDefault('password', $rfe);
			}
		}
	
		// Campo oculto para el id rol
		$mform->addElement('hidden', 'id_rol', '');
		$mform->setType('id_rol', PARAM_TEXT);
        $mform->setDefault('id_rol', $_GET['id_rol']);
        
		// Campo oculto para el ud del usuario
		$mform->addElement('hidden', 'id_user', '');
		$mform->setType('id_user', PARAM_TEXT);
        if(isset($user)) {
			$mform->setDefault('id_user', $user->id);
		}
	
		// Boton para enrolar al curso o registrarse
		if($quitalogin){//Sivoy a accesar a un curso y ya estoy registrado
			$mform->addElement('header', '', get_string('elegircursos', 'inea'));
			$mform->addElement('hidden', 'enrolar');
			$mform->setDefault('enrolar', 1);
            //$mform->addRule('enrolar', 'Estas enrolando.', 'required', null, 'client');
			//$mform->addElement('button','','Ir','');
            //$this->_form->_attributes['onsubmit']="return true;";
            $this->_form->_attributes['action']="$CFG->wwwroot/login/enrol.php";
			$mform->addElement('submit', 'enrol', get_string('ircursos', 'inea'));
		} else {//Si el usuario se va a registrar
			// Campo oculto para decirle al formulario que es el paso final del registro
			$mform->addElement('hidden', 'finalizar', '');
			$mform->setType('finalizar', PARAM_TEXT);
			$mform->setDefault('finalizar', true);
			
			$this->add_action_buttons(true, "Registro");
		}
		
		// Establecer las categorías del perfil para los campos
        // profile_signup_fields($mform);

		// Validacion captcha
        if (signup_captcha_enabled()) {
            $mform->addElement('recaptcha', 'recaptcha_element', get_string('security_question', 'auth'), array('https' => $CFG->loginhttps));
            $mform->addHelpButton('recaptcha_element', 'recaptcha', 'auth');
            $mform->closeHeaderBefore('recaptcha_element');
        }
	}//FIN FUNCION PRINCIPAL 

	function definition_after_data(){
		$mform = $this->_form;

		//$mform->applyFilter('username', 'moodle_strtolower');
        $mform->applyFilter('username', 'trim');

        // Trim required name fields.
        foreach (useredit_get_required_name_fields() as $field) {
            $mform->applyFilter($field, 'trim');
        }
	}

	function validation($data, $files) {
		global $CFG, $DB;
	
		$errors = parent::validation($data, $files);
		
		// Validacion del captcha
        if (signup_captcha_enabled()) {
            $recaptcha_element = $this->_form->getElement('recaptcha_element');
            if (!empty($this->_form->_submitValues['recaptcha_challenge_field'])) {
                $challenge_field = $this->_form->_submitValues['recaptcha_challenge_field'];
                $response_field = $this->_form->_submitValues['recaptcha_response_field'];
                if (true !== ($result = $recaptcha_element->verify($challenge_field, $response_field))) {
                    $errors['recaptcha'] = $result;
                }
            } else {
                $errors['recaptcha'] = get_string('missingrecaptchachallengefield');
            }
        }
        
		// Validar si el usuario ya existe
		if(!isset($data['enrolar'])) {
			$authplugin = get_auth_plugin($CFG->registerauth);

			if ($DB->record_exists('user', array('username'=>$data['username'], 'mnethostid'=>$CFG->mnet_localhost_id))) {
				$errors['username'] = get_string('usernameexists');
			} /*else {
				if (empty($CFG->extendedusernamechars)) {
					$coincidencias = preg_replace('[^(-\.[:alnum:])]', '', $data['username']);
					if (strcmp($data['username'], $coincidencias)) {
						$errors['username'] = get_string('alphanumerical');
					}
				}
				// Check allowed characters.
				if ($data['username'] !== core_text::strtolower($data['username'])) {
					$errors['username'] = get_string('usernamelowercase');
				} else {
					if ($data['username'] !== core_user::clean_field($data['username'], 'username')) {
						$errors['username'] = get_string('invalidusername');
					}
				}
			}*/
        
			//print_object($data);
			// verificar que ambos rfe coincidan
			if(isset($data['idnumber_ant']) && isset($data['idnumber'])) {
				if($data['idnumber_ant'] != $data['idnumber']) {
					$errors['firtsname'] = get_string('rfenocoincide', 'inea');
				}
			}
			
			//check if user exists in external db
			//TODO: maybe we should check all enabled plugins instead
			if ($authplugin->user_exists($data['username'])) {
				$errors['username'] = get_string('usernameexists');
			}
			
			// validar correo electronico
			if (!validate_email($data['email'])) {
				$errors['email'] = get_string('invalidemail');
			} else if ($DB->record_exists('user', array('email'=>$data['email']))) {
				$errors['email'] = get_string('emailexists');
			}
			
			// validar correo electronico (repeticion)
			if (empty($data['email2'])) {
				$errors['email2'] = get_string('missingemail');
			} else if ($data['email2'] != $data['email']) {
				$errors['email2'] = get_string('invalidemail');
			}
			
			// Si hubo un error en el correo, veriricar si esta permitido
			if (!isset($errors['email'])) {
				signup_captcha_enabled();
				if ($err = email_is_not_allowed($data['email'])) {
					$errors['email'] = $err;
				}
			}

			// Verificar si el usuario ya esta registrado en la plataforma
			if ($DB->record_exists('user', array('idnumber' => $data['idnumber']))) {
				$errors['idnumber'] = get_string('rfeyaregistrado', 'inea');
			}

			if ($data['country'] == 0 || $data['city'] == 0 || $data['skype'] == 0) {
				$errors['location'] = get_string('faltaorigen', 'inea');
			}
		} else {
			if ($DB->record_exists('user', array('idnumber'=>$data['idnumber_']))) {
				$errors['idnumber_'] = '';
			}
		}
		
		// Regresar los errores (si existen)
		if (count($errors)){
			return $errors;
		} else {
			return true;
		}
	}
	
	/**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return array
     */
    public function export_for_template(renderer_base $output) {
        ob_start();
        $this->display();
        $formhtml = ob_get_contents();
        ob_end_clean();
        $context = [
            'formhtml' => $formhtml
        ];
        return $context;
    }
}
?>
