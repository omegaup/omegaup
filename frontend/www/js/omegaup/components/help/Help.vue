<template>
  <div class="col-md-10 mx-auto mt-5">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">{{ T.helpAndSupport }}</h3>
      </div>
      <div class="card-body">
        <p class="card-text mb-4">{{ T.helpAndSupportDescription }}</p>
        <div class="row">
          <div class="col-md-4 mb-4" v-for="item in helpItems" :key="item.title">
            <div class="card h-100">
              <div class="card-body">
                <h5 class="card-title">{{ item.title }}</h5>
                <p class="card-text">{{ item.description }}</p>
                <a :href="item.link" class="btn btn-primary" target="_blank">{{ T.helpVisit }}</a>
              </div>
            </div>
          </div>
        </div>
        <hr class="my-4" />
        <h4 class="mb-3">{{ T.helpAdditionalResources }}</h4>
         <!-- Additional content can go here -->
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import T from '../../lang';

@Component
export default class Help extends Vue {
  T = T;

  get helpItems() {
    return [
      {
        title: T.helpVideoTutorials,
        description: T.helpVideoTutorialsDesc,
        link: 'https://www.youtube.com/user/omegaUp',
      },
      {
        title: T.helpDiscord,
        description: T.helpDiscordDesc,
        link: 'https://discord.gg/omegaUp',
      },
      {
        title: T.helpBlog,
        description: T.helpBlogDesc,
        link: 'https://blog.omegaup.com/',
      },
      {
        title: T.helpAlgorithmsBook,
        description: T.helpAlgorithmsBookDesc,
        link: 'https://libros.omegaup.com/',
      },
      {
        title: T.helpDocumentation,
        description: T.helpDocumentationDesc,
        link: 'https://github.com/omegaup/omegaup/wiki', // Or internal docs
      },
        {
        title: T.helpGithub,
        description: T.helpGithubDesc,
        link: 'https://github.com/omegaup/omegaup',
      },
    ];
  }
}
</script>
