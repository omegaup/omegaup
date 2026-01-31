<template>
  <div class="container-fluid max-width card pr-0 pl-0 custom-card">
    <ul class="nav nav-tabs introjs-tabs" role="tablist">
      <li
        v-for="(tabName, tabKey) in tabNames"
        :key="tabKey"
        class="nav-item"
        role="presentation"
      >
        <a
          class="nav-link"
          :href="`#${tabKey}`"
          :class="{ active: currentSelectedTab === tabKey }"
          data-toggle="tab"
          role="tab"
          @click="currentSelectedTab = tabKey"
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
        <div class="col-md-4 col-lg-3 p-0 ml-4">
          <input
            v-model="searchText"
            class="form-control introjs-search"
            type="text"
            :placeholder="T.courseCardsListSearch"
          />
        </div>
        <div
          v-if="currentSelectedTab === Tab.Public"
          class="col-md-4 col-lg-3 p-0 ml-3"
        >
          <select v-model="levelFilter" class="form-control">
            <option :value="Level.All">{{ T.courseLevelAllLevels }}</option>
            <option :value="Level.Introductory">
              {{ T.courseLevelIntroductoryLevel }}
            </option>
            <option :value="Level.Intermediate">
              {{ T.courseLevelIntermediateLevel }}
            </option>
            <option :value="Level.Advanced">
              {{ T.courseLevelAdvancedLevel }}
            </option>
          </select>
        </div>
      </div>
      <div
        v-for="(tabName, tabKey) in tabNames"
        :key="tabKey"
        class="tab-pane fade"
        :class="{
          show: currentSelectedTab === tabKey,
          active: currentSelectedTab === tabKey,
        }"
        role="tabpanel"
      >
        <div
          v-if="tabKey === Tab.Public"
          class="row row-cols-1 row-cols-md-2 row-cols-xl-3 p-4 introjs-join"
        >
          <omegaup-course-card-public
            v-for="course in filteredCards"
            :key="course.alias"
            :course="course"
            :logged-in="loggedIn"
            :has-visited-section="hasVisitedSection"
          ></omegaup-course-card-public>
        </div>
        <div
          v-if="tabKey === Tab.Enrolled"
          class="row"
          :class="{
            'row-cols-1 row-cols-md-2 row-cols-xl-3 p-4':
              loggedIn && filteredCards.length,
            'justify-content-center': !loggedIn || !filteredCards.length,
          }"
        >
          <template v-if="loggedIn">
            <template v-if="filteredCards.length">
              <omegaup-course-card-enrolled
                v-for="course in filteredCards"
                :key="course.alias"
                :course="course"
              ></omegaup-course-card-enrolled>
            </template>
            <div v-else class="empty-content my-2">
              {{ T.courseTabsEmptyEnrolledCourses }}
            </div>
          </template>
          <div v-else class="empty-content my-2">
            {{ T.courseCardMustLogIn }}
          </div>
        </div>
        <div
          v-if="tabKey === Tab.Finished"
          class="row"
          :class="{
            'row-cols-1 row-cols-md-2 row-cols-xl-3 p-4':
              loggedIn && filteredCards.length,
            'justify-content-center': !loggedIn || !filteredCards.length,
          }"
        >
          <template v-if="loggedIn">
            <template v-if="filteredCards.length">
              <omegaup-course-card-finished
                v-for="course in filteredCards"
                :key="course.alias"
                :course="course"
              ></omegaup-course-card-finished>
            </template>
            <div v-else class="empty-content my-2">
              {{ T.courseTabsEmptyFinishedCourses }}
            </div>
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
import * as ui from '../../ui';
import latinize from 'latinize';
import 'intro.js/introjs.css';
import introJs from 'intro.js';
import VueCookies from 'vue-cookies';
Vue.use(VueCookies, { expire: -1 });

import omegaup_Markdown from '../Markdown.vue';
import course_CardPublic from './CardPublic.vue';
import course_CardEnrolled from './CardEnrolled.vue';
import course_CardFinished from './CardFinished.vue';

export enum Tab {
  Enrolled = 'enrolled',
  Public = 'public',
  Finished = 'finished',
}

export enum Level {
  All = 'all',
  Introductory = 'introductory',
  Intermediate = 'intermediate',
  Advanced = 'advanced',
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
  @Prop({ default: Tab.Public }) selectedTab!: Tab;
  @Prop() hasVisitedSection!: boolean;

  T = T;
  ui = ui;
  Tab = Tab;
  Level = Level;
  currentSelectedTab = this.selectedTab;
  searchText = '';
  levelFilter = Level.All;

  mounted() {
    const title = T.joinCourseInteractiveGuideTitle;
    if (!this.hasVisitedSection) {
      introJs()
        .setOptions({
          nextLabel: T.interactiveGuideNextButton,
          prevLabel: T.interactiveGuidePreviousButton,
          doneLabel: T.interactiveGuideDoneButton,
          steps: [
            {
              title,
              intro: T.joinCourseInteractiveGuideWelcome,
            },
            {
              element: document.querySelector('.introjs-tabs') as Element,
              title,
              intro: T.joinCourseInteractiveGuideTabs,
            },
            {
              element: document.querySelector('.introjs-search') as Element,
              title,
              intro: T.joinCourseInteractiveGuideSearch,
            },
            {
              element: document.querySelector('.introjs-join') as Element,
              title,
              intro: T.joinCourseInteractiveGuideJoin,
            },
          ],
        })
        .start();
      this.$cookies.set('has-visited-join-course', true, -1);
    }
  }

  get tabNames(): Record<Tab, string> {
    return {
      [Tab.Public]: T.courseTabPublic,
      [Tab.Enrolled]: this.loggedIn
        ? ui.formatString(T.courseTabEnrolled, {
            course_count: this.courses.enrolled.length,
          })
        : T.courseTabEnrolledUnlogged,
      [Tab.Finished]: this.loggedIn
        ? ui.formatString(T.courseTabFinished, {
            course_count: this.courses.finished.length,
          })
        : T.courseTabFinishedUnlogged,
    };
  }

  get filteredCards():
    | types.CourseCardEnrolled[]
    | types.CourseCardPublic[]
    | types.CourseCardFinished[] {
    switch (this.currentSelectedTab) {
      case Tab.Enrolled:
        return this.courses.enrolled.filter(
          (course) =>
            this.searchText === '' ||
            latinize(course.name.toLowerCase()).includes(
              latinize(this.searchText.toLowerCase()),
            ),
        );
      case Tab.Finished:
        return this.courses.finished.filter(
          (course) =>
            this.searchText === '' ||
            latinize(course.name.toLowerCase()).includes(
              latinize(this.searchText.toLowerCase()),
            ),
        );
      default:
        // Only apply level filter to public courses
        return this.courses.public.filter((course) => {
          const matchesText =
            this.searchText === '' ||
            latinize(course.name.toLowerCase()).includes(
              latinize(this.searchText.toLowerCase()),
            );

          const matchesLevel =
            this.levelFilter === Level.All || course.level === this.levelFilter;

          return matchesText && matchesLevel;
        });
    }
  }
}
</script>

<style lang="scss">
@import '../../../../sass/main.scss';

.card > .row.no-gutters {
  background-color: $omegaup-white;
  min-height: 13.5rem;
  overflow-y: visible;

  .course-data p {
    font-size: 0.9rem;
  }

  .public-course-card {
    background-color: $omegaup-blue;
    height: 1em;
  }

  .enrolled-course-card {
    background-color: $omegaup-pink--lighter;
    height: 1em;
  }

  @media only screen and (min-width: 576px) {
    .public-course-card,
    .enrolled-course-card {
      height: auto;
    }
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

.max-width {
  max-width: 68.8rem;
  margin: 4rem auto;
}

.nav-link {
  padding: 0.6rem 1.2rem;
}

.nav-tabs,
.nav-link,
.nav-link-active,
.nav-link-hover {
  border-top: none !important;
  border-top-left-radius: 0 !important;
  border-top-right-radius: 0 !important;
}

@media (max-width: 576px) {
  .custom-card {
    padding: 1.25rem 2rem !important;
  }

  .row.m-0.mt-4 {
    display: flex;
    flex-wrap: nowrap;
    align-items: center;
  }

  .row.m-0.mt-4 > div {
    width: calc(50% - 25px);
    flex: 0 0 calc(50% - 25px);
    max-width: calc(50% - 25px);
  }
}

@media (min-width: 577px) and (max-width: 767px) {
  .row.m-0.mt-4 {
    display: flex;
    flex-wrap: nowrap;
    align-items: center;
  }

  .row.m-0.mt-4 > div {
    width: calc(50% - 25px);
    flex: 0 0 calc(50% - 25px);
    max-width: calc(50% - 25px);
  }
}
</style>
