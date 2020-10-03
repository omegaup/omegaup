<template>
  <div class="card h-100">
    <div
      class="d-flex justify-content-between card-header"
      v-bind:class="`card-header-${category}`"
    >
      <h5 class="m-0">
        {{
          category === 'female' ? T.coderOfTheMonthFemale : T.coderOfTheMonth
        }}
      </h5>
      <a
        class="card-header-help"
        href="https://blog.omegaup.com/reglas-del-coder-del-mes/"
      >
        <font-awesome-icon v-bind:icon="['fas', 'info-circle']" />
      </a>
    </div>
    <div
      class="card-body d-flex flex-column justify-content-center text-center"
    >
      <a
        v-bind:href="`/profile/${coderOfTheMonth.username}/`"
        v-if="!coderOfTheMonth.is_private"
      >
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
      <template v-if="!coderOfTheMonth.is_private">
        <div class="card-text">
          {{ coderOfTheMonth.name }}
        </div>
        <div class="card-text" v-if="coderOfTheMonth.school">
          <a v-bind:href="`/schools/profile/${coderOfTheMonth.school_id}/`">
            {{ coderOfTheMonth.school }}
          </a>
        </div>
        <div
          class="card-text"
          v-if="coderOfTheMonth.state && coderOfTheMonth.country !== 'xx'"
        >
          {{ coderOfTheMonth.state }}, {{ coderOfTheMonth.country }}
        </div>
      </template>
    </div>
    <div class="card-footer">
      <a
        v-bind:href="
          category == 'female'
            ? '/coderofthemonth/female/'
            : '/coderofthemonth/'
        "
        >{{ T.coderOfTheMonthFullList }}</a
      >
    </div>
  </div>
</template>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.card-header {
  color: white;
  background-color: #5588dd;

  &.card-header-female {
    background-color: #8855dd;
  }
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import user_Username from '../user/Username.vue';
import { types } from '../../api_types';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faInfoCircle } from '@fortawesome/free-solid-svg-icons';
library.add(faInfoCircle);

@Component({
  components: {
    FontAwesomeIcon,
    'omegaup-user-username': user_Username,
  },
})
export default class CoderOfTheMonth extends Vue {
  @Prop({ default: 'all' }) category!: string;
  @Prop() coderOfTheMonth!: types.UserProfile;

  T = T;
}
</script>
