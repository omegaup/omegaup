<template>
  <div class="panel panel-default">
    <div class="panel-body">
      <div>
        <div class="panel-heading">
          {{ T.groupsUploadCsv }} <input name="identities"
               type="file">
        </div>
        <div class="panel-heading">
          <a class="btn btn-primary"
               v-on:click.prevent="readCsv">{{ T.groupsUploadFile }}</a>
        </div>
      </div><br>
      <div class="panel panel-default no-bottom-margin"
           v-show="identities">
        <div class="panel-heading">
          <h3 class="panel-title">{{ T.wordsIdentities }}</h3>
        </div>
        <table class="table">
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
          <a class="btn btn-primary"
               v-on:click.prevent="bulkIdentities">{{ T.groupEditIdentities }}</a>
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

export default {
  props: {groupAlias: String},
  data: function() { return {T: T, identities: []};},
  methods: {
    readCsv: function() {
      let self = this;
      let fileUpload = document.getElementsByName('identities');
      let regex = /.*\.(?:csv|txt)$/;
      if (!regex.test(fileUpload[0].value.toLowerCase())) {
        UI.error(T.groupsInvalidCsv);
        return;
      }
      if (typeof(FileReader) == 'undefined') {
        UI.error(T.wordsBrowserDoesNotSupportHtml5);
        return;
      }
      self.identities = [];
      CSV.fetch({
           file: fileUpload[0].files[0],
         })
          .done(function(dataset) {
            for (let cells of dataset.records) {
              if (cells.length != 6) continue;
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
    generatePassword: function(len) {
      // Browser supports window.crypto
      if (typeof window.crypto == 'object') {
        let arr = new Uint8Array((len || 8) / 2);
        window.crypto.getRandomValues(arr);
        return Array.from(arr, function(dec) {
                      return ('0' + dec.toString(16)).substr(-2);
                    }).join('');
      }

      // Browser does not support window.crypto
      let password = '';
      let validChars =
          'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
      for (var i = 0; i < 8; i++) {
        password +=
            validChars.charAt(Math.floor(Math.random() * validChars.length));
      }
      return password;
    },
  },
};
</script>
