<template>
<div class="panel panel-primary">
	<div class="panel-heading" v-if="!update">
		<h3 class="panel-title">
            {{T.contestNew}}
		</h3>
	</div>
	<div class="panel-body">
		<div class="btn-group bottom-margin" v-if="update">
            <button class="btn btn-default" v-on:click="contestMode = 'OMI'">{{T.contestNewFormOmiStyle}}</button>
            <button class="btn btn-default" v-on:click="contestMode = 'IOI'">{{T.contestNewForm}}</button>
            <button class="btn btn-default" v-on:click="contestMode = 'CONACUP'">{{T.contestNewFormConacupStyle}}</button>
		</div>
		<form class="new_contest_form" v-on:submit.prevent="onSubmit">
				<div class="row">
					<div class="form-group col-md-6">
                        <label>{{T.wordsTitle}}</label>
                        <input v-model="title" type="text" size="30" class="form-control">
					</div>

					<div class="form-group col-md-6">
                        <label>{{T.contestNewFormShortTitle_alias_}}</label>
                        <input v-model="alias" type="text" class="form-control" disabled="update">
                        <p class="help-block">{{T.contestNewFormShortTitle_alias_Desc}}</p>
					</div>
				</div>

				<div class="row">
					<div class="form-group col-md-6">
                        <label>{{T.contestNewFormStartDate}}</label>
                        <omegaup-datetimepicker v-model="startTime"></omegaup-datetimepicker>
                        <p class="help-block">{{T.contestNewFormStartDateDesc}}</p>
					</div>

					<div class="form-group col-md-6">
                        <label>{{T.contestNewFormEndDate}}</label>
                        <omegaup-datetimepicker v-model="finishTime"></omegaup-datetimepicker>
                        <p class="help-block">{{T.contestNewFormEndDateDesc}}</p>
					</div>
				</div>

				<div class="row">
					<div class="form-group col-md-6">
                        <label>{{T.contestNewFormDescription}}</label>
						<textarea v-model="description" cols="30" rows="10" class="form-control"></textarea>
					</div>

					<div class="form-group col-md-6">
                        <label>{{T.contestNewFormDifferentStarts}}</label>
						<div class="checkbox">
							<label>
                                <input type="checkbox" v-model="windowLengthEnabled">	{{T.wordsEnable}}
							</label>
						</div>
                        <input type="text" :disabled="!windowLengthEnabled" size="3" class="form-control">
                        <p class="help-block">{{T.contestNewFormDifferentStartsDesc}}</p>
					</div>
				</div>

				<div class="row">
					<div class="form-group col-md-6">
                        <label>{{T.contestNewFormScoreboardTimePercent}}</label>
						<input v-model="scoreboard" type="text" size="3" class="form-control">
                        <p class="help-block">{{T.contestNewFormScoreboardTimePercentDesc}}</p>
					</div>

					<div class="form-group col-md-6">
                        <label>{{T.contestNewFormSubmissionsSeparation}}</label>
						<input v-model="submissionsGap" value="1" type="text" size="2" class="form-control">
                        <p class="help-block">{{T.contestNewFormSubmissionsSeparationDesc}}</p>
					</div>
				</div>

				<div class="row">
					<div class="form-group col-md-6">
                        <label>{{T.contestNewFormPenaltyType}}</label>
						<select v-model="penaltyType" class="form-control">
                            <option value="none">{{T.contestNewFormNoPenalty}}</option>
                            <option value="problem_open">{{T.contestNewFormByProblem}}</option>
                            <option value="contest_start">{{T.contestNewFormByContests}}</option>
                            <option value="runtime">{{T.contestNewFormByRuntime}}</option>
						</select>
                        <p class="help-block">{{T.contestNewFormPenaltyTypeDesc}}</p>
					</div>

					<div class="form-group col-md-6">
                        <label>{{T.wordsPenalty}}</label>
						<input v-model="penalty" type="text" size="2" class="form-control">
                        <p class="help-block">{{T.contestNewFormPenaltyDesc}}</p>
					</div>
				</div>

				<div class="row">
					<div class="form-group col-md-6">
                        <label>{{T.wordsFeedback}}</label>
						<select v-model="feedback" class="form-control">
                            <option value="yes">{{T.wordsYes}}</option>
                            <option value="no">{{T.wordsNo}}</option>
                            <option value="partial">{{T.wordsPartial}}</option>
						</select>
                        <p class="help-block">{{T.contestNewFormImmediateFeedbackDesc}}</p>
					</div>

					<div class="form-group col-md-6">
                        <label>{{T.contestNewFormPointDecrementFactor}}</label>
                        <input v-model="pointsDecayFactor" type="text" size="4" class="form-control">
                        <p class="help-block">{{T.contestNewFormPointDecrementFactorDesc}}</p>
					</div>
				</div>

				<div class="row">
					<div class="form-group col-md-6">
                        <label>{{T.contestNewFormScoreboardAtEnd}}</label>
						<select v-model="showScoreboardAfter" class="form-control">
                            <option value="1">{{T.wordsYes}}</option>
                            <option value="0">{{T.wordsNo}}</option>
						</select>
                        <p class="help-block">{{T.contestNewFormScoreboardAtEndDesc}}</p>
					</div>

					<div class="form-group col-md-6">
                        <label>{{T.wordsLanguage}}s</label>
						<br>
						<select class="form-control" multiple="multiple">
							{foreach item=language from=$LANGUAGES}
							<option value="{$language}">{$language}</option>
							{/foreach}
						</select>
                        <p class="help-block">{{T.contestNewFormLanguages}}</p>
					</div>
				</div>

				<div class="row">
					<div class="form-group col-md-6">
                        <label>{{T.contestNewFormBasicInformationRequired}}</label>
						<div class="checkbox">
							<label>
                                <input type="checkbox" v-model="needsBasicInformation">{{T.wordsEnable}}
							</label>
						</div>
                        <p class="help-block">{{T.contestNewFormBasicInformationRequiredDesc}}</p>
					</div>

					<div class="form-group col-md-6">
                        <label>{{T.contestNewFormUserInformationRequired}}</label>
						<select v-model="requestsUserInformation" class="form-control">
                            <option value="no">{{T.wordsNo}}</option>
                            <option value="optional">{{T.wordsOptional}}</option>
                            <option value="required">{{T.wordsRequired}}</option>
						</select>
                        <p class="help-block">{{T.contestNewFormUserInformationRequiredDesc}}</p>
					</div>
				</div>

				<div class="row" v-if="update">
					<div class="form-group col-md-6">
                        <label>{{T.contestNewFormPublic}}</label>
						<select v-model="public" class="form-control">
                            <option value="0">{{T.wordsNo}}</option>
                            <option value="1">{{T.wordsYes}}</option>
						</select>
                        <p class="help-block">{{T.contestNewFormPublicDesc}}</p>
					</div>

					<div class="form-group col-md-6">
                        <label>{{T.contestNewFormRegistration}}</label>
						<select v-model="contestantMustRegister" class="form-control">
                            <option value="0" selected="selected">{{T.wordsNo}}</option>
                            <option value="1">{{T.wordsYes}}</option>
						</select>
                        <p class="help-block">{{T.contestNewFormRegistrationDesc}}</p>
					</div>
				</div>

				<div class="form-group">
                <button type="submit" class="btn btn-primary" v-if="update">{{T.contestNewFormUpdateContest}}</button>
                <button type="submit" class="btn btn-primary" v-else>{{T.contestNewFormScheduleContest}}</button>
				</div>
		</form>
	</div>
</div>
</template>
<script>
import {T} from '../../omegaup.js';
import DateTimePicker from '../DateTimePicker.vue';
export default {
    props: {
        update: Boolean,
        contest: Object
    },
    data: function() {
        return {
            alias: this.contest.alias,
            contestantMustRegister: this.contest.contestant_must_register,
            description: this.contest.description,
            feedback: this.contest.feedback,
            finishTime: this.contest.finish_time,
            scoreboard: this.contest.scoreboard,
            needsBasicInformation: this.contest.needs_basic_information,
            penalty: this.contest.penalty,
            penaltyType: this.contest.penalty_type,
            penaltyCalcPolicy: this.contest.penalty_calc_policy,
            pointsDecayFactor: this.contest.poinst_decay_factor,
            public: this.contest.public,
            requestsUserInformation: this.contest.requests_user_information,
            startTime: this.contest.start_time,
            showPenalty: this.contest.show_penalty,
            showScoreboardAfter: this.contest.show_scoreboard_after,
            submissionsGap: this.contest.submissions_gap,
            title: this.contest.title,
            titlePlaceHolder: "",
            windowLength: this.contest.window_length,
            windowLengthEnabled: this.contest.window_length != null,
            T: T
        }
    },
    methods: {
        fillOmi: function() {
            this.titlePlaceHolder = T.contestNewFormTitlePlaceholderOmiStyle;
            this.windowLengthEnabled = false;
            this.windowLength = "";
            this.scoreboard = 0;
            this.pointsDecayFactor = 0;
            this.submissionsGap = 1;
            this.feedback = "yes";
            this.penalty = 0;
            this.penaltyType = "none";
            this.showScoreboardAfter = true;
        },
        fillPreIoi: function() {
            this.titlePlaceHolder = T.contestNewFormTitlePlaceholderIoiStyle;
            this.windowLengthEnabled = true;
            this.windowLength = 180;
            this.scoreboard = 0;
            this.pointsDecayFactor = 0;
            this.submissionsGap = 0;
            this.feedback = "yes";
            this.penalty = 0;
            this.penaltyType = "none";
            this.showScoreboardAfter = true;
        },
        fillConacup: function() {
            this.titlePlaceHolder = T.contestNewFormTitlePlaceholderConacupStyle;
            this.windowLengthEnabled = false;
            this.windowLength = "";
            this.scoreboard = 75;
            this.pointsDecayFactor = 0;
            this.submissionsGap = 1;
            this.feedback = "yes";
            this.penalty = 20;
            this.penaltyType = "none";
            this.showScoreboardAfter = true;
        },
        onSubmit: function() {
            this.$parent.$emit('updateContest', this);
        }
    },
    components: {
        'omegaup-datetimepicker': DateTimePicker
    },
}
</script>
