<template>
  <div data-follow-list>
    <p
      v-if="!users.length"
      class="text-muted text-center my-4"
      data-follow-list-empty
    >
      {{ emptyMessage }}
    </p>
    <template v-else>
      <ul class="list-unstyled mb-3">
        <li
          v-for="user in users"
          :key="user.username"
          class="mb-2"
          data-follow-list-item
        >
          <a
            class="follow-list-row d-flex align-items-center bg-white rounded px-3 py-2"
            :href="`/profile/${user.username}/`"
          >
            <img
              class="follow-list-avatar rounded-circle mr-3"
              :src="user.gravatar_32"
              alt=""
              data-follow-list-avatar
            />
            <span>
              <omegaup-user-username
                :classname="user.classname"
                :username="user.username"
              ></omegaup-user-username>
              <small v-if="user.name" class="d-block text-muted">{{
                user.name
              }}</small>
            </span>
          </a>
        </li>
      </ul>
      <omegaup-common-paginator
        v-if="totalPages > 1"
        :pager-items="pagerItems"
        @page-changed="(page) => $emit('page-changed', page)"
      ></omegaup-common-paginator>
    </template>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import common_Paginator from '../common/Paginator.vue';
import user_Username from './Username.vue';

export interface FollowListState {
  users: types.FollowUser[];
  totalRows: number;
  page: number;
}

@Component({
  components: {
    'omegaup-common-paginator': common_Paginator,
    'omegaup-user-username': user_Username,
  },
})
export default class UserFollowList extends Vue {
  @Prop({ default: () => [] }) users!: types.FollowUser[];
  @Prop({ default: 0 }) totalRows!: number;
  @Prop({ default: 1 }) page!: number;
  @Prop({ default: 20 }) pageSize!: number;
  @Prop({ default: '' }) emptyMessage!: string;

  T = T;

  get totalPages(): number {
    return Math.ceil(this.totalRows / this.pageSize);
  }

  // Mirrors \OmegaUp\Pager::paginate, which the server-side paginated lists
  // use, so this pager looks and behaves like every other one on the site.
  get pagerItems(): types.PageItem[] {
    const adjacent = 2;
    const pages = this.totalPages;
    const current = this.page < 1 || this.page > pages ? 1 : this.page;
    const items: types.PageItem[] = [];

    items.push({
      label: '«',
      class: current > 1 ? '' : 'disabled',
      page: current > 1 ? current - 1 : 0,
    });

    if (current > adjacent + 1) {
      items.push({ label: '1', class: '', page: 1 });
      items.push({ label: '...', class: 'disabled', page: 0 });
    }

    for (
      let i = Math.max(1, current - adjacent);
      i <= Math.min(pages, current + adjacent);
      i++
    ) {
      items.push({
        label: String(i),
        class: i === current ? 'active' : '',
        page: i,
      });
    }

    if (current + adjacent < pages) {
      items.push({ label: '...', class: 'disabled', page: 0 });
      items.push({ label: String(pages), class: '', page: pages });
    }

    items.push({
      label: '»',
      class: current < pages ? '' : 'disabled',
      page: current < pages ? current + 1 : 0,
    });

    return items;
  }
}
</script>

<style scoped>
.follow-list-row {
  color: inherit;
  text-decoration: none;
  transition: box-shadow 0.15s ease-in-out;
}

.follow-list-row:hover {
  text-decoration: none;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.follow-list-avatar {
  width: 2.5rem;
  height: 2.5rem;
  flex-shrink: 0;
}
</style>
