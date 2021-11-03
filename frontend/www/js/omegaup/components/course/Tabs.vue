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
      <div class="row m-0 mt-4">
        <div class="col-md-6 col-lg-3 p-0">
          <input
            v-model="searchText"
            class="form-control"
            type="text"
            :placeholder="T.courseCardsListSearch"
          />
        </div>
      </div>
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
        <div
          v-if="tabKey === Tab.Public"
          class="row row-cols-1 row-cols-md-2 row-cols-xl-3"
        >
          <omegaup-course-card-public
            v-for="course in filteredCards"
            :key="course.alias"
            :course="course"
            :logged-in="loggedIn"
          ></omegaup-course-card-public>
        </div>
        <div
          v-if="tabKey === Tab.Enrolled"
          class="row"
          :class="{
            'row-cols-1 row-cols-md-2 row-cols-xl-3': loggedIn,
            'justify-content-center': !loggedIn,
          }"
        >
          <template v-if="loggedIn">
            <omegaup-course-card-enrolled
              v-for="course in filteredCards"
              :key="course.alias"
              :course="course"
            ></omegaup-course-card-enrolled>
          </template>
          <div v-else class="empty-content my-2">
            {{ T.courseCardMustLogIn }}
          </div>
        </div>
        <div
          v-if="tabKey === Tab.Finished"
          class="row"
          :class="{
            'row-cols-1 row-cols-md-2 row-cols-xl-3': loggedIn,
            'justify-content-center': !loggedIn,
          }"
        >
          <template v-if="loggedIn">
            <omegaup-course-card-finished
              v-for="course in filteredCards"
              :key="course.alias"
              :course="course"
            ></omegaup-course-card-finished>
          </template>
          <div v-else class="empty-content my-2">
            {{ T.courseCardMustLogIn }}
          </div>
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
import course_CardFinished from './CardFinished.vue';

export enum Tab {
  Enrolled = 'enrolled',
  Public = 'public',
  Finished = 'finished',
}

@Component({
  components: {
    'omegaup-course-card-public': course_CardPublic,
    'omegaup-course-card-enrolled': course_CardEnrolled,
    'omegaup-course-card-finished': course_CardFinished,
    'omegaup-markdown': omegaup_Markdown,
  },
})
export default class CourseTabs extends Vue {
  @Prop() courses!: {
    enrolled: types.CourseCardEnrolled[];
    public: types.CourseCardPublic[];
    finished: types.CourseCardFinished[];
  };
  @Prop({ default: false }) loggedIn!: boolean;

  T = T;
  Tab = Tab;
  selectedTab = Tab.Public;
  searchText = '';

  get tabNames(): Record<Tab, string> {
    return {
      [Tab.Public]: T.courseTabPublic,
      [Tab.Enrolled]: this.loggedIn
        ? `${T.courseTabEnrolled} (${this.courses.enrolled.length})`
        : T.courseTabEnrolled,
      [Tab.Finished]: this.loggedIn
        ? `${T.courseTabFinished} (${this.courses.finished.length})`
        : T.courseTabFinished,
    };
  }

  get filteredCards():
    | types.CourseCardEnrolled[]
    | types.CourseCardPublic[]
    | types.CourseCardFinished[] {
    switch (this.selectedTab) {
      case Tab.Enrolled:
        return this.courses.enrolled.filter(
          (course) =>
            this.searchText === '' || course.name.includes(this.searchText),
        );
      case Tab.Finished:
        return this.courses.finished.filter(
          (course) =>
            this.searchText === '' || course.name.includes(this.searchText),
        );
      default:
        return this.courses.public.filter(
          (course) =>
            this.searchText === '' || course.name.includes(this.searchText),
        );
    }
  }
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

  .course-star {
    font-size: 3.2rem;
    line-height: normal;
  }
}

.empty-content {
  text-align: center;
  font-size: 2.25rem;
  color: var(--arena-contest-list-empty-category-font-color);
}
</style>
