<template>
  <div class="row">
    <div class="col-md-12 no-right-padding">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title">{{ T.profileSolvedProblems }}</h2>
        </div>
        <table class="table table-striped"
               v-for="(problems, user) in groupedSolvedProblems">
          <thead>
            <tr>
              <th v-bind:colspan="NUM_COLUMNS">{{ user }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="groups in problems">
              <td v-for="problem in groups">
                <a v-bind:href="`/arena/problem/${problem.alias}/`">{{ problem.title }}</a>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-show="!solvedProblems"><img src="/media/wait.gif"></div>
      </div>
      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title">{{ T.profileUnsolvedProblems }}</h2>
        </div>
        <table class="table table-striped"
               v-for="(problems, user) in groupedUnsolvedProblems">
          <thead>
            <tr>
              <th v-bind:colspan="NUM_COLUMNS">{{ user }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="groups in problems">
              <td v-for="problem in groups">
                <a v-bind:href="`/arena/problem/${problem.alias}/`">{{ problem.title }}</a>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-show="!unsolvedProblems"><img src="/media/wait.gif"></div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import omegaup from '../../api.js';

interface CourseProblems {
  [user: string]: omegaup.Problem[];
}

interface GroupedCourseProblems {
  [user: string]: omegaup.Problem[][];
}

@Component
export default class ActivitySubmissionsList extends Vue {
  @Prop() solvedProblems!: CourseProblems;
  @Prop() unsolvedProblems!: CourseProblems;

  T = T;
  static readonly NUM_COLUMNS = 3;

  get groupedSolvedProblems(): GroupedCourseProblems {
    return this.groupElements(this.solvedProblems);
  }

  get groupedUnsolvedProblems(): GroupedCourseProblems {
    return this.groupElements(this.unsolvedProblems);
  }

  groupElements(elements: CourseProblems): GroupedCourseProblems {
    let groups: GroupedCourseProblems = {};
    for (let user in elements) {
      groups[user] = [];
      for (
        let i = 0;
        i < elements[user].length;
        i += ActivitySubmissionsList.NUM_COLUMNS
      ) {
        groups[user].push(
          elements[user].slice(i, i + ActivitySubmissionsList.NUM_COLUMNS),
        );
      }
    }
    return groups;
  }
}

</script>
