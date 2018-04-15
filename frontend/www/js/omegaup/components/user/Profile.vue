<template>
<div class="row">
	<div class="col-md-2 no-right-padding">
		<div class="panel panel-default">
			<div class="panel-body">
				<div class="thumbnail bottom-margin"> <img v-bind:src="profile.gravatar_92"/></div>
				<div v-if="profile.email"><a href="/profile/edit/" class="btn btn-default">{{ T.profileEdit }}</a></div>
			</div>
		</div>
	</div>

	<div class="col-md-10 no-right-padding">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h2 class="panel-title">
					{{ profile.rankinfo.rank > 0 ? `#${profile.rankinfo.rank} - `: '' }}
					<omegaup-user-rankcolor
						v-bind:username="profile.username"
						v-bind:classname="profile.classname"
					></omegaup-user-rankcolor>
					<img v-if="profile.country_id" v-bind:src="`/media/flags/${profile.country_id}.png`" width="16" height="11" v-bind:title="profile.country_id"/>
				</h2>
			</div>
			<omegaup-user-basicinfo
				v-bind:name="profile.name"
				v-bind:username="profile.username"
				v-bind:classname="profile.classname"
				v-bind:email="profile.email"
				v-bind:country="profile.country"
				v-bind:state="profile.state"
    		v-bind:school="profile.school"
				v-bind:graduationDate="profile.graduation_date"
				v-bind:rank="rank">
			</omegaup-user-basicinfo>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading">
				<h2 class="panel-title">{{ T.profileContests }} <span class="badge">{{ contests ? contests.length:0 }}</span></h2>
			</div>
			<table class="table table-striped">
				<thead>
					<tr>
						<th>{{ T.profileContestsTableContest }}</th>
						<th>{{ T.profileContestsTablePlace }}</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="contest in contests">
						<td>
							<a v-bind:href="`/arena/${contest.data.alias}`">{{ contest.data.title }}</a>
						</td>
						<td>
							<b>{{ contest.place }}</b>
						</td>
					</tr>
				</tbody>
			</table>
			<div v-show="!contests"><img src="/media/wait.gif" /></div>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading">
				<h2 class="panel-title">{{ T.profileSolvedProblems }} <span class="badge">{{ solved_problems ? solved_problems.length:0 }}</span></h2>
			</div>
			<table class="table table-striped">
				<thead>
					<tr>
						<th colspan="3">{{ T.profileSolvedProblemsTableTitle }}</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="group in solved_problems">
						<td v-for="problem in group">
							<a v-bind:href="`/arena/problem/${problem.alias}`">{{ problem.title }}</a>
						</td>
					</tr>
				</tbody>
			</table>
			<div v-show="!solved_problems"><img src="/media/wait.gif" /></div>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading">
				<h2 class="panel-title">{{ T.profileUnsolvedProblems }} <span class="badge">{{ unsolved_problems ? unsolved_problems.length:0 }}</span></h2>
			</div>
			<table class="table table-striped">
				<thead>
					<tr>
						<th colspan="3">{{ T.profileUnsolvedProblemsTableTitle }}</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="group in unsolved_problems">
						<td v-for="problem in group">
							<a v-bind:href="`/arena/problem/${problem.alias}`">{{ problem.title }}</a>
						</td>
					</tr>
				</tbody>
			</table>
			<div v-show="!unsolved_problems"><img src="/media/wait.gif" /></div>
		</div>

		<div class="panel panel-default no-bottom-margin">
			<div class="panel-heading">
				<h2 class="panel-title">{{ T.profileStatistics }}</h2>
			</div>
			<omegaup-user-charts
				v-if="charts"
				v-bind:data="charts"
				v-bind:username="profile.username">
			</omegaup-user-charts>
		</div>

	</div>

</div>
</template>

<script>
import {T} from '../../omegaup.js';
import user_BasicInfo from './BasicInfo.vue';
import user_RankColor from './RankColor.vue';
import user_Charts from './Charts.vue';
import ChartsVue from './Charts.vue';
export default {
	props: {
		profile: Object,
		contests: Array,
		solved_problems: Array,
		unsolved_problems: Array,
		rank: String,
		charts: Object,
	},
	data: function() {
		return {
			T: T,
		}
	},
	components: {
		'omegaup-user-basicinfo': user_BasicInfo,
		'omegaup-user-rankcolor': user_RankColor,
		'omegaup-user-charts': user_Charts,
	}
}
</script>
