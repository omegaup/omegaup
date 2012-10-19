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
	require_once( "api/OmiReport.php");

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

	//$page->addComponent( "<a href='../arena/{$_GET['alias']}/'>Ir al concurso</a>"  );

	RequestContext::set('contest_alias', $_GET['alias']);
	$omiReport = new OmiReport();
	$djson = json_decode(json_encode($omiReport->ExecuteApi()));

	$nreports = sizeof($djson->report); 

	$page->addComponent( "<table style='width:100%' border=0>"  );	

	//reports
	for($r = 0 ; $r < $nreports ; $r++)	
	{
		$page->addComponent( "<tr><td><hr>"  );	
		
		$page->addComponent( "<strong>" . $djson->report[$r]->username . "</strong> ".$djson->report[$r]->name."" );

		/* 	$djson->report[$r]->total->points;  $djson->report[$r]->total->penalty;*/

		$nprobs = sizeof($djson->report[$r]->problems);

		$pkeys = get_object_vars( $djson->report[$r]->problems  );

		$page->addComponent( "</td></tr>"  );	

		//team, i think
		foreach($pkeys as $pp ){
			$page->addComponent( "<tr><td>"  );	
			
			$page->addComponent( "<div style='background-color: #0072C6; color: white; padding: 5px; overflow: hidden'> Puntos : " .$pp->points. "&nbsp;&nbsp;&nbsp;Penalty : ".$pp->penalty . "</div>" );


			if(!property_exists($pp, "run_details" )) continue;

			if(array_key_exists("source", $pp->run_details )){
				$page->addComponent( "<div style='display:block; overflow: hidden'><code>".$pp->run_details->source. "</code></div>" );
			}			


			if(!array_key_exists("cases", $pp->run_details )) continue;
	
			$page->addComponent( "</td></tr>"  );				


			$ccolor = 0;
			foreach($pp->run_details->cases as $c)
			{

				$ccolor ++;



				if($ccolor % 2 == 0)				
					$page->addComponent( "<tr >"  );
				else
					$page->addComponent( "<tr style='background-color: #F0F0F0'>"  ); 

				$page->addComponent( "<td>"  );

				$page->addComponent( "<table style='width:100%'>"  );
				$page->addComponent( "<tr>"  );
				$page->addComponent( "<td>name</td>"  );
				$page->addComponent( "<td>out_diff </td>"  );
				$page->addComponent( "<td>time</td>"  );
				$page->addComponent( "<td>time-wall</td>"  );
				$page->addComponent( "<td>syscall-count</td>"  );
				$page->addComponent( "<td>status</td>"  );
				$page->addComponent( "</tr>"  );





				$page->addComponent( "<tr>"  );
				$page->addComponent( "<td>" . $c->name. "</td>"  );

				if(property_exists($c->meta, "out_diff"))
					$page->addComponent( "<td>" .$c->meta->out_diff. " </td>"  );
				else
					$page->addComponent( "<td> - </td>"  );



				if(property_exists($c->meta, "time"))
					$page->addComponent( "<td>" .$c->meta->time. " </td>"  );
				else
					$page->addComponent( "<td> - </td>"  );



				if(property_exists($c->meta, "time-wall"))
					$page->addComponent( "<td>" .$c->meta->{'time-wall'} . " </td>"  );
				else
					$page->addComponent( "<td> - </td>"  );


				if(property_exists($c->meta, "syscall-count"))
					$page->addComponent( "<td>" .$c->meta->{'syscall-count'}. " </td>"  );
				else
					$page->addComponent( "<td> - </td>"  );




				$page->addComponent( "<td><strong>" .$c->meta->status. "</strong></td>"  );
				$page->addComponent( "</tr>"  );

				$page->addComponent( "</table>"  );

				$page->addComponent( "</td></tr>"  );	
			}

		}
		$page->addComponent( "</td>"  );	
		$page->addComponent( "</tr>"  );	
		

	}
	
	$page->addComponent( "</table>"  );	

	














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
                        url: \"../api/contest/\" + toSend.alias + \"/update/\",
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

	

