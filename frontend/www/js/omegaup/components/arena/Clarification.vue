<template>
  <tr
    :class="{
      resolved: clarification.answer,
      'direct-message': isDirectMessage,
    }"
  >
    <td class="text-center align-middle">
      {{
        !inContest ? clarification.contest_alias : clarification.problem_alias
      }}
    </td>
    <td class="text-center align-middle">{{ clarification.author }}</td>
    <td class="text-center align-middle">
      {{ time.formatDateTime(clarification.time) }}
    </td>
    <td class="align-middle">
      <pre>{{ clarification.message }}</pre>
    </td>
    <td v-if="isAdmin" class="align-middle">
      <template v-if="clarification.answer">
        <pre>{{ clarification.answer }}</pre>
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
      >
        <div class="form-group">
          <select v-model="selectedResponse" class="form-control">
            <option
              v-for="response in responses"
              :key="response.value"
              :value="response.value"
            >
              {{ response.text }}
            </option>
          </select>
        </div>
        <div
          v-if="selectedResponse === 'other'"
          class="form-group mt-2 mt-xl-0"
        >
          <textarea v-model="message" :placeholder="T.wordsAnswer"> </textarea>
        </div>
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
        <button
          class="btn btn-primary btn-sm mt-2 mt-lg-2"
          type="submit"
          @click.prevent="sendClarificationResponse"
        >
          {{ T.wordsSend }}
        </button>
      </form>
    </td>
    <td v-else class="align-middle">
      <pre v-if="clarification.answer">{{ clarification.answer }}</pre>
    </td>
  </tr>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import { types } from '../../api_types';
import * as time from '../../time';

@Component
export default class ArenaClarificationForm extends Vue {
  @Prop() clarification!: types.Clarification;
  @Prop() inContest!: boolean;
  @Prop({ default: false }) isAdmin!: boolean;

  T = T;
  time = time;

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
      answer: this.responseText,
      public: this.isPublic,
      message: this.message,
      problem_alias: this.clarification.problem_alias,
      time: new Date(),
    };
    this.showUpdateAnswer = false;
    this.$emit('clarification-response', response);
  }
}
</script>

<style lang="scss" scoped>
.resolved {
  color: rgb(70, 136, 71);
  background-image: linear-gradient(
    rgb(223, 240, 216) 0px,
    rgb(200, 229, 188) 100%
  );
  background-color: rgb(223, 240, 216);
}

.direct-message {
  color: rgb(125, 117, 18);
  background-image: linear-gradient(
    rgb(253, 245, 154) 0px,
    rgba(255, 249, 181, 0.5) 100%
  );
  background-color: rgb(223, 240, 216);
}
</style>
