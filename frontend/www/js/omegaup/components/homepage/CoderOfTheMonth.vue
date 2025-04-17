<template>
  <div class="card h-100">
    <div
      class="d-flex justify-content-between card-header"
      :class="`card-header-${category}`"
    >
      <h5 class="m-0">
        {{
          category === 'female' ? T.coderOfTheMonthFemale : T.coderOfTheMonth
        }}
      </h5>
      <a class="card-header-help" :href="CoderOfTheMonthPolicyURL">
        <font-awesome-icon :icon="['fas', 'info-circle']" />
      </a>
    </div>
    <div
      class="card-body d-flex flex-column justify-content-center text-center"
    >
      <a
        v-if="!coderOfTheMonth.is_private"
        :href="`/profile/${coderOfTheMonth.username}/`"
      >
        <img :src="coderOfTheMonth.gravatar_92" height="80" />
      </a>
      <h5 class="card-title">
        <omegaup-user-username
          :classname="coderOfTheMonth.classname"
          :linkify="true"
          :username="coderOfTheMonth.username"
          :country="coderOfTheMonth.country_id"
        ></omegaup-user-username>
      </h5>
      <template v-if="!coderOfTheMonth.is_private">
        <div class="card-text">
          {{ coderOfTheMonth.name }}
        </div>
        <div v-if="coderOfTheMonth.school" class="card-text">
          <a :href="`/schools/profile/${coderOfTheMonth.school_id}/`">
            {{ coderOfTheMonth.school }}
          </a>
        </div>
        <div
          v-if="coderOfTheMonth.state && coderOfTheMonth.country !== 'xx'"
          class="card-text"
        >
          {{ coderOfTheMonth.state }}, {{ coderOfTheMonth.country }}
        </div>
      </template>
    </div>
    <div class="card-footer">
      <a
        :href="
          category == 'female'
            ? '/coderofthemonth/female/'
            : '/coderofthemonth/'
        "
        >{{ T.coderOfTheMonthFullList }}</a
      >
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import user_Username from '../user/Username.vue';
import { types } from '../../api_types';
import { getBlogUrl } from '../../urlHelper';

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
  get CoderOfTheMonthPolicyURL(): string {
    return getBlogUrl('CoderOfTheMonthPolicyURL');
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.card-header {
  color: var(--coder-of-the-month-card-header-font-color);
  background-color: var(--coder-of-the-month-card-header-background-color);

  &.card-header-female {
    background-color: var(
      --coder-of-the-month-card-header-female-background-color
    );
  }
}
</style>
