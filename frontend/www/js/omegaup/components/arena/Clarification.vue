<template>
  <tr
    :class="{
      resolved: clarification.answer,
      'direct-message': isDirectMessage,
      'border border-primary': selected,
    }"
  >
    <td class="column-info text-center align-middle">
      <span
        v-if="
          'assignment_alias' in clarification && clarification.assignment_alias
        "
      >
        <span class="font-weight-bold">{{ T.clarificationHomework }}</span>
        {{ clarification.assignment_alias }}
      </span>
      <span class="font-weight-bold">{{
        'contest_alias' in clarification && clarification.contest_alias
          ? T.clarificationContest
          : T.clarificationProblem
      }}</span>
      {{
        'contest_alias' in clarification && clarification.contest_alias
          ? clarification.contest_alias
          : clarification.problem_alias
      }}
      <span data-author>
        <span class="font-weight-bold">{{ T.clarificationsAskedBy }}</span>
        <template v-if="clarification.receiver">
          {{ clarificationAuthorReceiver }}
        </template>
        <template v-else>
          <omegaup-user-username
            :username="clarification.author"
            :classname="clarification.author_classname"
          ></omegaup-user-username>
        </template>
      </span>
      <span class="font-weight-bold">{{ T.clarificationTime }}</span>
      {{ time.formatDateTime(clarification.time) }}
    </td>

    <td class="column-message align-middle" data-form-clarification-message>
      <span class="text-monospace text-dark">{{ clarification.message }}</span>
    </td>
    <td
      v-if="isAdmin"
      class="column-answer align-middle"
      data-form-clarification-resolved-answer
    >
      <template v-if="clarification.answer">
        <span class="text-monospace text-dark">{{ clarification.answer }}</span>
        <div v-if="!showUpdateAnswer" class="form-check mt-2 mt-xl-0">
          <label class="form-check-label">
            <input
              v-model="showUpdateAnswer"
              class="form-check-input"
              type="checkbox"
            />
            {{ T.clarificationUpdateAnswer }}
          </label>
        </div>
      </template>
      <form
        v-if="!clarification.answer || showUpdateAnswer"
        class="form-inline justify-content-between"
        data-form-clarification-answer
        @submit.prevent="sendClarificationResponse"
      >
        <div class="form-group mb-0">
          <select
            v-model="selectedResponse"
            class="form-control"
            data-select-answer
          >
            <option
              v-for="response in responses"
              :key="response.value"
              :value="response.value"
            >
              {{ response.text }}
            </option>
          </select>
        </div>
        <div v-if="selectedResponse === 'other'" class="form-group mt-1 mb-0">
          <textarea v-model="message" :placeholder="T.wordsAnswer"> </textarea>
        </div>
        <div class="d-flex justify-content-between w-100">
          <div class="form-check mt-2 mt-xl-0">
            <label class="form-check-label">
              <input
                v-model="isPublic"
                class="form-check-input"
                type="checkbox"
              />
              {{ T.wordsPublic }}
            </label>
          </div>
          <button class="btn btn-primary btn-sm mt-2" type="submit">
            {{ T.wordsSend }}
          </button>
        </div>
      </form>
    </td>
    <td
      v-else
      class="column-answer align-middle"
      data-clarification-answer-text
    >
      <span v-if="clarification.answer" class="text-monospace text-dark">{{
        clarification.answer
      }}</span>
    </td>
  </tr>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import { types } from '../../api_types';
import * as time from '../../time';
import * as ui from '../../ui';
import user_Username from '../user/Username.vue';

@Component({
  components: {
    'omegaup-user-username': user_Username,
  },
})
export default class ArenaClarification extends Vue {
  @Prop() clarification!: types.Clarification;
  @Prop({ default: false }) isAdmin!: boolean;
  @Prop({ default: false }) selected!: boolean;

  T = T;
  time = time;
  ui = ui;

  isPublic = this.clarification.public;
  message = '';
  selectedResponse = 'yes';
  showUpdateAnswer = false;
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

  formatStringWithUsernames(
    template: string,
    params: {
      author: { username: string; classname: string };
      receiver: { username: string; classname: string };
    },
  ): string {
    const authorSpan = `<span class="${params.author.classname} font-weight-bold">${params.author.username}</span>`;
    const receiverSpan = `<span class="${params.receiver.classname} font-weight-bold">${params.receiver.username}</span>`;
    return template
      .replace('%(author)', authorSpan)
      .replace('%(receiver)', receiverSpan);
  }

  get clarificationAuthorReceiver(): string {
    if (this.clarification.receiver) {
      return ui.formatString(T.clarificationsOnBehalf, {
        author: this.clarification.author,
        receiver: this.clarification.receiver,
      });
    }
    return this.clarification.author;
  }

  get isDirectMessage(): boolean {
    return (
      this.clarification.answer == null && this.clarification.receiver != null
    );
  }

  get responseText(): string {
    const response = this.responses.find(
      (response) => response.value === this.selectedResponse,
    );
    if (!response) {
      return this.selectedResponse;
    }
    return this.selectedResponse === 'other' ? this.message : response.text;
  }

  sendClarificationResponse(): void {
    const response: types.Clarification = {
      clarification_id: this.clarification.clarification_id,
      author: this.clarification.author,
      author_classname: this.clarification.author_classname,
      answer: this.responseText,
      public: this.isPublic,
      message: this.message,
      problem_alias: this.clarification.problem_alias,
      time: new Date(),
    };

    if (this.clarification.receiver && this.clarification.receiver_classname) {
      response.receiver = this.clarification.receiver;
      response.receiver_classname = this.clarification.receiver_classname;
    }

    this.showUpdateAnswer = false;
    this.$emit('clarification-response', response);
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.resolved {
  color: var(--clarification-resolved-font-color);
  background-image: linear-gradient(
    var(--clarification-resolved-gradient-from-background-color),
    var(--clarification-resolved-gradient-to-background-color)
  );
  background-color: var(--clarification-resolved-background-color);
}

.direct-message {
  color: var(--clarification-direct-message-font-color);
  background-image: linear-gradient(
    var(--clarification-direct-message-gradient-from-background-color),
    rgba(var(--clarification-direct-message-gradient-to-background-color), 0.5)
  );
  background-color: var(--clarification-direct-message-background-color);
}

.border {
  border-width: 3px !important;
}

span {
  font-size: 14px;
}
.column-info {
  min-width: 13rem;
  max-width: 14rem;
}
.column-message {
  min-width: 25rem;
  font-size: 14px;
}
.column-answer {
  min-width: 15rem;
}
</style>
