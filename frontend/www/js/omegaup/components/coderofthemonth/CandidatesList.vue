<template>
  <div v-if="isDisabled" class="system-in-maintainance m-5 text-center">
    <omegaup-markdown
      :markdown="T.coderOfTheMonthSystemInMaintainance"
    ></omegaup-markdown>
    <font-awesome-icon :icon="['fas', 'cogs']" />
  </div>
  <table v-else class="table table-striped table-hover table-responsive-sm">
    <thead>
      <tr>
        <th scope="col" class="text-center"></th>
        <th scope="col" class="text-center">
          {{ T.codersOfTheMonthUser }}
        </th>
        <th scope="col" class="text-center">
          {{ T.codersOfTheMonthCountry }}
        </th>

        <th scope="col" class="text-center">
          {{ T.profileStatisticsNumberOfSolvedProblems }}
        </th>
        <th scope="col" class="text-center">
          {{ T.rankScore }}
        </th>
        <th v-if="isMentor" scope="col" class="text-center">
          {{ T.wordsActions }}
        </th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="(coder, index) in coders" :key="index">
        <td class="text-center">
          <img :src="coder.gravatar_32" />
        </td>
        <td class="text-center align-middle">
          <omegaup-user-username
            :classname="coder.classname"
            :linkify="true"
            :username="coder.username"
          ></omegaup-user-username>
        </td>
        <td class="text-center align-middle">
          <omegaup-countryflag
            :country="coder.country_id"
          ></omegaup-countryflag>
        </td>
        <td class="text-center align-middle">
          {{ coder.problems_solved }}
        </td>
        <td class="text-center align-middle">
          {{ coder.score }}
        </td>
        <slot name="button-select-coder" :coder="coder"></slot>
      </tr>
    </tbody>
  </table>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import user_Username from '../user/Username.vue';
import country_Flag from '../CountryFlag.vue';
import { types } from '../../api_types';

import omegaup_Markdown from '../Markdown.vue';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faCogs } from '@fortawesome/free-solid-svg-icons';
library.add(faCogs);

@Component({
  components: {
    'omegaup-user-username': user_Username,
    'omegaup-countryflag': country_Flag,
    'omegaup-markdown': omegaup_Markdown,
    'font-awesome-icon': FontAwesomeIcon,
  },
})
export default class CoderOfTheMonthList extends Vue {
  @Prop() coders!: types.CoderOfTheMonthList[];
  @Prop() isMentor!: boolean;
  @Prop() selectedTab!: string;
  @Prop({ default: false }) isDisabled!: boolean;

  T = T;
}
</script>

<style scoped lang="scss">
.system-in-maintainance {
  font-size: 180%;
  color: var(--general-in-maintainance-color);
}
</style>
