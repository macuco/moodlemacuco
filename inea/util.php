<?php

class util{
    
	
	function util()
	{
	    //$this->PEAR();
	}
	
	function has_html_extension($url)
    {
        if(($this->getext($url)=='htm') || ($this->getext($url)=='html'))
        {
            return true;
        }else{
	        return false;
        }
    }

    function has_php_extension($url)
    {
        return ($this->getext($url)=='php');
    }
    function has_js_extension($url)
    {
        return ($this->getext($url)=='js');
    }
    
    function has_image_extension($url)
    {
        if(($this->getext($url)=='gif') || ($this->getext($url)=='jpg')|| ($this->getext($url)=='png'))
	    {
            return true;
        }else{
	        return false;
        }
    }

    function has_css_extension($url)
    {
        if($this->getext($url)=='css')
	    {
            return true;
        }else{
	        return false;
        }
    }
    
    function has_swf_extension($url)
    {

		/*****************************************************/
		// Floz determinar si se tiene extension flash 
		// Nos permite saber si se tiene la extension flash
        // reemplaza a if($this->getext($url)=='swf')
		if(substr($url,-3)=='swf' || strpos($url, '.swf?') !== false)
		/*****************************************************/
	    {
            return true;
        }else{
	        return false;
        }
    }
    
    
    function has_dcr_extension($url)
    {
        if($this->getext($url)=='dcr')
	    {
            return true;
        }else{
	        return false;
        }
    }
    
    function has_html_form($data)
    {
        if((strpos($data,'<textarea'))!==false || (strpos($data,'<input'))!==false || (strpos($data,'<select'))!==false)
        {
            return true;
        
        }else
        {
            return false;
        }
   
}


    function tiene_no_guarda($data)
    {
        if((strpos($data,'no_guarda'))!==false )
        {
            return true;

        }else
        {
            return false;
        }

}



    
    
    function contains_macromedia_event($event)
    {
        if((strpos($event,"MM_swapImage"))!==false || (strpos($event,"MM_nbGroup"))!==false || (strstr($event,"MM_openBrWindow"))!==false || (strstr($event,"MM_preloadImages"))!==false)
        {
            return true;    
	    }else{
            return false;
	    }
    }
    
	function getext($filename) 
	{
        $f = strrev($filename);
        $ext = substr($f, 0, strpos($f,"."));
        return strrev($ext);
//        return basename($filename);
    }

    function contains_html_form_elements($data)
    {
        if(eregi("<input([^>]*)>",$data) || eregi("<textarea([^>]*)>",$data))
        {
            return true;    
	    }else{
            return false;    
	    }
	
    }

    function get_file_from_url($url)
    {
        if($fp=@fopen($url,'r'))
        {	
            $line = '';	
            while(!feof($fp))
            {
                $line .= fread($fp,4096);		
            }
            fclose($fp);
        
            return $line;    
	    }else{
            return false;
	    }      
    }

    function write_file($path,$content)
    {
        if($fp=@fopen($path,'w'))
        {	
            fwrite($fp,$content);
            fclose($fp);
        
            return true;    
	    }else{
            return false;
	    }      
    }
    
    /*function is_xhtml($data)
    {   $parser = xml_parser_create();
        $result = xml_parse_into_struct($parser,$data,$values,$index);
        xml_parser_free($parser);
        return $result===1;
    }*/
    
    function is_xhtml($data)
    {   
        return (strpos($data,'<?xml')!==false);
    }
    
    function contains_img_prop_tag($data)
    {
        return strpos($data,'{img}')!==false; 
    }
    
    function contains_url_prop_tag($data)
    {
        return strpos($data,'{url}')!==false; 
    }
           
}
?>
