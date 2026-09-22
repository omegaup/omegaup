<template>
  <div class="card">
    <div class="card-body">
      <div class="mb-4">
        <omegaup-markdown :markdown="T.teamsGroupsCsvHelp"></omegaup-markdown>
        <div class="w-100 text-end">
          <a target="_blank" :href="SolutionViewFeatureGuideURL">
            {{ T.teamsGroupsCsvHelpMoreInfo }}
          </a>
        </div>
        <div class="card">
          <div class="container">
            <div class="row">
              <div class="col-sm form-check m-4">
                {{ T.groupsUploadCsvFile }}
                <input
                  name="identities"
                  type="file"
                  accept=".csv,.txt"
                  @change="readCsv"
                />
              </div>
              <div class="col-sm form-check my-4">
                <div class="container">
                  <h5 class="row">
                    {{ T.teamsGroupTeamsAdvancedOptions }}
                  </h5>
                  <div class="row">
                    <label class="form-check-label">
                      <input
                        v-model="humanReadable"
                        class="form-check-input"
                        type="checkbox"
                      />
                      {{ T.passwordHumanReadable }}
                    </label>
                  </div>
                  <div class="row">
                    <label class="form-check-label">
                      <input
                        v-model="selfGeneratedIdentities"
                        class="form-check-input"
                        type="checkbox"
                      />
                      {{ T.teamsGroupTeamsSelfGenerateIdentities }}
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <template v-if="identities.length > 0">
        <h3 class="card-header">{{ T.teamsGroupEditTeams }}</h3>
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th v-for="column in columns" :key="column.key">
                  {{ column.label }}
                </th>
              </tr>
            </thead>
            <tbody>
              <template v-for="item in items">
                <tr :key="item.username">
                  <th>{{ item.username }}</th>
                  <td>{{ item.name }}</td>
                  <td>{{ item.country_id }}</td>
                  <td>{{ item.state_id }}</td>
                  <td>{{ item.gender }}</td>
                  <td>{{ item.school_name }}</td>
                  <td>
                    <button
                      type="button"
                      :title="T.groupEditMembersAddMembers"
                      class="btn btn-primary d-inline-block mb-2"
                      @click="toggleDetails(item.username)"
                    >
                      <font-awesome-icon
                        :icon="['fas', 'user-plus']"
                        class="me-2"
                      ></font-awesome-icon>
                      <span class="badge text-bg-light">{{
                        item.usernames.length
                      }}</span>
                    </button>
                  </td>
                </tr>
                <tr
                  v-if="isExpanded(item.username)"
                  :key="`${item.username}-details`"
                >
                  <td :colspan="columns.length">
                    <div class="card">
                      <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                          <thead>
                            <tr>
                              <th
                                v-for="column in identitiesColumns"
                                :key="column.key"
                              >
                                {{ column.label }}
                              </th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr
                              v-for="identity in item.usernames"
                              :key="identity.username"
                            >
                              <td>{{ identity.username }}</td>
                              <td>{{ identity.password }}</td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
        <div class="card-footer">
          <button
            class="btn btn-primary d-inline-block mb-2"
            name="create-identities"
            :disabled="isLoading"
            @click.prevent="
              $emit('bulk-identities', { identities, identitiesTeams })
            "
          >
            {{
              !isLoading
                ? T.teamsGroupCreateIdentitiesAsTeams
                : T.teamsGroupCreatingIdentitiesAsTeams
            }}
            <font-awesome-icon
              v-if="isLoading"
              :icon="['fas', 'spinner']"
              class="ms-2 fa-spin"
            ></font-awesome-icon>
          </button>
          <div>
            <button
              class="btn btn-warning d-inline-block"
              data-download-csv-button
              @click.prevent="downloadIdentitiesCSV"
            >
              <font-awesome-icon :icon="['fas', 'download']" />
            </button>
            {{ T.groupsIdentityWarning }}
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import omegaup_Markdown from '../Markdown.vue';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
  faDownload,
  faUserPlus,
  faSpinner,
} from '@fortawesome/free-solid-svg-icons';
import common_MultiTypeahead from '../common/MultiTypeahead.vue';
import { getBlogUrl } from '../../urlHelper';

type TeamIdentity = types.Identity & {
  usernames: { username: string; password?: string }[];
};

library.add(faDownload, faUserPlus, faSpinner);
@Component({
  components: {
    FontAwesomeIcon,
    'omegaup-common-multi-typeahead': common_MultiTypeahead,
    'omegaup-markdown': omegaup_Markdown,
  },
})
export default class Upload extends Vue {
  @Prop({ default: null }) userErrorRow!: string | null;
  @Prop() searchResultUsers!: types.ListItem[];
  @Prop() numberOfContestants!: number;
  @Prop() isLoading!: boolean;

  T = T;
  get SolutionViewFeatureGuideURL(): string {
    return getBlogUrl('SolutionViewFeatureGuideURL');
  }
  identities: types.Identity[] = [];
  identitiesTeams: {
    [team: string]: { username: string; password?: string }[];
  } = {};
  humanReadable = false;
  selfGeneratedIdentities = false;
  typeaheadUsers: types.ListItem[] = [];
  expandedUsernames: string[] = [];
  columns = [
    {
      key: 'username',
      label: T.teamsGroupTeamName,
      stickyColumn: true,
      isRowHeader: true,
    },
    { key: 'name', label: T.profile },
    { key: 'country_id', label: T.profileCountry },
    { key: 'state_id', label: T.profileState },
    { key: 'gender', label: T.wordsGender },
    { key: 'school_name', label: T.profileSchool },
    { key: 'usernames', label: T.teamsGroupUsernames },
  ];
  identitiesColumns = [
    { key: 'username', label: T.profileUsername },
    { key: 'password', label: T.loginPassword },
  ];

  get items(): TeamIdentity[] {
    return this.identities.map((identity) => ({
      ...identity,
      usernames: this.identitiesTeams[identity.username],
    }));
  }

  toggleDetails(username: string): void {
    const index = this.expandedUsernames.indexOf(username);
    if (index === -1) {
      this.expandedUsernames.push(username);
    } else {
      this.expandedUsernames.splice(index, 1);
    }
  }

  isExpanded(username: string): boolean {
    return this.expandedUsernames.includes(username);
  }

  readFile(e: HTMLInputElement): File | null {
    return (e.files && e.files[0]) || null;
  }
  readCsv(ev: Event): void {
    const file = this.readFile(ev.target as HTMLInputElement);
    if (!file || file.name === '') {
      return;
    }
    const regex = /.*\.(?:csv|txt)$/;
    if (!regex.test(file.name.toLowerCase())) {
      this.$emit('invalid-file');
      return;
    }
    this.identities = [];
    this.$emit('read-csv', {
      identitiesTeams: this.identitiesTeams,
      identities: this.identities,
      file: file,
      humanReadable: this.humanReadable,
      selfGeneratedIdentities: this.selfGeneratedIdentities,
      numberOfContestants: this.numberOfContestants,
    });
  }

  downloadIdentitiesCSV() {
    const participants: types.Participant[] = [];
    for (const team of this.items) {
      if (!team.usernames) {
        continue;
      }
      for (const participant of team.usernames) {
        participants.push({
          country_id: team.country_id,
          gender: team.gender,
          name: team.name,
          school_name: team.school_name,
          state_id: team.state_id,
          username: team.username,
          participant_username: participant.username,
          participant_password: participant.password,
        });
      }
    }

    this.$emit('download-teams', participants);
  }
}
</script>
