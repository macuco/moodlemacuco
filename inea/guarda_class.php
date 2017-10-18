<?php
/*
require_once('url_class.php');
require_once('dir.conf');
*/
define('DEFAULT_FIELD_ID', 'NAME');
class guarda extends xml{
	var $parser;
	var $output;
	var $content;
	var $d;
	var $post;
	var $tags_changed;
	var $f;

	function guarda()
	{
		parent::xml();
	}

	function parse($post,$data,$path_archivo)
	{
		$this->post = $post;
		$this->data = $data;
		$this->path_archivo = $path_archivo;
		/*************************************/
		return parent::parse($data,$path_archivo);
		/**************************************/
	}


	function startElement($parser,$name,$attr){

		if($this->is_input_type($name))
		{
			$this->restore_input_values($attr);
		}

		if($this->is_select_type($name))
		{
			$this->restore_select_values($name,$attr);
		}

		/********************/
		parent::startElement($parser,$name,$attr);
		/********************/

		if($this->is_textarea_type($name) )
		{
			$this->restore_textarea_value($attr);

		}


	}//function

	function restore_input_values(&$attr)
	{	
		switch(strtolower($attr['TYPE']))
		{
			case 'text':        if(!$this->is_value_empty($this->post[$attr[DEFAULT_FIELD_ID]]))
			{
				$attr['VALUE'] = $this->post[$attr[DEFAULT_FIELD_ID]];
				
			}
			break;

			case 'hidden':        if(!$this->is_value_empty($this->post[$attr[DEFAULT_FIELD_ID]]))
			{
				$attr['VALUE'] = $this->post[$attr[DEFAULT_FIELD_ID]];
			}
			break;

			case 'checkbox':    if(isset($this->post[$attr[DEFAULT_FIELD_ID]]))
			{
				$attr['CHECKED'] = 'checked';

			}
			break;

			case 'radio':       if($this->post[$attr[DEFAULT_FIELD_ID]]==$attr['VALUE'])
			{
				$attr['CHECKED']='checked';
			}elseif(isset($attr['CHECKED']))
			{
				unset($attr['CHECKED']);
			}

		}
	}

	function restore_select_values($name,&$attr)
	{
		if($name=='SELECT')
		{
			$this->select_name = $attr[DEFAULT_FIELD_ID];
		}else
		{
			if($this->post["$this->select_name"]==$attr['VALUE'])
			{
				$attr['SELECTED']='selected';
			}elseif(isset($attr['SELECTED']))
			{
				unset($attr['SELECTED']);
			}
		}

	}

	function restore_textarea_value($attr)
	{
		if(!$this->is_value_empty($this->post[$attr[DEFAULT_FIELD_ID]]))
		{
			$this->d .= $this->post[$attr[DEFAULT_FIELD_ID]];
		}

	}

	function is_input_type($name)
	{
		return ($name=='INPUT');
	}

	function is_select_type($name)
	{
		return ($name=='SELECT' || $name=='OPTION');
	}


	function is_textarea_type($name)
	{
		return ($name=='TEXTAREA');
	}

	function is_value_empty($value)
	{
		return (trim($value)=='');
	}

}
?>
