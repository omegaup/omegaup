<template>
  <div>
    <div class="mb-3 text-right">
      <a class="btn btn-primary" href="/group/new/">{{ T.groupsCreateNew }}</a>
    </div>
    <div class="card">
      <div class="card-header mb-3">
        <h3 class="card-title">{{ T.wordsGroups }}</h3>
      </div>
      <table class="table" data-table-groups>
        <thead>
          <tr>
            <th>{{ T.wordsTitle }}</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="group in groups" :key="group.alias">
            <td>
              <strong
                ><a :href="groupScoreboardUrl(group)">{{
                  group.name
                }}</a></strong
              >
            </td>
            <td>
              <a :href="groupEditUrl(group)" :title="T.wordsEdit">
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
export default class GroupList extends Vue {
  @Prop() groups!: types.Group[];

  T = T;

  groupScoreboardUrl(group: types.Group): string {
    return `/group/${group.alias}/edit/#scoreboards`;
  }

  groupEditUrl(group: types.Group): string {
    return `/group/${group.alias}/edit/#edit`;
  }
}
</script>
