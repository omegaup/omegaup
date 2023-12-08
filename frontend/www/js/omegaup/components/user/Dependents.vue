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
          <td class="text-center">
            {{ dependent.name }}
            <br />
            <span
              v-if="userVerificationDeadline"
              class="font-italic d-block p-1 mt-1 text-light"
              :class="bannerColor"
              ><small>
                {{ dependentsStatusMessage }}
              </small></span
            >
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

  get daysUntilVerificationDeadline(): number | null {
    if (!this.userVerificationDeadline) {
      return null;
    }
    const today = new Date();
    const deadline = new Date(this.userVerificationDeadline);
    const timeDifference = deadline.getTime() - today.getTime();
    const daysDifference = Math.ceil(timeDifference / (1000 * 3600 * 24));
    return daysDifference;
  }

  get bannerColor(): string {
    if (this.daysUntilVerificationDeadline !== null) {
      if (this.daysUntilVerificationDeadline > 7) {
        return 'bg-secondary';
      }
      if (this.daysUntilVerificationDeadline <= 1) {
        return 'bg-danger';
      }
    }
    return 'bg-warning';
  }

  get dependentsStatusMessage(): string {
    if (this.daysUntilVerificationDeadline !== null) {
      if (this.daysUntilVerificationDeadline > 7) {
        return ui.formatString(T.dependentsBlockedMessage, {
          days: this.daysUntilVerificationDeadline,
        });
      }
      if (
        this.daysUntilVerificationDeadline > 1 &&
        this.daysUntilVerificationDeadline <= 7
      ) {
        return ui.formatString(T.dependentsMessage, {
          days: this.daysUntilVerificationDeadline,
        });
      }
    }
    return T.dependentsRedMessage;
  }
}
</script>
