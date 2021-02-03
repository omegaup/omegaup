<template>
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
          v-if="groupedCourses.invisible.length"
          class="col-sm-5 col-lg-8 text-right"
        >
          <label class="form-check-label">
            <input v-model="seeAll" class="form-check-input" type="checkbox" />
            {{ seeAllCoursesText }}
          </label>
        </div>
      </div>
      <div class="row mt-5 justify-content-between row-cols-1 row-cols-md-2">
        <omegaup-course-card
          v-for="course in groupedCourses.visible"
          :key="course.alias"
          :course="course"
          :type="
            type === CourseType.Public ? CourseType.Public : CourseType.Student
          "
        ></omegaup-course-card>
      </div>
      <div v-show="seeAll" class="dropdown-divider"></div>
      <div
        v-show="seeAll"
        class="row mt-4 justify-content-between row-cols-1 row-cols-md-2"
      >
        <omegaup-course-card
          v-for="course in groupedCourses.invisible"
          :key="course.alias"
          :course="course"
          :type="
            type === CourseType.Public ? CourseType.Public : CourseType.Finished
          "
        ></omegaup-course-card>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import course_FilteredList from './FilteredList.vue';
import { CourseType } from './Card.vue';
import course_Card from './Card.vue';

interface GroupedCourses {
  visible: types.FilteredCourse[];
  invisible: types.FilteredCourse[];
}

@Component({
  components: {
    'omegaup-course-filtered-list': course_FilteredList,
    'omegaup-course-card': course_Card,
  },
})
export default class CourseCardsList extends Vue {
  @Prop() courses!: types.StudentCourses;
  @Prop() type!: CourseType;

  CourseType = CourseType;
  T = T;
  searchText = '';
  seeAll = false;

  get cardTitle(): string {
    if (this.type === CourseType.Public) return T.courseListPublicCourses;
    if (this.type === CourseType.Student) return T.courseListIStudy;
    return '';
  }

  get seeAllCoursesText(): string {
    if (this.type === CourseType.Public) {
      return T.courseCardsListShowPastCourses;
    }
    return T.courseCardsListShowFinishedCourses;
  }

  get groupedCourses(): GroupedCourses {
    if (!(this.type in this.courses)) {
      return {
        visible: [],
        invisible: [],
      };
    }

    let courses: GroupedCourses = {
      visible: [],
      invisible: [],
    };

    this.courses[this.type].filteredCourses.current.courses.forEach(
      (course) => {
        if (this.searchText !== '' && !course.name.includes(this.searchText)) {
          return;
        }
        if (this.type === CourseType.Public) {
          courses.visible.push(course);
          return;
        }
        if (course.progress === 100) {
          courses.invisible.push(course);
        } else {
          courses.visible.push(course);
        }
      },
    );

    this.courses[this.type].filteredCourses.past.courses.forEach((course) => {
      if (this.searchText !== '' && !course.name.includes(this.searchText)) {
        return;
      }
      if (this.type === CourseType.Public) {
        courses.invisible.push(course);
        return;
      }
      if (course.progress === 100) {
        courses.invisible.push(course);
      } else {
        courses.visible.push(course);
      }
    });
    return courses;
  }
}
</script>
