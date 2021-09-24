<template>
  <div class="card">
    <div class="card-body">
      <div class="mb-4">
        <omegaup-markdown :markdown="T.teamsGroupsCsvHelp"></omegaup-markdown>
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
            @click.prevent="
              $emit('bulk-identities', { identities, identitiesTeams })
            "
          >
            {{ T.teamsGroupCreateIdentitiesAsTeams }}
          </button>
          <div>
            <button
              class="btn btn-warning d-inline-block"
              @click.prevent="$emit('download-teams', identities)"
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
import { faDownload, faUserPlus } from '@fortawesome/free-solid-svg-icons';
import common_MultiTypeahead from '../common/MultiTypeahead.vue';

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

library.add(faDownload, faUserPlus);
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

  T = T;
  identities: types.Identity[] = [];
  identitiesTeams: { [team: string]: string[] } = {};
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

  get items() {
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

  onAddUsers(row: BRow): void {
    this.identitiesTeams[row.item.username] = this.typeaheadUsers.map(
      (user) => user.key,
    );
    Vue.set(row.item, 'usernames', this.identitiesTeams[row.item.username]);
    row.toggleDetails();
  }
}
</script>
