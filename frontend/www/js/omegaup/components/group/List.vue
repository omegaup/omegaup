<template>
  <div>
    <div class="mb-3">
      <h3 class="card-title text-center">{{ T.wordsGroups }}</h3>
    </div>
    <div class="card ml-lg-4 mr-lg-4">
      <div class="card-header text-right">
        <a class="btn btn-primary" href="/group/new/">{{
          T.groupsCreateNew
        }}</a>
      </div>
      <table class="table mb-0" data-table-groups>
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
            <td class="text-right pr-lg-3">
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
