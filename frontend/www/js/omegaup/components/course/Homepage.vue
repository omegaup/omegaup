<template>
  <div data-homepage-course>
    <div class="row p-4 text-center justify-content-around align-items-center">
      <h2 class="col-12 mb-5 font-italic">{{ T.courseHomepageTitle }}</h2>
      <a
        class="col-xs-6 col-sm-3 btn btn-primary btn-lg mx-auto custom-button"
        role="button"
        href="/course/"
        >{{ T.wordsGetToKnowOurCourses }}</a
      >
    </div>
    <div class="dropdown-divider"></div>
    <div class="container-lg">
      <div
        class="row p-4 text-center text-md-left justify-content-around align-items-center"
      >
        <div class="p-3 col-md-5 mt-2 mt-md-0">
          <img
            class="img-fluid"
            src="/media/course_homepage/teachers_section.svg"
            :alt="T.courseHomepageTeachersSectionAlt"
          />
        </div>
        <div class="col-md-6 mt-2 mt-md-0">
          <omegaup-markdown
            :markdown="T.courseHomepageTeachersSectionContent"
          ></omegaup-markdown>
        </div>
      </div>
      <div
        class="row p-4 text-center text-md-left justify-content-around align-items-center"
      >
        <div class="p-3 col-md-5 order-md-2 mt-2 mt-md-0">
          <img
            class="img-fluid"
            src="/media/course_homepage/students_section.svg"
            :alt="T.courseHomepageStudentsSectionAlt"
          />
        </div>
        <div class="col-md-6 mt-2 mt-md-0">
          <omegaup-markdown
            :markdown="T.courseHomepageStudentsSectionContent"
          ></omegaup-markdown>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import T from '../../lang';
import omegaup_Markdown from '../Markdown.vue';

@Component({
  components: {
    'omegaup-markdown': omegaup_Markdown,
  },
})
export default class Homepage extends Vue {
  T = T;
}
</script>

<style lang="scss">
@import '../../../../sass/main.scss';

a.custom-button {
  color: $omegaup-white;
  letter-spacing: 0.016rem;
  font-size: 1.1rem;
}

[data-homepage-course] [data-markdown-statement] {
  p {
    margin-bottom: 0.88em;
    margin-left: 1em;
    font-size: 1.2rem;
    text-align: center;
    letter-spacing: 0.016rem;
  }
  ul,
  li {
    margin-bottom: 0.6rem;
  }
}
</style>
