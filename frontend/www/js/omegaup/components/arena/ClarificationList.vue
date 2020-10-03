<template>
  <div class="card">
    <h5 class="card-header">{{ T.wordsClarifications }}</h5>
    <div v-if="inContest" class="card-body">
      <button
        type="button"
        class="btn btn-primary"
        data-toggle="modal"
        data-target=".new-clarification-modal"
      >
        {{ T.wordsNewClarification }}
      </button>
      <div
        class="modal fade new-clarification-modal"
        tabindex="-1"
        role="dialog"
        aria-hidden="true"
      >
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">{{ T.wordsNewClarification }}</h5>
              <button
                type="button"
                class="close w-auto"
                data-dismiss="modal"
                aria-label="Close"
              >
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <label>
                {{ T.wordsProblem }}
                <select v-model="newClarification.problemAlias">
                  <option
                    v-for="problem in contestProblems"
                    v-bind:value="problem.alias"
                    v-bind:key="problem.alias"
                  >
                    {{ problem.title }}
                  </option>
                </select>
              </label>
              <textarea
                class="w-100"
                maxlength="200"
                v-model="newClarification.message"
                v-bind:placeholder="T.arenaClarificationCreateMaxLength"
              ></textarea>
            </div>
            <div class="modal-footer">
              <button
                type="button"
                class="btn btn-secondary"
                data-dismiss="modal"
              >
                {{ T.wordsClose }}
              </button>
              <button
                type="button"
                class="btn btn-primary"
                v-on:click.prevent="sendClarification"
              >
                {{ T.wordsSend }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table mb-0">
        <thead>
          <tr>
            <th class="text-center" scope="col" v-if="inContest">
              {{ T.wordsContest }}
            </th>
            <th class="text-center" scope="col" v-else>
              {{ T.wordsProblem }}
            </th>
            <th class="text-center" scope="col">{{ T.wordsAuthor }}</th>
            <th class="text-center" scope="col">{{ T.wordsTime }}</th>
            <th class="text-center" scope="col">{{ T.wordsMessage }}</th>
            <th class="text-center" scope="col">{{ T.wordsResult }}</th>
          </tr>
        </thead>
        <tbody>
          <omegaup-clarification
            v-for="clarification in clarifications"
            v-bind:in-contest="inContest"
            v-bind:key="clarification.clarification_id"
            v-bind:clarification="clarification"
            v-on:clarification-response="
              (id, responseText, isPublic) =>
                $emit('clarification-response', id, responseText, isPublic)
            "
          ></omegaup-clarification>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style lang="scss" scoped>
// Deep allows child components to inherit the styles (see: https://vue-loader.vuejs.org/guide/scoped-css.html#deep-selectors)
/deep/ pre {
  display: block;
  padding: 0.5rem;
  font-size: 0.8rem;
  line-height: 1.42857143;
  color: #333;
  word-break: break-all;
  background-color: #f5f5f5;
  border-radius: 4px;
}

.modal-dialog {
  max-width: 50%;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import { types } from '../../api_types';

import arena_Clarification from './Clarification.vue';

@Component({
  components: {
    'omegaup-clarification': arena_Clarification,
  },
})
export default class ArenaClarificationList extends Vue {
  @Prop() inContest!: boolean;
  @Prop() clarifications!: types.Clarification[];
  @Prop() contestProblems!: any[]; //TODO: decide how to pass problems for new clarification

  T = T;
  newClarification = {
    problem:
      this.inContest && this.contestProblems
        ? this.contestProblems[0].alias
        : null,
    message: '',
  };

  sendClarification(): void {
    //TODO: Emit an event to parent with the new clarification
  }

  sendClarificationResponse(): void {
    //TODO: Emit an event to parent with the response to clarification
  }
}
</script>
