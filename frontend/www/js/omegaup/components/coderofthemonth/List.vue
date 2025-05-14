<template>
  <div class="card ranking-width">
    <ul class="nav nav-tabs justify-content-arround" role="tablist">
      <li v-for="tab in availableTabs" :key="tab.id" class="nav-item">
        <a
          :href="getTabName(tab)"
          class="nav-link"
          data-toggle="tab"
          role="tab"
          :aria-controls="tab.id"
          :class="{ active: currentSelectedTab === tab.id }"
          :aria-selected="currentSelectedTab === tab.id"
          @click="getSelectedTab(tab)"
        >
          {{ tab.title }}
        </a>
      </li>
    </ul>
    <component
      :is="currentTabComponent"
      class="tab"
      :coders="visibleCoders"
      :is-mentor="isMentor"
      :can-choose-coder="canChooseCoder && !coderIsSelected"
    >
      <template #button-select-coder="{ coder }">
        <td
          v-if="currentSelectedTab == 'candidatesToCoderOfTheMonth' && isMentor"
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
      </template>
    </component>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import user_Username from '../user/Username.vue';
import coderofthemonth_CodersList from './CodersList.vue';
import coderofthemonth_TopCodersList from './TopCodersList.vue';
import coderofthemonth_CandidatesList from './CandidatesList.vue';
import country_Flag from '../CountryFlag.vue';
import { types } from '../../api_types';

@Component({
  components: {
    'omegaup-user-username': user_Username,
    'omegaup-countryflag': country_Flag,
    'omegaup-coders-list': coderofthemonth_CodersList,
    'omegaup-top-coders-list': coderofthemonth_TopCodersList,
    'omegaup-candidates-list': coderofthemonth_CandidatesList,
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
  @Prop() selectedTab!: string;

  T = T;
  currentSelectedTab = this.selectedTab;

  get availableTabs(): { id: string; component: string; title: string }[] {
    const availableTabs = [
      {
        id: 'codersOfTheMonth',
        component: 'omegaup-coders-list',
        title:
          this.category === 'all'
            ? T.codersOfTheMonth
            : T.codersOfTheMonthFemale,
      },
      {
        id: 'codersOfPreviousMonth',
        component: 'omegaup-top-coders-list',
        title:
          this.category === 'all'
            ? T.codersOfTheMonthRank
            : T.codersOfTheMonthFemaleRank,
      },
      {
        id: 'candidatesToCoderOfTheMonth',
        component: 'omegaup-candidates-list',
        title:
          this.category === 'all'
            ? T.codersOfTheMonthListCandidate
            : T.codersOfTheMonthFemaleListCandidate,
      },
    ];

    return availableTabs;
  }

  get visibleCoders(): types.CoderOfTheMonthList[] {
    switch (this.currentSelectedTab) {
      case 'codersOfTheMonth':
      default:
        return this.codersOfCurrentMonth;
      case 'codersOfPreviousMonth':
        return this.codersOfPreviousMonth;
      case 'candidatesToCoderOfTheMonth':
        return this.candidatesToCoderOfTheMonth;
    }
  }

  get currentTabComponent(): string {
    return (
      this.availableTabs.find((tab) => tab.id === this.currentSelectedTab)
        ?.component ?? 'codersOfTheMonth'
    );
  }

  getSelectedTab(tab: { id: string; component: string; title: string }): void {
    this.currentSelectedTab = tab.id;
    window.location.hash = tab.id;
  }

  getTabName(tab: { id: string; component: string; title: string }): string {
    return `#${tab.id}`;
  }
}
</script>

<style scoped>
.nav-link.active,
.nav-link:hover {
  border: none;
  border-left: 0.0625rem solid var(--border-color-light);
  border-right: 0.0625rem solid var(--border-color-light);
  border-top-left-radius: 0rem;
  border-top-right-radius: 0rem;
  background-color: var(--coder-row-hover-bg);
  transition: background-color 0.3s ease;
}

.nav .nav-tabs {
  border-bottom: 0rem;
}

.nav-link {
  font-weight: medium;
  letter-spacing: 0.022rem;
  padding: 0.65rem 1rem;
  transition: all 0.3s ease;
}

.ranking-width {
  max-width: 55rem;
  margin: 0 auto;
}

.card {
  transition: all 0.3s ease;
}

.card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}
</style>
