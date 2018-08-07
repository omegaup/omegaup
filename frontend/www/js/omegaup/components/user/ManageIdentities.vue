<template>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h2 class="panel-title">{{ T.profileManageIdentities }}</h2>
    </div>
    <div class="panel-body">
      <form class="form"
            v-on:submit.prevent="onAddIdentity">
        <div class="form-group">
          <label>{{ T.wordsIdentity }}</label> <span aria-hidden="true"
               class="glyphicon glyphicon-info-sign"
               data-placement="top"
               data-toggle="tooltip"
               v-bind:title="T.profileAddIdentitiesTooltip"></span> <input autocomplete="off"
               class="form-control typeahead"
               size="20"
               type="text">
        </div>
        <div class="form-group pull-right">
          <button class="btn btn-primary"
               type="submit">{{ T.wordsAddIdentity }}</button>
        </div>
      </form>
      <div v-if="identities.length == 0">
        <div class="empty-category">
          {{ T.profileIdentitiesEmpty }}
        </div>
      </div>
      <table class="table table-striped table-over"
             v-else="">
        <thead>
          <tr>
            <th>{{ T.wordsIdentity }}</th>
            <th>{{ T.profileIdentitiesMarkAsDefault }}</th>
            <th class="align-right">{{ T.wordsDelete }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="identity in identities">
            <td>{{ identity.username }}</td>
            <td><label><input name="default-identity"
                   type="radio"
                   v-bind:checked="identity.default"
                   v-on:click="onMarkAsDefault(identity)"></label></td>
            <td><button class="close"
                    type="button"
                    v-on:click="onRemove(identity)">×</button></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import UI from '../../ui.js';

export default {
  props: {
    T: Object,
    identities: Array,
  },
  data: function() {
    return {
      username: '',
    };
  },
  mounted: function() {
    let self = this;
    UI.identityTypeahead($('input.typeahead', self.$el),
                         function(event, item) { self.username = item.value; });
  },
  methods: {
    onAddIdentity: function() {
      this.username = $('input.typeahead.tt-input', this.$el).val();
      this.$emit('add-identity', this.username);
    },
    onMarkAsDefault: function(identity) {
      this.$emit('mark-as-default', identity.username);
    },
    onRemove: function(identity) { this.$emit('remove', identity);},
    reset: function() {
      this.username = '';
      $('input.typeahead', this.$el).typeahead('close').val('');
    },
  },
};
</script>

<style>
th.align-right {
  text-align: right;
}
</style>
