<template>
  <div class="card">
    <div v-if="isOrganizer" class="card-body introjs-info">
      <div class="mb-4">
        <omegaup-markdown
          :markdown="T.groupsCsvHelp"
          class="introjs-information"
        ></omegaup-markdown>
        <div class="form-check mb-4 introjs-password">
          <label class="form-check-label">
            <input
              v-model="humanReadable"
              class="form-check-input"
              type="checkbox"
            />
            {{ T.passwordHumanReadable }}
          </label>
        </div>
        {{ T.groupsUploadCsvFile }}
        <input
          name="identities"
          type="file"
          accept=".csv,.txt"
          class="introjs-upload"
          @change="readCsv"
        />
      </div>
      <template v-if="identities.length > 0">
        <h3 class="card-header">{{ T.wordsIdentities }}</h3>
        <div class="table-responsive">
          <table class="table" data-identities-table>
            <thead>
              <tr>
                <th>{{ T.profileUsername }}</th>
                <th>{{ T.profile }}</th>
                <th>{{ T.loginPassword }}</th>
                <th>{{ T.profileCountry }}</th>
                <th>{{ T.profileState }}</th>
                <th>{{ T.wordsGender }}</th>
                <th>{{ T.profileSchool }}</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="identity in identities"
                :key="identity.username"
                :class="{ 'alert-danger': userErrorRow === identity.username }"
              >
                <td class="username" data-identity-username>
                  <strong>{{ identity.username }}</strong>
                </td>
                <td>{{ identity.name }}</td>
                <td class="password" data-identity-password>
                  {{ identity.password }}
                </td>
                <td>
                  {{
                    identity.country_id === 'xx'
                      ? T.countryNotSet
                      : identity.country_id
                  }}
                </td>
                <td>{{ identity.state_id }}</td>
                <td>{{ identity.gender }}</td>
                <td>{{ identity.school_name }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="card-footer">
          <button
            class="btn btn-primary d-inline-block mb-2"
            name="create-identities"
            @click.prevent="$emit('bulk-identities', identities)"
          >
            {{ T.groupCreateIdentities }}
          </button>
          <div>
            <button
              class="btn btn-warning d-inline-block"
              @click.prevent="
                $emit('download-identities', identities, humanReadable)
              "
            >
              <font-awesome-icon :icon="['fas', 'download']" />
            </button>
            {{ T.groupsIdentityWarning }}
          </div>
        </div>
      </template>
    </div>
    <div v-else class="mt-5">
      <omegaup-markdown
        :markdown="T.groupIdentitiesNotRequiredPrivileges"
      ></omegaup-markdown>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import omegaup_Markdown from '../Markdown.vue';
import 'intro.js/introjs.css';
import introJs from 'intro.js';
import VueCookies from 'vue-cookies';
Vue.use(VueCookies, { expire: -1 });

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faDownload } from '@fortawesome/free-solid-svg-icons';
library.add(faDownload);

@Component({
  components: {
    FontAwesomeIcon,
    'omegaup-markdown': omegaup_Markdown,
  },
})
export default class Identities extends Vue {
  @Prop() groupAlias!: string;
  @Prop() userErrorRow!: string | null;
  @Prop() hasVisitedSection!: boolean;
  @Prop() isOrganizer!: boolean;

  T = T;
  identities: types.Identity[] = [];
  humanReadable = false;

  mounted() {
    if (!this.isOrganizer) {
      return;
    }
    const title = T.createIdentitiesInteractiveGuideTitle;
    if (!this.hasVisitedSection) {
      introJs()
        .setOptions({
          nextLabel: T.interactiveGuideNextButton,
          prevLabel: T.interactiveGuidePreviousButton,
          doneLabel: T.interactiveGuideDoneButton,
          steps: [
            {
              title,
              intro: T.createIdentitiesInteractiveGuideWelcome,
            },
            {
              element: document.querySelector(
                '.introjs-information p:nth-child(1)',
              ) as Element,
              title,
              intro: T.createIdentitiesInteractiveGuideInformation,
            },
            {
              element: document.querySelector(
                '.introjs-information p:nth-child(2)',
              ) as Element,
              title,
              intro: T.createIdentitiesInteractiveGuideFormat,
            },
            {
              element: document.querySelector(
                '.introjs-information pre',
              ) as Element,
              title,
              intro: T.createIdentitiesInteractiveGuideExample,
            },
            {
              element: document.querySelector(
                '.introjs-information button',
              ) as Element,
              title,
              intro: T.createIdentitiesInteractiveGuideCopy,
            },
            {
              element: document.querySelector('.introjs-password') as Element,
              title,
              intro: T.createIdentitiesInteractiveGuidePassword,
            },
            {
              element: document.querySelector('.introjs-upload') as Element,
              title,
              intro: T.createIdentitiesInteractiveGuideUpload,
            },
            {
              element: document.querySelector('.introjs-info') as Element,
              title,
              intro: T.createIdentitiesInteractiveGuideInformationPassword,
            },
            {
              element: document.querySelector('.introjs-info') as Element,
              title,
              intro: T.createIdentitiesInteractiveGuideInformationConfirm,
            },
          ],
        })
        .start();
      this.$cookies.set('has-visited-create-identities', true, -1);
    }
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
      identities: this.identities,
      file: file,
      humanReadable: this.humanReadable,
    });
  }
}
</script>
