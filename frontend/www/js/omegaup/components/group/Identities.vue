<template>
  <div class="panel panel-default">
    <div class="panel-body">
      <div>
        <div class="panel-heading">{{ T.groupsUploadCsv }}
          <input type="file" name="identities" /></div>
        <div class="panel-heading"><a class="btn btn-primary"
           v-on:click.prevent="readCsv">{{ T.groupsUploadFile }}</a></div>
      </div>
      <br>
      <div class="panel panel-default no-bottom-margin" v-show="identities">
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
        <div class="panel-heading"><a class="btn btn-primary"
             v-on:click.prevent="bulkIdentities">{{ T.groupEditIdentities }}</a>
           </div>
        <div>{{ T.groupsIdentityWarning }}</div>
      </div>
    </div>
  </div>
</template>

<script>
import {T, UI} from '../../omegaup.js';

export default {
  data: function() {
    return { T: T, identities: this.identities};
  },
  methods: {
    readCsv: function() {
      let self = this;
      let fileUpload = document.getElementsByName("identities");
      let regex = /^([a-zA-Z0-9\s_\\.\-:])+(.csv|.txt)$/;
      if (!regex.test(fileUpload[0].value.toLowerCase())) {
        UI.error(T.groupsInvalidCsv);
        return;
      }
      if (typeof (FileReader) == "undefined") {
        UI.error(T.wordsBrowserDoesNotSupportHtml5);
        return;
      }
      let reader = new FileReader();
      reader.onload = function (e) {
        let rows = e.target.result.split("\n");
        self.identities = [];
        for (let [index, row] of rows.entries()) {
          let cells = row.split(',');
          if (cells[0] != '') {
            self.identities[index] = [];
            self.identities[index]['username'] = cells[0];
            self.identities[index]['name'] = cells[1];
            self.identities[index]['password'] = self.generatePassword();
            self.identities[index]['country_id'] = cells[2];
            self.identities[index]['state_id'] = cells[3];
            self.identities[index]['gender'] = cells[4];
            self.identities[index]['school_name'] = cells[5];
          }
        }
      }
      reader.readAsText(fileUpload[0].files[0]);
    },
    bulkIdentities: function() {
      self = this;
      self.$emit('bulk-identities', self.identities);
    },
    generatePassword: function() {
      let string = '';
      let validChars =
         'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789&%$#_-';
      for (var i = 0; i < 8; i++) {
        string +=
               validChars.charAt(Math.floor(Math.random() * validChars.length));
      }
      return string;
    },
  },
};
</script>
