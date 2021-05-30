<template>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h2 class="panel-title">{{ T.profileManageIdentities }}</h2>
    </div>
    <div class="panel-body add-identity-panel">
      <form
        class="form add-identity-form"
        @submit.prevent="onAddIdentity(username, password)"
      >
        <div class="form-group">
          <label>{{ T.wordsIdentity }}</label>
          <span
            aria-hidden="true"
            class="glyphicon glyphicon-info-sign"
            data-placement="top"
            data-toggle="tooltip"
            :title="T.profileAddIdentitiesTooltip"
          ></span>
          <input
            v-model="username"
            autocomplete="off"
            class="form-control username-input"
            size="20"
            type="text"
          />
        </div>
        <div class="form-group">
          <label>{{ T.loginPassword }}</label>
          <input
            v-model="password"
            autocomplete="off"
            class="form-control password-input"
            size="20"
            type="password"
          />
        </div>
        <div class="form-group pull-right">
          <button class="btn btn-primary" type="submit">
            {{ T.wordsAddIdentity }}
          </button>
        </div>
      </form>
      <div v-if="identities.length == 0">
        <div class="empty-category">
          {{ T.profileIdentitiesEmpty }}
        </div>
      </div>
      <table v-else class="table table-striped table-over">
        <thead>
          <tr>
            <th>{{ T.wordsIdentity }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="identity in identities">
            <td>{{ identity.username }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Emit } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';

@Component
export default class UserManageIdentities extends Vue {
  @Prop() identities!: types.Identity[];
  T = T;
  username: string = '';
  password: string = '';

  @Emit('add-identity')
  onAddIdentity(username: string, password: string) {
    this.username = username;
    this.password = password;
  }
}
</script>

<style>
th.align-right {
  text-align: right;
}
</style>
