<template>
  <div>
    <div class="card">
      <h3 class="card-header mb-0">{{ cardTitle }}</h3>
      <div class="card-body">
        <div class="row justify-content-between align-items-center">
          <div class="col-sm-7 col-md-5 col-lg-4">
            <input
              v-model="searchText"
              class="form-control"
              type="text"
              :placeholder="T.courseCardsListSearch"
            />
          </div>
          <div
            v-if="groupedCourses.hidden.length"
            class="col-sm-5 col-lg-8 text-right"
          >
            <a href="#" @click="seeAll = !seeAll">{{
              togglePastCoursesText
            }}</a>
          </div>
        </div>
        <div class="row mt-5 justify-content-between row-cols-1 row-cols-md-2">
          <omegaup-course-card
            v-for="course in groupedCourses.unhidden"
            :key="course.alias"
            :course="course"
            :type="
              type === CourseListType.Public
                ? CourseListType.Public
                : CourseListType.Student
            "
          ></omegaup-course-card>
        </div>
        <template v-if="seeAll">
          <div class="dropdown-divider"></div>
          <div
            class="row mt-4 justify-content-between row-cols-1 row-cols-md-2"
          >
            <omegaup-course-card
              v-for="course in groupedCourses.hidden"
              :key="course.alias"
              :course="course"
              :type="
                type === CourseListType.Public
                  ? CourseListType.Public
                  : 'finished'
              "
            ></omegaup-course-card>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import course_FilteredList from './FilteredList.vue';
import course_Card from './Card.vue';

export enum CourseListType {
  Public = 'public',
  Student = 'student',
}

interface GroupedCourses {
  unhidden: types.FilteredCourse[]; //FIXME: Sugerencias para el nombre de este campo?
  hidden: types.FilteredCourse[];
}

@Component({
  components: {
    'omegaup-course-filtered-list': course_FilteredList,
    'omegaup-course-card': course_Card,
  },
})
export default class CourseCardsList extends Vue {
  @Prop() courses!: types.StudentCourses;
  @Prop() type!: string;

  CourseListType = CourseListType;
  T = T;
  searchText = '';
  seeAll = false;

  get cardTitle(): string {
    if (this.type === CourseListType.Public) return T.courseListPublicCourses;
    if (this.type === CourseListType.Student) return T.courseListIStudy;
    return '';
  }

  get togglePastCoursesText(): string {
    if (this.type === CourseListType.Public) {
      if (this.seeAll) {
        return T.courseCardsListHidePastCourses;
      }
      return T.courseCardsListShowPastCourses;
    }

    if (this.seeAll) {
      return T.courseCardsListHideFinishedCourses;
    }
    return T.courseCardsListShowFinishedCourses;
  }

  get groupedCourses(): GroupedCourses {
    if (!(this.type in this.courses)) {
      return {
        unhidden: [],
        hidden: [],
      };
    }

    let courses: GroupedCourses = {
      unhidden: [],
      hidden: [],
    };

    this.courses[this.type].filteredCourses.current.courses.forEach(
      (course) => {
        if (this.searchText !== '' && !course.name.includes(this.searchText)) {
          return;
        }
        if (this.type === CourseListType.Public) {
          courses.unhidden.push(course);
          return;
        }
        if (course.progress === 100) {
          courses.hidden.push(course);
        } else {
          courses.unhidden.push(course);
        }
      },
    );

    this.courses[this.type].filteredCourses.past.courses.forEach((course) => {
      if (this.searchText !== '' && !course.name.includes(this.searchText)) {
        return;
      }
      if (this.type === CourseListType.Public) {
        courses.unhidden.push(course);
        return;
      }
      if (course.progress === 100) {
        courses.hidden.push(course);
      } else {
        courses.unhidden.push(course);
      }
    });
    return courses;
  }
}
</script>
