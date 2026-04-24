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
              <a
                href="#"
                class="ml-2 text-danger"
                :title="T.groupArchive"
                @click.prevent="onArchive(group)"
              >
                <font-awesome-icon :icon="['fas', 'archive']" />
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
import { faEdit, faArchive } from '@fortawesome/free-solid-svg-icons';
library.add(faEdit, faArchive);

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
  onArchive(group: types.Group): void {
    if (!window.confirm(T.groupArchiveConfirm)) return;
    this.$emit('archive', group.alias);
  }
}
</script>
