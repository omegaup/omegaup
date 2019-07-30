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
              <td><strong>{{ identity.username }}</strong></td>
              <td>{{ identity.name }}</td>
              <th>{{ identity.password }}</th>
              <td>{{ identity.country_id }}</td>
              <td>{{ identity.state_id }}</td>
              <td>{{ identity.gender }}</td>
              <td>{{ identity.school_name }}</td>
            </tr>
          </tbody>
        </table>
        <div class="panel-heading">
          <button class="btn btn-primary"
               name="create_identities"
               v-on:click.prevent="bulkIdentities">{{ T.groupCreateIdentities }}</button>
        </div>
        <div>
          {{ T.groupsIdentityWarning }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {T, UI} from '../../omegaup.js';
import * as CSV from '../../../../third_party/js/csv.js/csv.js';

export default {
  props: {groupAlias: String},
  data: function() { return {T: T, identities: []};},
  methods: {
    readCsv: function() {
      let self = this;
      let fileUpload = self.$el.querySelector('input[type=file]');
      let regex = /.*\.(?:csv|txt)$/;
      if (!regex.test(fileUpload.value.toLowerCase())) {
        UI.error(T.groupsInvalidCsv);
        return;
      }
      self.identities = [];
      CSV.fetch({
           file: fileUpload.files[0],
         })
          .done(function(dataset) {
            if (dataset.fields.length != 6) {
              UI.error(T.groupsInvalidCsv);
              return;
            }
            for (let cells of dataset.records) {
              self.identities.push({
                username: self.groupAlias + ':' + cells[0],
                name: cells[1],
                password: self.generatePassword(),
                country_id: cells[2],
                state_id: cells[3],
                gender: cells[4],
                school_name: cells[5],
              });
            }
          });
    },
    bulkIdentities: function() {
      self = this;
      self.$emit('bulk-identities', self.identities);
    },
    generatePassword: function() {
      const validChars = 'acdefhjkmnpqruvwxyACDEFHJKLMNPQRUVWXY346';
      const len = 8;
      // Browser supports window.crypto
      if (typeof window.crypto == 'object') {
        let arr = new Uint8Array(2 * len);
        window.crypto.getRandomValues(arr);
        return Array.from(arr.filter(value => value <=
                                              (255 - 255 % validChars.length)),
                          value => validChars[value % validChars.length])
            .join('')
            .substr(0, len);
      }

      // Browser does not support window.crypto
      let password = '';
      for (var i = 0; i < len; i++) {
        password +=
            validChars.charAt(Math.floor(Math.random() * validChars.length));
      }
      return password;
    },
  },
};
</script>
