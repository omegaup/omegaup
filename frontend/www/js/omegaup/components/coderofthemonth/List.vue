<template>
  <div class="card ranking-width">
    <ul class="nav nav-tabs justify-content-arround" role="tablist">
      <li class="nav-item">
        <a
          href="#"
          class="nav-link"
          data-toggle="tab"
          role="tab"
          aria-controls="codersOfTheMonth"
          :class="{ active: selectedTab === 'codersOfTheMonth' }"
          :aria-selected="selectedTab === 'codersOfTheMonth'"
          @click="selectedTab = 'codersOfTheMonth'"
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
          :class="{ active: selectedTab === 'codersOfPreviousMonth' }"
          :aria-selected="selectedTab === 'codersOfPreviousMonth'"
          @click="selectedTab = 'codersOfPreviousMonth'"
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
          :class="{
            active: selectedTab === 'candidatesToCoderOfTheMonth',
          }"
          :aria-selected="selectedTab === 'candidatesToCoderOfTheMonth'"
          @click="selectedTab = 'candidatesToCoderOfTheMonth'"
        >
          {{
            category == 'all'
              ? T.codersOfTheMonthListCandidate
              : T.codersOfTheMonthFemaleListCandidate
          }}
        </a>
      </li>
    </ul>
    <table class="table table-striped table-hover table-responsive-sm">
      <thead>
        <tr>
          <th scope="col" class="text-center"></th>
          <th scope="col" class="text-center">
            {{ T.codersOfTheMonthUser }}
          </th>
          <th scope="col" class="text-center">
            {{ T.codersOfTheMonthCountry }}
          </th>
          <th
            v-if="selectedTab == 'codersOfTheMonth'"
            scope="col"
            class="text-center"
          >
            {{ T.codersOfTheMonthDate }}
          </th>

          <template v-if="selectedTab == 'candidatesToCoderOfTheMonth'">
            <th scope="col" class="text-center">
              {{ T.profileStatisticsNumberOfSolvedProblems }}
            </th>
            <th scope="col" class="text-center">
              {{ T.rankScore }}
            </th>
            <th v-if="isMentor" scope="col" class="text-center">
              {{ T.wordsActions }}
            </th>
          </template>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(coder, index) in visibleCoders" :key="index">
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
          <td
            v-if="selectedTab == 'codersOfTheMonth'"
            class="text-center align-middle"
          >
            {{ coder.date }}
          </td>
          <td
            v-if="selectedTab == 'candidatesToCoderOfTheMonth'"
            class="text-center align-middle"
          >
            {{ coder.problems_solved }}
          </td>
          <td
            v-if="selectedTab == 'candidatesToCoderOfTheMonth'"
            class="text-center align-middle"
          >
            {{ coder.score }}
          </td>
          <td
            v-if="selectedTab == 'candidatesToCoderOfTheMonth' && isMentor"
            class="text-center align-middle"
          >
            <button
              v-if="canChooseCoder && !coderIsSelected"
              class="btn btn-sm btn-primary"
              @click="$emit('select-coder', coder.username, category)"
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
import T from '../../lang';
import user_Username from '../user/Username.vue';
import country_Flag from '../CountryFlag.vue';
import { types } from '../../api_types';

@Component({
  components: {
    'omegaup-user-username': user_Username,
    'omegaup-countryflag': country_Flag,
  },
})
export default class CoderOfTheMonthList extends Vue {
  @Prop() codersOfCurrentMonth!: types.CoderOfTheMonthList[];
  @Prop() codersOfPreviousMonth!: types.CoderOfTheMonthList[];
  @Prop() candidatesToCoderOfTheMonth!: types.CoderOfTheMonthList[];
  @Prop() canChooseCoder!: boolean;
  @Prop() coderIsSelected!: boolean;
  @Prop() isMentor!: boolean;
  @Prop() category!: string;

  T = T;
  selectedTab = 'codersOfTheMonth';

  get visibleCoders(): types.CoderOfTheMonthList[] {
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

<style scoped>
.nav-link.active,
.nav-link:hover {
  border: none;
  border-left: 0.0625rem solid #dee2e6;
  border-right: 0.0625rem solid #dee2e6;
  border-top-left-radius: 0rem;
  border-top-right-radius: 0rem;
}
.nav .nav-tabs {
  border-bottom: 0rem;
}

.nav-link {
  font-weight: medium;
  letter-spacing: 0.022rem;
  padding: 0.65rem 1rem;
}
.ranking-width {
  max-width: 55rem;
  margin: 0 auto;
}
</style>
