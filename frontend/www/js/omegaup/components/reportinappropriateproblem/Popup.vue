<template>
	<div class="reportinappropriateproblem-popup">
		<a href="#" v-on:click="onReportInappropriateProblem">
			{{ T.wordsReportProblem }}
		</a>
		<form v-show="showReportDialog" class="panel panel-default popup">
			<template v-if="currentView == 'question'">
				<button class="close" type="button" v-on:click="onHide">×</button>
				<div class="title-text">
					{{ T.reportProblemFormTitle }}
				</div>
				<div class="form-group">
					<div class="question-text">
						{{ T.reportProblemFormQuestion }}
					</div>
					<select name="selectedReason" v-model="selectedReason" class="control-label"> 
						<option value="not a statement">{{ T.reportProblemFormNotAProblemStatement }}</option>
						<option value="offensive">{{ T.reportProblemFormOffensive }}</option>
						<option value="spam">{{ T.reportProblemFormSpam }}</option>
						<option value="other">{{ T.reportProblemFormOtherReason }}</option>
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">{{ T.reportProblemFormAdditionalComments }}</label>
					<textarea name="rationale" class="input-text" type="text" v-model="rationale"></textarea>
				</div>
				<div class="button-row">
					<button class="col-md-4 btn btn-primary"
						type="submit"
						v-bind:disabled="selectedReason == undefined ? true : (rationale.length &lt;= 0 ? selectedReason == 'other' : false)"
						v-on:click.prevent="onSubmit">{{ T.wordsSend }}</button>
				</div>
			</template>
			<template v-if="currentView == 'thanks'">
				<div class="centered">
					<h1>{{ T.reportProblemFormThanksForReview }}</h1>
				</div>
			</template>
		</form>
	</div>
</template>

<script>
import {T} from '../../omegaup.js';
import UI from '../../ui.js';

export default {
	props: {},

	data: function() {
		return {
			T: T,
			UI: UI,
			rationale: '',
			currentView: 'question',
			showReportDialog: false,
			selectedReason: undefined
		};
	},

	methods: {

		onHide() {
			this.showReportDialog = false;
		},

		onReportInappropriateProblem() {
			this.showReportDialog = true;
			this.currentView = 'question';
			this.rationale = '';
			this.selectedReason = undefined;
		},

		onSubmit() {
			this.$emit('submit', this);
			this.currentView = 'thanks';
			var self = this;
			setTimeout(function() { self.onHide() }, 1000);
		}
	}
};

</script>

<style>

.reportinappropriateproblem-popup .popup {
	position: fixed;
	bottom: 10px;
	right: 4%;
	z-index: 9999999 !important;
	width: 420px;
	height: 310px;
	margin: 2em auto 0 auto;
	border: 2px solid #ccc;
	padding: 1em;
	overflow: auto;
}

.reportinappropriateproblem-popup .question-text {
	font-weight: bold;
	padding-bottom: 4px;
}

.reportinappropriateproblem-popup .title-text {
	font-weight: bold;
	font-size: 1em;
	padding-bottom: 1em;
}

.reportinappropriateproblem-popup .control-label {
	width: 100%;
}

.reportinappropriateproblem-popup .input-text {
	height: 100px;
	width: 100%;
}

.reportinappropriateproblem-popup .button-row {
	width: 100%;
	margin-left: 66%;
}	

.reportinappropriateproblem-popup .centered {
	margin-left: 20%;
	margin-top: 24%;
	position: absolute;
}	
</style>
