<?php
/*
 Nombre: url_class.php
 Fecha de creaci�: 8-Agosto-02
 �tima modificaci�: 28-Agosto-02
 Descripci�:
 - Crea un parser XML estilo SAX
 - Modifica las rutas de las ligas
 - Renombra los controles de las formas HTML
 Flavio Lozano morales
 flavio.lozano@gmail.com
 Scripts relacionadaos: dir.php
 Versi�: 1.0
 */


//require_once(DIR_CONFIG.'/'.NOM_DIR_CURSO.'/dir.conf');

require_once('base.php');
require_once('util.php');

//FLM:require_once('../../admin/modulo_editores/editores_class2.php');
//$contextid    = optional_param('contextid', 0, PARAM_INT); 
 

  
  
  

class xml extends base {
	var $parser;
	var $d;
	var $direc;
	var $self;
	var $start_to_copy;
	var $is_radio;
	var $radio_id;
	var $value;
	var $id;
	var $path_archivo;
	var $data;
	var $u;
	var $wd;
	var $invitado;
	// Flavio Lozano
	// Detectar si estamos hablando de una pelicula de FLASH
	var $objeto = '';
	var $iniciaFlash = false;
	var $funcionflash = false;
	var $tutor = 0;

	// Steel
	var $buttonsReady = false;
	
	

	function xml(){

		$this->d = '';
		$this->value = 0;
		$this->id = 0;
		$this->u = new util();
		parent::base();



	}

	
	
	
	
	function set_bad_files($array){
		$this->bad_files = $array;
	}

	function parse($data,$path_archivo){

		$this->data = $data;
		$this->path_archivo = $path_archivo;


		//$this->restore_array();

		return parent::parse($data);

	}


	function startElement($parser,$name,$attr)
	{
		
		

		if($name == 'PARAM')
		{
			//$attr['VALUE'] = $this->new_path($attr['VALUE']);
			// Flavio Lozano Permitir enviar parametros del INEA a Flash
			if (strtolower($attr['NAME']) == "movie" )
			$this->parametrosFlash($attr, 'VALUE');
		}
		// Flavio lozano Permitir enviar parametros del INEA a Flash
		if($name=="EMBED") {
			if (strpos($attr['SRC'],'.swf') !== false) {
				$this->parametrosFlash($attr, 'SRC');
			}
		}

		$this->copyStartTag($name,$attr);

		/****************************************************************************************
		 Seccion para insertar los botones de 'Cancelar' y 'Guardar Respuestas'
		 para que los botones sean insertados en cualquier parte,
		 dentro de la pagina XHTML, debera de existir
		 la etiqueta '<input type="hidden" name="boton_enviar" value="titulo_actividad" />'
		 dentro de dicha pagina
		 ****************************************************************************************/

		if($name == "INPUT" && $attr['NAME']  == 'boton_enviar' && $attr['TYPE'] == 'hidden' && $this->u->has_html_form($this->data) === true ) {
			$this->d .= $this->addButtonsToForm();
			//los botones han sido activados
			$this->buttonsReady = true;
		}

		/******** Fin de seccion para insertar los botones de 'Cancelar' y 'Guardar Respuestas' ********/
		$this->estado = 1;


	}//function

	/****************************************************************************************
	 Funcion que regresa los elementos a ser insertados como parte de los botones
	 'Cancelar' y 'Guardar Respuestas'
	 Steel E. V. George
	 ****************************************************************************************/

	function addButtonsToForm()
	{


	
		if ( ($this->tutor==1 ) || ( $this->u->tiene_no_guarda($this->data) === true  ) )
		$include_end_form = 'inea/include_hidden2.htm';
		else
		$include_end_form = 'inea/include_hidden.htm';
		
		
		
		
		$file = $this->u->get_file_from_url($include_end_form);

		if($file!==false) {
			$file = str_replace('{path_actual}',$this->path_archivo,$file);
			$file = str_replace('{start_time}',time(),$file);
			$file = str_replace('{url_ruta}',$this->url_ruta,$file);
		}


		return $file;
	}

	// Flavio Lozano aumentar parametros de Flash a la Pelicula
	function parametrosFlash(& $attr, $valor) {
		$nivel = $this->editor?2:($this->tutor?1:0);
		$width = empty($attr['WIDTH']) ? intval($this->fw) : intval($attr['WIDTH']);
		$height = empty($attr['HEIGHT']) ? intval($this->fh) : intval($attr['HEIGHT']);

		$url = strpos($attr[$valor], '?') === false ? '?' : '&';
		$swforig = $attr[$valor];
//		echo "invitado".$this->invitado;
//		echo "tutor".$this->tutor;
//		die();
//		$attr[$valor] = $this->url_ruta."/loader.swf?swf=$swforig&id_alumno=".$this->id."&acceso_tutor={$this->tutor}&ruta_pagina=".urlencode($this->path_archivo)."&nivel=".$nivel."&ineaid=".$this->ineaid."&rutabase=".$this->url_ruta."/";
$attr[$valor] = $this->url_ruta."/loader.swf?swf=$swforig&id_alumno=".$this->id."&acceso_tutor={$this->tutor}&ruta_pagina=".urlencode($this->path_archivo)."&nivel=".$nivel."&invitado=".$this->invitado."&ineaid=".$this->ineaid."&rutabase=".$this->url_ruta."/";
		$mostrar = false;
		//if (($this->editor || !$this->tutor) &&($width > 120 && $height > 120)) {
		if (($this->editor || !$this->tutor) &&($width > 80 && $height > 80)) {
			$obj = get_record_select('inea_detail', "ineaid=".$this->ineaid." and url='".$this->path_archivo."' and swf='".$this->rutaSwf($this->path_archivo,$swforig)."'");
			if ($this->editor) {
				if(isset($attr['HEIGHT']))
					$attr['HEIGHT'] = intval($attr['HEIGHT'])+40;
				else $attr['HEIGHT'] = 40;
				$mostrar = true;
				if ($obj) {
					$attr[$valor] .= "&calificacion=".$obj->maxgrade."&activo=".$obj->active."&titulo=".$obj->title;
				}
			} elseif (!$this->tutor) {
				if ($obj && $obj->active == 1) {
					$mostrar = true;
					if(isset($attr['HEIGHT']))
						$attr['HEIGHT'] = intval($attr['HEIGHT'])+40;
					else $attr['HEIGHT'] = 40;
				}
			}
			if ($this->invitado) {
				$mostrar = false;
			}

		}
		if ($mostrar) {
			$attr[$valor] .= "&barra=si";
			$obj = get_record_select('inea_answers', "ineaid=".$this->ineaid." and userid=".$this->id." and url='".$this->path_archivo."' and swf='".$this->rutaSwf($this->path_archivo,$swforig)."'");
			if ($obj) {
				$attr[$valor] .= "&contestado=si";
			}
		} elseif ($this->tutor) {
			$obj = get_record_select('inea_answers', "ineaid=".$this->ineaid." and userid=".$this->id." and url='".$this->path_archivo."' and swf='".$this->rutaSwf($this->path_archivo,$swforig)."'");
			if ($obj) {
				$attr[$valor] .= "&contestado=si";
			}
		}
	}
	function rutaSwf($base, $swf) {
		$r2 = dirname($base).'/'.$swf;
		$r = '';
		while ($r2 != $r) {
			$r = $r2;
			$r2 = ereg_replace("/[A-Za-z0-9_]+/\.\.", '',$r2);
		}
		return $r;
	}

	/* Flavio Lozano Convertir Flash a javascript para saber si se tiene instalado Flash*/
	function copyStartTag($name,$attr) {
		if (($name=="OBJECT" && $attr['CLASSID'] == "clsid:D27CDB6E-AE6D-11cf-96B8-444553540000")) {
			$this->iniciaFlash = true;
			$this->fw = $attr['WIDTH'];
			$this->fh = $attr['HEIGHT'];
			if (($this->editor || !$this->tutor) &&(intval($attr['WIDTH']) > 80 && intval($attr['HEIGHT']) > 80)) {
				$attr['HEIGHT'] = intval($attr['HEIGHT'])+40;
			}
		}

		$r = '';
		$r .= strtolower("<$name");
		//Copia los atributos
		foreach($attr as $key => $value){
			$r .= strTolower(" $key=")."\"$value\"";
		}

		if($this->is_empty_tag($name))
		$r .= sprintf("/>");
		else
		$r .= sprintf(">");


		if ($this->iniciaFlash) {
			$this->objeto .= $r;
		}
		else {
			$r  .= $this->includeStartForm($name);
			$this->d .= $r;
		}
	}
	// Flavio Lozano Convertir Javascript Flash
	function convertirjava($texto) {
		$t = addslashes($texto);
		$t = str_replace("\r","", $t);
		$t = str_replace("\n","');\n  document.write('", $t);
		return "  document.write('$t');";
	}
	// Flavio Lozano finalizar conversio Flash
	function terminaScript($name) {
		if(!$this->is_empty_tag($name))
		$this->objeto .= strToLower("</$name>");

		if ($name=="OBJECT") {
			$this->iniciaFlash = false;
			$this->objeto = $this->convertirjava($this->objeto);

			$error = $this->u->get_file_from_url("obtener_flash_player.htm");
			$error = $this->convertirjava($error);

			$this->d .= "<script language='Javascript'>\n<!--\n";
			if (!$this->funcionflash) {
				$this->d .= "function _flashinstalado() {if (document.all && navigator.mimeTypes[\"application/x-shockwave-flash\"] == null) {if (!this._validaExplorer) document.write('<scri'+'pt>function _validaExplorer() { try {var xObj = new ActiveXObject(\"ShockwaveFlash.ShockwaveFlash\"); xObj=null; return true; } catch (e) { return false; }}  </scri'+'pt>'); return _validaExplorer();} else return navigator.mimeTypes && navigator.mimeTypes[\"application/x-shockwave-flash\"]; } \n";
				$this->funcionflash = true;
			}

			$this->d .= "if (_flashinstalado()) { \n".
			$this->objeto."\n".
			"} else {\n".
			$error."\n".
			"} \n//-->\n</script>";
			$this->objeto = '';
		}
	}
	function copyEndTag($name)
	{
		// Floz deteccion Flash
		if ($this->iniciaFlash) {
			$this->terminaScript($name);
		} else {
			if(!$this->is_empty_tag($name)){
				if ($name == 'HEAD') {
					global $USER, $cursoid;
					if(isset($USER->javascript[$cursoid]))
						$this->d .= $USER->javascript[$cursoid];
				}
				$this->d .= strToLower("</$name>");
			}
		}
	}

	function characterData($parser,$data){
		// Floz javascript FLASH
		if ($this->iniciaFlash) {
			$this->objeto .= $data;
		} else {
			$this->d .= $data;
		}
		//echo "==$data== ";
	}

	function includeStartForm($name)
	{
		if($name=='BODY' && $this->u->has_html_form($this->data)===true){

			$file = $this->u->get_file_from_url('inea/include_form.htm');


			if($file!==false) {
				return  str_replace('{form}','',$file);

			}
		}

	}

	function includeEndForm($name)
	{
		if($name=='BODY' && $this->u->has_html_form($this->data)===true)
		{

			// Si no existen botones de guardar respuestas, entonces a�dirlos a la pagina
			if ($this->editor) {
				$obj = get_record('inea_detail', 'ineaid', $this->ineaid, 'url', $this->path_archivo,'swf','null');
				$det = get_record('inea', 'id', $this->ineaid);
				if ($obj){
					$titulo = $obj->title;
					$calificacion = choose_from_menu(make_grades_menu($det->maxgrade),'calificacion',$obj->maxgrade,'choose','','0',true);
					$activo = choose_from_menu(array(1=>'Si', 0=>'No'),'activo',$obj->active,'choose','','0',true);
				} else {
					$titulo = '';
					$calificacion = choose_from_menu(make_grades_menu($det->maxgrade),'calificacion','','choose','','0',true);
					$activo = choose_from_menu(array(1=>'Si', 0=>'No'),'activo',1,'choose','','0',true);
				}
				//print_object($obj);
				$include_end_form = 'inea/include_hidden3.htm';
				$file = $this->u->get_file_from_url($include_end_form);
				if($file!==false) {
					//choose_from_menu(make_grades_menu(100),'grades[]',$r->grade,'choose','','0',true)
					$file = str_replace('{titulo}',$titulo,$file);
					$file = str_replace('{calificacion}',$calificacion,$file);
					$file = str_replace('{activo}',$activo,$file);
					$file = str_replace('{url_ruta}',$this->url_ruta,$file);
					$file    .= '</form>';
				}

			} elseif( !$this->buttonsReady ) {

//	if (empty($this->tutor) && empty($this->editor)){
//			$include_end_form = 'inea/include_hidden2.htm';
//			
//		}

//judaav si es invitado no mostramos la imagen de guardar

				
				
				if ( ( $this->tutor == 1 ) || ( $this->u->tiene_no_guarda($this->data) === true  ) || $this->invitado ) {
					$include_end_form = 'inea/include_hidden2.htm';
				} else {
					$include_end_form = 'inea/include_hidden.htm';
				}

				$file = $this->u->get_file_from_url($include_end_form);

				if($file!==false) {
					$file = str_replace('{path_actual}',$this->path_archivo,$file);
					$file = str_replace('{start_time}',time(),$file);
					$file = str_replace('{url_ruta}',$this->url_ruta,$file);
					$file    .= '</form>';
				}

				/*
				 $include_end_form = INCLUDE_HIDDEN;

				 $file = $this->u->get_file_from_url($include_end_form);

				 if($file!==false)
				 {
				 $file     = str_replace('{path_actual}',$this->path_archivo,$file);
				 $file     = str_replace('{start_time}',time(),$file);
				 $file    .= '</form>';
				 $temp = '';

				 }

				 */
			} else {
				// Steel, ya existe el boton de Guardar respuestas en la pagina en proceso
				$file    = '</form>';
			}

			return $file;

		}
	}

	function endElement($parser,$name){

		$this->d .= $this->includeEndForm($name);

		$this->copyEndTag($name);
		$this->estado = 2;
	}//function

	function piHandler($parser,$target,$data){
	}
	function defaultHandler($parser,$data){

		$this->d .= $data;
		//echo "*$data* ";
	}



	function get_parser(){
		return $this->parser;
	}

	function get_xml(){
		return $this->d;
	}


	function is_empty_tag($tag)
	{
		$empty_tags = array('br','img','input','link','meta','frame','param','embed');

		return (in_array(strtolower($tag),$empty_tags));
	}
}
?>