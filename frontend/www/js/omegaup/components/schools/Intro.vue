<template>
  <div class="omegaup-schools-intro">
    <div class="page-header header-bg">
      <h1>{{ T.schoolsLearnProgBySolvProblems }}</h1>
      <p><a class="btn btn-primary"
         href="/course/"
         role="button">{{ T.schoolsStartUsingSchools }}</a></p>
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
  data: function() { return {};},
};
</script>

<style>
.header-bg {
    background-image: url(/media/teacher3.jpg);
    background-position-x: -200px;
    background-position-y: -270px;
    color: white;
    height: 250px;
    padding: 10px;
    text-align: center;
}

.header-bg h1 {
    font-size: 40px ;
    margin-top: 80px;
    text-shadow: 1px 1px #678DD7;
}
body {
    background: #fff;
}
</style>
