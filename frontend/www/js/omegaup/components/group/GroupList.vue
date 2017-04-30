<template>
<div class="panel panel-default">
	<div class="panel-body">
		<div class="bottom-margin">
			<a href="/group/new/" class="btn btn-primary">{{ T.groupsCreateNew }}</a>
		</div>

    <div class="panel panel-default no-bottom-margin">
      <div class="panel-heading">
        <h3 class="panel-title">{{ T.wordsGroups }}</h3>
      </div>
      <table class="table">
        <thead>
        <tr>
          <th>{{ T.wordsTitle }}</th>
          <th></th>
        </tr>
        </thead>
        <tbody>
          <tr v-for="group in groups">
            <td><b>
              <a v-bind:href="groupScoreboardUrl(group)">{{ group.name }}</a>
            </b></td>
            <td>
              <a class="glyphicon glyphicon-edit"
                  v-bind:title="T.wordsEdit"
                  v-bind:href="groupEditUrl(group)"></a>
            </td>
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
    groups: Array,
  },
  data: function() {
    return {
      T: T,
    };
  },
  methods: {
    groupScoreboardUrl: function(group) {
      return '/group/' + group.alias + '/edit/#scoreboards';
    },
    groupEditUrl: function(group) {
      return '/group/' + group.alias + '/edit/#edit';
    },
  },
};
</script>