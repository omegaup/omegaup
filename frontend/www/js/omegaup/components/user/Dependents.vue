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
          <th scope="col" class="text-center">{{ T.dependentsStatus }}</th>
        </tr>
      </thead>
      <tbody>
        <!-- Empty-state -->
        <tr v-if="!dependents || dependents.length === 0">
          <td colspan="3" class="empty-category text-center text-muted py-4">
            {{ T.dependentsEmptyState }}
          </td>
        </tr>

        <!-- Normal rows -->
        <tr v-for="(dependent, index) in dependents" v-else :key="index">
          <th scope="row" class="text-center">{{ index + 1 }}</th>
          <td class="text-center">
            <omegaup-user-username
              :classname="dependent.classname"
              :username="dependent.username"
              :linkify="true"
            ></omegaup-user-username>
          </td>
          <td class="d-block mt-1" :class="bannerColor(dependent)">
            <small
              class="font-italic font-weight-bold"
              :class="textColor(dependent)"
            >
              {{ dependentsStatusMessage(dependent) }}
            </small>
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
import user_Username from '../user/Username.vue';

@Component({
  components: {
    'omegaup-user-username': user_Username,
  },
})
export default class UserDependents extends Vue {
  @Prop() dependents!: types.UserDependent[];

  T = T;
  ui = ui;

  daysUntilVerificationDeadline(
    parentEmailVerificationDeadline: Date | null,
  ): number | null {
    if (!parentEmailVerificationDeadline) {
      return null;
    }
    const today = new Date();
    const deadline = new Date(parentEmailVerificationDeadline);
    const timeDifference = deadline.getTime() - today.getTime();
    const daysDifference = Math.ceil(timeDifference / (1000 * 3600 * 24));
    return daysDifference;
  }

  bannerColor(dependent: types.UserDependent): string {
    if (dependent.parent_verified) {
      return 'background-success';
    }
    const daysUntilVerificationDeadline = this.daysUntilVerificationDeadline(
      dependent.parent_email_verification_deadline ?? null,
    );
    if (daysUntilVerificationDeadline == null) {
      return 'background-success';
    }
    if (daysUntilVerificationDeadline > 1) {
      return 'background-warning';
    }
    if (daysUntilVerificationDeadline == 1) {
      return 'background-danger';
    }
    return 'background-secondary';
  }

  dependentsStatusMessage(dependent: types.UserDependent): string {
    if (dependent.parent_verified) {
      return T.dependentsVerified;
    }
    const daysUntilVerificationDeadline = this.daysUntilVerificationDeadline(
      dependent.parent_email_verification_deadline ?? null,
    );
    if (daysUntilVerificationDeadline == null) {
      return T.dependentsVerified;
    }
    if (daysUntilVerificationDeadline > 1) {
      return ui.formatString(T.dependentsMessage, {
        days: daysUntilVerificationDeadline,
      });
    }
    if (daysUntilVerificationDeadline == 1) {
      return T.dependentsOneDayUntilVerificationDeadline;
    }
    return T.dependentsBlockedMessage;
  }

  textColor(dependent: types.UserDependent): string {
    if (dependent.parent_verified) {
      return 'text-white';
    }
    const daysUntilVerificationDeadline = this.daysUntilVerificationDeadline(
      dependent.parent_email_verification_deadline ?? null,
    );
    if (daysUntilVerificationDeadline == null) {
      return 'text-white';
    }
    if (daysUntilVerificationDeadline > 1) {
      return 'text-black';
    }
    return 'text-white';
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.background-success {
  background-color: var(--status-success-color);
}

.background-warning {
  background-color: var(--status-warning-color);
}

.background-danger {
  background-color: var(--status-error-color);
}

.background-secondary {
  background-color: var(--status-secondary-color);
}
</style>
