<template>
  <div class="omegaup-admin-support panel-primary panel">
    <div class="panel-heading">
      <h2 class="panel-title">
        {{ T.omegaupTitleSupportDashboard }}
        <span v-if="username != null">- {{ username }}</span>
      </h2>
    </div>
    <div class="panel-body">
      <div class="row">
        <form class="form" @submit.prevent="onSearchEmail">
          <div class="col-md-4">
            <div class="input-group">
              <input
                v-model="email"
                class="form-control"
                name="email"
                type="text"
                :disabled="username != null"
                :placeholder="T.email"
              />
              <span class="input-group-btn"
                ><button
                  class="btn btn-default"
                  type="button"
                  :disabled="username != null"
                  @click.prevent="onSearchEmail"
                >
                  {{ T.wordsSearch }}
                </button></span
              >
            </div>
          </div>
        </form>
        <form
          v-show="username != null"
          class="form"
          @submit.prevent="onVerifyUser"
        >
          <div class="col-md-4 bottom-margin">
            <button
              class="btn btn-default btn-block"
              type="button"
              :disabled="verified"
              @click.prevent="onVerifyUser"
            >
              <template v-if="verified">
                <span aria-hidden="true" class="glyphicon glyphicon-ok"></span>
                {{ T.userVerified }}
              </template>
              <template v-else>
                {{ T.userVerify }}
              </template>
            </button>
          </div>
          <div v-show="username != null" class="col-md-4 bottom-margin">
            <label>
              <template v-if="lastLogin != null">
                {{
                  ui.formatString(T.userLastLogin, {
                    lastLogin: lastLogin.toLocaleString(T.locale),
                  })
                }}
              </template>
              <template v-else>
                {{ T.userNeverLoggedIn }}
              </template></label
            >
          </div>
        </form>
      </div>
      <div class="row bottom-margin">
        <form
          v-show="username != null"
          class="form bottom-margin"
          @submit.prevent="onGenerateToken"
        >
          <div class="col-md-12">
            <div class="input-group bottom-margin">
              <input
                v-model="link"
                class="form-control"
                name="link"
                type="text"
                :placeholder="T.passwordGenerateTokenDesc"
              />
              <span class="input-group-btn"
                ><button
                  class="btn btn-default"
                  name="copy"
                  type="button"
                  :aria-label="T.passwordCopyToken"
                  :disabled="link == ''"
                  :title="T.passwordCopyToken"
                  @click.prevent="onCopyToken"
                >
                  <span
                    aria-hidden="true"
                    class="glyphicon glyphicon-copy"
                  ></span>
                </button>
                <button
                  class="btn btn-default"
                  type="button"
                  :title="T.passwordGenerateTokenDesc"
                  @click.prevent="onGenerateToken"
                >
                  {{ T.passwordGenerateToken }}
                </button></span
              >
            </div>
            <div class="text-right">
              <button
                class="btn btn-primary submit"
                type="reset"
                @click.prevent="onReset"
              >
                {{ T.wordsCancel }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Emit } from 'vue-property-decorator';
import T from '../../lang';
import * as ui from '../../ui';

@Component
export default class AdminSupport extends Vue {
  @Prop() username!: string;
  @Prop() verified!: boolean;
  @Prop() link!: string;
  @Prop() lastLogin!: Date;

  T = T;
  ui = ui;
  email: string = '';

  @Emit('search-email')
  onSearchEmail(): string {
    return this.email;
  }

  @Emit('verify-user')
  onVerifyUser(): string {
    return this.email;
  }

  @Emit('generate-token')
  onGenerateToken(): string {
    return this.email;
  }

  onCopyToken(): void {
    const copyText = this.$el.querySelector(
      'input[name=link]',
    ) as HTMLInputElement;
    copyText.select();
    document.execCommand('copy');
    this.$emit('copy-token');
  }

  onReset(): void {
    this.$emit('reset');
  }
}
</script>
