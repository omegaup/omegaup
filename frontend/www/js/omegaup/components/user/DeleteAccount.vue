<template>
  <div class="card">
    <form class="form" @submit.prevent="$emit('remove')">
      <div class="form-group">
        <div class="alert alert-danger">
          <h4 class="alert-heading">{{ T.wordsDangerZone }}</h4>
          <hr />
          <omegaup-markdown
            :markdown="T.accountDeleteMessage"
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
      :title="T.accountDeleteRequireConfirmation"
      :ok-title="T.accountDeleteOk"
      ok-variant="danger"
      :cancel-title="T.accountDeleteCancel"
      @ok="$emit('request-delete-account')"
    >
      <p>{{ T.accountDeleteConfirmationMessage }}</p>
    </b-modal>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import Vue from 'vue';
import { ModalPlugin } from 'bootstrap-vue';
import T from '../../lang';
import omegaup_Markdown from '../Markdown.vue';

Vue.use(ModalPlugin);

export default defineComponent({
  name: 'UserDeleteAccount',
  components: {
    'omegaup-markdown': omegaup_Markdown,
  },
  emits: ['remove', 'request-delete-account'],
  data(): {
    T: typeof T;
    showConfirmationModal: boolean;
    username: string;
  } {
    return {
      T,
      showConfirmationModal: false,
      username: '',
    };
  },
});
</script>
