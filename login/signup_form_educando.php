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

class login_signup_form extends moodleform implements renderable/*, templatable*/ {
    function definition() {
        global $DB, $USER, $CFG, $REG;
		
		// SCRIPT INEA
		// RUDY: Recupero RFE y entidad
		$idnumber   = optional_param('idnumber', 1, PARAM_TEXT);
		$entidad    = optional_param('entidad', 1, PARAM_INT);
		$eseducando = isset($_POST['eseducando'])? $_POST['eseducando'] : false;
		$quitalogin = false;
		$readonly = 'readonly style="border: 0px;"';
		
		$mform = $this->_form;
		
		if(isset($idnumber) && $idnumber != "") {	//RUDY: si se tiene el RFE se permite continuar	
            if($DB->record_exists('user', array('idnumber'=>$idnumber))) {	//RuDY: si existe el registro en la tabla user
				
				//buscamos el rfe en la tabla user
                $user = inea_get_user_from_rfe($idnumber);
				if($user->confirmed == 0) {
					//$mform =& $this->_form;
					$mform->addElement('header', '', get_string('error').':');
					$mform->addElement('static', '', get_string('msjnoconfirmado', 'inea'));
					$mform->addElement('button','', get_string('back'),'onclick="javascript:window.location.href=\'signup.php?id_rol='.$_GET['id_rol'].'&saltar=1\'"');
					return;
                } else {
                    //$readonly = 'readonly style="border: 0px;"';
                    $quitalogin = true;
                    $user->url==5 ? $eseducando = true : $eseducando = false;
                }
                    
				/*if(!$eseducando){
					//$grupos = get_records('groups_members gm, '.$CFG->prefix.'groups g', 'gm.groupid=g.id AND gm.userid',$user->id,'','substring(g.name,10) as asesorid' );
                    $todos_los_grupos = recordset_to_array(get_recordset_select('groups', 'name LIKE \'%_%_'.$user->id.'\'','','id, name' ));
                    if(empty($todos_los_grupos)){
						$eseducando=true;
                    }else{
						//TODO No tiene grupos asignados y no es asesor ¿Lo asigno como asesor?
                        $eseducando=false;
                    }
                }*/

                $clave_instituto = $user->instituto;

		    	//$skype = $_POST['skype_'];
                $email = isset($_POST['email'])? $_POST['email'] : '';
                $email2 = isset($_POST['email2'])? $_POST['email2'] : '';
				//$msn = $_POST['msn'];

                foreach ($user as $clave=>$valor) {
                    $_POST[$clave] = $valor;
                }
					
				if($esnuevo) {
					//$_POST['skype_'] = $skype;
					$_POST['email'] = $email;
					$_POST['email2'] = $email2;
				}
					
                //if(!$user->msn){
					//$_POST['msn']=$msn;
                //}
			   
			   
            } else {	//RuDY: si NO existe el registro en la tabla user
				
				//$registro = inea_get_record_sasa($entidad, $idnumber);	// RUDY: Funcion que realiza conexion con SASA
				// No se puede vincular con la base de datos externa, se crea un arreglo ficticio
				$registro = array(
				'ideducando' => 456313,
				'icvemodelo' => 10,
				'cpaterno' => 'ESCOBEDO',
				'cmaterno' => 'SEGURA',
				'cnombre' => 'JUAN JOSE',
				'sexo' => 1,
				'cfecha' => '27/07/1998',
				'icveentfed' => 9,
				'icvemunicipio' => 37,
				'icvecz' => 8,
				);
				
				if(isset($registro) && $registro['ideducando']!=0) {	//RUDY: si se encuentra el registro en SASA procede. SASA regresa un cero para el ideducando si no encuentra el registro.
					//if(true){
					//print_object($registro);
					$id_modelo = $registro['icvemodelo'];
					$id_sasa = $registro['ideducando'];
					$apellido_paterno = trim($registro['cpaterno']);
					$apellido_materno = trim($registro['cmaterno']);
					$nombre = trim($registro['cnombre']);
					$id_sexo = trim($registro['sexo']);
					$fecha_nacimiento = $registro['cfecha'];
					$clave_instituto = $registro['icveentfed'];
					$clave_municipio = $registro['icvemunicipio'];
					$clave_zona = $registro['icvecz'];				  
							  
					$eseducando = true;
					$esnuevo = true;
				} 
			}  
        }

		//echo $_POST['idnumber'];
		if(!$eseducando){
            $mform->addElement('header', '', get_string('error').':');
            $mform->addElement('static', '', get_string('msjnoregistrado', 'inea'));
            $mform->addElement('button','', get_string('back'),'onclick="javascript:window.location.href=\'signup.php?id_rol='.$_GET['id_rol'].'&saltar=1\'"');
            return;
        }
		
		/* Campos del formulario de registro para el educando */
        $mform->addElement('header', '', get_string('datoseducando', 'inea'));
		
		// Apellido Paterno
		$mform->addElement('text', 'lastname', get_string('apaterno', 'inea'), 'size="25" '.$readonly);
        $mform->setType('lastname', PARAM_TEXT);
        if(!$esnuevo) {
			$mform->addRule('lastname', get_string('noapaterno_','inea'), 'lettersonly', null, 'client');
		}
        if(isset($apellido_paterno)) {
			$mform->setDefault('lastname', $apellido_paterno);
		}
	
		// Apellido Materno
        $mform->addElement('text', 'icq', get_string('amaterno', 'inea'), 'size="25" '.$readonly);
        $mform->setType('icq', PARAM_TEXT);
		if(!$esnuevo) {
			$mform->addRule('icq', get_string('noamaterno_', 'inea'), 'lettersonly', null, 'client');
		}
        if(isset($apellido_materno)) {
			$mform->setDefault('icq', $apellido_materno);
		}
		
		// Nombre
        $mform->addElement('text', 'firstname', get_string('nombres', 'inea'), 'size="25" '.$readonly);
        $mform->setType('firstname', PARAM_TEXT);
        if(!$esnuevo) {
			$mform->addRule('firstname', get_string('nonombres_', 'inea'), 'lettersonly', null, 'client');
		}
        if(isset($nombre)) {
			$mform->setDefault('firstname', $nombre);
		}
		
		// Genero (Sexo)
        $mform->addElement('text', 'yahoo', get_string('sexo', 'inea'), 'size="25" '.$readonly);
        $mform->setType('yahoo', PARAM_TEXT);
        if(isset($id_sexo)) {
			($id_sexo == 1) ? $id_sexo = 'Masculino' : $id_sexo = 'Femenino';
		}
        $mform->setDefault('yahoo', $id_sexo);
	
		// Fecha de Nacimiento
        $mform->addElement('text', 'aim', get_string('fechanacimiento', 'inea'), 'size="10" '.$readonly);
        $mform->setType('aim', PARAM_TEXT);
        if(isset($fecha_nacimiento)) {
			$mform->setDefault('aim', $fecha_nacimiento);
		}
        
		if($quitalogin) {
			// RFE/RFC
            $rfe = $_POST['idnumber'];
            $mform->addElement('text', 'idnumber_', get_string('rfe', 'inea'), 'size="20" '.$readonly);
            $mform->setType('idnumber_', PARAM_TEXT);
            $mform->addRule('idnumber_', get_string('rfeincorrecto', 'inea'), 'alphanumeric', null, 'client');
            $mform->setDefault('idnumber_', $rfe);

			// Instituto
            $instituto = get_instituto($_POST['instituto']);
            $clave_instituto = $instituto->cdesie." - ".$instituto->cidenie;
            $mform->addElement('text', 'instituto_', get_string('instituto', 'inea'), 'size="80" '.$readonly);
            $mform->setType('instituto_', PARAM_TEXT);
            $mform->addRule('instituto_', get_string('noinstituto', 'inea'), '', null, 'client');
            $mform->setDefault('instituto_', $clave_instituto);

			// Zona
            $clave_zona = get_zona($_POST['instituto'], $_POST['zona']);
            $clave_zona = $clave_zona->cdescz;
            $mform->addElement('text', 'zona_', get_string('zona', 'inea'), 'size="25" '.$readonly);
            $mform->setType('zona_', PARAM_TEXT);
            $mform->addRule('zona_', get_string('nozona', 'inea'), '', null, 'client');
            $mform->setDefault('zona_', $clave_zona);
        } else {
			// RFE/RFC
            $mform->addElement('text', 'idnumber', get_string('rfe', 'inea'), 'size="20"'.$readonly);
            $mform->setType('idnumber', PARAM_TEXT);
            if(isset($rfe)) {
				$mform->setDefault('idnumber', $rfe);
			}
            
			// Modelo
            $mform->addElement('text', 'modelo', get_string('modelo','inea'), 'size="20" '.$readonly);
			$modelo = inea_get_modelo($id_modelo);
			//print_object($modelo);
			$nombre_modelo = $modelo->cdesmodelo;
            $mform->setType('modelo', PARAM_TEXT);
            $mform->setDefault('modelo', $nombre_modelo);

			// Id de SASA
            $mform->addElement('text', 'id_sasa', get_string('idsasa', 'inea'), 'size="20" '.$readonly);
            $mform->setType('id_sasa', PARAM_TEXT);
            $mform->setDefault('id_sasa', $id_sasa);

			// Instituto
            $mform->addElement('text', 'instituto_', get_string('instituto', 'inea'), 'size="70" '.$readonly);
            $mform->setType('instituto_', PARAM_TEXT);
			if(isset($clave_instituto) && ($clave_instituto != "")) {
				$instituto = inea_get_instituto($clave_instituto);
				$nombre_instituto = $instituto->cdesie." - ".$instituto->cidenie;
				$mform->setDefault('instituto_', $nombre_instituto);
			}

			/*$mform->addElement('text', 'city', get_string('municipio', 'inea'), 'size="30" '.$readonly);
            $mform->setType('city', PARAM_TEXT);
            $mform->setDefault('city', $clave_municipio);*/		

			// Instituto (valor)
			$mform->addElement('hidden', 'instituto', '', 'size="20" id="intituto"');
            //$mform->addRule('instituto', 'required', 'required', null, 'server');
            $mform->setDefault('instituto', $clave_instituto);
        }
		
        /* El Filtrado para el pais, estado, municipio  */
        $select1[0] = get_string('selectpais','inea'); 
		$select1[1] = get_string('mx','inea');
        $select1[2] = get_string('eu','inea');
        $MEXICO = 1;
        
		$ocupaciones = inea_get_ocupaciones();
        $mform->addElement('hidden', 'country', '', 'size="20" id="country"');
        $mform->setType('country', PARAM_TEXT);
        $mform->setDefault('country', $MEXICO);
		
		// Campos para datos geograficos (pais, entidad, municipio, zona, plaza)
		if(!$quitalogin) { //Si es registro de usuario
            $mform->addElement('hidden', 'institution', '', 'size="20" id="institution"');
            $mform->addRule('institution', get_string('required'), 'required', null, 'server');
            $mform->setType('institution', PARAM_TEXT);
			$mform->setDefault('institution', $clave_instituto);
			
            $mform->addElement('hidden', 'instituto', '', 'size="20" id="instituto"');
            $mform->addRule('instituto', get_string('required'), 'required', null, 'server');
			$mform->setType('instituto', PARAM_TEXT);
            $mform->setDefault('instituto', $clave_instituto);

            $mform->addElement('hidden', 'city', '', 'size="20" id="city"');// City es el municipio
            $mform->setType('city', PARAM_TEXT);
            $mform->setDefault('city', $clave_municipio);

            $mform->addElement('hidden', 'zona', '', 'size="20" id="zona"');// zona
            $mform->setType('zona', PARAM_TEXT);
            $mform->setDefault('zona', $clave_zona);
			
			/*$mform->addElement('hidden', 'skype', '', 'id="skype"');// skype es la plaza
            $mform->setType('skype', PARAM_TEXT);
            $mform->setDefault('skype', 0);*/
			
            $mform->addElement('hidden', 'icvemodesume', '', 'id="icvemodesume"');// icvemodesume es el modelo
            $mform->setType('icvemodesume', PARAM_TEXT);
            $mform->setDefault('icvemodesume', $id_modelo);

            $mform->addElement('hidden', 'sasa', '', 'id="sasa"');
            $mform->setType('sasa', PARAM_TEXT);
            $mform->setDefault('sasa', $id_sasa);
			
            $mform->addElement('hidden', 'eseducando', '', 'id="eseducando"');
            $mform->setType('eseducando', PARAM_TEXT);
            $mform->setDefault('eseducando', $eseducando);

            $entidades = inea_get_entidades();
            $municipios = inea_get_municipios();
            $plazas = inea_get_plazas();
			
			// Crear la lista para el campo Instituto
			if(isset($_POST['instituto']) && $_POST['instituto'] != ""){
				$objinstituto = inea_get_instituto($_POST['instituto']);
				$select2[$clave_instituto] = $objinstituto->cdesie;
			}else if(isset($clave_instituto) && $clave_instituto != ""){
				$objinstituto = inea_get_instituto($clave_instituto);
				$select2[$clave_instituto] = $objinstituto->cdesie;
			}
			
			// Crear la lista para el campo Municipio
            $select3[0][0] = " -- Seleccionar municipio -- ";
            foreach ($municipios as $municipio) {
                if($municipio->icvepais == $MEXICO){
                    $select3[$municipio->icveentfed][$municipio->icvemunicipio] = $municipio->cdesmunicipio;
                }
            }
            
			// Crear la lista para el campo Plaza
			$select4[0][0][0] =  " -- Seleccionar plaza -- ";
			foreach ($plazas as $plaza) {
                if($plaza->icvepais == $MEXICO){
                    $select4[$plaza->icveentfed][$plaza->icvemunicipio][$plaza->id] = $plaza->cnomplaza;
                }
            }

            // Create the Element
            $url = 'filtro_ajax.php';
            $sel =& $mform->addElement('hierselect', 'location', 'Origen: ( Estado | Municipio | Plaza)','onchange="asignarOrigen(this.form,\''.$url.'\',1);"');// And add the selection options
            $sel->setOptions(array($select2, $select3, $select4));
            $mform->addRule('location', get_string('faltaorigen', 'inea'), 'required', null, 'server');
			
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

			// Campo entidad
            $mform->addElement('hidden', 'institution', get_string('entidad', 'inea'), 'size="20" id="institution"'.$readonly);
            $mform->setType('institution', PARAM_TEXT);

			// Campo municipio
            $mform->addElement('text', 'city', get_string('municipio', 'inea'), 'size="25" id="city"'.$readonly);// City es el municipio
            $mform->setType('city', PARAM_TEXT);

			// Campo plaza
            $mform->addElement('text', 'skype', get_string('plazacomunitaria', 'inea'), 'size="25" '.$readonly);
            $mform->setType('skype', PARAM_TEXT);
            $mform->addRule('skype', get_string('noplazacomunitaria', 'inea'), 'required', null, 'server');

			// Campo zona
            $mform->addElement('hidden', 'zona', get_string('zona', 'inea'), 'size="25" '.$readonly);
            $mform->setType('zona', PARAM_TEXT);
            //$mform->addRule('zona', get_string('nozona', 'inea'), 'required', null, 'server');
        }
		
		// Campos para el correo electronico (email)
		if(!$quitalogin) {
			
            $mform->addElement('text', 'email', get_string('email'), 'maxlength="100" size="25" ');
			$mform->setType('email', core_user::get_property_type('email'));
			$mform->addRule('email', get_string('missingemail'), 'required', null, 'client');
            if(isset($mail)){
				$mform->setDefault('email', $mail);
			}
			$mform->setForceLtr('email');
			
			$mform->addElement('text', 'email2', get_string('emailagain'), 'maxlength="100" size="25" ');
            $mform->setType('email2', core_user::get_property_type('email'));
            $mform->addRule('email2', get_string('missingemail'), 'required', null, 'client');
            if(isset($mail)) {
				$mform->setDefault('email2', $mail);
			}
			$mform->setForceLtr('email2');
        } else {
            $_POST["email_"] = $_POST['email'];
            $mform->addElement('text', 'email_', get_string('email'), 'maxlength="100" size="40" '.$readonly);
            $mform->setType('email_', core_user::get_property_type('email'));
        }
		
		// Campo para determinar la ocupacion
		if(!$quitalogin) {
            $listaocupaciones[''] ="--- Seleccionar ocupacion ---";
            foreach($ocupaciones as $id => $op_ocupacion){
                $listaocupaciones[$id] = $op_ocupacion->cdesocupacion;
            }
            $mform->addElement('select', 'msn', get_string('ocupacion', 'inea'), $listaocupaciones);
            $mform->addRule('msn', get_string('noocupacion', 'inea'), 'required', null, 'server');
            if(isset($ocupacion)) {
				$mform->setDefault('msn', $ocupacion);
			}
        } else {
            $_POST['msn'] = $ocupaciones[$_POST['msn']]->cdesocupacion;
            $mform->addElement('text', 'msn', get_string('ocupacion','inea'), 'size="60" '.$readonly);
            $mform->setType('msn', PARAM_TEXT);
            $mform->addRule('msn', get_string('noocupacion','inea'), 'required', null, 'server');
        }
		
		// Campos de usuario y password
		if(!$quitalogin) {//Si se va a registrar un usuario, muestro los campos de usuario y password
            $mform->addElement('header', '', get_string('createuserandpass'));
			
			// Campo para el nombre de usuario
			$mform->addElement('text', 'username', get_string('username'), 'maxlength="100" size="16" '.$readonly);
			$mform->setType('username', PARAM_RAW);
			$mform->addRule('username', get_string('missingusername'), 'required', null, 'server');
			$mform->setDefault('username', $idnumber);
			
			// Campo para el password
			/*if (!empty($CFG->passwordpolicy)) {
				$mform->addElement('static', 'passwordpolicyinfo', '', print_password_policy());
			}*/
			$mform->addElement('text', 'password', get_string('password'), 'maxlength="32" size="16" '.$readonly);
			$mform->setType('password', core_user::get_property_type('password'));
			$mform->addRule('password', get_string('missingpassword'), 'required', null, 'server');
			$mform->setDefault('password', $idnumber);
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
		if($quitalogin) {//Sivoy a accesar a un curso y ya estoy registrado
            $mform->addElement('header', '', get_string('elegircursos', 'inea'));
			$mform->addElement('hidden', 'enrolar');
			$mform->setDefault('enrolar', 1);
            $this->_form->_attributes['action']="$CFG->wwwroot/login/enrol.php";
			$mform->addElement('submit', 'enrol', get_string('ircursos', 'inea'));
        } else { //Si el usuario se va a registrar
			// Campo oculto para decirle al formulario que es el paso final del registro
			$mform->addElement('hidden', 'finalizar', '');
			$mform->setType('finalizar', PARAM_TEXT);
			$mform->setDefault('finalizar', true);
		
            $this->add_action_buttons(true, get_string('registro', 'inea'));
        }

		// Establecer las categorías del perfil para los campos
        // profile_signup_fields($mform);

		// Validacion captcha
        if (signup_captcha_enabled()) {
            $mform->addElement('recaptcha', 'recaptcha_element', get_string('security_question', 'auth'), array('https' => $CFG->loginhttps));
            $mform->addHelpButton('recaptcha_element', 'recaptcha', 'auth');
            $mform->closeHeaderBefore('recaptcha_element');
        }
    }

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
		
		if(!isset($data['enrolar'])) {
			$authplugin = get_auth_plugin($CFG->registerauth);
			
			if ($DB->record_exists('user', array('username'=>$data['username'], 'mnethostid'=>$CFG->mnet_localhost_id))) {
				$errors['username'] = get_string('usernameexists');
			} /*else {
				// Check allowed characters.
				if ($data['username'] !== core_text::strtolower($data['username'])) {
					$errors['username'] = get_string('usernamelowercase');
				} else {
					if ($data['username'] !== core_user::clean_field($data['username'], 'username')) {
						$errors['username'] = get_string('invalidusername');
					}
				}
			}*/

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

			/*if ($data['country']==0 || $data['city']==0 || $data['skype']==0) {
				$errors['location'] = get_string('faltaorigen', 'inea');
			} */
		}else{
			//echo "Juan";exit;
			$errors['msn'] = '';
		}

		// Regresar los errores (si existen)
		if (count($errors)){
			return $errors;
		} else {
			return true;
		}
    }
	
	//Force to not submit data
	/*function prevent_submit() {
		$mform = $this->_form;
		
		if($mform->_flagSubmitted == 1){
			$mform->_flagSubmitted = NULL;
		}
	}*/

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
