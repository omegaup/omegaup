<template>
  <div class="d-flex justify-content-center">
    <ul class="pagination m-0">
      <li
        v-for="(page, index) in pagerItems"
        :key="index"
        class="page-item"
        :class="page.class"
      >
        <a
          v-if="page.url"
          class="page-link"
          :href="page.url"
          :class="{ disabled: page.class === 'active' }"
          :aria-disabled="page.class === 'active'"
        >
          {{ page.label }}
        </a>

        <a
          v-else
          class="page-link"
          :class="{ disabled: page.class === 'active' }"
          :aria-disabled="page.class === 'active'"
          @click.prevent="$emit('page-changed', page.page)"
        >
          {{ page.label }}
        </a>
      </li>
    </ul>
  </div>
</template>
