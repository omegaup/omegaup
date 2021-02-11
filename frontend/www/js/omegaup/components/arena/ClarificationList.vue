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
            <form
              class="form"
              @submit.prevent="
                $emit('new-clarification', { problemAlias, message })
              "
            >
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
                  <select v-model="problemAlias" required="required">
                    <option
                      v-for="problem in problems"
                      :key="problem.alias"
                      :value="problem.alias"
                    >
                      {{ problem.text }}
                    </option>
                  </select>
                </label>
                <textarea
                  v-model="message"
                  class="w-100"
                  maxlength="200"
                  required="required"
                  :placeholder="T.arenaClarificationCreateMaxLength"
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
                <button class="btn btn-primary">{{ T.wordsSend }}</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table mb-0">
        <thead>
          <tr>
            <th class="text-center" scope="col">
              {{ !inContest ? T.wordsContest : T.wordsProblem }}
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
            :key="clarification.clarification_id"
            :in-contest="inContest"
            :is-admin="isAdmin"
            :clarification="clarification"
            @clarification-response="
              (id, responseText, isPublic) =>
                $emit('clarification-response', id, responseText, isPublic)
            "
          ></omegaup-clarification>
        </tbody>
      </table>
    </div>
  </div>
</template>

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
  @Prop({ default: false }) isAdmin!: boolean;
  @Prop() clarifications!: types.Clarification[];
  @Prop({ default: () => [] }) problems!: types.NavbarProblemsetProblem[];

  T = T;
  problemAlias = this.problems[0]?.alias ?? null;
  message: null | string = null;
}
</script>

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
