<template>
  <div class="container-fluid p-0 mt-0" data-user-edit-email>
    <div class="row">
      <div class="col-md-2">
        <omegaup-user-maininfo :profile="profile" :edit="true" />
      </div>
      <div class="col-md-10">
        <div class="card">
          <h2 class="card-header">{{ T.userEmailEditEmail }}</h2>
          <div class="card-body">
            <form class="form-horizontal" @submit.prevent="onSubmit">
              <div class="form-group">
                <label class="col-md-3 control-label" for="email">{{
                  T.userEmailEditEmail
                }}</label>
                <div class="col-md-7">
                  <!-- id-lint off -->
                  <input
                    id="email"
                    v-model.trim="currentEmail"
                    data-email-input
                    class="form-control"
                    name="email"
                    size="30"
                    type="email"
                    required
                  />
                  <!-- id-lint on -->
                </div>
              </div>
              <div class="form-group">
                <div class="col-md-offset-3 col-md-7">
                  <button
                    data-submit-email
                    class="btn btn-primary"
                    type="submit"
                    :disabled="submitDisabled"
                  >
                    {{ T.wordsSaveChanges }}
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import T from '../../lang';
import { types } from '../../api_types';
import user_MainInfo from './MainInfo.vue';

@Component({
  components: {
    'omegaup-user-maininfo': user_MainInfo,
  },
})
export default class UserEmailEdit extends Vue {
  @Prop({ default: '' }) email!: string;
  @Prop() profile!: types.UserProfileInfo;
  T = T;
  currentEmail: string = this.email ?? '';

  @Watch('email')
  onEmailChanged(newValue: string): void {
    this.currentEmail = newValue ?? '';
  }

  get submitDisabled(): boolean {
    const trimmedEmail = (this.currentEmail ?? '').trim();
    const initialEmail = (this.email ?? '').trim();
    return trimmedEmail.length === 0 || trimmedEmail === initialEmail;
  }

  onSubmit(): void {
    if (this.submitDisabled) {
      return;
    }
    this.$emit('submit', (this.currentEmail ?? '').trim());
  }
}
</script>
