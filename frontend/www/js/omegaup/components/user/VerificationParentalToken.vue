<template>
  <div class="card">
    <h1 class="card-header">{{ T.omegaupTitleParentalVerificationToken }}</h1>
    <div class="card-body">
      <b-icon-check-circle-fill
        v-if="hasParentalVerificationToken"
        scale="2"
        variant="success"
      ></b-icon-check-circle-fill>
      <b-icon-x-circle-fill
        v-else
        scale="2"
        variant="danger"
      ></b-icon-x-circle-fill>
      <omegaup-markdown :markdown="message"></omegaup-markdown>

      <a href="/dependents/">{{
        T.verificationParentalTokenGoToMyDependents
      }}</a>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';

import omegaup_Markdown from '../Markdown.vue';

import {
  BIconCheckCircleFill,
  BIconXCircleFill,
} from 'bootstrap-vue';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';

@Component({
  components: {
    BIconCheckCircleFill,
    BIconXCircleFill,
    'omegaup-markdown': omegaup_Markdown,
  },
})
export default class UserVerificationParentalToken extends Vue {
  @Prop() message!: string;
  @Prop() hasParentalVerificationToken!: boolean;

  T = T;
}
</script>
