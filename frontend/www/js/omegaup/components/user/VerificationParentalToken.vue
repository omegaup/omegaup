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
import { defineComponent } from 'vue';
import Vue from 'vue';
import T from '../../lang';

import omegaup_Markdown from '../Markdown.vue';

import {
  BIconCheckCircleFill,
  BIconXCircleFill,
  BootstrapVue,
} from 'bootstrap-vue';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
Vue.use(BootstrapVue);

export default defineComponent({
  name: 'UserVerificationParentalToken',
  components: {
    BIconCheckCircleFill,
    BIconXCircleFill,
    'omegaup-markdown': omegaup_Markdown,
  },
  props: {
    message: {
      type: String,
      required: true,
    },
    hasParentalVerificationToken: {
      type: Boolean,
      required: true,
    },
  },
  data(): {
    T: typeof T;
  } {
    return {
      T,
    };
  },
});
</script>
