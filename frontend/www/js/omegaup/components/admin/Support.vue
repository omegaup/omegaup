<template>
  <div class="card">
    <div class="text-white bg-primary card-header">
      <div class="card-title h4">
        {{ T.omegaupTitleSupportDashboard }}
        <span v-if="username != null">- {{ username }}</span>
      </div>
    </div>
    <div class="card-body">
      <div class="row mb-3">
        <div class="col-md-6">
          <form class="form w-100" @submit.prevent="onSearchEmail">
            <div class="input-group">
              <input
                v-model="email"
                class="form-control"
                name="email"
                type="text"
                required="required"
                :disabled="username != null"
                :placeholder="T.email"
              />
              <div class="input-group-append">
                <button
                  class="btn btn-outline-secondary"
                  type="submit"
                  :disabled="username != null"
                >
                  {{ T.wordsSearch }}
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
      <template v-if="username != null">
        <div class="row mb-3">
          <div class="col-md">
            <form class="form w-100" @submit.prevent="onVerifyUser">
              <button
                class="btn btn-outline-secondary"
                type="button"
                :disabled="verified"
                @click.prevent="onVerifyUser"
              >
                <template v-if="verified">
                  <font-awesome-icon icon="check" :style="{ color: 'green' }" />
                  {{ T.userVerified }}
                </template>
                <template v-else>
                  {{ T.userVerify }}
                </template>
              </button>
            </form>
          </div>
          <div data-last-login class="col-md">
            <label v-if="lastLogin != null" class="font-weight-bold">
              {{
                ui.formatString(T.userLastLogin, {
                  lastLogin: time.formatDateTime(lastLogin),
                })
              }}
            </label>
            <label v-else>
              {{ T.userNeverLoggedIn }}
            </label>
          </div>
          <div data-birth-date class="col-md">
            <label v-if="birthDate != null" class="font-weight-bold">
              {{
                ui.formatString(T.userBirthDate, {
                  birthDate: time.formatDate(birthDate),
                })
              }}
            </label>
          </div>
        </div>
        <div class="row mb-3">
          <form class="form w-100" @submit.prevent="onGenerateToken">
            <div class="col-md-12">
              <div class="input-group">
                <input
                  :value="link"
                  class="form-control"
                  name="link"
                  type="text"
                  :placeholder="T.passwordGenerateTokenDesc"
                  readonly
                  @focus="$event.target.select()"
                />
                <div class="input-group-append">
                  <button
                    v-clipboard="() => link"
                    :disabled="!link"
                    class="btn btn-outline-secondary"
                    name="copy"
                    type="button"
                    data-copy-to-clipboard
                    :aria-label="T.passwordCopyToken"
                    :title="T.passwordCopyToken"
                    @click.prevent="
                      ui.success(T.passwordResetLinkCopiedToClipboard)
                    "
                  >
                    <font-awesome-icon icon="clipboard" />
                  </button>
                  <button
                    class="btn btn-outline-secondary"
                    type="button"
                    :title="T.passwordGenerateTokenDesc"
                    @click.prevent="onGenerateToken"
                  >
                    {{ T.passwordGenerateToken }}
                  </button>
                </div>
              </div>
            </div>
          </form>
        </div>
        <div class="row mb-3">
          <form class="form w-100" @submit.prevent="onUpdateEmail">
            <div class="col-md-12">
              <div class="input-group">
                <input
                  v-model="newEmail"
                  class="form-control"
                  name="new_email"
                  type="text"
                  required="required"
                  :placeholder="T.adminSupportTypeNewEmail"
                />
                <div class="input-group-append">
                  <button
                    class="btn btn-outline-secondary"
                    type="submit"
                    :title="T.adminSupportTypeNewEmail"
                  >
                    {{ T.wordsSaveChanges }}
                  </button>
                </div>
              </div>
            </div>
          </form>
        </div>
        <div class="row float-right">
          <div class="col-md-12">
            <button
              class="btn btn-secondary"
              type="reset"
              @click.prevent="onReset"
            >
              {{ T.wordsCancel }}
            </button>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Emit } from 'vue-property-decorator';
import Clipboard from 'v-clipboard';
import T from '../../lang';
import * as ui from '../../ui';
import * as time from '../../time';

import {
  FontAwesomeIcon,
  FontAwesomeLayers,
  FontAwesomeLayersText,
} from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
library.add(fas);
Vue.use(Clipboard);

export interface UpdateEmailRequest {
  email: string;
  newEmail: string;
}

@Component({
  components: {
    'font-awesome-icon': FontAwesomeIcon,
    'font-awesome-layers': FontAwesomeLayers,
    'font-awesome-layers-text': FontAwesomeLayersText,
  },
})
export default class AdminSupport extends Vue {
  @Prop() username!: string;
  @Prop() verified!: boolean;
  @Prop() link!: string;
  @Prop() lastLogin!: null | Date;
  @Prop() birthDate!: null | Date;

  T = T;
  ui = ui;
  time = time;
  email: null | string = null;
  newEmail: null | string = null;

  @Emit('search-email')
  onSearchEmail(): null | string {
    if (this.email == null) return null;
    return this.email;
  }

  @Emit('update-email')
  onUpdateEmail(): null | UpdateEmailRequest {
    if (this.email == null || this.newEmail == null) return null;
    return { email: this.email, newEmail: this.newEmail };
  }

  @Emit('verify-user')
  onVerifyUser(): null | string {
    if (this.email == null) return null;
    return this.email;
  }

  @Emit('generate-token')
  onGenerateToken(): null | string {
    if (this.email == null) return null;
    return this.email;
  }

  @Emit('reset')
  onReset() {
    this.email = null;
    this.newEmail = null;
  }
}
</script>
