{extends file="profile.tpl"}
{block name="content"}
	<div class="col-md-10">
		<div class="panel panel-default">
			<div class="panel-body hidden">
				<div id="verdict-chart"><img src="/media/wait.gif" /></div>
			</div>
			<div class="panel-heading">
				<h2 class="panel-title">{#userEditEditProfile#}</h2>
			</div>
			<div class="panel-body">
				<form id="user_profile_form" class="form-horizontal" role="form">
					<div class="form-group">
						<label for="name" class="col-md-4 control-label">{#profile#}</label>
						<div class="col-md-7">
							<input id='name' name='name' value='' type='text' size='30' class="form-control">
						</div>
					</div>

					<div class="form-group">
						<label for="birth_date" class="col-md-4 control-label">{#userEditBirthDate#}</label>
						<div class="col-md-7">
							<input id='birth_date' name='birth_date' value='' type='text' size ='10' class="form-control">
						</div>
					</div>

					<div class="form-group">
						<label for="gender" class="col-md-4 control-label">{#wordsGender#}</label>
						<div class="col-md-7">
						<select id="gender" name='gender' class="form-control" >
							<option value="female">{#wordsGenderFemale#}</option>
							<option value="male">{#wordsGenderMale#}</option>
							<option value="other">{#wordsGenderOther#}</option>
							<option value="decline">{#wordsGenderDecline#}</option>
						</select>
						</div>
					</div>

					<div class="form-group">
						<label for="country_id" class="col-md-4 control-label">{#userEditCountry#}</label>
						<div class="col-md-7">
							<select name='country_id' id='country_id' class="form-control">
								<option value=""></option>
								{foreach from=$COUNTRIES item=country}
								<option value="{$country->country_id}">{$country->name}</option>
								{/foreach}
							</select>
						</div>
					</div>

					<div class="form-group">
						<label for="state_id" class="col-md-4 control-label">{#profileState#}</label>
						<div class="col-md-7">
							<select name='state_id' id='state_id' disabled="true" class="form-control"></select>
						</div>
					</div>

					<div class="form-group">
						<label for="school" class="col-md-4 control-label">{#profileSchool#}</label>
						<div class="col-md-7">
							<input id='school' name='school' value='' type='text' size='20' class="form-control" />
							<label title="{#profileUploadLogoSchool#}" {if $profile.userinfo.school_logo}class="hidden"{/if}>
								<input type="file" accept="image/*"/>
							</label>
						</div>
						<input id='school_id' name='school_id' value="" type='hidden'>
					</div>

					<div class="form-group">
						<label for="locale" class="col-md-4 control-label">{#userEditLanguage#}</label>
						<div class="col-md-7">
						<select id="locale" name='locale' class="form-control" >
							<option value="es">{#wordsSpanish#}</option>
							<option value="en">{#wordsEnglish#}</option>
							<option value="pt">{#wordsPortuguese#}</option>
							<option value="pseudo">pseudo-loc</option>
						</select>
						</div>
					</div>

					<div class="form-group">
						<label for="scholar_degree" class="col-md-4 control-label">{#userEditSchoolGrade#}</label>
						<div class="col-md-7">
							<select name="scholar_degree" id="scholar_degree" class="form-control">
								<option value="Elementary">{#userEditElementary#}</option>
								<option value="Middle school">{#userEditMiddleSchool#}</option>
								<option value="High school">{#userEditHighSchool#}</option>
								<option value="Bachelor's">{#userEditBachelors#}</option>
								<option value="Master's">{#userEditMasters#}</option>
								<option value="Doctorate">{#userEditDoctorate#}</option>
								<option value="Post-doc">Post-doc</option>
							</select>
						</div>
					</div>

					<div class="form-group">
						<label for="programming_language" class="col-md-4 control-label">{#userEditPreferredProgrammingLanguage#}</label>
						<div class="col-md-7">
						<select id="programming_language" name="programming_language" class="form-control" >
							<option value=""></option>
							{foreach from=$PROGRAMMING_LANGUAGES item=programming_language}
							<option value="{$programming_language}">{$programming_language}</option>
							{/foreach}
						</select>
						</div>
					</div>

					<div class="form-group">
						<label for="graduation_date" class="col-md-4 control-label">{#userEditGraduationDate#}</label>
						<div class="col-md-7">
							<input id='graduation_date' name='graduation_date' value='' type='text' size ='10' class="form-control">
						</div>
					</div>

					<div class="form-group">
						<label class="col-md-4 control-label">{#userEditProfileImage#}</label>
						<div class="col-md-7">
							<a href="http://www.gravatar.com" target="_blank" class="btn btn-link">{#userEditGravatar#} {$CURRENT_USER_EMAIL}</a>
						</div>
					</div>

					<div class="form-group">
						<span class="col-md-4 control-label">&nbsp;</span>
						<div class="col-md-7">
							<input type="checkbox" id="recruitment_optin" name='recruitment_optin'>
							<label for="recruitment_optin" style="display: inline;">{#userEditRecruitmentOptin#}</label>
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
						<label for="name" class="col-md-4 control-label">{#userEditChangePasswordOldPassword#}</label>
						<div class="col-md-7">
							<input id='old-password' name='name' value='' type='password' size='30' class="form-control">
						</div>
					</div>
					<div class="form-group">
						<label for="name" class="col-md-4 control-label">{#userEditChangePasswordNewPassword#}</label>
						<div class="col-md-7">
							<input id='new-password-1' name='name' value='' type='password' size='30' class="form-control">
						</div>
					</div>
					<div class="form-group">
						<label for="name" class="col-md-4 control-label">{#userEditChangePasswordRepeatNewPassword#}</label>
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

	<script type="text/javascript" src="{version_hash src="/js/user.edit.js"}"></script>
{/block}
