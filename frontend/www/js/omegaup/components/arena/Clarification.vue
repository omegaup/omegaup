<template>
  <tr>
    <td class="text-center align-middle">
      {{
        inContest ? clarification.contest_alias : clarification.problem_alias
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
          <select class="form-control" v-model="selectedResponse">
            <option
              v-for="response in responses"
              v-bind:value="response.value"
              v-bind:key="response.value"
            >
              {{ response.text }}</option
            >
          </select>
        </div>
        <div
          class="form-group mt-2 mt-xl-0"
          v-if="selectedResponse === 'other'"
        >
          <textarea v-model="message" v-bind:placeholder="T.wordsAnswer">
          </textarea>
        </div>
        <div class="form-check mt-2 mt-xl-0">
          <label class="form-check-label">
            <input
              class="form-check-input"
              type="checkbox"
              v-model="isPublic"
            />
            {{ T.wordsPublic }}
          </label>
        </div>
        <button
          class="btn btn-primary btn-sm mt-2 mt-lg-2"
          type="submit"
          v-on:click.prevent="sendClarificationResponse"
        >
          {{ T.wordsSend }}
        </button>
      </form>
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

  T = T;
  time = time;

  isPublic = this.clarification.public;
  message = '';
  selectedResponse = 'yes';
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

  sendClarificationResponse(): void {
    //TODO: Emit an event to parent with the response to clarification
  }
}
</script>
