<template>
  <div>
    <form
      class="form add-identity-form"
      @submit.prevent="$emit('add-identity', { username, password })"
    >
      <div class="form-group">
        <p>
          {{ T.addContestUsernameAndPassword }}
        </p>
      </div>
      <div class="form-group">
        <label class="w-100">
          {{ T.wordsIdentity }}
          <font-awesome-icon
            :title="T.profileAddIdentitiesTooltip"
            icon="info-circle"
          ></font-awesome-icon>
          <input
            v-model="username"
            data-identity-username
            autocomplete="off"
            class="form-control username-input"
            size="20"
            type="text"
          />
        </label>
      </div>
      <div class="form-group">
        <label class="w-100">
          {{ T.loginPassword }}
          <omegaup-password-input
            v-model="password"
            data-identity-password
            autocomplete="off"
            input-class="password-input"
            :size="20"
          />
        </label>
      </div>
      <div class="form-group text-right">
        <button class="btn btn-primary" type="submit" data-add-identity-button>
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
        <tr
          v-for="identity in identities"
          :key="identity.username"
          data-added-identity-username
        >
          <td>{{ identity.username }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';

import {
  FontAwesomeIcon,
  FontAwesomeLayers,
  FontAwesomeLayersText,
} from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
import omegaup_PasswordInput from '../common/PasswordInput.vue';
library.add(fas);

@Component({
  components: {
    'font-awesome-icon': FontAwesomeIcon,
    'font-awesome-layers': FontAwesomeLayers,
    'font-awesome-layers-text': FontAwesomeLayersText,
    'omegaup-password-input': omegaup_PasswordInput,
  },
})
export default class ManageIdentities extends Vue {
  @Prop() identities!: types.Identity[];

  T = T;
  username = '';
  password = '';
}
</script>
