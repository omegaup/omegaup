<template>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="bottom-margin">
        <a class="btn btn-primary"
             href="/group/new/">{{ T.groupsCreateNew }}</a>
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
              <td><strong><a v-bind:href="groupScoreboardUrl(group)">{{ group.name
              }}</a></strong></td>
              <td>
                <a class="glyphicon glyphicon-edit"
                    v-bind:href="groupEditUrl(group)"
                    v-bind:title="T.wordsEdit"></a>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Emit } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import omegaup from '../../api.js';

@Component({})
export default class GroupList extends Vue {
  @Prop() groups!: omegaup.Group[];

  T = T;

  groupScoreboardUrl(group: omegaup.Group): string {
    return `/group/${group.alias}/edit/#scoreboards`;
  }

  groupEditUrl(group: omegaup.Group): string {
    return `/group/${group.alias}/edit/#edit`;
  }
}

</script>
