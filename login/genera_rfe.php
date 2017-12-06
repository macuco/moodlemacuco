<?php

//array("MUÑOZ","PEREZ","JUANMANUEL");
/*if(!isset($_POST['paterno'])){
	$_POST['paterno'] = "Muñoz";
	$_POST['materno'] = "Pérez";
	$_POST['nombre'] = "Juan Manuel";
	$_POST['fecha'] = "24/06/1985";
}*/

if(isset($_POST['paterno'])&&isset($_POST['materno'])&&isset($_POST['nombre'])&&isset($_POST['fecha'])){
	$_POST["paterno"] 	= utf8_decode($_POST["paterno"]);
	$_POST["materno"] 	= utf8_decode($_POST["materno"]);
	$_POST["nombre"] 	= utf8_decode($_POST["nombre"]);

/*	$_POST["paterno"] 	= $_POST["paterno"];
	$_POST["materno"] 	= $_POST["materno"];
	$_POST["nombre"] 	= $_POST["nombre"];//	print_r($_POST); */
	//echo ord(utf8_encode($_POST["nombre"]{0}))." ".utf8_encode($_POST["nombre"]{0});
//	print_r(htmlentities($_POST));
	echo GenerarLlaveRFE($_POST['paterno'], $_POST['materno'], $_POST['nombre'], $_POST['fecha']); 
}

$WNomGral = array("MUÑOZ","PEREZ","JUANMANUEL");

//PARA OBTENER LA LLAVE O RFE SOLO ES NECESARIO LLAMAR A LA FUNCION  GenerarLlaveRFE($paterno,$materno,$nombres); 
//echo "<br/> LLAVE:".GenerarLlaveRFE("Muñoz", "Pérez", "Juan Manuel","24/06/1985");
//echo "<br/> LLAVE:".GenerarLlaveRFE("kmBFmÓCA�? ÑÑr:X", "lkl�?DEYn:ir", "qAú1 zVÑ7EGc ?V:nI", "22/06/0156");

/* FUNCION PRINCIPAL */
function GenerarLlaveRFE($WPaterno, $WMaterno="", $WNombre, $Nacimiento) {
	$dtTemp;
	$strDate;
	$WNomGral = array("","","");
	$Cadena_a; $Vocal; $WPaterno2; $WMaterno2; $WNombre2;
	$Cadena_b = "";
	$strFrase = "";
	$i; $longfind; $posicion;
	$WNomGralH = array("","","");

	/* Quitar espacios en el nombre */
	$WPaterno = trim($WPaterno);
	$WMaterno = trim($WMaterno);
	$WNombre = trim($WNombre);
		
	/* Poner el nombre en mayusculas */
	$WPaterno = strtoupper($WPaterno);
	$WMaterno = strtoupper($WMaterno);
	
	//echo "<br/>Materno:".$WMaterno;
	$WNombre = strtoupper($WNombre);

	// QUITA ACENTOS DERECHOS-INVERTIDOS Y DIERISIS
	$WNomGral[0] = utf8_encode($WPaterno);
	$WNomGral[1] = utf8_encode($WMaterno);
	$WNomGral[2] = utf8_encode($WNombre);// ant A= "�?" I="�?" U="Ú"

	//$arrayA = array("É","é","è","È","Ë","ë","Á" ,"á","Ä","ä", "Í","í","�?","ï", "Ó","ó","Ö","ö", "Ú","ú","Ü","ü", "ñ");
	//$arrayB = array("E","E","E","E","E","E", "A","A","A","A", "I","I","I","I", "O","O","O","O", "U","U","U","U", "Ñ");
		
	$arrayA = array("É","é","è","È","Ë","ë","Á", chr(255),"Ä","ä", "Í","í","�?","ï", "Ó","ó","Ö","ö", "Ú","ú","Ü","ü", "ñ",chr(241));
	$arrayB = array("E","E","E","E","E","E", "A","A","A","A", "I","I","I","I", "O","O","O","O", "U","U","U","U", "Ñ", "Ñ");

	for ($i = 0; $i <= 2; $i++) {
		$Cadena_a = $WNomGral[$i];
		$Cadena_a = str_replace($arrayA,$arrayB,$Cadena_a);	
		$longfind = strlen($Cadena_a);
		
		for ($posicion = 0; $posicion <= ($longfind - 1); $posicion++) {
			$Vocal = $Cadena_a{$posicion}; //obtiene letra por letra                        
			
			/*******************************/
			//echo "<br/> Vocal: ".$Vocal;
			//TODO HACER CONPARACIONES
			/*if ($Vocal==("É") || $Vocal==("Ë")) {
				$Vocal = "E";
			} else if ($Vocal==("�?") || $Vocal==("Ä") ) {
				$Vocal = "A";
			} else if ($Vocal==("�?") || $Vocal==("�?") ) {
				$Vocal = "I";
			} else if ($Vocal==("Ó") || $Vocal==("Ö") ) {
				$Vocal = "O";
			} else if ($Vocal==("Ú") || $Vocal==("Ü") ) {
				$Vocal = "U";
			}*/
					
			/* ************************************************/
			//TODO Investigar que hace en esta parte
			//Pattern p = Pattern.compile("[\\p{Punct}&&[^_]]+");
			//Matcher m = p.matcher(Vocal);
			//if (m.find()) {
				//Vocal = "";
			//}
			/* ************************************************/
				
			$patron = '/[^([:alpha:]|[:digit:])(^_)(^ñ-Ñ)(^[:space:])]+/';
			if (preg_match($patron, $Vocal)) {
				$Vocal = "";
			}
				
			$Cadena_b .= $Vocal; //Voy
		}
		
		//echo "<br/>Cadena_b: ".$Cadena_b;
		$WNomGral[$i] = $Cadena_b;// Reemplaza la cadena, por cadena sin acentos
		$Cadena_b = ""; // limpia la cadena temporal
	}
		
	/* Limpia espacios nuevamente */
	$WPaterno2 = trim($WNomGral[0]);
	$WMaterno2 = trim($WNomGral[1]);
	$WNombre2 = trim($WNomGral[2]);

	$WNomGralH[0] = $WPaterno2;
	$WNomGralH[1] = $WMaterno2;
	$WNomGralH[2] = $WNombre2;
	/*  ************************/

	// QUITA MARIA Y JOSE DEL NOMBRE
	if ($WNombre2 == ("MARIA")) {
	} else if (strpos($WNombre2, "MARIA ")==0) {
		$WNombre2 = str_replace("MARIA ", "", $WNombre2);
	}
	
	if ($WNombre2==("JOSE")) {
	} else if (strpos($WNombre2,"JOSE ")==0) {
		$WNombre2 = str_replace("JOSE ", "",$WNombre2);
	}

	// if (WNombre2.equals("MARIA")){
	// }else if(WNombre2.contains("MARIA ")){
	// WNombre2 = WNombre2.replace("MARIA ","");
	// }
	
	// if (WNombre2.equals("JOSE")){
	// }else if(WNombre2.contains("JOSE ")){
	// WNombre2 = WNombre2.replace("JOSE ","");
	// }
		
	$WPaterno3 = DescomponerEnPalabras($WPaterno2);
	//echo("<br/>Descompuesto en palabras Paterno: ".$WPaterno3);
	$WMaterno3 = DescomponerEnPalabras($WMaterno2);
	//echo("<br/>Descompuesto en palabras Materno: ".$WMaterno3);
	$WNombre3 = DescomponerEnPalabras($WNombre2);
	//echo("<br/>".$WNombre2." Descompuesto en palabras Nombre: ".$WNombre3);

	$WNomGral[0] = $WPaterno3;
	$WNomGral[1] = $WMaterno3;
	$WNomGral[2] = $WNombre3;
		
	/* Obtiene la fecha en formato YYMMDD */
	//DateFormat myDateFormat = new SimpleDateFormat("dd/MM/yyyy");
	//DateFormat outputFormat = new SimpleDateFormat("yyMMdd");
	//try {
		//dtTemp = myDateFormat.parse(Nacimiento);
		//strDate = outputFormat.format(dtTemp);
		//System.out.println("line:120-> "+strDate);
	//} catch (ParseException e) {
		//e.printStackTrace();
	//}
	
	$fecha = explode("/", $Nacimiento);
	//$anio = str_split($fecha[2], 2);
	$anio = $fecha[2]{2}."".$fecha[2]{3};
	//$anio = substr($fecha[2],1,2);
	$strDate = $anio.$fecha[1].$fecha[0];
	//echo("<br/>line:144-> ".$strDate);

	//TODO Investigar que hace CreateRFE(Str)
	$Resultado = CreateRFE($WNomGral).$strDate;  // Concatena el nombre con las 4 letras del nombre
	//echo("<br/>CreateRFE(Nombre) + strDate: ". $Resultado);
	//TODO investigar como saca la clave homogenea
	//echo("<br/>HH: ".ArmarValoresH($WNomGralH));
	$vArm = ArmarValoresH($WNomGralH);
		
	$Resultado = $Resultado.$vArm;
	//echo "<br/> VALORES ARMADOS: ".strlen($Resultado);
	//echo "<br/>RESULTADO CON HH:".$Resultado;
	//echo("<br/>4 letras nombre + fecha + ArmarValoresH(WNomGralH) ".$Resultado);
	$Nueva_RFE = DigitoRFC($Resultado);
	//echo("<br/>Agregando el digito del RFC ".$Nueva_RFE);

	return $Nueva_RFE;
}
/* FIN FUNCION PRINCIPAL   */

function DescomponerEnPalabras($strFrase){
	$strTotal = "";
	// Split input with the pattern
	
	$resulta = explode(" ", $strFrase);
	$ListPrep = array("D", "DE", "DEGLI", "DEL",
		"DELLA", "DES", "DI", "DU", "LA", "LAS", "LOS", "VAN", "LO",
		"VANDEN", "VANDER", "VON", "Y", "MA", "J", "M", "MA.", "J.",
		"M.");
	for ($i = 0; $i < count($resulta); $i++) {
		for ($j = 0; $j < count($ListPrep); $j++) {
			if ($resulta[$i]==$ListPrep[$j]) {
				$resulta[$i] = "";
			}
		}
		$strTotal = $strTotal.$resulta[$i];
	}
	
	return $strTotal;
}

/* Recibe de entrada apellido paterno, materno y nombre */
function CreateRFE($WNomGral) {
	/* Declaracion de variables  */
	$WPaterno3 = $WNomGral[0];
	$Vocales = array("A", "E", "I", "O", "U" );
	$RFE_Nom = "";
	$i = 0; $x = 0; $longfind = 0;
	$Vocal;
	$bandera = 0;
	/* ***********************/
		
	//echo("WWWW ".$WNomGral[0]);
	// Validaciones para la longitud del Nombre
	if (strlen($WNomGral[0]) == 1 || strlen($WNomGral[0]) == 2) {
		$RFE_Nom = substr($WNomGral[0],0, 1);//Obtengo la primer letra
		if ($WNomGral[1] == "") {
			$RFE_Nom .= substr($WNomGral[2],0, 2);
			$RFE_Nom .= substr($WNomGral[2],0, 1);
		} else if ($WNomGral[1] != "") {
			$RFE_Nom .= substr($WNomGral[1],0, 1);
			$RFE_Nom .= substr($WNomGral[2],0, 2);
		}
	} else {
		$RFE_Nom = substr($WNomGral[0],0, 1);
		for ($i = 1; $i < strlen($WNomGral[0]); $i++) {
			$Vocal = $WNomGral[0]{$i};
			//echo "<br/>Vocal lin-234: ".$Vocal;
			$x = 0;
			while ($x <= 4) {//Busca la primer vocal dentro del apelido paterno
				if ( ($Vocal==$Vocales[$x]) && $bandera == 0) {
					$RFE_Nom .= $Vocales[$x];
					$bandera = 1;//Bandera para saber si hubo vocal
				}
				$x += 1;
			}
		}
		//echo("<br/>line-201: ".$RFE_Nom);
		//echo("<br/>bandera>".$bandera);
		if ($bandera == 0) {//SI NO TUBO VOCAL EL APELLIDO PART.
			$RFE_Nom = substr($WNomGral[0],0, 1);
			if ($WNomGral[1] == "") {
				$RFE_Nom .= substr($WNomGral[2],0, 2);
				$RFE_Nom .= substr($WNomGral[2],0, 1);
			} else if ($WNomGral[1] != "") {
				$RFE_Nom .= substr($WNomGral[1],0, 1);
				$RFE_Nom .= substr($WNomGral[2],0, 2);
			}
		} else {
			if ($WNomGral[1] == "") {
				$RFE_Nom .= substr($WNomGral[2],0, 2);
			} else if ($WNomGral[1] != "") {
				$RFE_Nom .= substr($WNomGral[1],0, 1);
				$RFE_Nom .= substr($WNomGral[2],0, 1);
			}
		}

	}
	
	// CAMBIA PALABRAS ANTISONANTES
	$ListAltisonantes = array ( "BABA", "BACA", "BATO",
			"BOBA", "BOBO", "BOFE", "BUEI", "BUEY", "CACA", "CACO", "CAGA",
			"CAGO", "CAKA", "CAKO", "COGE", "COJA", "COLA", "COJE", "COJI",
			"COJO", "CULO", "FETO", "FOCA", "GATA", "GUEI", "GUEY", "JOTO",
			"KACA", "KACO", "KAGA", "KAGO", "KAKA", "KOGE", "KOJO", "KULO",
			"LOBA", "LOCA", "LOCO", "LOKA", "LOKO", "LORA", "LORO", "MALA",
			"MAME", "MAMO", "MEAR", "MEAS", "MEON", "MION", "MOCO", "MULA",
			"PEDA", "PEDO", "PENE", "PUTA", "PUTO", "PITO", "QULO", "RATA",
			"ROBA", "ROBE", "ROBO", "RUIN", "SAPO", "VACA", "VAGA", "VAGO" );
	
	for ($i = 0; $i < count($ListAltisonantes); $i++) {
		if ($RFE_Nom == $ListAltisonantes[$i]) {
			$RFE_Nom = substr($RFE_Nom,0, 3)."X";
		}
	}
	
	return $RFE_Nom;
}

// ARMA VALORES DEL NOMBRE PARA LA DETERMINACION DEL HOMONIMO
/* @PARAM String[] */
function ArmarValoresH($WNomGral) {
	$ValorEntero = 0;
	$ValorString = "0";
	$homoClave = "";
	
	//echo "<br/>CADENA DE PARAMETRO: ".$WNomGral[0]." - ".$WNomGral[1]." - ".$WNomGral[2];
	$valores = array(" "=>0,
		"A"=>11,
		"B"=>12,
		"C"=>13,
		"D"=>14,
		"E"=>15,
		"F"=>16,
		"G"=>17,
		"H"=>18,
		"I"=>19,
		"J"=>21,
		"K"=>22,
		"L"=>23,
		"M"=>24,
		"N"=>25,
		"O"=>26,
		"P"=>27,
		"Q"=>28,
		"R"=>29,
		"S"=>32,
		"T"=>33,
		"U"=>34,
		"V"=>35,
		"W"=>36,
		"X"=>37,
		"Y"=>38,
		"Z"=>39,
		"Ñ"=>10,
	chr(209)=>10);
			
	$tmpEnie = "Ñ";// PARA VERIFICAR QUE VENGA UNA EÑE
		
	for ($i = 0; $i <= 2; $i++) {
		$longfind = strlen($WNomGral[$i]);
		for ($j = 0; $j < $longfind; $j++) {
			$Subcadena = substr($WNomGral[$i],$j, 1);
			if($Subcadena==$tmpEnie{0}){
				$Subcadena = substr($WNomGral[$i],$j, 2);
			}
	
			//echo "<br/>line-336 SUBCADENA: ".$Subcadena."  valor: ".$valores[$Subcadena];
			//try {
			$ValorString .= $valores[$Subcadena];
			//echo("VSTR: $Subcadena ".$ValorString." --- ".$valores[$Subcadena]." <br/>");
			$ValorEntero = $ValorString;
			//} catch (Exception e) {}
		}
		
		if ($i == 0) {
			$ValorString .= "00";
		}
		
		if ($i == 1 && $longfind > 0) {
			$ValorString .= "00";
		}
	}
	
	//echo " <br/>VST: ".$ValorString;
	// CALCULO PARA HOMONIMOS SOLO CON VALORES PERMITIDOS
	$longitud = strlen($ValorString);
	$NumChar1 = ""; $NumChar2 = ""; $NumChar3 = ""; $NumChar4 = "";
	$encontredbl;
	$encontre1 = 0; $encontre2 = 0; $posicion = 0; $i = 2;
	// try{
	for ($j = 0; $j < ($longitud - 1); $j++) {
		//try {
		$NumChar1 = substr($ValorString,$j, 2);
		$NumChar2 = substr($NumChar1,1, 2);
		//echo("<br/> Num1: ".$NumChar1." Num2 ".$NumChar2."<br/>");
		$encontre1 = parseInt($NumChar1);
		$encontre2 = parseInt($NumChar2);
		$posicion = $posicion + ($encontre1 * $encontre2);
		$i += 1;
		//} catch (Exception e) {}
	}

	$NumChar3 = $posicion;
	$longitud = strlen($NumChar3);

	if ($longitud < 4) {
		$NumChar4 = $NumChar3;
	} else {
		$encontre1 = $longitud - 3;
		$NumChar4 = substr($NumChar3,$encontre1, 3);
		//echo ("<br/> CHAR4: ".$NumChar4);
	}
	
	$encontre1 = parseInt($NumChar4);
	$encontre2 = $encontre1 % 34;

	$encontredbl = $encontre1 / 34;
	$encontre1 = parseInt($encontredbl); //TODO   CHECAR COMO TRUNCAR UN NUMERO
	//echo("<br/>ENCONTRE1:  ".$encontre1."  ENCONTRE2:  ".$encontre2);

	// DETERMINA LOS HOMONIMOS
	$homoclaves = array( 
		"0"=> "1",
		"1"=> "2",
		"2"=> "3",
		"3"=> "4",
		"4"=> "5",
		"5"=> "6",
		"6"=> "7",
		"7"=> "8",
		"8"=> "9",
		"9"=> "A",
		"10"=> "B",
		"11"=> "C",
		"12"=> "D",
		"13"=> "E",
		"14"=> "F",
		"15"=> "G",
		"16"=> "H",
		"17"=> "I",
		"18"=> "J",
		"19"=> "K",
		"20"=> "L",
		"21"=> "M",
		"22"=> "N",
		"23"=> "P",
		"24"=> "Q",
		"25"=> "R",
		"26"=> "S",
		"27"=> "T",
		"28"=> "U",
		"29"=> "V",
		"30"=> "W",
		"31"=> "X",
		"32"=> "Y",
		"33"=> "Z");

	$primerchar = $encontre1;
	$segundochar = $encontre2;

	//try {
	$homoClave = $homoclaves[$primerchar];
	$homoClave .= $homoclaves[$segundochar];
	//} catch (Exception e) {	}
	return $homoClave;
}

/**
 *
 * @param  String
 */
function DigitoRFC($RFC) {
	//echo "<br/> ENTRADA: ".strlen($RFC);
	$digitos = array(
		"0"=>0,
		"1"=>1,
		"2"=>2,
		"3"=>3,
		"4"=>4,
		"5"=>5,
		"6"=>6,
		"7"=>7,
		"8"=>8,
		"9"=>9,
		"A"=>10,
		"B"=>11,
		"C"=>12,
		"D"=>13,
		"E"=>14,
		"F"=>15,
		"G"=>16,
		"H"=>17,
		"I"=>18,
		"J"=>19,
		"K"=>20,
		"L"=>21,
		"M"=>22,
		"N"=>23,
		"O"=>25,
		"P"=>26,
		"Q"=>27,
		"R"=>28,
		"S"=>29,
		"T"=>30,
		"U"=>31,
		"V"=>32,
		"W"=>33,
		"X"=>34,
		"Y"=>35,
		"Z"=>36,
		"Ñ"=>24 );
		 
	$tmpEnie = "Ñ";
	$posicion = 0;
	$encontre = 13;
	$dbldiv;
	$nRFC = 11;

	for ($j = 0; $j <= $nRFC; $j++) {
		$Subcadena = $RFC{$j};//VER SUBSTR
		//echo "<br/> Subcadena1 -> ".$Subcadena;
		if($Subcadena==$tmpEnie{0}){ 
			$Subcadena = substr($RFC,$j,2); $j++; $nRFC++; 
		}//Excepcion de la Ñ
			
		//echo "<br/> Subcadena2 -> ".$Subcadena;
		$ValorString = "";
		$ValorEntero = 0;
		//try {
		$ValorString .= $digitos[$Subcadena];
		$ValorEntero = $ValorString;
		if ($ValorEntero == 24) {
			$ValorEntero = 23;
		}
		
		$posicion = $posicion + ($ValorEntero * $encontre);
		$encontre = $encontre - 1;
		//} catch (Exception e) {}	
	}
		
	$encontre = $posicion % 11;
	$dbldiv = $posicion / 11;	
	$posicion = parseInt($dbldiv);
	//echo "<br/>DBLDIV2: ".$posicion;

	if ($encontre == 0) {
		$RFC .= "0";
	} else {
		$encontre = 11 - $encontre;
		if ($encontre == 10) {
			$RFC .= "A";
		} else {
			$RFC .= $encontre;
		}		
	}
		
	return $RFC;
}

function parseInt($string) {
  	if(preg_match('/(\d+)/', $string, $array)) {
  		return $array[1];
  	} else {
  		return 0;
  	}
}
?>