/* @autor Juan Manuel -> */
/* Primero agregar las cabeceras, una funcion para hacer la gestion de la respuesta del servidor y un gestor de eventos */
function createREQ(){
	try {
		req = new XMLHttpRequest();
	} catch(err1){
		/* Algunas versiones de IE */
		try {
			req = new ActiveXObject("Msxml2.XMLHTTP");
		} catch(err2){
			try {
				req = new ActiveXObject("Microsoft.XMLHTTP");
			} catch(err3) {
				req = false;
			}			
		}
	}
	return req;
}
/* url: URL al que se llamara, query: Parametros, req: Instancia HTTPRequest*/
function requestGET(url, query, req){
	myRand = parseInt(Math.random()*999999999999);
	req.open("GET",url+'?'+query+'&rand='+myRand,true);
	req.send(null);
}
/* funcion request post */
function requestPOST(url, query,req){
	req.open("POST",url,true);
	req.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	req.send(query);
}
/*Funcion que debe de determinar si la peticion es POST o GET*/
/* Determina si la funcion es como texto o xml*/
/*
 * PARAM:
 * url
 * query: peticion
 * callback: la funcion con la que se realizara la respuesta
 * reqtype: Tipo de peticion (get-post)
 * getxml: Para determinar si es xml o no (1-0)
*/
function doAjax(url,query,callback,reqtype,getxml){
	var myreq = createREQ(); //Crear instancia del objeto XMLHTTPRequest
	/* Funcion que se estara ejecutando mientras carga */
	myreq.onreadystatechange = function() {
		if(myreq.readyState == 4) { // Si hay respuesta del servidor
			if(myreq.status == 200) { //Si la respuesta es ok
				/* Verificamos en tipo de respuesta */
				var item = myreq.responseText;// Por defaul la respuesta es como text
				if(getxml == 1){// si queremos respuesta cml
					item = myreq.responseXML; //crear instancia responseXML
				}
				/********************************/			
				doCallback(callback,item);//ejecutamos la funcion				
			}
		}
	}
	/* Verificamos el tipo de peticion */
	if(reqtype=='post'){
		requestPOST(url,query,myreq);
	} else {
		requestGET(url,query,myreq);
	}	
}
/* Identifica que funcion se va a llamar y que tipo de respuesta tendra */
function doCallback(callback,item){
		eval(callback+'(item)');
}
function prueba(url,query,callback,reqtype,getxml){
	alert(url+"  "+callback+"  "+reqtype);
}

/* FUNCIONES DE LLAMADO */
function recibir(rfe){
  document.getElementById("id_idnumber").value=rfe;
}
/* <- @autor Juan Manuel */

/* @autor Edgar Olaf -> */
function eliminarElementos(seccion){
  var division = document.getElementById(seccion); // Obtener la seccion
  if (division){	// Si existe
  	var padre = division.parentNode;  // Obtener el padre de la seccion 
	padre.removeChild(division);	// Remover la seccion
  } // if
}
var url = 'registrar_usuario_a_curso.php';
function recibirCursos(tiposXML) {
  select_cursos = document.getElementById("cursos");
  while (select_cursos.options.length )
    select_cursos.options[0] = null;
  myOption = document.createElement("OPTION");
  myOption.text = "------Seleccionar------";
  myOption.value = -1;            
  select_cursos.options.add(myOption);
  if(tiposXML.childNodes.length>0){
	tipos = tiposXML.getElementsByTagName("option");
	for(i=0;i<tipos.length;i++){
      myOption = document.createElement("OPTION");
      myOption.text = tipos[i].childNodes[0].nodeValue;
      myOption.value = tipos[i].getAttribute("value");
      select_cursos.options.add(myOption);
    } // for
  } // if
}
function recibirAsesores(tiposXML) {
  select_asesores = document.getElementById("asesores");
  while (select_asesores.options.length )
    select_asesores.options[0] = null;
  myOption = document.createElement("OPTION");
  myOption.text = "------Seleccionar------";
  myOption.value = -1;
  select_asesores.options.add(myOption);
  if(tiposXML.childNodes.length>0){
	tipos = tiposXML.getElementsByTagName("option");
	for(i=0;i<tipos.length;i++){
      myOption = document.createElement("OPTION");
      myOption.text = tipos[i].childNodes[0].nodeValue;
      myOption.value = tipos[i].getAttribute("value");
      select_asesores.options.add(myOption);
    }// for
    if(tipos.length==0){
      select_asesores.options[0] = null;
      myOption = document.createElement("OPTION");
      myOption.text = "No hay asesores para este curso";
      myOption.value = -1;
      select_asesores.options.add(myOption);
    } // if
  }
}
function recibirCursosRegistrados(tiposXML) {
  info = document.getElementById('datos');
  info.innerHTML = tiposXML;
  desactivar();
  asignarNombre();
}
function generarCursos(id_categoria,accion,id_usuario) {
  doAjax(url,'id_categoria='+id_categoria+'&id_usuario='+id_usuario+'&accion='+accion,'recibirCursos','post',1);
}
function generarAsesores(id_usuario,id_curso,accion) {
  doAjax(url,'id_usuario='+id_usuario+'&id_curso='+id_curso+'&accion='+accion,'recibirAsesores','post',1);
}
function generarCursosRegistrados(id_usuario,id_rol) {
  doAjax(url,'id_usuario='+id_usuario+'&id_rol='+id_rol+'&accion=getcursosregistrados','recibirCursosRegistrados','post',0);
}
function registrarCurso(id_usuario,id_rol,id_curso,id_grupo) {
  control = validar();
  if(control == 0) {
    if(id_grupo > 0 || id_grupo==-2)
      doAjax(url,'id_usuario='+id_usuario+'&id_rol='+id_rol+'&id_curso='+id_curso+'&id_grupo='+id_grupo+'&accion=registrarcursos','recibeUsuario','post',1);
    else
      doAjax(url,'id_usuario='+id_usuario+'&id_rol='+id_rol+'&id_curso='+id_curso+'&accion=registrarcursos','recibeUsuario','post',1);
  } // if
}
function recibeUsuario(tiposXML) {
  if(tiposXML.childNodes.length>0){
    id_usuarioX = tiposXML.getElementsByTagName("id");
    id_rolX = tiposXML.getElementsByTagName("id_rol");
    id_usuario = id_usuarioX[0].childNodes[0].nodeValue;
    id_rol = id_rolX[0].childNodes[0].nodeValue;
    generarCursosRegistrados(id_usuario,id_rol);	
    respuesta(1,'');
  } // if
}
function respuesta(msg,tmp) {
  if(msg == 1) {
    cursox = document.getElementById('cursos').options[document.getElementById('cursos').selectedIndex].text;
    alert("Se ha registrado satisfactoriamente en el curso: \n"+cursox);
  }
  else
    alert("Se ha desmatriculado satisfactoriamente del curso: \n"+tmp);
  document.getElementById('id_categoria').value=-1;
  select_cursos = document.getElementById("cursos");
  while (select_cursos.options.length )
    select_cursos.options[0] = null;
  myOption = document.createElement("OPTION");
  myOption.text = "------Seleccionar------";
  myOption.value = -1;
  select_cursos.options.add(myOption);
  try {
    select_asesores = document.getElementById("asesores");
    while (select_asesores.options.length )
      select_asesores.options[0] = null;
    myOption = document.createElement("OPTION");
    myOption.text = "------Seleccionar------";
    myOption.value = -1;
    select_asesores.options.add(myOption);
  }
  catch(e) {
  	//alert(e.description);
  }
}
function desmatricular(id_usuario,id_rol,id_curso) {
  doAjax(url,'id_usuario='+id_usuario+'&id_rol='+id_rol+'&id_curso='+id_curso+'&accion=desmatricular','recibeDesmatricular','post',1);
}
function recibeDesmatricular(tiposXML) {
  if(tiposXML.childNodes.length>0){
    id_usuarioX = tiposXML.getElementsByTagName("id");
    id_rolX = tiposXML.getElementsByTagName("id_rol");
    cursoX = tiposXML.getElementsByTagName("curso");
    id_usuario = id_usuarioX[0].childNodes[0].nodeValue;
    id_rol = id_rolX[0].childNodes[0].nodeValue;
    curso = cursoX[0].childNodes[0].nodeValue;
    generarCursosRegistrados(id_usuario,id_rol);
    respuesta(0,curso);
  } // if
}
function validar() {
  centinela = 0;
  if(document.getElementById('id_categoria').value == -1 || document.getElementById('cursos').value == -1)
    centinela = 1;
  try {
  	if(document.getElementById('asesores').value == -1)
  	  centinela = 1;
  }
  catch(e) {
  	//alert(e.description);
  }
  if(centinela == 1)
    alert("Falta proporcionar informacion.");
  return centinela;
}
function desactivar() {
  div = document.getElementById("mensaje");
  if(parseInt(document.getElementById('cursando').value) >= parseInt(document.getElementById('maxcursos').value)) {
  	div.style.visibility='visible';
    document.getElementById('inicioregistrar').disabled=true;
  	document.getElementById('id_categoria').disabled=true;
    document.getElementById('cursos').disabled=true;
    document.getElementById('registra').disabled=true;
    try {
      document.getElementById('asesores').disabled=true;
    }
    catch(e) {
      //alert(e.description);
    } // try catch
  }
  else {
    div.style.visibility='hidden';
    document.getElementById('inicioregistrar').disabled=false;
  	document.getElementById('id_categoria').disabled=false;
    document.getElementById('cursos').disabled=false;
    document.getElementById('registra').disabled=false;
    try {
      document.getElementById('asesores').disabled=false;
    }
    catch(e) {
      //alert(e.description);
    } // try catch  	
  } // if else
}
function asignarNombre() {
  if(parseInt(document.getElementById('cursando').value) <= 0)
    document.getElementById('inicioregistrar').value = "Registrarse a un curso";
  else
    document.getElementById('inicioregistrar').value = "Registrarse a un nuevo curso";
}
function activarDesactivarSeccion(ob,control) {
  if(control == 1) {
    document.getElementById('_registro').style.visibility='visible';
    ob.style.visibility='hidden';
  }
  else {
  	centinela = 0;
    if(document.getElementById('id_categoria').value == -1 || document.getElementById('cursos').value == -1) {
      centinela = 1;
    } // if
    try {
  	  if(document.getElementById('asesores').value == -1)
  	    centinela = 1;
    }
    catch(e) {
  	  //alert(e.description);
    }
    if(centinela == 0) {
      document.getElementById('_registro').style.visibility='hidden';
      document.getElementById('inicioregistrar').style.visibility='visible';
    } // if
  } // if else
}
function activarDesactivarBoton(ob,control) {
  if(control == 1)
    ob.disabled=true;
  else
    ob.disabled=false;
}
