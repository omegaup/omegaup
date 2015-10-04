{extends file="profile.tpl"} 
{block name="content"}
	<div class="col-md-10">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h2 class="panel-title">{#userEditEditProfile#}</h2>
			</div>
			<div class="panel-body">
				<form id="user_profile_form" class="form-horizontal" role="form">
					<div class="form-group">
						<label for="name" class="col-md-3 control-label">{#profile#}</label>
						<div class="col-md-7">
							<input id='name' name='name' value='' type='text' size='30' class="form-control">
						</div>
					</div>
					
					<div class="form-group">
						<label for="birth_date" class="col-md-3 control-label">{#userEditBirthDate#}</label>
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
						<label for="state_id" class="col-md-3 control-label">{#profileState#}</label>
						<div class="col-md-7">
							<select name='state_id' id='state_id' disabled="true" class="form-control"></select>
						</div>
					</div>
					
					<div class="form-group">
						<label for="school" class="col-md-3 control-label">{#profileSchool#}</label>
						<div class="col-md-7">
							<input id='school' name='school' value='' type='text' size='20' class="form-control" /> 
						</div>
						<input id='school_id' name='school_id' value='' type='hidden'>
					</div>

					<div class="form-group">
						<label for="locale" class="col-md-3 control-label">{#wordsLanguage#}</label>
						<div class="col-md-7">
						<select id="locale" name='locale' class="form-control" >
							<option value="es">es</option>
							<option value="en">en</option>
							<option value="pt">pt</option>
							<option value="pseudo">pseudo-loc</option>
						</select>
						</div>
					</div>
					
					<div class="form-group">
						<label for="scholar_degree" class="col-md-3 control-label">{#userEditSchoolGrade#}</label>
						<div class="col-md-7">
							<select name='scholar_degree' id='scholar_degree' class="form-control">			
								<option value='{#userEditElementary#}'>{#userEditElementary#}</option>
								<option value='{#userEditMiddleSchool#}'>{#userEditMiddleSchool#}</option>
								<option value='{#userEditHighSchool#}'>{#userEditHighSchool#}</option>
								<option value='{#userEditBachelors#}'>{#userEditBachelors#}</option>
								<option value='Maestría'>Maestría</option>
								<option value='{#userEditDoctorate#}'>{#userEditDoctorate#}</option>
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
						<label class="col-md-3 control-label">{#userEditProfileImage#}</label>
						<div class="col-md-7">
							<a href="http://www.gravatar.com" target="_blank" class="btn btn-link">Súbela en Gravatar.com usando tu email: {$CURRENT_USER_EMAIL}</a>
						</div>
					</div>
					
					<div class="form-group">
						<div class="col-md-offset-3 col-md-7">
							<button type='submit' class="btn btn-primary">{#wordsSaveChanges#}</button>
						</div>
					</div>
				</form>				
			</div>
			
		</div>
						
		<div class="panel panel-default">
			<div class="panel-heading">
				<h2 class="panel-title">{#userEditChangePassword#}</h2>
			</div>
			<div class="panel-body">
				<form id="change-password-form" class="form-horizontal" role="form">
					<div class="form-group">
						<label for="name" class="col-md-3 control-label">{#userEditChangePasswordOldPassword#}</label>
						<div class="col-md-7">
							<input id='old-password' name='name' value='' type='password' size='30' class="form-control">
						</div>
					</div>
					<div class="form-group">
						<label for="name" class="col-md-3 control-label">{#userEditChangePasswordNewPassword#}</label>
						<div class="col-md-7">
							<input id='new-password-1' name='name' value='' type='password' size='30' class="form-control">
						</div>
					</div>
					<div class="form-group">
						<label for="name" class="col-md-3 control-label">{#userEditChangePasswordRepeatNewPassword#}</label>
						<div class="col-md-7">
							<input id='new-password-2' name='name' value='' type='password' size='30' class="form-control">
						</div>
					</div>
						
					<div class="form-group">
						<div class="col-md-offset-3 col-md-7">
							<button type='submit' class="btn btn-primary">{#wordsSaveChanges#}</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	
	<script type="text/javascript" src="/js/user.edit.js?ver=9bb909"></script>
{/block}
