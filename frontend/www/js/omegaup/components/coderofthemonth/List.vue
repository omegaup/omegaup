<template>
  <div class="card w-100">
    <ul class="nav nav-tabs justify-content-around" role="tablist">
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
import { defineComponent } from 'vue';
import type { PropType } from 'vue';
import T from '../../lang';
import user_Username from '../user/Username.vue';
import coderofthemonth_CodersList from './CodersList.vue';
import coderofthemonth_TopCodersList from './TopCodersList.vue';
import coderofthemonth_CandidatesList from './CandidatesList.vue';
import country_Flag from '../CountryFlag.vue';
import { types } from '../../api_types';

export default defineComponent({
  name: 'CoderOfTheMonthList',
  components: {
    'omegaup-user-username': user_Username,
    'omegaup-countryflag': country_Flag,
    'omegaup-coders-list': coderofthemonth_CodersList,
    'omegaup-top-coders-list': coderofthemonth_TopCodersList,
    'omegaup-candidates-list': coderofthemonth_CandidatesList,
  },
  props: {
    codersOfCurrentMonth: {
      type: Array as PropType<types.CoderOfTheMonthList[]>,
      required: true,
    },
    codersOfPreviousMonth: {
      type: Array as PropType<types.CoderOfTheMonthList[]>,
      required: true,
    },
    candidatesToCoderOfTheMonth: {
      type: Array as PropType<types.CoderOfTheMonthList[]>,
      required: true,
    },
    canChooseCoder: {
      type: Boolean,
      required: true,
    },
    coderIsSelected: {
      type: Boolean,
      required: true,
    },
    isMentor: {
      type: Boolean,
      required: true,
    },
    category: {
      type: String,
      required: true,
    },
    selectedTab: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      T,
      currentSelectedTab: this.selectedTab,
    };
  },
  computed: {
    availableTabs(): { id: string; component: string; title: string }[] {
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
    },
    visibleCoders(): types.CoderOfTheMonthList[] {
      switch (this.currentSelectedTab) {
        case 'codersOfTheMonth':
        default:
          return this.codersOfCurrentMonth;
        case 'codersOfPreviousMonth':
          return this.codersOfPreviousMonth;
        case 'candidatesToCoderOfTheMonth':
          return this.candidatesToCoderOfTheMonth;
      }
    },
    currentTabComponent(): string {
      return (
        this.availableTabs.find((tab) => tab.id === this.currentSelectedTab)
          ?.component ?? 'codersOfTheMonth'
      );
    },
  },
  watch: {
    selectedTab(newTab: string): void {
      this.currentSelectedTab = newTab;
    },
  },
  methods: {
    getSelectedTab(tab: {
      id: string;
      component: string;
      title: string;
    }): void {
      this.currentSelectedTab = tab.id;
      window.location.hash = tab.id;
    },
    getTabName(tab: { id: string; component: string; title: string }): string {
      return `#${tab.id}`;
    },
  },
});
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

.nav-tabs {
  border-bottom: 0rem;
  flex-wrap: nowrap !important;
  overflow-x: auto;
  overflow-y: hidden;
}

.nav-link {
  font-weight: medium;
  letter-spacing: 0.022rem;
  padding: 0.65rem 1rem;
  white-space: nowrap;
}
</style>
