<template>
  <div class="container-fluid p-5">
    <ul class="nav nav-tabs" role="tablist">
      <li
        v-for="(tabName, tabKey) in tabNames"
        :key="tabKey"
        class="nav-item"
        role="presentation"
      >
        <a
          class="nav-link"
          :href="`#${tabKey}`"
          :class="{ active: selectedTab === tabKey }"
          data-toggle="tab"
          role="tab"
          @click="selectedTab = tabKey"
          >{{ tabName }}</a
        >
      </li>
      <li class="ml-auto">
        <a class="nav-link border-0" href="/course/home/">{{
          T.wordsReadMore
        }}</a>
      </li>
    </ul>
    <div class="tab-content">
      <!-- TODO: Add search input. -->
      <div
        v-for="(tabName, tabKey) in tabNames"
        :key="tabKey"
        class="tab-pane fade py-4 px-2"
        :class="{
          show: selectedTab === tabKey,
          active: selectedTab === tabKey,
        }"
        role="tabpanel"
      >
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3">
          <template v-if="tabKey === Tab.Public">
            <omegaup-course-card-public
              v-for="course in courses[tabKey]"
              :key="course.alias"
              :course="course"
            ></omegaup-course-card-public>
          </template>
          <template v-if="tabKey === Tab.Enrolled">
            <omegaup-course-card-enrolled
              v-for="course in courses[tabKey]"
              :key="course.alias"
              :course="course"
            ></omegaup-course-card-enrolled>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';

import omegaup_Markdown from '../Markdown.vue';
import course_CardPublic from './CardPublic.vue';
import course_CardEnrolled from './CardEnrolled.vue';

export enum Tab {
  Enrolled = 'enrolled',
  Public = 'public',
  Finished = 'finished',
}

@Component({
  components: {
    'omegaup-course-card-public': course_CardPublic,
    'omegaup-course-card-enrolled': course_CardEnrolled,
    'omegaup-markdown': omegaup_Markdown,
  },
})
export default class CourseTabs extends Vue {
  @Prop() courses!: {
    enrolled: types.CourseCardEnrolled[];
    public: types.CourseCardPublic[];
    finished: types.CourseCardFinished[];
  };

  T = T;
  Tab = Tab;
  tabNames: Record<Tab, string> = {
    [Tab.Public]: T.courseTabPublic,
    [Tab.Enrolled]: T.courseTabEnrolled,
    [Tab.Finished]: T.courseTabFinished,
  };
  selectedTab = Tab.Public;
}
</script>

<style lang="scss">
@import '../../../../sass/main.scss';

.card > .row.no-gutters {
  background-color: $omegaup-white;
  height: 12.5rem;
  overflow-y: auto;

  .course-data p {
    font-size: 0.9rem;
  }

  .public-course-card {
    background-color: $omegaup-blue;
  }

  .enrolled-course-card {
    background-color: $omegaup-pink--lighter;
  }

  .finished-course-card {
    background-color: $omegaup-grey--lighter;
  }

  .progress-bar {
    background-color: $omegaup-yellow;
  }
}
</style>
