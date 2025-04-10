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
            v-model="currentProblemAlias"
            class="form-control"
            required="required"
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
        <label
          v-if="users.length != 0"
          class="col-md-6 col-form-label font-weight-bold"
        >
          {{ T.wordsMessageTo }}
          <select
            v-model="currentUsername"
            class="form-control"
            :required="users"
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
            v-model="message"
            class="w-100"
            maxlength="200"
            required="required"
            :placeholder="T.arenaClarificationMaxLength"
            data-new-clarification-message
          ></textarea>
        </label>
      </div>
      <div class="form-group row">
        <div class="col-sm-10">
          <button
            type="submit"
            class="btn btn-primary"
            :disabled="!canSubmitClarification"
          >
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

@Component({
  components: {
    'omegaup-overlay-popup': omegaup_OverlayPopup,
  },
})
export default class ArenaNewClarificationPopup extends Vue {
  @Prop({ default: () => [] }) problems!: types.NavbarProblemsetProblem[];
  @Prop({ default: () => [] }) users!: types.ContestUser[];
  @Prop({ default: null }) problemAlias!: null | string;
  @Prop({ default: null }) username!: null | string;
  @Prop({ default: 'user-rank-unranked' }) currentUserClassName!: string;

  T = T;
  message: null | string = null;
  currentProblemAlias = this.problemAlias;
  currentUsername = this.username;

  get filteredUsers(): { username: string; name: string }[] {
    return this.users.map((user) => {
      return {
        username: user.username,
        name: !user.is_owner ? user.username : T.wordsPublic,
      };
    });
  }

  get ownerUsername(): null | string {
    return this.users.find((user) => user.is_owner)?.username ?? null;
  }

  get canSubmitClarification(): boolean {
    return (
      this.message != null &&
      (this.currentUsername != null || this.users.length == 0) &&
      this.currentProblemAlias != null
    );
  }

  onSubmit(): void {
    if (this.currentProblemAlias == null || this.message == null) return;
    const clarificationRequest: types.Clarification = {
      clarification_id: 0,
      author: this.currentUsername ?? '',
      author_classname: this.currentUserClassName ?? 'user-rank-unranked',
      problem_alias: this.currentProblemAlias,
      message: this.message,
      public:
        this.ownerUsername != null &&
        this.currentUsername != null &&
        this.ownerUsername == this.currentUsername,
      time: new Date(),
      receiver_classname: 'user-rank-unranked',
    };
    this.$emit('new-clarification', {
      clarification: clarificationRequest,
      clearForm: () => this.clearForm(),
    });
  }

  clearForm(): void {
    this.$emit('dismiss');
  }
}
</script>
