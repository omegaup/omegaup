{extends file="profile.tpl"} 
{block name="content"}
	<div class="col-md-10">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h2 class="panel-title">Edita tu perfil</h2>
			</div>
			<div class="panel-body">
				<form id="user_profile_form" class="form-horizontal" role="form">
					<div class="form-group">
						<label for="name" class="col-md-3 control-label">Nombre</label>
						<div class="col-md-7">
							<input id='name' name='name' value='' type='text' size='30' class="form-control">
						</div>
					</div>
					
					<div class="form-group">
						<label for="birth_date" class="col-md-3 control-label">Fecha de nacimiento</label>
						<div class="col-md-7">
							<input id='birth_date' name='birth_date' value='' type='text' size ='10' class="form-control">
						</div>
					</div>
					
					<div class="form-group">
						<label for="country_id" class="col-md-3 control-label">País</label>
						<div class="col-md-7">
							<select name='country_id' id='country_id' class="form-control">
								<option value=""></option>
								<option value="AD">Andorra</option>
								<option value="AE">United Arab Emirates</option>
								<option value="AF">Afghanistan</option>
								<option value="AG">Antigua and Barbuda</option>
								<option value="AI">Anguilla</option>
								<option value="AL">Albania</option>
								<option value="AM">Armenia</option>
								<option value="AN">Netherlands Antilles</option>
								<option value="AO">Angola</option>
								<option value="AQ">Antarctica</option>
								<option value="AR">Argentina</option>
								<option value="AS">American Samoa</option>
								<option value="AT">Austria</option>
								<option value="AU">Australia</option>
								<option value="AW">Aruba</option>
								<option value="AX">Åland Islands</option>
								<option value="AZ">Azerbaijan</option>
								<option value="BA">Bosnia and Herzegovina</option>
								<option value="BB">Barbados</option>
								<option value="BD">Bangladesh</option>
								<option value="BE">Belgium</option>
								<option value="BF">Burkina Faso</option>
								<option value="BG">Bulgaria</option>
								<option value="BH">Bahrain</option>
								<option value="BI">Burundi</option>
								<option value="BJ">Benin</option>
								<option value="BL">Saint Barthélemy</option>
								<option value="BM">Bermuda</option>
								<option value="BN">Brunei Darussalam</option>
								<option value="BO">Bolivia</option>
								<option value="BR">Brazil</option>
								<option value="BS">Bahamas</option>
								<option value="BT">Bhutan</option>
								<option value="BV">Bouvet Island</option>
								<option value="BW">Botswana</option>
								<option value="BY">Belarus</option>
								<option value="BZ">Belize</option>
								<option value="CA">Canada</option>
								<option value="CC">Cocos (Keeling) Islands</option>
								<option value="CD">Congo, The Democratic Republic of the</option>
								<option value="CF">Central African Republic</option>
								<option value="CG">Congo</option>
								<option value="CH">Switzerland</option>
								<option value="CI">Côte D'Ivoire</option>
								<option value="CK">Cook Islands</option>
								<option value="CL">Chile</option>
								<option value="CM">Cameroon</option>
								<option value="CN">China</option>
								<option value="CO">Colombia</option>
								<option value="CR">Costa Rica</option>
								<option value="CU">Cuba</option>
								<option value="CV">Cape Verde</option>
								<option value="CX">Christmas Island</option>
								<option value="CY">Cyprus</option>
								<option value="CZ">Czech Republic</option>
								<option value="DE">Germany</option>
								<option value="DJ">Djibouti</option>
								<option value="DK">Denmark</option>
								<option value="DM">Dominica</option>
								<option value="DO">Dominican Republic</option>
								<option value="DZ">Algeria</option>
								<option value="EC">Ecuador</option>
								<option value="EE">Estonia</option>
								<option value="EG">Egypt</option>
								<option value="EH">Western Sahara</option>
								<option value="ER">Eritrea</option>
								<option value="ES">Spain</option>
								<option value="ET">Ethiopia</option>
								<option value="FI">Finland</option>
								<option value="FJ">Fiji</option>
								<option value="FK">Falkland Islands (Malvinas)</option>
								<option value="FM">Micronesia, Federated States of</option>
								<option value="FO">Faroe Islands</option>
								<option value="FR">France</option>
								<option value="GA">Gabon</option>
								<option value="GB">United Kingdom</option>
								<option value="GD">Grenada</option>
								<option value="GE">Georgia</option>
								<option value="GF">French Guiana</option>
								<option value="GG">Guernsey</option>
								<option value="GH">Ghana</option>
								<option value="GI">Gibraltar</option>
								<option value="GL">Greenland</option>
								<option value="GM">Gambia</option>
								<option value="GN">Guinea</option>
								<option value="GP">Guadeloupe</option>
								<option value="GQ">Equatorial Guinea</option>
								<option value="GR">Greece</option>
								<option value="GS">South Georgia and the South Sandwich Islands</option>
								<option value="GT">Guatemala</option>
								<option value="GU">Guam</option>
								<option value="GW">Guinea-Bissau</option>
								<option value="GY">Guyana</option>
								<option value="HK">Hong Kong</option>
								<option value="HM">Heard Island and McDonald Islands</option>
								<option value="HN">Honduras</option>
								<option value="HR">Croatia</option>
								<option value="HT">Haiti</option>
								<option value="HU">Hungary</option>
								<option value="ID">Indonesia</option>
								<option value="IE">Ireland</option>
								<option value="IL">Israel</option>
								<option value="IM">Isle of Man</option>
								<option value="IN">India</option>
								<option value="IO">British Indian Ocean Territory</option>
								<option value="IQ">Iraq</option>
								<option value="IR">Iran, Islamic Republic of</option>
								<option value="IS">Iceland</option>
								<option value="IT">Italy</option>
								<option value="JE">Jersey</option>
								<option value="JM">Jamaica</option>
								<option value="JO">Jordan</option>
								<option value="JP">Japan</option>
								<option value="KE">Kenya</option>
								<option value="KG">Kyrgyzstan</option>
								<option value="KH">Cambodia</option>
								<option value="KI">Kiribati</option>
								<option value="KM">Comoros</option>
								<option value="KN">Saint Kitts and Nevis</option>
								<option value="KP">Korea, Democratic People's Republic of</option>
								<option value="KR">Korea, Republic of</option>
								<option value="KW">Kuwait</option>
								<option value="KY">Cayman Islands</option>
								<option value="KZ">Kazakhstan</option>
								<option value="LA">Lao People's Democratic Republic</option>
								<option value="LB">Lebanon</option>
								<option value="LC">Saint Lucia</option>
								<option value="LI">Liechtenstein</option>
								<option value="LK">Sri Lanka</option>
								<option value="LR">Liberia</option>
								<option value="LS">Lesotho</option>
								<option value="LT">Lithuania</option>
								<option value="LU">Luxembourg</option>
								<option value="LV">Latvia</option>
								<option value="LY">Libyan Arab Jamahiriya</option>
								<option value="MA">Morocco</option>
								<option value="MC">Monaco</option>
								<option value="MD">Moldova, Republic of</option>
								<option value="ME">Montenegro</option>
								<option value="MF">Saint Martin</option>
								<option value="MG">Madagascar</option>
								<option value="MH">Marshall Islands</option>
								<option value="MK">Macedonia, The Former Yugoslav Republic of</option>
								<option value="ML">Mali</option>
								<option value="MM">Myanmar</option>
								<option value="MN">Mongolia</option>
								<option value="MO">Macao</option>
								<option value="MP">Northern Mariana Islands</option>
								<option value="MQ">Martinique</option>
								<option value="MR">Mauritania</option>
								<option value="MS">Montserrat</option>
								<option value="MT">Malta</option>
								<option value="MU">Mauritius</option>
								<option value="MV">Maldives</option>
								<option value="MW">Malawi</option>
								<option value="MX" selected="1">Mexico</option>
								<option value="MY">Malaysia</option>
								<option value="MZ">Mozambique</option>
								<option value="NA">Namibia</option>
								<option value="NC">New Caledonia</option>
								<option value="NE">Niger</option>
								<option value="NF">Norfolk Island</option>
								<option value="NG">Nigeria</option>
								<option value="NI">Nicaragua</option>
								<option value="NL">Netherlands</option>
								<option value="NO">Norway</option>
								<option value="NP">Nepal</option>
								<option value="NR">Nauru</option>
								<option value="NU">Niue</option>
								<option value="NZ">New Zealand</option>
								<option value="OM">Oman</option>
								<option value="PA">Panama</option>
								<option value="PE">Peru</option>
								<option value="PF">French Polynesia</option>
								<option value="PG">Papua New Guinea</option>
								<option value="PH">Philippines</option>
								<option value="PK">Pakistan</option>
								<option value="PL">Poland</option>
								<option value="PM">Saint Pierre and Miquelon</option>
								<option value="PN">Pitcairn</option>
								<option value="PR">Puerto Rico</option>
								<option value="PS">Palestinian Territory, Occupied</option>
								<option value="PT">Portugal</option>
								<option value="PW">Palau</option>
								<option value="PY">Paraguay</option>
								<option value="QA">Qatar</option>
								<option value="RE">Reunion</option>
								<option value="RO">Romania</option>
								<option value="RS">Serbia</option>
								<option value="RU">Russian Federation</option>
								<option value="RW">Rwanda</option>
								<option value="SA">Saudi Arabia</option>
								<option value="SB">Solomon Islands</option>
								<option value="SC">Seychelles</option>
								<option value="SD">Sudan</option>
								<option value="SE">Sweden</option>
								<option value="SG">Singapore</option>
								<option value="SH">Saint Helena</option>
								<option value="SI">Slovenia</option>
								<option value="SJ">Svalbard and Jan Mayen</option>
								<option value="SK">Slovakia</option>
								<option value="SL">Sierra Leone</option>
								<option value="SM">San Marino</option>
								<option value="SN">Senegal</option>
								<option value="SO">Somalia</option>
								<option value="SR">Suriname</option>
								<option value="ST">Sao Tome and Principe</option>
								<option value="SV">El Salvador</option>
								<option value="SY">Syrian Arab Republic</option>
								<option value="SZ">Swaziland</option>
								<option value="TC">Turks and Caicos Islands</option>
								<option value="TD">Chad</option>
								<option value="TF">French Southern Territories</option>
								<option value="TG">Togo</option>
								<option value="TH">Thailand</option>
								<option value="TJ">Tajikistan</option>
								<option value="TK">Tokelau</option>
								<option value="TL">Timor-Leste</option>
								<option value="TM">Turkmenistan</option>
								<option value="TN">Tunisia</option>
								<option value="TO">Tonga</option>
								<option value="TR">Turkey</option>
								<option value="TT">Trinidad and Tobago</option>
								<option value="TV">Tuvalu</option>
								<option value="TW">Taiwan, Province Of China</option>
								<option value="TZ">Tanzania, United Republic of</option>
								<option value="UA">Ukraine</option>
								<option value="UG">Uganda</option>
								<option value="UM">United States Minor Outlying Islands</option>
								<option value="US">United States</option>
								<option value="UY">Uruguay</option>
								<option value="UZ">Uzbekistan</option>
								<option value="VA">Holy See (Vatican City State)</option>
								<option value="VC">Saint Vincent and the Grenadines</option>
								<option value="VE">Venezuela</option>
								<option value="VG">Virgin Islands, British</option>
								<option value="VI">Virgin Islands, U.S.</option>
								<option value="VN">Viet Nam</option>
								<option value="VU">Vanuatu</option>
								<option value="WF">Wallis And Futuna</option>
								<option value="WS">Samoa</option>
								<option value="YE">Yemen</option>
								<option value="YT">Mayotte</option>
								<option value="ZA">South Africa</option>
								<option value="ZM">Zambia</option>
								<option value="ZW">Zimbabwe</option>
							</select>
						</div>
					</div>
					
					<div class="form-group">
						<label for="state_id" class="col-md-3 control-label">Estado</label>
						<div class="col-md-7">
							<select name='state_id' id='state_id' disabled="true" class="form-control"></select>
						</div>
					</div>
					
					<div class="form-group">
						<label for="school" class="col-md-3 control-label">Escuela</label>
						<div class="col-md-7">
							<input id='school' name='school' value='' type='text' size='20' class="form-control" /> 
						</div>
						<input id='school_id' name='school_id' value='' type='hidden'>
					</div>

					<div class="form-group" id="school-found">
						<div class="col-md-offset-3 col-md-7">
							<div class="alert alert-info">Tu escuela aún no existe en OmegaUp. Será agregada cuando guardes tus cambios.</div>
						</div>
					</div>

					<div class="form-group">
						<label for="locale" class="col-md-3 control-label">Lenguaje</label>
						<div class="col-md-7">
						<select id="locale" name='locale' class="form-control" >
							<option value="es">espa&ntilde;ol</option>
							<option value="en">english</option>
							{if $CURRENT_USER_IS_ADMIN eq '1'}
							<option value="ps-ps">pseudo-loc (hackerboy)</option>
							{/if}
						</select>
						</div>
					</div>
					
					<div class="form-group">
						<label for="scholar_degree" class="col-md-3 control-label">Grado escolar</label>
						<div class="col-md-7">
							<select name='scholar_degree' id='scholar_degree' class="form-control">			
								<option value='Primaria'>Primaria</option>
								<option value='Secundaria'>Secundaria</option>
								<option value='Preparatoria'>Preparatoria</option>
								<option value='Licenciatura'>Licenciatura</option>
								<option value='Maestría'>Maestría</option>
								<option value='Doctorado'>Doctorado</option>
								<option value='Post-doc'>Post-doc</option>
							</select>
						</div>
					</div>
					
					<div class="form-group">
						<label for="graduation_date" class="col-md-3 control-label">Fecha de graduación</label>
						<div class="col-md-7">
							<input id='graduation_date' name='graduation_date' value='' type='text' size ='10' class="form-control">
						</div>
					</div>
					
					<div class="form-group">
						<label class="col-md-3 control-label">Imagen de perfil</label>
						<div class="col-md-7">
							<a href="http://www.gravatar.com" target="_blank" class="btn btn-link">Súbela en Gravatar.com usando tu email: {$CURRENT_USER_EMAIL}</a>
						</div>
					</div>
					
					<div class="form-group">
						<div class="col-md-offset-3 col-md-7">
							<button type='submit' class="btn btn-primary">Guardar cambios</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	
	<script>
{literal}
		$("#birth_date").datepicker();
		$("#graduation_date").datepicker();
		
		$("#school-found").hide();
		$("#school").typeahead({
			ajax: {
				url: "/api/school/list/",
				preProcess: function (data) {
					if (data.length === 0) {
						$("#school-found").slideDown();
						$("#school_id").val(-1);
					} else {
						$("#school-found").slideUp();
					}
					return data;
				}
			},
			display: 'label',
			minLength: 2,
			itemSelected: function (item, val, text) {
				$("#school_id").val(val);											
			}
		});
		
		$('#country_id').change(function () {
			// Clear select
			$('#state_id option').each(function(index, option) {
				$(option).remove();
			});
			
			if ($("#country_id").val() == 'MX') {
				// Enable
				$('#state_id').removeAttr('disabled');
				
				$('#state_id').append($('<option></option>').attr('value', '52').text('Aguascalientes'));
				$('#state_id').append($('<option></option>').attr('value', '53').text('Baja California'));
				$('#state_id').append($('<option></option>').attr('value', '54').text('Baja California Sur'));
				$('#state_id').append($('<option></option>').attr('value', '55').text('Campeche'));
				$('#state_id').append($('<option></option>').attr('value', '56').text('Chiapas'));
				$('#state_id').append($('<option></option>').attr('value', '57').text('Chihuahua'));
				$('#state_id').append($('<option></option>').attr('value', '58').text('Coahuila'));
				$('#state_id').append($('<option></option>').attr('value', '59').text('Colima'));
				$('#state_id').append($('<option></option>').attr('value', '60').text('Distrito Federal'));
				$('#state_id').append($('<option></option>').attr('value', '61').text('Durango'));
				$('#state_id').append($('<option></option>').attr('value', '62').text('Guanajuato'));
				$('#state_id').append($('<option></option>').attr('value', '63').text('Guerrero'));
				$('#state_id').append($('<option></option>').attr('value', '64').text('Hidalgo'));
				$('#state_id').append($('<option></option>').attr('value', '65').text('Jalisco'));
				$('#state_id').append($('<option></option>').attr('value', '66').text('Mexico'));
				$('#state_id').append($('<option></option>').attr('value', '67').text('Michoacan'));
				$('#state_id').append($('<option></option>').attr('value', '68').text('Morelos'));
				$('#state_id').append($('<option></option>').attr('value', '69').text('Nayarit'));
				$('#state_id').append($('<option></option>').attr('value', '70').text('Nuevo Leon'));
				$('#state_id').append($('<option></option>').attr('value', '71').text('Oaxaca'));
				$('#state_id').append($('<option></option>').attr('value', '72').text('Puebla'));
				$('#state_id').append($('<option></option>').attr('value', '73').text('Queretaro'));
				$('#state_id').append($('<option></option>').attr('value', '74').text('Quintana Roo'));
				$('#state_id').append($('<option></option>').attr('value', '75').text('San Luis Potosi'));
				$('#state_id').append($('<option></option>').attr('value', '76').text('Sinaloa'));
				$('#state_id').append($('<option></option>').attr('value', '77').text('Sonora'));
				$('#state_id').append($('<option></option>').attr('value', '78').text('Tabasco'));
				$('#state_id').append($('<option></option>').attr('value', '79').text('Tamaulipas'));
				$('#state_id').append($('<option></option>').attr('value', '80').text('Tlaxcala'));
				$('#state_id').append($('<option></option>').attr('value', '81').text('Veracruz'));
				$('#state_id').append($('<option></option>').attr('value', '82').text('Yucatan'));
				$('#state_id').append($('<option></option>').attr('value', '83').text('Zacatecas'));			
			}
			else if ($("#country_id").val() == 'US') {
				// Enable
				$('#state_id').removeAttr('disabled');
				
				$('#state_id').append($('<option></option>').attr('value', '1').text('Alabama'));
				$('#state_id').append($('<option></option>').attr('value', '2').text('Alaska'));
				$('#state_id').append($('<option></option>').attr('value', '3').text('Arizona'));
				$('#state_id').append($('<option></option>').attr('value', '4').text('Arkansas'));
				$('#state_id').append($('<option></option>').attr('value', '5').text('California'));
				$('#state_id').append($('<option></option>').attr('value', '6').text('Colorado'));
				$('#state_id').append($('<option></option>').attr('value', '7').text('Connecticut'));
				$('#state_id').append($('<option></option>').attr('value', '8').text('Delaware'));
				$('#state_id').append($('<option></option>').attr('value', '9').text('District of Columbia'));
				$('#state_id').append($('<option></option>').attr('value', '10').text('Florida'));
				$('#state_id').append($('<option></option>').attr('value', '11').text('Georgia'));
				$('#state_id').append($('<option></option>').attr('value', '12').text('Hawaii'));
				$('#state_id').append($('<option></option>').attr('value', '13').text('Idaho'));
				$('#state_id').append($('<option></option>').attr('value', '14').text('Illinois'));
				$('#state_id').append($('<option></option>').attr('value', '15').text('Indiana'));
				$('#state_id').append($('<option></option>').attr('value', '16').text('Iowa'));
				$('#state_id').append($('<option></option>').attr('value', '17').text('Kansas'));
				$('#state_id').append($('<option></option>').attr('value', '18').text('Kentucky'));
				$('#state_id').append($('<option></option>').attr('value', '19').text('Louisiana'));
				$('#state_id').append($('<option></option>').attr('value', '20').text('Maine'));
				$('#state_id').append($('<option></option>').attr('value', '21').text('Maryland'));
				$('#state_id').append($('<option></option>').attr('value', '22').text('Massachusetts'));
				$('#state_id').append($('<option></option>').attr('value', '23').text('Michigan'));
				$('#state_id').append($('<option></option>').attr('value', '24').text('Minnesota'));
				$('#state_id').append($('<option></option>').attr('value', '25').text('Mississippi'));
				$('#state_id').append($('<option></option>').attr('value', '26').text('Missouri'));
				$('#state_id').append($('<option></option>').attr('value', '27').text('Montana'));
				$('#state_id').append($('<option></option>').attr('value', '28').text('Nebraska'));
				$('#state_id').append($('<option></option>').attr('value', '29').text('Nevada'));
				$('#state_id').append($('<option></option>').attr('value', '30').text('New Hampshire'));
				$('#state_id').append($('<option></option>').attr('value', '31').text('New Jersey'));
				$('#state_id').append($('<option></option>').attr('value', '32').text('New Mexico'));
				$('#state_id').append($('<option></option>').attr('value', '33').text('New York'));
				$('#state_id').append($('<option></option>').attr('value', '34').text('North Carolina'));
				$('#state_id').append($('<option></option>').attr('value', '35').text('North Dakota'));
				$('#state_id').append($('<option></option>').attr('value', '36').text('Ohio'));
				$('#state_id').append($('<option></option>').attr('value', '37').text('Oklahoma'));
				$('#state_id').append($('<option></option>').attr('value', '38').text('Oregon'));
				$('#state_id').append($('<option></option>').attr('value', '39').text('Pennsylvania'));
				$('#state_id').append($('<option></option>').attr('value', '40').text('Rhode Island'));
				$('#state_id').append($('<option></option>').attr('value', '41').text('South Carolina'));
				$('#state_id').append($('<option></option>').attr('value', '42').text('South Dakota'));
				$('#state_id').append($('<option></option>').attr('value', '43').text('Tennessee'));
				$('#state_id').append($('<option></option>').attr('value', '44').text('Texas'));
				$('#state_id').append($('<option></option>').attr('value', '45').text('Utah'));
				$('#state_id').append($('<option></option>').attr('value', '46').text('Vermont'));
				$('#state_id').append($('<option></option>').attr('value', '47').text('Virginia'));
				$('#state_id').append($('<option></option>').attr('value', '48').text('Washington'));
				$('#state_id').append($('<option></option>').attr('value', '49').text('West Virginia'));
				$('#state_id').append($('<option></option>').attr('value', '50').text('Wisconsin'));
				$('#state_id').append($('<option></option>').attr('value', '51').text('Wyoming'));
				
			} else {
				// Disable
				$('#state_id').attr('disabled','disabled');								
			}			
		});
		
		omegaup.getProfile(null, function(data) {
			$("#username").html(data.userinfo.username);
			$("#name").val(data.userinfo.name);
			$("#birth_date").val(onlyDateToString(data.userinfo.birth_date));
			$("#graduation_date").val(onlyDateToString(data.userinfo.graduation_date));
			$("#country_id").val(data.userinfo.country_id);
			$("#locale").val(data.userinfo.locale);
			
			// Update state dropdown status
			$('#country_id').trigger('change');
			
			$("#state_id").val(data.userinfo.state_id);
			$("#scholar_degree").val(data.userinfo.scholar_degree);
			$("#school_id").val(data.userinfo.school_id);
			$("#school").val(data.userinfo.school);
		});
		
		var formSubmit = function() {
			var birth_date = new Date($("#birth_date").val());
			birth_date.setHours(23);
			
			var graduation_date = new Date($("#graduation_date").val());
			graduation_date.setHours(23);
			
			omegaup.updateProfile($("#name").val(), 
								  birth_date.getTime() / 1000, 
								  $("#country_id").val(), 
								  $("#state_id").val(), 
								  $("#scholar_degree").val(), 
								  graduation_date.getTime() / 1000,
								  $("#school_id").val(),
								  $("#school").val(),
								  $("#locale").val(),
								  function(response){
								  
									if (response.status == "ok") {
										$('#status').html("Perfil actualizado correctamente!");
										$('#status').addClass("alert-success");
										$('#status').slideDown();
										return false;
									}		
			});
			
			return false; // Prevent page refresh on submit
		};
		
		
		$('form#user_profile_form').submit(formSubmit);
{/literal}		
	</script>
	

{/block}





