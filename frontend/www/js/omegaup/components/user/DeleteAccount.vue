<template>
  <div class="card">
    <form class="form" @submit.prevent="$emit('remove')">
      <div class="mb-3">
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
    <div v-if="showConfirmationModal">
      <div class="modal fade show d-block" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                {{ T.accountDeleteRequireConfirmation }}
              </h5>
              <button
                type="button"
                class="btn-close"
                @click="showConfirmationModal = false"
              ></button>
            </div>
            <div class="modal-body">
              <p>{{ T.accountDeleteConfirmationMessage }}</p>
            </div>
            <div class="modal-footer">
              <button
                type="button"
                class="btn btn-secondary"
                @click="showConfirmationModal = false"
              >
                {{ T.accountDeleteCancel }}
              </button>
              <button
                type="button"
                class="btn btn-danger"
                @click="onConfirmDelete"
              >
                {{ T.accountDeleteOk }}
              </button>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-backdrop fade show"></div>
    </div>
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

  onConfirmDelete(): void {
    this.$emit('request-delete-account');
    this.showConfirmationModal = false;
  }
}
</script>
