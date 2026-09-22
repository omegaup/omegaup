<template>
  <div class="card">
    <h1 class="card-header">{{ T.omegaupTitleParentalVerificationToken }}</h1>
    <div class="card-body">
      <font-awesome-icon
        v-if="hasParentalVerificationToken"
        icon="check-circle"
        class="text-success"
        size="2x"
      ></font-awesome-icon>
      <font-awesome-icon
        v-else
        icon="times-circle"
        class="text-danger"
        size="2x"
      ></font-awesome-icon>
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

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import {
  faCheckCircle,
  faTimesCircle,
} from '@fortawesome/free-solid-svg-icons';
library.add(faCheckCircle, faTimesCircle);

@Component({
  components: {
    FontAwesomeIcon,
    'omegaup-markdown': omegaup_Markdown,
  },
})
export default class UserVerificationParentalToken extends Vue {
  @Prop() message!: string;
  @Prop() hasParentalVerificationToken!: boolean;

  T = T;
}
</script>
