<template>
  <div class="card">
    <form class="form" @submit.prevent="$emit('remove')">
      <div class="form-group">
        <div class="alert alert-danger">
          <h4 class="alert-heading">{{ T.wordsDangerZone }}</h4>
          <hr />
          <omegaup-markdown
            :markdown="T.wordsDangerZoneDesc"
          ></omegaup-markdown>
          <br /><br />
          <button
            class="btn btn-danger"
            type="submit"
            @click="showConfirmationModal = true"
          >
            {{ T.wordsDelete }}
          </button>
        </div>
      </div>
    </form>
    <b-modal
      v-model="showConfirmationModal"
      title="accountDeleteRequireConfirmation"
      ok-title="T.accountDeleteOk"
      ok-variant="danger"
      cancel-title="T.accountDeleteCancel"
      @ok="$emit('request-delete-account')"
    >
      <p>Are you sure you want to delete your account?</p>
    </b-modal>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import T from '../../lang';
import omegaup_Markdown from '../Markdown.vue';

@Component({ components: { 'omegaup-markdown': omegaup_Markdown } })
export default class UserDeleteAccount extends Vue {
  T = T;
  showConfirmationModal = false;
  username = '';

  // onConfirmDelete(): void {
  //   this.$emit('request-delete-account'), {
  //    username: this.username,
  //   };
  // }
}
</script>
