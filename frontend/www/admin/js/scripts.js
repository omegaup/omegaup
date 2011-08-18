var nuevo = 0;
var guardando = 0;
// para mostrar texto en los inputs y desaparecerlo cuando se da click o escribe algo.
function borraG(obj,op,text){
	
	if(text==undefined){
		if(nuevo == 1)return;
		v = $(obj).attr("id");
		v = v.replace("_t","");
		text = $("#"+v).val();
	}
	if(obj.style.color=="")
		obj.style.color="#BEBFBF";
	//obj.value = obj.style.color;
	if(obj.value==text&&op!=2&&(obj.style.color=="rgb(190, 191, 191)"||obj.style.color=="#bebfbf")){
		obj.value="";
		obj.style.color="#494B4B";
	}else
	if(obj.value==""&&op!=1){
		obj.value=text;
		obj.style.color="#BEBFBF";
	}
}


//definir tamano de caja de texto

function defTam(){
	v = $("#text").css("height");
	v = v.replace("px","");
	v = v - 75;
	$(".source").css("height",v);
}

// esconder capas input, output o text segun corresponda
function hide_div(op){
	time = 500;
	switch(op){
		case 1:	
			$("#texto").show(time);
			$("#input").hide();
			$("#output").hide();
		break
		case 2:
			$("#input").show(time);
			$("#texto").hide();
			$("#output").hide();
		break;
		case 3:
			$("#output").show(time);
			$("#input").hide();
			$("#texto").hide();
		break;
	}
}
//para las sugerencias
function searchFormatQuery(){
	val = $("#buscar_s").val();
	p = val.split(" ");
	q="'";
	for(i=0; i<p.length; i++){
		q+=p[i]+"%";
	}
	q+="'";
	return q;
}
// para guardar los cambios de los campos que tienen texto, en save.php se hara un update o insert segun corresponda
function save(){
	guardando = 1;
	cad = '<img src="img/load.gif" /> ';
	//id_change = 0;
	$("#bm2").html(cad);	
	$.post("save.php",{
			action:"1",
			problem_id:$("#problem_id").val(),
			title:$("#title_t").val(),
			public:$("#public_t").val(),
			time_limit:$("#time_limit_t").val(),
			memory_limit:$("#memory_limit_t").val(),
			visits:$("#visits_t").val(),
			submissions:$("#submissions_t").val(),
			accepted:$("#accepted_t").val(),
			difficulty:$("#difficulty_t").val()			
	},function(data) {
		data = data.replace("[","");
		data = data.replace("]","");		
		var item = jQuery.parseJSON(data);		
			if(item.status === "Ok"){
				//alert("guardo" + item.id);
				loadData(item.id);
				$('.search2').each(function() {
					$(this).val("");
					borraG(this,2);					
				});				
				$("#bm2").html("Guardar");
			}
		
	});
	wait();
}
	//esperar si aun no ah cambiado el valor del problem_id
	
function wait(){
	val = $("#problem_id").val().length;
	if($("#problem_id").val() == "-1"  || val == 0){
		setTimeout("wait()",500);
	}else{		
		//alert("guardara los archivos "+$("#problem_id").val());
		saveFiles();
	}
}
function saveFiles(){

	//guardar html	
	cad = '<img src="img/load.gif" /> ';
	$("#bm2").html(cad);
	$.post("save.php",{
		action:"2",
		problem_id:$("#problem_id").val(),
		html:$("#tex").val()
	},function(data) {
		data = data.replace("[","");
		data = data.replace("]","");		
		var item = jQuery.parseJSON(data);		
			if(item.status === "Ok"){
				setTimeout("loadFiles()",500);
				$("#bm2").html("Guardar");
			}			
		
	});
	//guardar out
	cad = '<img src="img/load.gif" /> ';
	$("#bm2").html(cad);
	$.post("save.php",{
		action:"4",
		problem_id:$("#problem_id").val(),
		out:$("#out").val()
	},function(data) {
		data = data.replace("[","");
		data = data.replace("]","");		
		var item = jQuery.parseJSON(data);		
			if(item.status === "Ok"){	
				setTimeout("loadFiles()",500);
				$("#bm2").html("Guardar");
			}		
	});
	//guardar in
	cad = '<img src="img/load.gif" /> ';
	$("#bm2").html(cad);
	$.post("save.php",{
		action:"3",
		problem_id:$("#problem_id").val(),
		in:$("#in").val()
	},function(data) {
		data = data.replace("[","");
		data = data.replace("]","");		
		var item = jQuery.parseJSON(data);		
			if(item.status === "Ok"){		
				setTimeout("loadFiles()",500);
				$("#bm2").html("Guardar");
			}			
	});
}

//para cargar los datos de un problema por id
function loadData(id){	

	$.getJSON('data.php?',{q:id},function(data) {		
		$.each(data, function(i, item) {
			$("#title_t").val(item.title);
			$("#title").val(item.title);
			$("#public_t").val(item.public);
			$("#public").val(item.public);
			$("#time_limit_t").val(item.time_limit);
			$("#time_limit").val(item.time_limit);
			$("#memory_limit_t").val(item.memory_limit);
			$("#memory_limit").val(item.memory_limit);
			$("#visits_t").val(item.visits);
			$("#visits").val(item.visits);
			$("#submissions_t").val(item.submissions);
			$("#submissions").val(item.submissions);
			$("#accepted_t").val(item.accepted);
			$("#accepted").val(item.accepted);
			$("#difficulty_t").val(item.difficulty);
			$("#difficulty").val(item.difficulty);
			if(guardando == 0) setTimeout("loadFiles()",500);
			nuevo = 0;
			guardando = 0;
			$("#problem_id").val(item.problem_id);
			
		});		
	});
	
}

//para buscar
function search(){
	if($("#buscar_s").val()===''){
		$("#sugg").hide();
		return;
	}
	t = $("#buscar_s").position();
	q = searchFormatQuery();	
		cad = '<img src="img/load.gif" /> ';
		$("#sugg").html(cad);			
	$.getJSON('suggestions.php?',{q:q},function(data) {				
			html="";
			$.each(data, function(i, item) {
				html+= "<a onclick='loadData("+item.problem_id+");$(\"#sugg\").hide()' >"+item.title+"</a></br>";
			});
			$("#sugg").html(html);
	});
		
	$("#sugg").show();
	$("#sugg").css("top",t.top + 30);
	$("#sugg").css("left",t.left + 4);
}
//cargar el contenido de los archivos a los textareas
function loadFiles(){
	//para los archivos .html
    $.post("searchFile.php",{option:1,problem_id:$("#problem_id").val()}, function(data){
		 $("#tex").val( data);
	});
	//para los datos de entrada
	 $.post("searchFile.php",{option:2,problem_id:$("#problem_id").val()}, function(data){
		 $("#in").val(data);
	});
	//para los datos de salida
	 $.post("searchFile.php",{option:3,problem_id:$("#problem_id").val()}, function(data){
		 $("#out").val(data);
	});
	//para las imagenes
	$.post("searchFile.php",{option:4,problem_id:$("#problem_id").val()}, function(data){
		 $("#img").html(data);
	});
}
//replaceAll
   function replaceAll( text, busca, reemplaza ){
		while (text.toString().indexOf(busca) != -1)
			text = text.toString().replace(busca,reemplaza);
		return text;
	}
//Para eliminar una imagen


function delImg(file){
	$.post("process.php",{action:5,problem_id:$("#problem_id").val(),file:file}, function(data){
		 //para las imagenes
		$.post("searchFile.php",{option:4,problem_id:$("#problem_id").val()}, function(data){
			$("#img").html(data);
		});
	});
}

// para borrar un registro
function del(){
	$.post("process.php",{action:6,problem_id:$("#problem_id").val()}, function(data){
			newDat();
			alert(data);
	});
}
//funcion para crear un nuevo registro
function newDat(){
	 $(".search2").val('');
	nuevo = 1;
	$("#problem_id").val("-1");
	setTimeout("loadFiles()",500);
}
//lo que se ejecute cuando la pagina ya cargo
$(document).ready(function($) {
	$('a[rel*=facebox]').facebox({
			loadingImage : '../admin/img/loading.gif',
			closeImage   : '../admin/img/closelabel.png',			
			closeLabel  : '../admin/img/closelabel.png'
		})
	 
	defTam();
  // Cargar archivos para el problema que se muestra actualmente
  loadFiles();
  //esconder las capas para los casos de prueba
   hide_div(1);
   //esconder el div de sugerencias
   $("#sugg").hide();
   $("#buscar_s").keyup(function () {	
		search();
   });
   $('#b1').click(function() {
		 hide_div(1);
	});
   $("#b2").click( function () {
		 hide_div(2);
   });
   $("#b3").click( function () {
		 hide_div(3);
   });
   //boton "nuevo"
   $("#bm1").click( function () {
		 newDat();
   });
   //boton "guardar"
   $("#bm2").click( function () {
		  save();		  
   });
    //boton "eliminar"
   $("#bm3").click( function () {
		  del();		  
   });
   //Para ver la previsualizacion de un problema 
   $("#showHtmlProblem").click( function () {
	//	$("a").attr("href","files/problems/"+ $("#problem_id").val()+".html");
		//$("a").attr("href","files/problems/1.html");		
		jQuery.facebox({ ajax: 'files/problems/'+$("#problem_id").val()+'.html',closeLabel  : '../admin/img/closelabel.png' });
   });
      
	
   //boton para subir el html del problema
   $("#upload_text").click( function () {
    var button = $('#upload_text');
        new AjaxUpload(button,{
            action: 'process.php?action=1&problem_id='+ $("#problem_id").val(), 
            name: 'html',
            onSubmit : function(file, ext){
                // cambiar el texto del boton cuando se selecicione la imagen        
                button.text('Subiendo');	
                // desabilitar el boton
                this.disable();							          
            },
            onComplete: function(file, response){
				//alert(response);
                button.text('Cargar un archivo');				                         
                // Habilitar boton otra vez
                this.enable();           
				setTimeout("loadFiles()",500);
            }
        });
	});
	//boton para subir los casos de prueba para datos de entrada
	$("#upload_in").click( function () {
    var button = $('#upload_in');
        new AjaxUpload(button,{
            action: 'process.php?action=2&problem_id='+ $("#problem_id").val(), 
            name: 'in',
            onSubmit : function(file, ext){
                // cambiar el texto del boton cuando se selecicione la imagen        
                button.text('Subiendo');	
                // desabilitar el boton
                this.disable();							          
            },
            onComplete: function(file, response){
				//alert(response);
                button.text('Cargar un archivo');				                         
                // Habilitar boton otra vez
                this.enable();           
				setTimeout("loadFiles()",500);
            }
        });
	});
	//boton para subir los casos de prueba para datos de salida
	$("#upload_out").click( function () {
    var button = $('#upload_out');
        new AjaxUpload(button,{
            action: 'process.php?action=3&problem_id='+ $("#problem_id").val(), 
            name: 'out',
            onSubmit : function(file, ext){
                // cambiar el texto del boton cuando se selecicione la imagen        
                button.text('Subiendo');	
                // desabilitar el boton
                this.disable();							          
            },
            onComplete: function(file, response){
				//alert(response);
                button.text('Cargar un archivo');				                         
                // Habilitar boton otra vez
                this.enable();           
				loadFiles();
            }
        });
	});
	$("#upload_img").click( function () {
		if($("#problem_id").val() == "-1"){
			alert("Debes crear o seleccionar primero un problema.");
			return;
		}
    var button = $('#upload_img');
        new AjaxUpload(button,{
            action: 'process.php?action=4&problem_id='+ $("#problem_id").val(), 
            name: 'img',
            onSubmit : function(file, ext){
                // cambiar el texto del boton cuando se selecicione la imagen        
                button.text('Subiendo');	
                // desabilitar el boton
                this.disable();							          
            },
            onComplete: function(file, response){
				//alert(response);
                button.text('Subir imagen');				                         
                // Habilitar boton otra vez
                this.enable();           
				loadFiles();
            }
        });
	});
	
});

