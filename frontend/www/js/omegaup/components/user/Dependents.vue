<template>
  <div class="card">
    <h5
      class="card-header d-flex justify-content-between align-items-center rank-title"
    >
      {{ T.dependentsTitle }}
    </h5>
    <table class="table mb-0">
      <thead>
        <tr>
          <th scope="col" class="text-center">#</th>
          <th scope="col" class="text-center">{{ T.dependentsUser }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(dependent, index) in dependents" :key="index">
          <th scope="row" class="text-center">{{ index + 1 }}</th>
          <td class="text-center" :class="bannerColor">
            {{ dependent.name }}
            <br />
            <span v-if="userVerificationDeadline" class="span-alert">
              {{
                daysUntilVerificationDeadline > 1
                  ? ui.formatString(T.dependentsMessage, {
                      days: daysUntilVerificationDeadline,
                    })
                  : T.dependentsRedMessage
              }}
            </span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import * as ui from '../../ui';
import { types } from '../../api_types';

@Component
export default class UserDependents extends Vue {
  @Prop() dependents!: types.UserDependentsPayload[];
  @Prop() userVerificationDeadline!: Date | null;

  T = T;
  ui = ui;

  get bannerColor() {
    const today = new Date();
    const deadline = new Date(this.userVerificationDeadline as Date);
    if (deadline.toDateString() === today.toDateString()) {
      return 'bg-danger';
    } else {
      return 'bg-warning';
    }
  }

  get daysUntilVerificationDeadline(): number | null {
    if (this.userVerificationDeadline) {
      const today = new Date();
      const deadline = new Date(this.userVerificationDeadline);
      const timeDifference = deadline.getTime() - today.getTime();
      const daysDifference = Math.ceil(timeDifference / (1000 * 3600 * 24));
      return daysDifference;
    } else {
      return null;
    }
  }
}
</script>

<style lang="scss">
@import '../../../../sass/main.scss';

.span-alert {
  font-size: 0.9rem;
  font-style: italic;
}
</style>
