<?php

	/*
	 * LEVEL_NEEDED defines the users who can see this page.
	 * Anyone without permission to see this page, will	
	 * be redirected to a page saying so.
	 * This variable *must* be set in order to bootstrap
	 * to continue. This is by design, in order to prevent
	 * leaving open holes for new pages.
	 * 
	 * */
	define( "LEVEL_NEEDED", false );

	require_once( "../../server/inc/bootstrap.php" );
	
	require_once( "api/ShowProblemInContest.php");

    $page = new OmegaupAdminTabPage();

    $page->addComponent( new TitleComponent("Concurso ". $_GET["alias"].""));


	if(!isset($_GET["alias"])){
    	$page->addComponent( new TitleComponent("Este concurso no existe", 2) );
		$page->render(); exit();
	}

	$this_contests = ContestsDAO::search( new Contests( array( "alias" => $_GET["alias"] ) ) );

	if( count($this_contests) == 0){
    	$page->addComponent( new TitleComponent("Este concurso no existe", 2) );
		$page->render(); exit();		
	}
	
	$this_contest = $this_contests[0];
	
	if( is_null($this_contest) ){
    	$page->addComponent( new TitleComponent("Este concurso no existe", 2) );
		$page->render(); exit();
	}

	$page->nextTab("Detalles");
	$page->addComponent( "<a href='../arena/{$_GET['alias']}/'>Ir al concurso</a>"  );



	$aV = ContestsDAO::getByAlias($_GET["alias"]);
	$eContest = new DAOFormComponent($aV);
	$eContest->setEditable(false);
	$page->addComponent($eContest);




	$page->nextTab("Envios");





	$page->nextTab("Chavos");
	$page->addComponent("Enviar correos, bannear, nuevos weyes, etc");



	$page->nextTab("Stats");
	$page->addComponent("GAnalitics u otras cosas, mostrar los auth tokens de los weyes que entraron al concurso con los diferentes ip's");


	$page->nextTab("Editar");
	$page->addComponent("<script>
		function doEditar(){
				 var toSend = {};
				 //upadateContes
				 $(\"table input\").each( function (n, el ){ 
				 		if(el.value.length == 0 ) return;
						toSend[ el.name ] = el.value;
				 });
				

				$.ajax({
                        url: \"../arena/contests/\" + toSend.alias + \"/update/\",
                        dataType: \"json\",
                        type:\"POST\",
                        data: toSend,
                        beforeSend: function( xhr ) {
                            //$(\"#submit\").hide();
                        },
                        success: function(a,b,c){
                            $(\"<p title='OmegaUp'>Success !</p>\").dialog({
								modal: true,
								buttons: {
									Ok: function() {
										$( this ).dialog( \"close\" );
										window.location = \"contest.php?alias=\" + $(\"#_alias\").val();
									}
								}
							});
                        },
                        error:function(a,b,c){
                            r = $.parseJSON(a.responseText);
                            $(\"<p title='OmegaUp'>\"+r.error+\"</p>\").dialog({
								modal: true,
								buttons: {
									Ok: function() {
										$( this ).dialog( \"close\" );
									}
								}
							});
                        }
                        
                    });
				console.log(toSend);

		}</script>");

	$eContest = new DAOFormComponent($aV);
	$eContest->setEditable(true);
	$eContest->hideField( "contest_id" );
	$eContest->addOnClick( "Editar concurso", "doEditar()");


	$page->addComponent($eContest);


	$page->render();

	

