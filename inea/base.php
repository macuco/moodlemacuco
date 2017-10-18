<?php

require_once('XML_ERROR.php');

class base{
	var $parser;
	var $already_processed;
	var $path_archivo;

	function base(){
		//$this->PEAR();
		 
		$this->already_processed = false;
		 
		$this->parser = xml_parser_create('UTF-8');

		xml_set_object($this->parser,$this);
		xml_parser_set_option($this->parser,XML_OPTION_SKIP_WHITE,1);
		xml_set_element_handler($this->parser,"startElement","endElement");
		xml_set_character_data_handler($this->parser,"characterData");
		xml_set_default_handler($this->parser,"defaultHandler");
		xml_set_processing_instruction_handler($this->parser,"piHandler");
		 
	}

	/*Destructor*/
	function _base(){
		if(is_resource($this->parser)){
			xml_parser_free($this->parser);
		}
	}

	function parse($data){
		if(!xml_parse($this->parser,$data)){
			return new XML_ERROR($this->parser);
		}else{
			return true;
		}
	}
	function startElement($parser, $name, $attr)
	{
	}

	function endElement($parser, $name)
	{
	}

	function characterData($parser, $data)
	{
	}

	function defaultHandler($parser, $data)
	{

	}
	function piHandler($parser, $target, $data)
	{

	}

	function is_file_already_processed()
	{

	}
}
?>
