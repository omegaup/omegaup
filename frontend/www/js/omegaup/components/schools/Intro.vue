<template>
  <div class="omegaup-schools-intro">
        <div class="page-header header-bg">
          <h1>{{ T.schoolsLearnProgBySolvProblems }}</h1>
          <p><a href="/course/" class="btn btn-primary" role="button">{{ T.schoolsStartUsingSchools }}</a> </p>
        </div>
        <div class="row">
          <div class="col-sm-6 col-md-4">
            <div class="thumbnail">
              <img src="/media/u30.png">
              <div class="caption">
                <h3>{{ T.schoolsCreateYourCourse }}</h3>
                <p>{{ T.schoolsCreateCoursesThatMeetNeeds }}</p>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-md-4">
            <div class="thumbnail">
              <img src="/media/u35.png">
              <div class="caption">
                <h3>{{ T.schoolsManageProgressOfStudents }}</h3>
                <p>{{ T.schoolsCreateCoursesThatMeetNeeds }}</p>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-md-4">
            <div class="thumbnail">
              <img src="/media/u30.png">
              <div class="caption">
                <h3>{{ T.schoolsCreateYourProblems }}</h3>
                <p>{{ T.schoolsCreateCoursesThatMeetNeeds }}</p>
              </div>
            </div>
          </div>
        </div>
    </div>
</template>

<script>
export default {
  props: {
    T: Object,
  },
  data: function() {
    return {
    };
  },
};
</script>
