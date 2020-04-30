<template>
  <div class="container-lg p-5">
    <ul class="nav nav-tabs" role="tablist">
      <li class="nav-item">
        <a
          href="#"
          class="nav-link"
          data-toggle="tab"
          role="tab"
          aria-controls="codersOfTheMonth"
          v-bind:class="{ active: selectedTab === 'codersOfTheMonth' }"
          v-bind:aria-selected="selectedTab === 'codersOfTheMonth'"
          v-on:click="selectedTab = 'codersOfTheMonth'"
        >
          {{
            category == 'all' ? T.codersOfTheMonth : T.codersOfTheMonthFemale
          }}
        </a>
      </li>
      <li class="nav-item">
        <a
          href="#"
          class="nav-link"
          data-toggle="tab"
          role="tab"
          aria-controls="codersOfPreviousMonth"
          v-bind:class="{ active: selectedTab === 'codersOfPreviousMonth' }"
          v-bind:aria-selected="selectedTab === 'codersOfPreviousMonth'"
          v-on:click="selectedTab = 'codersOfPreviousMonth'"
        >
          {{
            category == 'all'
              ? T.codersOfTheMonthRank
              : T.codersOfTheMonthFemaleRank
          }}
        </a>
      </li>
      <li class="nav-item">
        <a
          href="#"
          class="nav-link"
          data-toggle="tab"
          role="tab"
          aria-controls="candidatesToCoderOfTheMonth"
          v-bind:class="{
            active: selectedTab === 'candidatesToCoderOfTheMonth',
          }"
          v-bind:aria-selected="selectedTab === 'candidatesToCoderOfTheMonth'"
          v-on:click="selectedTab = 'candidatesToCoderOfTheMonth'"
        >
          {{
            category == 'all'
              ? T.codersOfTheMonthListCandidate
              : T.codersOfTheMonthFemaleListCandidate
          }}
        </a>
      </li>
    </ul>
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th scope="col" class="text-center"></th>
          <th scope="col" class="text-center">
            {{ T.codersOfTheMonthCountry }}
          </th>
          <th scope="col" class="text-center">{{ T.codersOfTheMonthUser }}</th>
          <th
            scope="col"
            class="text-center"
            v-if="selectedTab == 'codersOfTheMonth'"
          >
            {{ T.codersOfTheMonthDate }}
          </th>
          <th
            scope="col"
            class="text-right"
            v-if="selectedTab == 'candidatesToCoderOfTheMonth'"
          >
            {{ T.profileStatisticsNumberOfSolvedProblems }}
          </th>
          <th
            scope="col"
            class="text-right"
            v-if="selectedTab == 'candidatesToCoderOfTheMonth'"
          >
            {{ T.rankScore }}
          </th>
          <th
            scope="col"
            class="text-center"
            v-if="selectedTab == 'candidatesToCoderOfTheMonth' && isMentor"
          >
            {{ T.wordsActions }}
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-bind:key="index" v-for="(coder, index) in visibleCoders">
          <td class="text-center">
            <img v-bind:src="coder.gravatar_32" />
          </td>
          <td class="text-center">
            <omegaup-countryflag
              v-bind:country="coder.country_id"
            ></omegaup-countryflag>
          </td>
          <td class="text-center">
            <omegaup-user-username
              v-bind:classname="coder.classname"
              v-bind:linkify="true"
              v-bind:username="coder.username"
            ></omegaup-user-username>
          </td>
          <td v-if="selectedTab == 'codersOfTheMonth'" class="text-center">
            {{ coder.date }}
          </td>
          <td
            class="text-right"
            v-if="selectedTab == 'candidatesToCoderOfTheMonth'"
          >
            {{ coder.problems_solved }}
          </td>
          <td
            class="text-right"
            v-if="selectedTab == 'candidatesToCoderOfTheMonth'"
          >
            {{ coder.score }}
          </td>
          <td
            class="text-center"
            v-if="selectedTab == 'candidatesToCoderOfTheMonth' && isMentor"
          >
            <button
              class="btn btn-sm btn-primary"
              v-if="canChooseCoder && !coderIsSelected"
              v-on:click="$emit('select-coder', coder.username)"
            >
              {{
                category == 'all'
                  ? T.coderOfTheMonthChooseAsCoder
                  : T.coderOfTheMonthFemaleChooseAsCoder
              }}
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import user_Username from '../user/Username.vue';
import country_Flag from '../CountryFlag.vue';

@Component({
  components: {
    'omegaup-user-username': user_Username,
    'omegaup-countryflag': country_Flag,
  },
})
export default class CoderOfTheMonthList extends Vue {
  @Prop() codersOfCurrentMonth!: omegaup.CoderOfTheMonth[];
  @Prop() codersOfPreviousMonth!: omegaup.CoderOfTheMonth[];
  @Prop() candidatesToCoderOfTheMonth!: omegaup.CoderOfTheMonth[];
  @Prop() canChooseCoder!: boolean;
  @Prop() coderIsSelected!: boolean;
  @Prop() isMentor!: boolean;
  @Prop() category!: string;

  T = T;
  selectedTab = 'codersOfTheMonth';

  get visibleCoders(): omegaup.CoderOfTheMonth[] {
    switch (this.selectedTab) {
      case 'codersOfTheMonth':
      default:
        return this.codersOfCurrentMonth;
      case 'codersOfPreviousMonth':
        return this.codersOfPreviousMonth;
      case 'candidatesToCoderOfTheMonth':
        return this.candidatesToCoderOfTheMonth;
    }
  }
}
</script>
