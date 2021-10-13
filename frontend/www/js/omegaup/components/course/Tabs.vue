<template>
  <div class="container-fluid p-5">
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
      <!-- TODO: Add search input. -->
      <div
        v-for="tab in tabs"
        :key="tab.id"
        class="tab-pane fade py-4 px-2"
        :class="{
          show: selectedTab === tab.id,
          active: selectedTab === tab.id,
        }"
        role="tabpanel"
      >
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3">
          <omegaup-course-card
            v-for="course in courses[tab.id]"
            :key="course.alias"
            :course="course"
            :type="tab.id"
          ></omegaup-course-card>
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
import course_Card from './Cardv2.vue';

export enum Tabs {
  Enrolled = 'enrolled',
  Public = 'public',
  Finished = 'finished',
}

@Component({
  components: {
    'omegaup-course-card': course_Card,
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
  Tabs = Tabs;
  tabs = {
    public: {
      id: Tabs.Public,
      name: T.courseTabPublic,
    },
    enrolled: {
      id: Tabs.Enrolled,
      name: T.courseTabEnrolled,
    },
    finished: {
      id: Tabs.Finished,
      name: T.courseTabFinished,
    },
  };
  selectedTab = this.tabs.public.id;
}
</script>
