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
 * Strings for component 'resource', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    mod_resource
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['clicktodownload'] = 'Click {$a} link to download the file.';
$string['clicktoopen2'] = 'Click {$a} link to view the file.';
$string['configdisplayoptions'] = 'Select all options that should be available, existing settings are not modified. Hold CTRL key to select multiple fields.';
$string['configframesize'] = 'When a web page or an uploaded file is displayed within a frame, this value is the height (in pixels) of the top frame (which contains the navigation).';
$string['configparametersettings'] = 'This sets the default value for the Parameter settings pane in the form when adding some new resources. After the first time, this becomes an individual user preference.';
$string['configpopup'] = 'When adding a new resource which is able to be shown in a popup window, should this option be enabled by default?';
$string['configpopupdirectories'] = 'Should popup windows show directory links by default?';
$string['configpopupheight'] = 'What height should be the default height for new popup windows?';
$string['configpopuplocation'] = 'Should popup windows show the location bar by default?';
$string['configpopupmenubar'] = 'Should popup windows show the menu bar by default?';
$string['configpopupresizable'] = 'Should popup windows be resizable by default?';
$string['configpopupscrollbars'] = 'Should popup windows be scrollable by default?';
$string['configpopupstatus'] = 'Should popup windows show the status bar by default?';
$string['configpopuptoolbar'] = 'Should popup windows show the tool bar by default?';
$string['configpopupwidth'] = 'What width should be the default width for new popup windows?';
$string['contentheader'] = 'Content';
$string['displayoptions'] = 'Available display options';
$string['displayselect'] = 'Display';
$string['displayselect_help'] = 'This setting, together with the file type and whether the browser allows embedding, determines how the file is displayed. Options may include:

* Automatic - The best display option for the file type is selected automatically
* Embed - The file is displayed within the page below the navigation bar together with the file description and any blocks
* Force download - The user is prompted to download the file
* Open - Only the file is displayed in the browser window
* In pop-up - The file is displayed in a new browser window without menus or an address bar
* In frame - The file is displayed within a frame below the navigation bar and file description
* New window - The file is displayed in a new browser window with menus and an address bar';
$string['displayselect_link'] = 'mod/file/mod';
$string['displayselectexplain'] = 'Choose display type, unfortunately not all types are suitable for all files.';
$string['dnduploadresource'] = 'Create file resource';
$string['encryptedcode'] = 'Encrypted code';
$string['filenotfound'] = 'File not found, sorry.';
$string['filterfiles'] = 'Use filters on file content';
$string['filterfilesexplain'] = 'Select type of file content filtering, please note this may cause problems for some Flash and Java applets. Please make sure that all text files are in UTF-8 encoding.';
$string['filtername'] = 'Resource names auto-linking';
$string['forcedownload'] = 'Force download';
$string['framesize'] = 'Frame height';
$string['legacyfiles'] = 'Migration of old course file';
$string['legacyfilesactive'] = 'Active';
$string['legacyfilesdone'] = 'Finished';
$string['modifieddate'] = 'Modified {$a}';
$string['modulename'] = 'Actividad INEA';
$string['modulename_help'] = 'The file module enables a teacher to provide a file as a course resource. Where possible, the file will be displayed within the course interface; otherwise students will be prompted to download it. The file may include supporting files, for example an HTML page may have embedded images or Flash objects.

Note that students need to have the appropriate software on their computers in order to open the file.

A file may be used

* To share presentations given in class
* To include a mini website as a course resource
* To provide draft files of certain software programs (eg Photoshop .psd) so students can edit and submit them for assessment';
$string['modulename_link'] = 'mod/resource/view';
$string['modulenameplural'] = 'Files';
$string['notmigrated'] = 'This legacy resource type ({$a}) was not yet migrated, sorry.';
$string['optionsheader'] = 'Display options';
$string['page-mod-resource-x'] = 'Any file module page';
$string['pluginadministration'] = 'File module administration';
$string['pluginname'] = 'File';
$string['popupheight'] = 'Pop-up height (in pixels)';
$string['popupheightexplain'] = 'Specifies default height of popup windows.';
$string['popupresource'] = 'This resource should appear in a popup window.';
$string['popupresourcelink'] = 'If it didn\'t, click here: {$a}';
$string['popupwidth'] = 'Pop-up width (in pixels)';
$string['popupwidthexplain'] = 'Specifies default width of popup windows.';
$string['printintro'] = 'Display resource description';
$string['printintroexplain'] = 'Display resource description below content? Some display types may not display description even if enabled.';
$string['inea:addinstance'] = 'Add a new resource';
$string['resourcecontent'] = 'Files and subfolders';
$string['resourcedetails_sizetype'] = '{$a->size} {$a->type}';
$string['resourcedetails_sizedate'] = '{$a->size} {$a->date}';
$string['resourcedetails_typedate'] = '{$a->type} {$a->date}';
$string['resourcedetails_sizetypedate'] = '{$a->size} {$a->type} {$a->date}';
$string['inea:exportresource'] = 'Export resource';
$string['inea:view'] = 'View resource';
$string['search:activity'] = 'File';
$string['selectmainfile'] = 'Please select the main file by clicking the icon next to file name.';
$string['showdate'] = 'Show upload/modified date';
$string['showdate_desc'] = 'Display upload/modified date on course page?';
$string['showdate_help'] = 'Displays the upload/modified date beside links to the file.

If there are multiple files in this resource, the start file upload/modified date is displayed.';
$string['showsize'] = 'Show size';
$string['showsize_help'] = 'Displays the file size, such as \'3.1 MB\', beside links to the file.

If there are multiple files in this resource, the total size of all files is displayed.';
$string['showsize_desc'] = 'Display file size on course page?';
$string['showtype'] = 'Show type';
$string['showtype_desc'] = 'Display file type (e.g. \'Word document\') on course page?';
$string['showtype_help'] = 'Displays the type of the file, such as \'Word document\', beside links to the file.

If there are multiple files in this resource, the start file type is displayed.

If the file type is not known to the system, it will not display.';
$string['uploadeddate'] = 'Uploaded {$a}';

//***************************************** INEA
$string['infoineaprofile'] = 'Información INEA';
$string['menentere'] = 'Me enteré del curso por:';
$string['newmodule'] = 'Nueva actividad INEA';
$string['maximumattempts'] = 'Número de intentos';
$string['stagesize'] = 'Tamaño de marco/ventana';
$string['width'] = 'Anchura';
$string['package'] = 'Paquete';
$string['nolimit'] = 'Intentos ilimitados';
$string['attempt'] = 'intento';
$string['attempt1'] = '1 intento';
$string['attempts'] = 'intentos';
$string['attemptsx'] = '$a intentos';
$string['height'] = 'Altura';
$string['display'] = 'Mostrar';
$string['options'] = 'Opciones';
$string['iframe'] = 'Ventana actual';
$string['hidden'] = 'Oculto';
$string['popup'] = 'Abrir actividad INEA en una ventana nueva';
$string['resizable'] = 'Permitir el cambio de tamaño de la ventana';
$string['scrollbars'] = 'Permitir desplazamiento de la ventana';
$string['location'] = 'Mostrar la barra de ubicación';
$string['menubar'] = 'Mostrar la barra de menú';
$string['toolbar'] = 'Mostrar la barra de herramientas';
$string['status'] = 'Estatus';

//***************************************** REGISTRO
$string['registro'] = 'Resgistro';
$string['msjnoconfirmado'] = 'El usuario aun no ha confirmado su registro.<br/> Verifique el correo de confirmación en su correo electrónico.';
$string['msjnoregistrado'] = 'El usuario no esta registrado en el sistema o no es educando.<br/> Comprueba que los datos que se proporcionaron sean los correctos.';
$string['datospersonales'] = 'Datos personales';
$string['datosasesor'] = 'Datos del Asesor';
$string['datoseducando'] = 'Datos del Educando';
$string['datosreaponsablee'] = 'Datos del Responsable Estatal';
$string['datosdeltutor'] = 'Datos Del Tutor';
$string['apaterno'] = 'Apellido paterno';
$string['noa3'] = 'Error en apellido paterno';
$string['amaterno'] = 'Apellido materno';
$string['nombres'] = 'Nombre(s)';
$string['numdocumento'] = 'Número de documento o pasaporte';
$string['rfe'] = 'RFE';
$string['norfe'] = 'Falta especificar el RFE';
$string['rfeincorrecto'] = 'Datos del RFE incorrectos';
$string['rfenocoincide'] = 'Los datos no coinciden con el RFE proporcionado';
$string['rfeyaregistrado'] = 'Un usuario con el mismo RFE ya esta registrado en el sistema';
$string['sexo'] = 'Sexo (M/F)';
$string['area'] = 'Área';
$string['puesto'] = 'Puesto';
$string['fechanacimiento'] = 'Fecha de nacimiento(dd/mm/aaaa)';
$string['edad'] = 'Edad';
$string['ocupacion'] = 'Ocupación'; 
$string['missingcity'] = 'Falta la ciudad';
$string['missingcountry'] = 'Falta el país';
$string['missingdescription'] = 'Falta la descripción';
$string['missingemail'] = 'Falta la dirección de correo electrónico';
$string['nonumdocumento'] = 'Falta el número de documento';
$string['nosexo'] = 'Falta especificar género';
$string['noarea'] = 'Falta especificar área';
$string['nopuesto'] = 'Falta especificar puesto';
$string['nofechanacimiento'] = 'Falta especificar fecha de nacimiento';
$string['noefechanacimiento'] = 'Error al especificar fecha de nacimiento';
$string['noedad'] = 'Falta especificar la edad';
$string['missingfullname'] = 'Falta el nombre completo';
$string['noapaterno_'] = 'Error al especificar el apellido paterno';
$string['noapaterno'] = 'Falta el apellido paterno';
$string['noamaterno_'] = 'Error al especificar apellido materno';
$string['noamaterno'] = 'Falta el apellido materno';
$string['nonombres_'] = 'Error al especificar el/los nombre(s)';
$string['nonombres'] = 'Falta el/los nombre(s)';
$string['noocupacion'] = 'Falta Ocupaci&oacute;n';
$string['missingnewpassword'] = 'Falta la nueva contraseña';
$string['missingpassword'] = 'Falta la contraseña';
$string['missingshortname'] = 'Falta el nombre corto';
$string['missingshortsitename'] = 'Falta el nombre corto del sitio';
$string['missingsitedescription'] = 'Falta la descripción del sitio';
$string['datosprofesionales'] = 'Datos profesionales y laborales actualizados';
$string['profesion'] = 'Profesión';
$string['noprofesion'] = 'Falta la profesión';
$string['situacionlaboral'] = 'Situación laboral';
$string['tipousuario'] = 'Tipo de Usuario';
$string['nosituacionlaboral'] = 'Falta indicar su situación Laboral';
$string['ambitolaboral'] = 'Ámbito laboral';
$string['noambitolaboral'] = 'Falta indicar el ámbito laboral';
$string['tipoinstitucion'] = 'Tipo de institución';
$string['notipoinstitucion'] = 'Falta el tipo de institución';
$string['institucion'] = 'Institución';
$string['noinstitucion'] = 'Falta la institución';
$string['division'] = 'División';
$string['nodivision'] = 'Falta la división';
$string['cargo'] = 'Cargo';
$string['nocargo'] = 'Falta la cargo';
$string['correspondencia'] = 'Datos para correspondencia';
$string['asesor'] = 'Datos del Asesor';
$string['calleno'] = 'Calle y Número';
$string['nocalleno'] = 'Falta Calle y Número';
$string['ciudad'] = 'Ciudad';
$string['entidad'] = 'Entidad';
$string['estado'] = 'Estado';
$string['municipio'] = 'Municipio';
$string['selectestado'] = 'Seleccione Provincia/Estado';
$string['selectpais'] = 'Seleccione el País';
$string['noestado'] = 'Falta especificar la Provincia/Estado';
$string['cp'] = 'Código Postal';
$string['telefono'] = 'Teléfono de contacto';
$string['notelefono'] = 'Falta teléfono de contacto';
$string['pais'] = 'País';
$string['nopais'] = 'Falta especificar el País';
$string['plazacomunitaria'] = 'Plaza comunitaria';
$string['noplazacomunitaria'] = 'Falta especificar plaza comunitaria';
$string['plaza'] = 'Plaza';
$string['noplaza'] = 'Falta la plaza';
$string['zona'] = 'Zona';
$string['nozona'] = 'Falta la zona';
$string['instituto'] = 'Instituto';
$string['noinstituto'] = 'Falta el instituto';
$string['modelo'] = 'Modelo';
$string['nomodelo'] = 'Falta el modelo';
$string['idsasa'] = 'ID de SASA';
$string['elegircursos'] = 'Elegir cursos';
$string['ircursos'] = 'Ir a los cursos';
$string['faltaorigen'] = 'Falta especificar el origen correctamente';
$string['usuarioyaregistrado'] = 'Un usuario con el mismo RFE ya está registrado en el sistema.';
$string['usersreg'] = 'Usuarios registrados';

//***************************************** PAÍS
$string['mx'] = 'México';
$string['eu'] = 'Estados Unidos';

//***************************************** ESTADOS
$string['AD'] = 'Aguas Calientes';
$string['AE'] = 'Baja California';
$string['AF'] = 'Baja California Sur';
$string['AG'] = 'Campeche';
$string['AI'] = 'Chiapas';
$string['AL'] = 'Chihuahua';
$string['AM'] = 'Coahuila';
$string['AN'] = 'Colima';
$string['AO'] = 'Durango';
$string['AQ'] = 'Guanajuato';
$string['AR'] = 'Guerrero';
$string['AS'] = 'Hidalgo';
$string['AT'] = 'Jalisco';
$string['AU'] = 'México';
$string['AW'] = 'Michoacan';
$string['AZ'] = 'Morelos';
$string['BA'] = 'Nayarit';
$string['BB'] = 'Nuevo León';
$string['BD'] = 'Oaxaca';
$string['BE'] = 'Puebla';
$string['BF'] = 'Querétaro';
$string['BG'] = 'Quintana Roo';
$string['BH'] = 'San Luis Potosí';
$string['BI'] = 'Sinaloa';
$string['BJ'] = 'Sonora';
$string['BM'] = 'Tabasco';
$string['BN'] = 'Tamaulipas';
$string['BO'] = 'Tlaxcala';
$string['BR'] = 'Veracruz';
$string['BS'] = 'Yucatán';
$string['BT'] = 'Zacatecas';

//***************************************** ENROLAMIENTO
$string['eventenrolamientoinea'] = 'Enrolamiento de usuarios en el MEVyt';