<?php


class SubmitSolutionComponent implements GuiComponent{
	
	/**
	  *
	  *
	  *
	  *			
	  **/
	protected $problem;
	protected $contest_id;
	
	
	public function __construct (){
		
	}
	
	
	
	public function renderCmp(){
		?>
			<h2>Enviar Solucion</h2>
			<div   id="upload_0">

				<input id="flash_upload_file" name="fileInput" type="file" />

			</div>

			<script>
            var uploadify = function() {
                $('#flash_upload_file').uploadify({
                    'uploader'  : 'js/uploadify/uploadify.swf',
                    'script'    : 'ajax/enviar.php',
                    'cancelImg' : 'uploadify/cancel.png',
                    'auto'      : false,
                    'height'    : 30,
                    'sizeLimit' : 1024*1024,
                    'buttonText': 'Buscar Archivo',
					'fileDesc' 	:'Codigo Fuente',
					'fileExt'	: '*.c;*.cpp;*.java;*.cs;*.pl;*.py',
                    'onSelect'  : function (e, q, f)  { 
							source_file.file_name = f.name;
							var parts = f.name.split(".");
							source_file.lang_ext = parts[parts.length -1 ];
							$("#ready_to_submit").fadeIn();
							
						},
                    'onCancel'  : function ()  { 
							$("#ready_to_submit").fadeOut();
							
						},
                    'onComplete'  : function (a,b,c,json_response,f){ 
							try{
								doneUploading( $.parseJSON(json_response));	
							}catch(e){
								console.error(e);
							}
						
						},
				 	'onError'  : function (){ 
							alert('Error');
					}
                });
            };

			</script>
			<script type="text/javascript" src="js/uploadify/swfobject.js"></script>
	        <script type="text/javascript" src="js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
			<link rel="stylesheet" type="text/css" href="js/uploadify/uploadify.css" />
		<?php
	}
	
	
}