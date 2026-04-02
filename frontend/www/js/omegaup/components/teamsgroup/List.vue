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
      <table v-if="teamsGroups.length > 0" class="table data-table-teams-groups">
    <thead>
        <tr>
            <th>{{ T.teamsGroupName }}</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <tr v-for="teamsGroup in teamsGroups" :key="`${teamsGroup.type}_${teamsGroup.alias}`">
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
<div v-else class="empty-category text-center py-5">
    <font-awesome-icon :icon="['fas', 'users']" size="4x" class="text-muted mb-3" />
    <h3 class="mb-2">{{ T.teamsGroupsEmptyTitle }}</h3>
    <p class="text-secondary mb-4">{{ T.teamsGroupsEmptyDescription }}</p>
    <a href="/teamsgroup/new/" class="btn btn-primary btn-md">
        {{ T.teamsGroupsCreateNew }}
    </a>
</div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faEdit, faUsers } from '@fortawesome/free-solid-svg-icons';
library.add(faEdit, faUsers);
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
