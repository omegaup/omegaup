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
                  >
                    {{ problem.title }}</option
                  >
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
            <th class="text-center" scope="col" v-else="">
              {{ T.wordsProblem }}
            </th>
            <th class="text-center" scope="col">{{ T.wordsAuthor }}</th>
            <th class="text-center" scope="col">{{ T.wordsTime }}</th>
            <th class="text-center" scope="col">{{ T.wordsMessage }}</th>
            <th class="text-center" scope="col">{{ T.wordsResult }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(clarification, idx) in clarifications">
            <td class="text-center align-middle">
              {{
                inContest
                  ? clarification.contest_alias
                  : clarification.problem_alias
              }}
            </td>
            <td class="text-center align-middle">{{ clarification.author }}</td>
            <td class="text-center align-middle">
              {{ time.formatDateTime(clarification.time) }}
            </td>
            <td class="align-middle">
              <pre>{{ clarification.message }}</pre>
            </td>
            <td class="align-middle">
              <pre v-if="clarification.answer">{{ clarification.answer }}</pre>
              <form class="form-inline justify-content-between">
                <div class="form-group">
                  <select
                    class="form-control"
                    v-model="clarificationsResponses[idx].selectedOption"
                  >
                    <option
                      v-for="response in responses"
                      v-bind:value="response.value"
                    >
                      {{ response.text }}</option
                    >
                  </select>
                </div>
                <div
                  class="form-group mt-2 mt-xl-0"
                  v-if="clarificationsResponses[idx].selectedOption === 'other'"
                >
                  <textarea
                    v-model="clarificationsResponses[idx].customResponse"
                    v-bind:placeholder="T.wordsAnswer"
                  >
                  </textarea>
                </div>
                <div class="form-check mt-2 mt-xl-0">
                  <label class="form-check-label">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      v-bind:value="clarificationsResponses[idx].public"
                    />
                    {{ T.wordsPublic }}
                  </label>
                </div>
                <button
                  class="btn btn-primary btn-sm mt-2 mt-lg-2"
                  type="submit"
                  v-on:click.prevent="sendClarificationResponse(idx)"
                >
                  {{ T.wordsSend }}
                </button>
              </form>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style scoped>
pre {
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
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import T from '../../lang';
import { types } from '../../api_types';
import * as UI from '../../ui';
import * as time from '../../time';

@Component
export default class ArenaClarificationList extends Vue {
  @Prop() inContest!: boolean;
  @Prop() clarifications!: types.Clarification[];
  @Prop() contestProblems!: any[]; //TODO: decide how to pass problems for new clarification

  T = T;
  time = time;

  newClarification = {
    problem:
      this.inContest && this.contestProblems
        ? this.contestProblems[0].alias
        : null,
    message: '',
  };

  responses = [
    {
      value: 'yes',
      text: T.wordsYes,
    },
    {
      value: 'no',
      text: T.wordsNo,
    },
    {
      value: 'nocomment',
      text: T.wordsNoComment,
    },
    {
      value: 'readAgain',
      text: T.wordsReadAgain,
    },
    {
      value: 'other',
      text: T.wordsOther,
    },
  ];

  clarificationsResponses = this.clarifications.map(clarification => {
    return {
      clarification_id: clarification.clarification_id,
      public: clarification.public,
      selectedOption: 'yes',
      customResponse: null,
    };
  });

  sendClarificationResponse(index: number): void {
    //TODO: Emit an event to parent with the response to clarification
  }

  sendClarification(): void {
    //TODO: Emit an event to parent with the new clarification
  }

  @Watch('clarifications')
  onClarificationsChange() {
    this.clarificationsResponses = this.clarifications.map(clarification => {
      return {
        clarification_id: clarification.clarification_id,
        public: clarification.public,
        selectedOption: 'yes',
        customResponse: null,
      };
    });
  }
}
</script>
