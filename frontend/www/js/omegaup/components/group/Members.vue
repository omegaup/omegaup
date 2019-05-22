<template>
  <div class="panel panel-primary">
    <div class="panel-body">
      <form class="form"
            v-on:submit.prevent="onAddMember">
        <div class="form-group">
          <label>{{ T.wordsMember }} <input autocomplete="off"
                 class="form-control typeahead"
                 name="username"
                 size="20"
                 type="text"></label>
        </div><button class="btn btn-primary"
              type="submit">{{ T.wordsAddMember }}</button>
      </form>
    </div>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>{{ T.wordsUser }}</th>
          <th>{{ T.contestEditRegisteredAdminDelete }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="identity in identities">
          <td>
            <a v-bind:href="memberProfileUrl(identity.username)">{{ identity.username }}</a>
          </td>
          <td>
            <a class="glyphicon glyphicon-remove"
                href="#"
                v-bind:title="T.groupEditMembersRemove"
                v-on:click="onRemove(identity.username)"></a>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import {T, UI} from '../../omegaup.js';
export default {
  props: {
    identities: Array,
  },
  data: function() {
    return {
      T: T,
      memberUsername: '',
      username: '',
    };
  },
  mounted: function() {
    let self = this;
    UI.userTypeahead($('input.typeahead', self.$el), function(event, item) {
      self.memberUsername = item.value;
    });
  },
  methods: {
    onAddMember: function() {
      let hintElem = $('input.typeahead.tt-hint', this.$el);
      let hint = hintElem.val();
      if (hint) {
        // There is a hint currently visible in the UI, the user likely
        // expects that hint to be used when trying to add someone, instead
        // of what they've actually typed so far.
        this.memberUsername = hint;
      } else {
        this.memberUsername = $('input.typeahead.tt-input', this.$el).val();
      }
      this.$emit('add-member', this.memberUsername);
    },
    onRemove: function(username) { this.$emit('remove', username);},
    reset: function() {
      this.memberUsername = '';
      let inputElem = $('input.typeahead', this.$el);
      inputElem.typeahead('close');
      inputElem.val('');
    },
    memberProfileUrl: function(member) { return '/profile/' + member + '/';},
  },
};
</script>

<style>
label {
  display: inline;
}
</style>
