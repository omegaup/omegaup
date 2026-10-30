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
            <form
              class="form-horizontal"
              @submit.prevent="$emit('submit', currentEmail)"
            >
              <div class="form-group">
                <label class="col-md-3 control-label" for="email">{{
                  T.userEmailEditEmail
                }}</label>
                <div class="col-md-7">
                  <input
                    v-model="currentEmail"
                    class="form-control"
                    name="email"
                    size="30"
                    type="text"
                  />
                </div>
              </div>
              <div class="form-group">
                <div class="col-md-offset-3 col-md-7">
                  <button class="btn btn-primary" type="submit">
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
import { defineComponent, PropType } from 'vue';
import T from '../../lang';
import { types } from '../../api_types';
import user_MainInfo from './MainInfo.vue';

export default defineComponent({
  name: 'UserEmailEdit',
  components: {
    'omegaup-user-maininfo': user_MainInfo,
  },
  props: {
    email: {
      type: String,
      default: '',
    },
    profile: {
      type: Object as PropType<types.UserProfileInfo>,
      required: true,
    },
  },
  emits: ['submit'],
  data() {
    return {
      T,
      currentEmail: this.email,
    };
  },
  watch: {
    email(newEmail: string): void {
      this.currentEmail = newEmail;
    },
  },
});
</script>
