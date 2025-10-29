<template>
  <div class="card">
    <div class="card-body">
      <div class="mb-4">
        <omegaup-markdown :markdown="T.teamsGroupsCsvHelp"></omegaup-markdown>
        <div class="w-100 text-right">
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
        <b-table responsive striped hover :items="items" :fields="columns">
          <template #cell(usernames)="row">
            <b-button
              :title="T.groupEditMembersAddMembers"
              class="d-inline-block mb-2"
              variant="primary"
              @click="row.toggleDetails"
            >
              <font-awesome-icon
                :icon="['fas', 'user-plus']"
                class="mr-2"
              ></font-awesome-icon>
              <b-badge variant="light">{{ row.item.usernames.length }}</b-badge>
            </b-button>
          </template>
          <template #row-details="row">
            <b-form @submit.prevent="onAddUsers(row)">
              <b-card>
                <b-table
                  responsive
                  striped
                  hover
                  :items="row.item.usernames"
                  :fields="identitiesColumns"
                ></b-table>
              </b-card>
            </b-form>
          </template>
        </b-table>
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
              class="ml-2 fa-spin"
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

// Import Bootstrap an BootstrapVue CSS files (order is important)
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';

// Import Only Required Plugins
import {
  TablePlugin,
  ButtonPlugin,
  BadgePlugin,
  CardPlugin,
  BForm,
  BRow,
  BCol,
} from 'bootstrap-vue';
Vue.use(TablePlugin);
Vue.use(ButtonPlugin);
Vue.use(BadgePlugin);
Vue.use(CardPlugin);

type TeamIdentity = types.Identity & {
  usernames: { username: string; password?: string }[];
};

library.add(faDownload, faUserPlus, faSpinner);
@Component({
  components: {
    FontAwesomeIcon,
    'omegaup-common-multi-typeahead': common_MultiTypeahead,
    'omegaup-markdown': omegaup_Markdown,
    BForm,
    BRow,
    BCol,
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
