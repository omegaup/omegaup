<template>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="upload-csv">
        <div class="panel-heading">
          {{ T.groupsUploadCsvFile }} <input name="identities"
               type="file">
        </div>
        <div class="panel-heading">
          <a class="btn btn-primary"
               v-on:click.prevent="readCsv">{{ T.groupsUploadCsvFile }}</a>
        </div>
      </div><br>
      <div class="panel panel-default no-bottom-margin"
           v-show="identities.length &gt; 0">
        <div class="panel-heading">
          <h3 class="panel-title">{{ T.wordsIdentities }}</h3>
        </div>
        <table class="identities-table table">
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
            <tr v-for="identity in identities">
              <td class="username"><strong>{{ identity.username }}</strong></td>
              <td>{{ identity.name }}</td>
              <td class="password">{{ identity.password }}</td>
              <td>{{ identity.country_id }}</td>
              <td>{{ identity.state_id }}</td>
              <td>{{ identity.gender }}</td>
              <td>{{ identity.school_name }}</td>
            </tr>
          </tbody>
        </table>
        <div class="panel-heading">
          <button class="btn btn-primary"
               name="create-identities"
               v-on:click.prevent="$emit('bulk-identities', identities)">{{ T.groupCreateIdentities
               }}</button>
        </div>
        <div>
          {{ T.groupsIdentityWarning }}
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Emit } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import UI from '../../ui.js';
import omegaup from '../../api.js';

@Component
export default class Identities extends Vue {
  @Prop() groupAlias!: string;

  T = T;
  identities: omegaup.Identity[] = [];

  readCsv(): void {
    const fileUpload = <HTMLInputElement>(
      this.$el.querySelector('input[type=file]')
    );
    const regex = /.*\.(?:csv|txt)$/;

    if (!regex.test(fileUpload.value.toLowerCase())) {
      UI.error(T.groupsInvalidCsv);
      return;
    }
    this.$emit('read-csv', this, fileUpload);
  }
}

</script>
