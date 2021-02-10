<template>
  <omegaup-overlay-popup @dismiss="$emit('dismiss')">
    <form
      data-new-clarification
      class="d-flex flex-column h-100"
      @submit.prevent="onSubmit"
    >
      <div class="form-group row mt-5">
        <label class="col-md-6 col-form-label font-weight-bold">
          {{ T.wordsProblem }}
          <select
            v-model="newClarification.problem"
            class="form-control"
            data-new-clarification-problem
          >
            <option
              v-for="problem in problems"
              :key="problem.alias"
              :value="problem.alias"
            >
              {{ problem.text }}
            </option>
          </select>
        </label>
        <label v-if="users" class="col-md-6 col-form-label font-weight-bold">
          {{ T.wordsMessageTo }}
          <select
            v-model="newClarification.username"
            class="form-control"
            data-new-clarification-user
          >
            <option
              v-for="user in filteredUsers"
              :key="user.username"
              :value="user.username"
            >
              {{ user.name }}
            </option>
          </select>
        </label>
      </div>
      <div class="form-group row">
        <label class="col-md-12 col-form-label font-weight-bold">
          {{ T.arenaClarificationCreate }}
          <textarea
            v-model="newClarification.message"
            class="w-100"
            maxlength="200"
            required="required"
            :placeholder="T.arenaClarificationMaxLength"
          ></textarea>
        </label>
      </div>
      <div class="form-group row">
        <div class="col-sm-10">
          <button type="submit" class="btn btn-primary">
            {{ T.wordsSend }}
          </button>
        </div>
      </div>
    </form>
  </omegaup-overlay-popup>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import omegaup_OverlayPopup from '../OverlayPopup.vue';

export interface NewClarification {
  problem?: string;
  username?: string;
  message: string;
}

@Component({
  components: {
    'omegaup-overlay-popup': omegaup_OverlayPopup,
  },
})
export default class ArenaNewClarificationPopup extends Vue {
  // TODO: Change the type NavbarContestProblem with NavbarProblemsetProblem
  // when PR #5126 is merged
  @Prop({ default: () => [] }) problems!: types.NavbarContestProblem[];
  @Prop({ default: () => [] }) users!: types.ContestUser[];
  @Prop() newClarification!: NewClarification;

  T = T;

  get filteredUsers(): { username: string; name: string }[] {
    return this.users.map((user) => {
      return {
        username: user.username,
        name: !user.is_owner ? user.username : T.wordsPublic,
      };
    });
  }

  clearForm(): void {
    this.$emit('dismiss');
  }

  onSubmit(): void {
    this.$emit('new-clarification', this, this.newClarification);
  }
}
</script>
