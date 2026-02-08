<template>
  <div class="row">
    <div class="col-md-12 no-right-padding">
      <div class="card mb-5">
        <div class="card-header">
          <h2 class="card-title">{{ T.profileSolvedProblems }}</h2>
        </div>
        <table
          v-for="(problems, user) in groupedSolvedProblems"
          class="table table-striped"
        >
          <thead>
            <tr>
              <th :colspan="NUM_COLUMNS">{{ user }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="groups in problems">
              <td v-for="problem in groups">
                <a :href="`/arena/problem/${problem.alias}/`">{{
                  problem.title
                }}</a>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-show="!solvedProblems">
          <img src="/media/wait.gif" alt="Loading" />
        </div>
      </div>
      <div class="card">
        <div class="card-header">
          <h2 class="card-title">{{ T.profileUnsolvedProblems }}</h2>
        </div>
        <table
          v-for="(problems, user) in groupedUnsolvedProblems"
          class="table table-striped"
        >
          <thead>
            <tr>
              <th :colspan="NUM_COLUMNS">{{ user }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="groups in problems">
              <td v-for="problem in groups">
                <a :href="`/arena/problem/${problem.alias}/`">{{
                  problem.title
                }}</a>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-show="!unsolvedProblems">
          <img src="/media/wait.gif" alt="Loading" />
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';

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
  readonly NUM_COLUMNS = 3;

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
      for (let i = 0; i < elements[user].length; i += this.NUM_COLUMNS) {
        groups[user].push(elements[user].slice(i, i + this.NUM_COLUMNS));
      }
    }
    return groups;
  }
}
</script>
