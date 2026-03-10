<template>
  <div class="card ranking-width">
    <ul class="nav nav-tabs justify-content-around" role="tablist">
      <li v-for="tab in availableTabs" :key="tab.id" class="nav-item">
        <a
  :href="getTabName(tab)"
  class="nav-link"
  role="tab"
  :aria-controls="tab.id"
  :class="{ active: currentSelectedTab === tab.id }"
  :aria-selected="currentSelectedTab === tab.id"
  @click.prevent="getSelectedTab(tab)"
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

  // initial tab
  currentSelectedTab = this.selectedTab;

  get availableTabs(): { id: string; component: string; title: string }[] {
    return [
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
  }

  get visibleCoders(): types.CoderOfTheMonthList[] {
    switch (this.currentSelectedTab) {
      case 'codersOfPreviousMonth':
        return this.codersOfPreviousMonth;

      case 'candidatesToCoderOfTheMonth':
        return this.candidatesToCoderOfTheMonth;

      case 'codersOfTheMonth':
      default:
        return this.codersOfCurrentMonth;
    }
  }

  get currentTabComponent(): string {
    return (
      this.availableTabs.find((tab) => tab.id === this.currentSelectedTab)
        ?.component ?? 'omegaup-coders-list'
    );
  }

  getSelectedTab(tab: { id: string; component: string; title: string }): void {
    this.currentSelectedTab = tab.id;
    window.history.pushState(null, '', `#${tab.id}`);
  }

  getTabName(tab: { id: string; component: string; title: string }): string {
    return `#${tab.id}`;
  }

  mounted(): void {
    const hash = window.location.hash.replace('#', '');
    if (hash) {
      this.currentSelectedTab = hash;
    }

    window.addEventListener('popstate', () => {
      const newHash = window.location.hash.replace('#', '');
      if (newHash) {
        this.currentSelectedTab = newHash;
      }
    });
  }
}
</script>

<style scoped>

.nav-link.active,
.nav-link:hover {
  border: none;
  border-left: 0.0625rem solid #dee2e6;
  border-right: 0.0625rem solid #dee2e6;
}

.nav .nav-tabs {
  border-bottom: 0rem;
}

.nav-tabs {
  flex-wrap: nowrap;
  overflow-x: auto;
  overflow-y: hidden;
  white-space: nowrap;
}

.nav-link {
  font-weight: medium;
  letter-spacing: 0.022rem;
  padding: 0.65rem 1rem;
}

.ranking-width {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
}

</style>