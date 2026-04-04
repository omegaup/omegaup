<template>
  <div>
    <div class="mb-3 text-right">
      <a class="btn btn-primary mx-1" href="/teamsgroup/new/">{{
        T.teamsGroupsCreateNew
      }}</a>
    </div>
    <div class="card">
      <div class="card-header mb-3">
        <h3 class="card-title">{{ T.omegaupTitleTeamsGroups }}</h3>
      </div>
      <table class="table" data-table-teams-groups>
        <thead>
          <tr>
            <th>{{ T.teamsGroupTeamsGroupName }}</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="teamsGroup in teamsGroups"
            :key="`${teamsGroup.type}_${teamsGroup.alias}`"
          >
            <td>
              <strong>
                <a :href="teamsGroupUrl(teamsGroup)">{{ teamsGroup.name }}</a>
              </strong>
            </td>
            <td>
              <a :href="teamsGroupEditUrl(teamsGroup)" :title="T.wordsEdit">
                <font-awesome-icon :icon="['fas', 'edit']" />
              </a>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faEdit } from '@fortawesome/free-solid-svg-icons';
library.add(faEdit);
@Component({
  components: {
    FontAwesomeIcon,
  },
})
export default class TeamsGroupList extends Vue {
  @Prop() teamsGroups!: types.TeamsGroup[];
  T = T;
  teamsGroupUrl(teamsGroup: types.TeamsGroup): string {
    return `/teamsgroup/${teamsGroup.alias}/edit/#teams`;
  }
  teamsGroupEditUrl(teamsGroup: types.TeamsGroup): string {
    return `/teamsgroup/${teamsGroup.alias}/edit/#edit`;
  }
}
</script>
