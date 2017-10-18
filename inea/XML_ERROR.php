<?php
class XML_ERROR
{
var $msg;
var $code;
var $line;
var $col;

    function XML_ERROR($parser)
    {   
        if( is_resource($parser) )
        {
		    $this->msg   = xml_error_string(xml_get_error_code($parser));
            $this->code  = xml_get_error_code($parser);
		    $this->line  = xml_get_current_line_number($parser);
            $this->col   = xml_get_current_column_number($parser);
        }
    }
	
    function isError($obj)
	{           
		return (is_object($obj) && (strtolower(get_class($obj)) == 'xml_error' || is_subclass_of($obj,'xml_error')) && $obj->code != 27); //FLM: Codigo 27 PHP5
    }

    function getCode()
    {
        return $this->code;
    }

    function getMessage()
    {
        return $this->msg;
    }
	
    function getLine()
    {
        return $this->line;
    }

    function getCol()
    {
        return $this->col;
    }



}
?>