<template>
  <div>
    <ul class="nav nav-tabs" role="tablist">
      <li
        v-for="tab in tabs"
        :key="tab.id"
        class="nav-item"
        role="presentation"
      >
        <a
          class="nav-link"
          :href="`#${tab.id}`"
          :class="{ active: selectedTab === tab.id }"
          data-toggle="tab"
          role="tab"
          @click="selectedTab = tab.id"
          >{{ tab.name }}</a
        >
      </li>
      <li class="ml-auto">
        <a class="nav-link border-0" href="/course/home/">{{
          T.wordsReadMore
        }}</a>
      </li>
    </ul>
    <div class="tab-content">
      <!-- TODO: Show content based on tab -->
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import T from '../../lang';

export enum Tabs {
  Enrolled = 'enrolled',
  General = 'general',
  Finished = 'finished',
}

@Component
export default class CourseTabs extends Vue {
  T = T;
  Tabs = Tabs;
  tabs = {
    enrolled: {
      id: Tabs.Enrolled,
      name: T.courseTabEnrolled,
    },
    general: {
      id: Tabs.General,
      name: T.courseTabGeneral,
    },
    finished: {
      id: Tabs.Finished,
      name: T.courseTabFinished,
    },
  };
  selectedTab = this.tabs.enrolled.id;
}
</script>
