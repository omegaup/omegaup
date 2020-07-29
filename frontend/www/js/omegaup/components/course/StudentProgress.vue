<template>
  <div>
    <tr>
      <td>
        <a v-bind:href="studentProgressUrl()" class="text-center align-middle">
          {{student.name || student.username}}
        </a>
      </td>
      <td class="score d-flex flex-column justify-content-center align-items-center" v-for="assignment in assignments">
        <p class="mb-1">{{ Math.round(score(student, assignment)) }}</p>
        <div class="d-flex justify-content-center" v-for="problem in student.progress[assignment.alias]">
          <div v:bind:class="getProblemColor(problem)"></div>
        </div>
      </td>
    </tr>
  </div>
</template>
   
<style> 
.box {
width: 20px;
height: 20px;
border: 1px solid black;
margin:  -0.5px;
}

.bg-green {
  background: green;
}

.bg-yellow {
  background: yellow;
}

.bg-red {
  background: red;
}

.bg-black {
  background: black;
}
</style>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import { types } from '../../api_types';
import T from '../../lang';

@Component
export default class StudentProgress extends Vue {
  @Prop() course!: types.CourseDetails;
  @Prop() student!: types.StudentProgress;
  @Prop() assignments!: types.CourseAssignment;

  T = T;

  score(
    student: types.StudentProgress,
    assignment: types.CourseAssignment,
  ): number {
    if (!student.progress.hasOwnProperty(assignment.alias)) {
      return 0;
    }
    let score = 0;
    for (let problem in student.progress[assignment.alias]) {
      score += student.progress[assignment.alias][problem];
    }
    return parseFloat(score.toString());
  }

  getProblemColor(
    problemScore: number
  ): String {
  
    if(problemScore > 70) {
      return "box bg-green";
    }
    else if(problemScore >= 50) {
      return "box bg-yellow";
    }
    else if(problemScore > 0) {
      return "box bg-red";
    }
    else {
      return "box bg-black";
    }
  }

  studentProgressUrl(): string {
    return `/course/${this.course.alias}/student/${this.student.name}/`;
  }

}
</script>
