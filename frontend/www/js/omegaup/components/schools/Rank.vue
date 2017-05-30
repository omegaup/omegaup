<template>
<div class=" panel panel-default" id="school-rank" >
	<div class="panel-heading">
		<h3 class="panel-title">{{T.rankHeaderPreCount}} {{rank.length}} {{T.schoolRankHeaderPostCount}}</h3>
	</div>
	<div class="panel-body no-padding">
		<div class="table-responsive">
			<table class="table table-striped table-hover no-margin" id="school-rank-table">
				<thead>
					<tr>
						<th>#</th>
						<th colspan="2">{{T.profileSchool}}</th>
						<th class="numericColumn data-rank">{{T.activeUsers}}</th>
						<th class="numericColumn data-rank">{{T.profileSolvedProblems}}</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="(school, index) in rank">
						<td>{{index + 1}}</td>
						<td colspan="2" class="cell-school-name">
							<div class="school-name-rank">{{school.name}}</div>
						</td>
						<td class="numericColumn data-rank">{{school.distinct_users}}</td>
						<td class="numericColumn data-rank">{{school.distinct_problems}}</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
</template>

<script>
import {T} from '../../omegaup.js';

export default {
  props: {
    rank: Array,
  },
  data: function() {
    return {
      T: T
    };
  },
};
</script>

<style>
.table-rank {
	width: 100%;
}

.cell-school-name {
	max-width: 0;
}

.school-name-rank {
	overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.data-rank {
	width: 15%;
}

</style>