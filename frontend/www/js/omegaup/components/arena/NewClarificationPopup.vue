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
            v-model="problemAlias"
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
        <label v-if="users" class="col-md-6 col-form-label font-weight-bold">
          {{ T.wordsMessageTo }}
          <select
            v-model="username"
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
          ></textarea>
        </label>
      </div>
      <div class="form-group row">
        <div class="col-sm-10">
          <button
            type="submit"
            class="btn btn-primary"
            :disbaled="!shouldSubmitClarification"
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

  T = T;
  message: null | string = null;

  get filteredUsers(): { username: string; name: string }[] {
    return this.users.map((user) => {
      return {
        username: user.username,
        name: !user.is_owner ? user.username : T.wordsPublic,
      };
    });
  }

  get ownerUsername(): null | string {
    if (this.users == null) return null;
    return this.users.find((user) => user.is_owner)?.username ?? null;
  }

  get shouldSubmitClarification(): boolean {
    return (
      this.message != null &&
      (this.username != null || this.users.length == 0) &&
      this.problemAlias != null
    );
  }

  onSubmit(): void {
    if (this.problemAlias == null || this.message == null) return;
    const clarificationRequest: types.Clarification = {
      clarification_id: 0,
      author:
        this.users != null && this.username != null ? this.username : undefined,
      problem_alias: this.problemAlias,
      message: this.message,
      public:
        this.ownerUsername != null &&
        this.username != null &&
        this.ownerUsername == this.username,
      time: new Date(),
    };
    this.$emit('new-clarification', clarificationRequest);
  }
}
</script>
