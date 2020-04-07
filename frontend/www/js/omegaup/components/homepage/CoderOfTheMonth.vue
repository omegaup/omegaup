<template>
  <div class="card">
    <h5 class="card-header">
      {{ female ? T.wordsCoderOfTheMonthFemale : T.wordsCoderOfTheMonth }}
    </h5>
    <div class="card-body text-center">
      <a v-bind:href="`/profile/${coderOfTheMonth.username}/`">
        <img v-bind:src="coderOfTheMonth.gravatar_92" height="80" />
      </a>
      <h5 class="card-title">
        <omegaup-user-username
          v-bind:classname="coderOfTheMonth.classname"
          v-bind:linkify="true"
          v-bind:username="coderOfTheMonth.username"
          v-bind:country="coderOfTheMonth.country_id"
        ></omegaup-user-username>
      </h5>
      <div class="card-text">
        {{ coderOfTheMonth.name }}
      </div>
      <div class="card-text">
        {{ coderOfTheMonth.school }}
      </div>
      <div
        class="card-text"
        v-if="coderOfTheMonth.state && coderOfTheMonth.country !== 'xx'"
      >
        {{ coderOfTheMonth.state }}, {{ coderOfTheMonth.country }}
      </div>
    </div>
    <div class="card-footer">
      <a href="/coderofthemonth/">{{ T.coderOfTheMonthFullList }}</a>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import user_Username from '../user/Username.vue';

@Component({
  components: {
    'omegaup-user-username': user_Username,
  },
})
export default class CoderOfTheMonth extends Vue {
  @Prop() female!: boolean;
  @Prop() coderOfTheMonth!: omegaup.CoderOfTheMonth;

  T = T;
}
</script>
