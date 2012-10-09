<?php


class UserProfileComponent implements GuiComponent{
	
	//is this the user objetc or id?
	private $user;
	private $editable;
	
	function __construct($user){
		if(!($user instanceof VO)){
			//TODO throw correct exception
			return null;
		}
		
		$this->user = $user;
		$this->editable = false;		
	}
	
	public function setEditable($editable){
		$this->editable = $editable;
	}


	private function renderFirstLogin(){
		?>
		<script type="text/javascript">
		function continuar(){
			//send to api, and move on to normal profile
			var oU = oU || new OmegaUp (); 

			oU.UserEdit(null, 
				$("#name").val(),
				null, //$("#email").val(),
				$("#birthDate").val(),
				$("#school").val(),
				null,
				null,
				function(data){ 
					if(data.status == "error"){
						alert(data.error);
						return;
					}

					window.location = "profile.php?edit=ok";

				});
		}
		</script>

		<!-- @TODO move this inline styles to css -->
		<h1 style="display:inline">Bienvenido a </h1>

		<img style="width: 119px;
					-webkit-box-shadow: 0 0 0;
					margin-top: 5px;
					margin-left: -5px;
					display: inline;
					padding: 0px;
					background: 
					transparent;
					margin: 0px;
					margin-top: 0px;" src="media/omegaup_curves.png">

		<h1 style="display:inline;"><?php echo $this->user->getName(); ?></h1>
		<br>
		<h3>Porfavor actualiza tu informacion</h3>

		<?php

		$df = new FormComponent();

		$pEmail = EmailsDAO::getByPK( $this->user->getMainEmailId() );

		$df->addField( "name", "Nombre", "text", 					$this->user->getName() );
		//$df->addField( "email", "Correo electronico", "text", 		$pEmail->getEmail() );
		$df->addField( "birthDate", "Fecha de nacimiento", "date", 	$this->user->getBirthDate() );
		$df->addField( "school", "Escuela", "text", 	$this->user->getSchoolId() );
		$df->addOnClick("Continuar", "continuar()");

		echo $df->renderCmp();

	}
	
	public function renderCmp(){

		//test if the profile we are showing
		//is the owners profile
		if(LoginController::isLoggedIn()){
			$cu = LoginController::getCurrentUser();

			if(
				$this->user->getUserId() == $cu->getUserId()
				&& isset($_GET["fl"]) 
				&& ($_GET["fl"] == 1)
			){
				return self::renderFirstLogin();
			}
		}
			
		

		if($this->editable){
			?>
				<script type="text/javascript" charset="utf-8">
					var profile_edit = function(){
							//hide form
							$("#actual_form").fadeOut("slow", function(){
								$("#editable_form").fadeIn();	
							});
							//show editable form
						};
				
					var profile_edit_cancel = function (){

						$("#editable_form").fadeOut("slow", function(){
							$("#actual_form").fadeIn();	

							$("#password").val("");
							$("#oldPassword").val("");

						});

					}



					var profile_edit_ok = function(){


						//validate, basic
						if($("#password").val().length > 0){

							//validate shit
							if($("#oldPassword").val().length == 0){
								alert("You must provide your old password");
								return;
							}

						}



						var oU = oU || new OmegaUp (); 

						oU.UserEdit(null, 
							$("#ename").val(),
							null, //$("#email").val(),
							null, //$("#birthDate").val(),
							$("#eschool").val(),
							$("#password").val().length == 0 ? null : $("#password").val().length(),
							$("#oldPassword").val().length == 0 ? null : $("#oldPassword").val().length(),

							function(data){ 
								if(data.status == "error"){
									alert(data.error);
									return;
								}

								window.location = "profile.php?edit=ok";

							});
									 
						/*
						//send ajax
						$.ajax({
						  url: 'arena/user/edit',
						  data: toEdit,
						  success: function(data) {
						    
						    alert('Load was performed.');
						  },
						  error : function (data,b,c){
						  	console.log(data,b,c);
						  }
						});
						*/
		
						
					}

						//list registred schools
						current_schools = <?php echo json_encode( SchoolsDAO::getAll() ); ?>;
				</script>
				
				
				
			<?php
			
			//add editable form
			/*
			$editable_form = new DAOFormComponent( $this->user );
			
			$editable_form->hideField( array("solved", "password", "user_id", "submissions", "last_access", "username" )  );
			
			if( sizeof( SchoolsDAO::getAll() ) == 0){
				
			}else{
				$editable_form->createComboBoxJoin( "school_id", "school_id",  new Schools() );
			}

			$editable_form->wrapWith("id", "editable_form");
			$editable_form->wrapWith("style", "display: none;");
			echo $editable_form->renderCmp();
			*/
			
			
			
			
		}
		
		?>
			
			<table border="0" id="editable_form" style="display:none;">
				<tr>

					<td>Nombre</td>  
					<td><input type="text" id="ename" value="<?php echo $this->user->getName(); ?>"></td>

					<!--<td>Escuela</td>  
					<td><input type="text" id="eschool" ></td>
				-->
				</tr>


				<tr>
					<td colspan=4><hr></td>
				</tr>
				<tr>

					<td>Password</td>  
					<td><input type="password" id="password" ></td>
					<td>Old Password</td>  
					<td><input type="password" id="oldPassword" ></td>

				</tr>



				<tr>
					<td><a onClick='profile_edit_cancel()'>
						<div class="Boton">Cancelar</div></a></td>

					<td><a onClick='profile_edit_ok()'>
						<div class="Boton OK">Guardar cambios</div></a></td>
				</tr>



			</table>




			<table border="0" id="actual_form">
				<tr>
					<td><a onClick='profile_edit()'><div class="Boton">Editar mi perfil</div></a></td>
				</tr>
				<tr>
					<td><img src="https://secure.gravatar.com/avatar/<?php echo md5($this->user->getUsername()); ?>?s=128"></td>
					<td valign=top><h1>
						<?php 
							if(is_null( $this->user->getName() ))
								echo $this->user->getUserName();
							else
								echo $this->user->getName();
						?>
					</h1>
					
					<table border="0">
						<tr><td>Solved</td><td>
							<?php echo $this->user->getSolved(); ?>
						</td></tr>
						<tr><td>Submissions</td><td>
							<?php echo $this->user->getSubmissions(); ?>
						</td></tr>	
						<tr><td>Country</td><td>
							<?php echo $this->user->getCountryId(); ?>,
							<?php echo $this->user->getStateId(); ?>
						</td></tr>
						<tr><td>School</td><td>
							<?php echo $this->user->getSchoolId(); ?>
						</td></tr>
						
					</table>
					
					</td>
				</tr>
				<tr>
					<!--
					<td>
						<h2>Badges</h2>
						<?php
							
							$badges = UsersBadgesDAO::search( new UsersBadges( array( "user_id" => $this->user->getUserId() ) ) );
							foreach( $badges as $badge ){
								//print badge name
								$actual_badge = BadgeDAO::getByPK($badge->getBadgeId());
								echo $actual_badge->getName();
							}
						?>
					</td>
					-->
				</tr>
				
				
				
				
			</table>
		
		<?php
		
		/*
		object(Users)#4 (14) { ["user_id":protected]=> string(1) "2" ["username":protected]=> string(19) "alan.gohe@gmail.com" ["password":protected]=> NULL ["main_email_id":protected]=> NULL ["name":protected]=> NULL ["solved":protected]=> string(1) "0" ["submissions":protected]=> string(1) "0" ["country_id":protected]=> NULL ["state_id":protected]=> NULL ["school_id":protected]=> NULL ["scholar_degree":protected]=> NULL ["graduation_date":protected]=> NULL ["birth_date":protected]=> NULL ["last_access":protected]=> string(19) "2011-12-31 04:15:45" }
		*/
	}
	
	
}
