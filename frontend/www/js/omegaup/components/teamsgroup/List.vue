<template>
  <div>
    <div v-if="hasTeamsGroups" class="mb-3 text-right">
      <a class="btn btn-primary mx-1" href="/teamsgroup/new/">
        {{ T.teamsGroupsCreateNew }}
      </a>
    </div>
    <div class="card">
      <div
        class="card-header d-flex justify-content-between align-items-center"
      >
        <h3 class="card-title mb-0">{{ T.omegaupTitleTeamsGroups }}</h3>
        <label class="mb-0">
          <input v-model="showArchived" type="checkbox" />
          {{ T.teamsGroupShowArchived }}
        </label>
      </div>

      <table v-if="hasTeamsGroups" class="table" data-table-teams-groups>
        <thead>
          <tr>
            <th>{{ T.teamsGroupTeamsGroupName }}</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="teamsGroup in visibleTeamsGroups"
            :key="`${teamsGroup.type}_${teamsGroup.alias}`"
          >
            <td>
              <strong>
                <a :href="teamsGroupUrl(teamsGroup)">
                  {{ teamsGroup.name }}
                </a>
              </strong>
            </td>
            <td>
              <a :href="teamsGroupEditUrl(teamsGroup)" :title="T.wordsEdit">
                <font-awesome-icon :icon="['fas', 'edit']" />
              </a>

              <button
                class="btn btn-link p-0 ml-2 btn-archive"
                :title="teamsGroup.archived ? T.wordsUnarchive : T.wordsArchive"
                @click="archiveGroup(teamsGroup)"
              >
                <font-awesome-icon
                  :icon="
                    teamsGroup.archived
                      ? ['fas', 'box-open']
                      : ['fas', 'archive']
                  "
                />
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <omegaup-common-empty-state
        v-else
        :icon="['fas', 'users']"
        :title="T.teamsGroupEmptyTitle"
        :description="T.teamsGroupEmptyDescription"
        :button-text="T.createTeamsGroup"
        button-link="/teamsgroup/new/"
      />
    </div>

    <b-modal
      v-model="showArchiveModal"
      :title="archiveModalTitle"
      :ok-title="T.wordsYes"
      :cancel-title="T.wordsNo"
      ok-variant="primary"
      cancel-variant="secondary"
      @ok="confirmArchive"
    >
      <p>{{ archiveModalBody }}</p>
    </b-modal>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
  faEdit,
  faArchive,
  faBoxOpen,
} from '@fortawesome/free-solid-svg-icons';
import common_EmptyState from '../common/EmptyState.vue';

library.add(faEdit, faArchive, faBoxOpen);
@Component({
  components: {
    FontAwesomeIcon,
    'omegaup-common-empty-state': common_EmptyState,
  },
})
export default class TeamsGroupList extends Vue {
  @Prop() teamsGroups!: types.TeamsGroup[];
  T = T;
  showArchiveModal = false;
  selectedTeamsGroup: types.TeamsGroup | null = null;

  teamsGroupUrl(teamsGroup: types.TeamsGroup): string {
    return `/teamsgroup/${teamsGroup.alias}/edit/#teams`;
  }
  teamsGroupEditUrl(teamsGroup: types.TeamsGroup): string {
    return `/teamsgroup/${teamsGroup.alias}/edit/#edit`;
  }
  get hasTeamsGroups(): boolean {
    return this.visibleTeamsGroups && this.visibleTeamsGroups.length > 0;
  }
  showArchived: boolean = false;
  get visibleTeamsGroups(): types.TeamsGroup[] {
    if (this.showArchived) {
      return this.teamsGroups.filter((g) => g.archived);
    }
    return this.teamsGroups.filter((g) => !g.archived);
  }

  get archiveModalTitle(): string {
    if (!this.selectedTeamsGroup) return '';
    return this.selectedTeamsGroup.archived ? T.wordsUnarchive : T.wordsArchive;
  }

  get archiveModalBody(): string {
    if (!this.selectedTeamsGroup) return '';
    return this.selectedTeamsGroup.archived
      ? T.teamsGroupUnarchiveConfirmText
      : T.teamsGroupArchiveConfirmText;
  }

  archiveGroup(teamsGroup: types.TeamsGroup) {
    this.selectedTeamsGroup = teamsGroup;
    this.showArchiveModal = true;
  }

  confirmArchive() {
    if (!this.selectedTeamsGroup) return;
    this.$emit('archive-group', {
      teamsGroup: this.selectedTeamsGroup,
      archived: !this.selectedTeamsGroup.archived,
    });
    this.selectedTeamsGroup = null;
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.btn-archive {
  color: var(--teams-group-archive-btn-color);
}
</style>
