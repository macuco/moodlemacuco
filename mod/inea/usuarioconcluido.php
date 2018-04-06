<?php

    require_once('../../config.php');
    require_once($CFG->libdir.'/adminlib.php');
    require_once($CFG->libdir.'/authlib.php');
    require_once($CFG->dirroot.'/user/filters/lib.php');
    require_once($CFG->dirroot.'/user/lib.php');
    if(file_exists($CFG->dirroot . '/mod/inea/inealib_jmp.php')){
        require_once($CFG->dirroot . '/mod/inea/inealib_jmp.php');
    }
    if(file_exists($CFG->dirroot . '/mod/inea/inealib.php')){
        require_once($CFG->dirroot . '/mod/inea/inealib.php');
    }

    //$confirm      	= optional_param('confirm', '', PARAM_ALPHANUM);   //md5 confirmation hash
	//$confirmuser  	= optional_param('confirmuser', 0, PARAM_INT);
	//$deleteuser     = optional_param('deleteuser', 0, PARAM_INT);
    $updateuser  	= optional_param('updateuser', 0, PARAM_INT); // INEA - Id de usuario
    $groupid  		= optional_param('groupid', 0, PARAM_INT); // INEA - Id de grupo
    $roleid  		= optional_param('roleid', EDUCANDO, PARAM_INT); // INEA - Educando por Default
    $sort         	= optional_param('sort', 'courseid', PARAM_ALPHANUM); // Order by courseid
    $dir          	= optional_param('dir', 'ASC', PARAM_ALPHA);
    $page         	= optional_param('page', 0, PARAM_INT);
    $perpage      	= optional_param('perpage', 30, PARAM_INT);        // how many per page
    //$ru           = optional_param('ru', '2', PARAM_INT);            // show remote users
    //$lu           = optional_param('lu', '2', PARAM_INT);            // show local users
    //$acl          = optional_param('acl', '0', PARAM_INT);           // id of user to tweak mnet ACL (requires $access)
    //$suspend      = optional_param('suspend', 0, PARAM_INT);
    //$unsuspend    = optional_param('unsuspend', 0, PARAM_INT);
    //$unlock       = optional_param('unlock', 0, PARAM_INT);
	$icvepais     	= optional_param('icvepais', 1, PARAM_INT); // INEA - Mexico por default
	$icveentfed   	= optional_param('icveentfed', 0, PARAM_INT); // INEA - Filtro para Entidad
	$courseid    	= optional_param('courseid', 0, PARAM_INT); // INEA - Filtro por Curso    
    
	//admin_externalpage_setup('editusers');
	$PAGE->set_url('/mod/inea/usuarioconcluido.php', array('sort' => $sort, 'dir' => $dir, 'perpage' => $perpage, 'page'=>$page)); // INEA - Agregar filtro por entidad al url
	$PAGE->set_pagelayout('admin');
    
	$sitecontext = context_system::instance();
    $site = get_site();

	require_login($site);
	
    if (!has_capability('moodle/user:update', $sitecontext) and !has_capability('moodle/user:delete', $sitecontext)) {
        print_error('nopermissions', 'error', '', 'edit/delete users');
    }
	
    $strupdate   = get_string('update');
    $strdelete = get_string('delete');
	$strconfirm = get_string('confirm');
    //$strdeletecheck = get_string('deletecheck');
    //$strshowallusers = get_string('showallusers');
    //$strsuspend = get_string('suspenduser', 'admin');
    //$strunsuspend = get_string('unsuspenduser', 'admin');
    //$strunlock = get_string('unlockaccount', 'admin');
    
    if (empty($CFG->loginhttps)) {
        $securewwwroot = $CFG->wwwroot;
    } else {
        $securewwwroot = str_replace('http:','https:',$CFG->wwwroot);
    }
	
	// INEA - Verificar si el usuario actual es responsable estatal
	$isresponsable = false;
	$isadmin = false;
	$entidadresponsable = 0;
	$currentuser = $DB->get_record('user', array('id' => $USER->id), '*', MUST_EXIST);
	if($myroles = inea_get_system_roles($currentuser->id)) {
		foreach($myroles as $id_rol=>$nombre_rol) {
			// Es responasable estatal ?
			if($id_rol == RESPONSABLE) {
				$isresponsable = true;
				$entidadresponsable = isset($currentuser->institution)? $currentuser->institution : 0;
				break;
			}
		}
	}
	//exit;
	// INEA - Si es responsable estatal filtrar usuarios por entidad
	if($entidadresponsable) {
		$icveentfed = $entidadresponsable;
	} 

	// INEA - Mostrar opcion de filtrado por entidad si es administrador
	$admins = get_admins();
	foreach($admins as $admin) {
		if ($USER->id == $admin->id) {
			$isadmin = true;
			break;
		}
	}

	$urlparams = array('sort' => $sort, 'dir' => $dir, 'perpage' => $perpage, 'page'=>$page);
	
	// INEA - Validar si viene de un curso
	if(!empty($courseid)) {
		$urlparams = array_merge($urlparams, array('courseid' => $courseid));
	}
	
	$returnurl = new moodle_url('/mod/inea/usuarioconcluido.php', $urlparams);

	// INEA - Verificar si es admin o responsable estatal
	if(!$isadmin && !$isresponsable) {
		print_error('nopermissions', 'error', '', 'edit/delete users');
	}
	
    // The $user variable is also used outside of these if statements.
    $user = null;
	
    /*if ($confirmuser and confirm_sesskey()) {
        require_capability('moodle/user:update', $sitecontext);
        if (!$user = $DB->get_record('user', array('id'=>$confirmuser, 'mnethostid'=>$CFG->mnet_localhost_id))) {
            print_error('nousers');
        }

        $auth = get_auth_plugin($user->auth);

        $result = $auth->user_confirm($user->username, $user->secret);

        if ($result == AUTH_CONFIRM_OK or $result == AUTH_CONFIRM_ALREADY) {
			// INEA -- Poner el id del rol en el campo url
			if($roleid) {
				$user->url = $roleid;
				if($DB->update_record('user', $user)) {
				}
			}
            redirect($returnurl);
        } else {
            echo $OUTPUT->header();
            redirect($returnurl, get_string('usernotconfirmed', '', fullname($user, true)));
        }

    } else if ($deleteuser and confirm_sesskey()) {              // Delete a selected user, after confirmation
        require_capability('moodle/user:delete', $sitecontext);

        $user = $DB->get_record('user', array('id'=>$deleteuser, 'mnethostid'=>$CFG->mnet_localhost_id), '*', MUST_EXIST);

        if ($user->deleted) {
            print_error('usernotdeleteddeleted', 'error');
        }
        if (is_siteadmin($user->id)) {
            print_error('useradminodelete', 'error');
        }

        if ($confirm != md5($deleteuser)) {
            echo $OUTPUT->header();
            $fullname = fullname($user, true);
            echo $OUTPUT->heading(get_string('deleteuser', 'admin'));

            $optionsyes = array('delete'=>$deleteuser, 'confirm'=>md5($deleteuser), 'sesskey'=>sesskey());
            $deleteurl = new moodle_url($returnurl, $optionsyes);
            $deletebutton = new single_button($deleteurl, get_string('delete'), 'post');

            echo $OUTPUT->confirm(get_string('deletecheckfull', '', "'$fullname'"), $deletebutton, $returnurl);
            echo $OUTPUT->footer();
            die;
        } else if (data_submitted()) {
            if (delete_user($user)) {
                \core\session\manager::gc(); // Remove stale sessions.
                redirect($returnurl);
            } else {
                \core\session\manager::gc(); // Remove stale sessions.
                echo $OUTPUT->header();
                echo $OUTPUT->notification($returnurl, get_string('deletednot', '', fullname($user, true)));
            }
        }
    }*/
	
	if ($updateuser) {
        require_capability('moodle/user:update', $sitecontext);
        if (!$user = $DB->get_record('user', array('id'=>$updateuser, 'mnethostid'=>$CFG->mnet_localhost_id))) {
            print_error('nousers');
        }
		
		if(!$group = $DB->get_record('groups', array('id'=>$groupid))) {
			print_error('nogroup', 'group');
		}
		
		// Fecha actual del sistema
		$fecha_actual = time();
	
		// Actualizar la fecha de conclusion de curso
		$DB->set_field('groups_members', 'fecha_concluido', $fecha_actual, array('groupid' => $group->id, 'userid' => $user->id));
		
        redirect($returnurl);
    }/* else if ($acl and confirm_sesskey()) {
        if (!has_capability('moodle/user:update', $sitecontext)) {
            print_error('nopermissions', 'error', '', 'modify the NMET access control list');
        }
        if (!$user = $DB->get_record('user', array('id'=>$acl))) {
            print_error('nousers', 'error');
        }
        if (!is_mnet_remote_user($user)) {
            print_error('usermustbemnet', 'error');
        }
        $accessctrl = strtolower(required_param('accessctrl', PARAM_ALPHA));
        if ($accessctrl != 'allow' and $accessctrl != 'deny') {
            print_error('invalidaccessparameter', 'error');
        }
        $aclrecord = $DB->get_record('mnet_sso_access_control', array('username'=>$user->username, 'mnet_host_id'=>$user->mnethostid));
        if (empty($aclrecord)) {
            $aclrecord = new stdClass();
            $aclrecord->mnet_host_id = $user->mnethostid;
            $aclrecord->username = $user->username;
            $aclrecord->accessctrl = $accessctrl;
            $DB->insert_record('mnet_sso_access_control', $aclrecord);
        } else {
            $aclrecord->accessctrl = $accessctrl;
            $DB->update_record('mnet_sso_access_control', $aclrecord);
        }
        $mnethosts = $DB->get_records('mnet_host', null, 'id', 'id,wwwroot,name');
        redirect($returnurl);

    } else if ($suspend and confirm_sesskey()) {
        require_capability('moodle/user:update', $sitecontext);

        if ($user = $DB->get_record('user', array('id'=>$suspend, 'mnethostid'=>$CFG->mnet_localhost_id, 'deleted'=>0))) {
            if (!is_siteadmin($user) and $USER->id != $user->id and $user->suspended != 1) {
                $user->suspended = 1;
                // Force logout.
                \core\session\manager::kill_user_sessions($user->id);
                user_update_user($user, false);
            }
        }
        redirect($returnurl);

    } else if ($unsuspend and confirm_sesskey()) {
        require_capability('moodle/user:update', $sitecontext);

        if ($user = $DB->get_record('user', array('id'=>$unsuspend, 'mnethostid'=>$CFG->mnet_localhost_id, 'deleted'=>0))) {
            if ($user->suspended != 0) {
                $user->suspended = 0;
                user_update_user($user, false);
            }
        }
        redirect($returnurl);

    } else if ($unlock and confirm_sesskey()) {
        require_capability('moodle/user:update', $sitecontext);

        if ($user = $DB->get_record('user', array('id'=>$unlock, 'mnethostid'=>$CFG->mnet_localhost_id, 'deleted'=>0))) {
            login_unlock_account($user);
        }
        redirect($returnurl);
    }*/
    
    // create the user filter form
	$ffiltering = array('realname' => 1, 'lastname' => 1, 'firstname' => 1, 'username' => 1, 'city' => 1, 'skype' => 1, 'idnumber' => 1, 'concluido' => 0);
	// INEA - Crear filtro para el Curso
	if(!$courseid) {
		$ffiltering = array_merge($ffiltering, array('course' => 0));
	}
	if($isadmin) {
		$ffiltering = array_merge($ffiltering, array('institution' => 0));
	}
	$ufiltering = new user_filtering($ffiltering);
    echo $OUTPUT->header();
    
    // Carry on with the user listing
    $context = context_system::instance();
    $extracolumns = array();//get_extra_user_fields($context, array('idnumber'));
    
    // Get all user name fields as an array.
    //$extracolumns = array();
    $extracolumns[] = 'idnumber';
    $extracolumns[] = 'institution';
    $extracolumns[] = 'city';
    $extracolumns[] = 'skype';
	$extracolumns[] = 'course';
	$extracolumns[] = 'usergroup';
	$extracolumns[] = 'concluido';
	$extracolumns[] = 'fecha_concluido';
    $allusernamefields = get_all_user_name_fields(false, null, null, null, true);
    //print_object($extracolumns);
    $columns = array_merge($allusernamefields, $extracolumns, array('institution', 'city', 'skype', 'course', 'concluido', 'fecha_concluido'));

    //print_object($columns);
    
    foreach ($columns as $column) {
        $string[$column] = inea_get_user_field_name($column);
        if ($sort != $column) {
            $columnicon = "";
            if ($column == "lastaccess") {
                $columndir = "DESC";
            } else {
                $columndir = "ASC";
            }
        } else {
            $columndir = $dir == "ASC" ? "DESC":"ASC";
            if ($column == "lastaccess") {
                $columnicon = ($dir == "ASC") ? "sort_desc" : "sort_asc";
            } else {
                $columnicon = ($dir == "ASC") ? "sort_asc" : "sort_desc";
            }
            $columnicon = $OUTPUT->pix_icon('t/' . $columnicon, get_string(strtolower($columndir)), 'core',
                                            ['class' => 'iconsort']);

        }
        $$column = "<a href=\"usuarioconcluido.php?sort=$column&amp;dir=$columndir\">".$string[$column]."</a>$columnicon";
    }
    
    // We need to check that alternativefullnameformat is not set to '' or language.
    // We don't need to check the fullnamedisplay setting here as the fullname function call further down has
    // the override parameter set to true.
    $fullnamesetting = $CFG->alternativefullnameformat;
    // If we are using language or it is empty, then retrieve the default user names of just 'firstname' and 'lastname'.
    if ($fullnamesetting == 'language' || empty($fullnamesetting)) {
        // Set $a variables to return 'firstname' and 'lastname'.
        $a = new stdClass();
        $a->firstname = 'firstname';
        $a->lastname = 'lastname';
        // Getting the fullname display will ensure that the order in the language file is maintained.
        $fullnamesetting = get_string('fullnamedisplay', null, $a);
    }

    // Order in string will ensure that the name columns are in the correct order.
    $usernames = order_in_string($allusernamefields, $fullnamesetting);
    $fullnamedisplay = array();
    foreach ($usernames as $name) {
        // Use the link from $$column for sorting on the user's name.
        $fullnamedisplay[] = ${$name};
    }
    // All of the names are in one column. Put them into a string and separate them with a /.
    $fullnamedisplay = implode(' / ', $fullnamedisplay);
    // If $sort = name then it is the default for the setting and we should use the first name to sort by.
    if ($sort == "name") {
        // Use the first item in the array.
        $sort = reset($usernames);
    }
   
    list($extrasql, $params) = $ufiltering->get_sql_filter();
	
	// INEA - Filtrar por rol EDUCANDO
	if(!$roleid) {
		$roleid = !empty(EDUCANDO)? EDUCANDO : 5;
	}
	
	if($roleid) {
		if(!empty($extrasql)) {
			$extrasql .= ' AND';
		}
		$extrasql .= ' ra.roleid = :roleid ';
		$params = array_merge($params, array('roleid' => $roleid));
	}
	
	// INEA - Filtrar por curso
	if($courseid) {
		if(!empty($extrasql)) {
			$extrasql .= ' AND';
		}
		$extrasql .= ' ug.courseid = :courseid ';
		$params = array_merge($params, array('courseid' => $courseid));
	}
	
	// INEA - Filtrar por entidad federativa
	if ($icveentfed && $isresponsable) {
		if(!empty($extrasql)) {
			$extrasql .= ' AND';
		}
		$extrasql .= ' u.institution = :entidad ';
		$params = array_merge($params, array('entidad' => $icveentfed));
	}
   
	//print_object($extrasql);
	//print_object($params);
    $users = inea_get_users_listing($sort, $dir, $page*$perpage, $perpage, '', '', '',
            $extrasql, $params, $context);
	
	$urlparams = array('sort' => $sort, 'dir' => $dir, 'perpage' => $perpage);
	
	// INEA - Agregar filtrado por Curso a la direccion URL
	if(!empty($courseid)) {
		$urlparams = array_merge($urlparams, array('courseid' => $courseid));
	}
	
	// INEA - Agregar filtrado por entidad federativa a la direccion URL
	if ($icveentfed && ($isresponsable || $isadmin)) {
		$urlparams = array_merge($urlparams, array('icveentfed' => $icveentfed));
	}
    $baseurl = new moodle_url('/mod/inea/usuarioconcluido.php', $urlparams);
    
	$usercount = 0;
	
    //$strall = get_string('all');
   
	/*$countries = get_string_manager()->get_list_of_countries(false);
	if (empty($mnethosts)) {
		$mnethosts = $DB->get_records('mnet_host', null, 'id', 'id,wwwroot,name');
	}

	foreach ($users as $key => $user) {
		if (isset($countries[$user->country])) {
			$users[$key]->country = $countries[$user->country];
		}
	}
	if ($sort == "country") {  // Need to resort by full country name, not code
		foreach ($users as $user) {
			$susers[$user->id] = $user->country;
		}
		asort($susers);
		foreach ($susers as $key => $value) {
			$nusers[] = $users[$key];
		}
		$users = $nusers;
	}*/

	$table = new html_table();
	$table->head = array ();
	$table->colclasses = array();
	$table->head[] = $fullnamedisplay;
	$table->attributes['class'] = 'admintable generaltable';
	//print_object($extracolumns);
	foreach ($extracolumns as $field) {
		//print_object($field);
		//print_object(${$field});
		$table->head[] = ${$field};
	}
	//print_object($table->head);
	//$table->head[] = $city;
	//$table->head[] = $country;
	//$table->head[] = $lastaccess;
	$table->head[] = get_string('update').' '.get_string('fecha_concluido', 'inea');
	$table->colclasses[] = 'centeralign';
	$table->head[] = "";
	$table->colclasses[] = 'centeralign';

	$table->id = "users";
	foreach ($users as $user) {
		$buttons = array();
		$lastcolumn = '';
		$usercount += 1;
		//complete_user_role($user);
		//$user->rol="MACUCO".complete_user_role($user);
		
		// suspend button
		/*if (has_capability('moodle/user:update', $sitecontext)) {
			if (is_mnet_remote_user($user)) {
				// mnet users have special access control, they can not be deleted the standard way or suspended
				$accessctrl = 'allow';
				if ($acl = $DB->get_record('mnet_sso_access_control', array('username'=>$user->username, 'mnet_host_id'=>$user->mnethostid))) {
					$accessctrl = $acl->accessctrl;
				}
				$changeaccessto = ($accessctrl == 'deny' ? 'allow' : 'deny');
				$buttons[] = " (<a href=\"?acl={$user->id}&amp;accessctrl=$changeaccessto&amp;sesskey=".sesskey()."\">".get_string($changeaccessto, 'mnet') . " access</a>)";

			} else {
				if ($user->suspended) {
					$url = new moodle_url($returnurl, array('unsuspend'=>$user->id, 'sesskey'=>sesskey()));
					$buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/show', $strunsuspend));
				} else {
					if ($user->id == $USER->id or is_siteadmin($user)) {
						// no suspending of admins or self!
					} else {
						$url = new moodle_url($returnurl, array('suspend'=>$user->id, 'sesskey'=>sesskey()));
						$buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/hide', $strsuspend));
					}
				}

				if (login_is_lockedout($user)) {
					$url = new moodle_url($returnurl, array('unlock'=>$user->id, 'sesskey'=>sesskey()));
					$buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/unlock', $strunlock));
				}
			}
		}*/

		// edit button
		/*if (has_capability('moodle/user:update', $sitecontext)) {
			// prevent editing of admins by non-admins
			if (is_siteadmin($USER) or !is_siteadmin($user)) {
				$url = new moodle_url($securewwwroot.'/user/editadvanced.php', array('id'=>$user->id, 'course'=>$site->id));
				$buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/edit', $stredit));
			}
		}*/
		
		// the last column - confirm or mnet info
		/*if (is_mnet_remote_user($user)) {
			// all mnet users are confirmed, let's print just the name of the host there
			if (isset($mnethosts[$user->mnethostid])) {
				$lastcolumn = get_string($accessctrl, 'mnet').': '.$mnethosts[$user->mnethostid]->name;
			} else {
				$lastcolumn = get_string($accessctrl, 'mnet');
			}

		} else if ($user->confirmed == 0) {
			if (has_capability('moodle/user:update', $sitecontext)) {
				$lastcolumn = html_writer::link(new moodle_url($returnurl, array('confirmuser'=>$user->id, 'sesskey'=>sesskey())), $strconfirm);
			} else {
				$lastcolumn = "<span class=\"dimmed_text\">".get_string('confirm')."</span>";
			}
		}*/

		/*if ($user->lastaccess) {
			$strlastaccess = format_time(time() - $user->lastaccess);
		} else {
			$strlastaccess = get_string('never');
		}*/
		
		// INEA - boton de actualizar usuario
		if (($user->concluido > 0) && has_capability('moodle/user:update', $sitecontext)) {
			// prevent editing of admins by non-admins
			if ($isadmin or !is_siteadmin($user)) {
				$urlparams = array_merge($urlparams, array('updateuser' => $user->id, 'groupid' => $user->usergroup));
				$url = new moodle_url('/mod/inea/usuarioconcluido.php', $urlparams);
				$buttons[] = html_writer::link($url, $OUTPUT->pix_icon('i/reload', get_string('update')));
			}
		}
		
		// INEA - boton de confirmar usuario
		/*if ($user->confirmed == 0 && has_capability('moodle/user:update', $sitecontext)) {
			if (is_mnet_remote_user($user) or $user->id == $USER->id or is_siteadmin($user)) {
				// no mostrar boton
			} else {
				$url = new moodle_url($returnurl, array('confirmuser'=>$user->id, 'roleid' => $user->role, 'sesskey'=>sesskey()));
				$buttons[] = html_writer::link($url, $OUTPUT->pix_icon('i/checkpermissions', $strconfirm));
			}
		}*/
		
		// INEA - boton de borrar usuario
		/*if (has_capability('moodle/user:delete', $sitecontext)) {
			if (is_mnet_remote_user($user) or $user->id == $USER->id or is_siteadmin($user)) {
				// no deleting of self, mnet accounts or admins allowed
			} else {
				
				$url = new moodle_url($returnurl, array('deleteuser'=>$user->id, 'sesskey'=>sesskey()));
				$buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/delete', $strdelete));
			}
		}*/
		
		$fullname = fullname($user, true);
		
		//$usertemp = $DB->get_record('user', array('id'=>$user->id));
		
		// INEA - Obtener Municipio
		if(!empty($user->institution) && !empty($user->city)) {
			$municipio = inea_get_municipio($user->country, $user->institution, $user->city);
			$user->city = isset($municipio->cdesmunicipio)? $municipio->cdesmunicipio : '';
		}
		
		// INEA - Obtener Entidad
		if(!empty($user->institution)) {
			$entidad = inea_get_entidad($user->country, $user->institution);
			//print_object($entidad);
			$user->institution = isset($entidad->cdesentfed)? $entidad->cdesentfed : ''; 
		}
		
		// INEA - Obtener Plaza
		if(!empty($user->skype)) {
			$plaza = inea_get_plaza($user->skype);
			$user->skype = isset($plaza->cnomplaza)? $plaza->cnomplaza : '';
		}
		
		// INEA - Obtener nombre del Curso
		if(!empty($user->role)) {
			$user->role = inea_get_valorcampo('role', 'name', array('id' => $user->role));
		}
		
		// INEA - Obtener nombre del Curso
		if(!empty($user->course)) {
			$user->course = inea_get_valorcampo('course', 'fullname', array('id' => $user->course));
		}
		
		// INEA - Obtener nombre del Curso
		if(!empty($user->usergroup)) {
			$user->usergroup = inea_get_valorcampo('groups', 'name', array('id' => $user->usergroup));
		}
		
		// INEA - Mostrar concluido SI/NO
		$lconcluido = array(0 => 'No', 1 => 'Si');
		$user->concluido = isset($lconcluido[$user->concluido])? $lconcluido[$user->concluido] : 'No';
		
		// INEA - Mostrar fecha de conclusion
		$user->fecha_concluido = !empty($user->fecha_concluido)? date('d-m-Y', $user->fecha_concluido) : 'N/A';
		
		$row = array();
		$row[] = "<a href=\"../../user/view.php?id=$user->id&amp;course=$site->id\">$fullname</a>";
		//print_object($extracolumns);
		//print_object($user);
		foreach ($extracolumns as $field) {
			$row[] = $user->{$field};
		}       
		//$row[] = $omunicipio;
		//$row[] = $user->country;
		//$row[] = $strlastaccess;
		/*if ($user->suspended) {
			foreach ($row as $k=>$v) {
				$row[$k] = html_writer::tag('span', $v, array('class'=>'usersuspended'));
			}
		}*/
		$row[] = implode(' ', $buttons);
		$row[] = $lastcolumn;
		$table->data[] = $row;
	}
    
	// INEA - Si existen estudiantes mostrar la lista
	if($usercount > 0) {
		echo $OUTPUT->heading("$usercount ".get_string('students')."<br>");
	} else {
		$match = array();
		echo $OUTPUT->heading(get_string('nousersfound')."<br>");

        $table = NULL;
	}
	
	echo $OUTPUT->paging_bar($usercount, $page, $perpage, $baseurl);
	
	flush();
	
    // add filters
    $ufiltering->display_add();
    $ufiltering->display_active();
    
    if (!empty($table)) {
        echo html_writer::start_tag('div', array('class'=>'no-overflow'));
        echo html_writer::table($table);
        echo html_writer::end_tag('div');
        echo $OUTPUT->paging_bar($usercount, $page, $perpage, $baseurl);
    }
    /*if (has_capability('moodle/user:create', $sitecontext)) {
        $url = new moodle_url($securewwwroot . '/user/editadvanced.php', array('id' => -1));
        echo $OUTPUT->single_button($url, get_string('addnewuser'), 'get');
    }*/

    echo $OUTPUT->footer();
