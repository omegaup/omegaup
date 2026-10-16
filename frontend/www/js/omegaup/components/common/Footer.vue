<template>
  <footer class="common-footer text-center mt-5">
    <div class="container-xl">
      <div class="footer-navigation d-lg-flex align-items-start py-5 m-auto">
        <div class="footer-brand mb-4 mb-lg-0">
          <a class="footer-logo d-inline-flex align-items-center" href="/">
            <span class="footer-logo-wordmark" :aria-label="T.frontPageFooterLogoAlt"
              ><span class="footer-logo-omega">omega</span
              ><span class="footer-logo-up">Up</span></span
          ></a>
          <div class="slogan">
            {{ T.frontPageFooter }}
          </div>
          <div class="footer-sponsor">
            <h4 class="column-title">{{ T.frontPageFooterSponsors }}</h4>
            <a
              href="https://news.airbnb.com/2025-community-fund/"
              target="_blank"
            >
              <img
                class="sponsor-logo"
                src="/media/homepage/airbnb_logo.svg"
                alt="AirbnbLogo"
                width="100"
              />
            </a>
          </div>
        </div>
        <div class="footer-list-section footer-site w-50 mb-4 mb-lg-0 mx-auto">
          <h4 class="column-title">{{ T.frontPageFooterSite }}</h4>
          <ul>
            <li class="mt-1">
              <a href="/arena/">{{ T.navContests }}</a>
            </li>
            <li class="mt-1">
              <a href="/problem/">{{ T.navProblems }}</a>
            </li>
            <li class="mt-1">
              <a href="/rank/">{{ T.navRanking }}</a>
            </li>
            <li class="mt-1">
              <a href="/course/">{{ T.navCourses }} </a>
            </li>
            <li class="mt-1">
              <a :href="OmegaUpBlogURL" target="_blank">{{ T.navBlog }}</a>
            </li>
          </ul>
        </div>
        <div
          class="footer-list-section footer-organization d-inline-block w-50 mb-4"
        >
          <h4 class="column-title">{{ T.frontPageFooterOrganization }}</h4>
          <ul>
            <li class="mt-1">
              <a href="https://omegaup.org/#about" target="_blank">{{
                T.frontPageFooterAboutUs
              }}</a>
            </li>
            <li class="mt-1">
              <a href="https://omegaup.org/#team" target="_blank">{{
                T.frontPageFooterTeam
              }}</a>
            </li>
          </ul>
        </div>
        <div
          class="footer-list-section footer-developers d-inline-block w-50 mb-4"
        >
          <h4 class="column-title">{{ T.frontPageDevelopers }}</h4>
          <ul>
            <li class="mt-1">
              <a
                href="https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/Development-Environment-Setup-Process.md"
                target="_blank"
                >{{ T.frontPageFooterHelpUs }}</a
              >
            </li>
            <li class="mt-1">
              <a href="https://github.com/omegaup/omegaup" target="_blank">
                <font-awesome-icon :icon="['fab', 'github']" />
                Github
              </a>
            </li>
            <li class="mt-1">
              <a
                v-if="!omegaUpLockDown && isLoggedIn"
                href="https://github.com/omegaup/omegaup/issues/new"
                target="_blank"
                rel="nofollow"
                @click="$event.target.href = reportAnIssueURL()"
                >{{ T.reportAnIssue }}</a
              >
            </li>
          </ul>
        </div>
        <div
          class="footer-list-section footer-contact w-50 mb-4 mb-lg-0 mx-auto"
        >
          <h4 class="column-title">{{ T.frontPageFooterContact }}</h4>
          <ul>
            <li class="mt-1">
              <a href="mailto:hello@omegaup.com">hello@omegaup.com</a>
            </li>
            <li class="mt-1">
              <a href="https://www.facebook.com/omegaup/" target="_blank">
                <font-awesome-icon :icon="['fab', 'facebook']" />
                Facebook
              </a>
            </li>
            <li class="mt-1">
              <a href="https://discord.gg/K3JFd9d3wk" target="_blank">
                <font-awesome-icon :icon="['fab', 'discord']" />
                Discord
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>
    <div class="copy mt-3">
      <div
        class="container-xl d-md-flex justify-content-between align-items-center py-3"
      >
        <ul
          class="mb-2 m-md-0 list-unstyled d-flex justify-content-around d-md-inline-flex order-md-12"
        >
          <li class="pr-2">
            <a :href="CodeofConductPolicyURL" target="_blank">
              {{ T.frontPageFooterCodeConduct }}
            </a>
          </li>
          <li class="footer-legal-separator" aria-hidden="true">•</li>
          <li>
            <a :href="PrivacyPolicyURL" target="_blank">
              {{ T.frontPageFooterPrivacyPolicy }}
            </a>
          </li>
        </ul>
        <div>
          {{
            ui.formatString(T.frontPageFooterCopyright, {
              currentYear: new Date().getFullYear(),
            })
          }}
        </div>
      </div>
    </div>
  </footer>
</template>

<script lang="ts">
import { Component, Vue, Prop } from 'vue-property-decorator';
import * as ui from '../../ui';
import T from '../../lang';
import { reportAnIssueURL } from '../../errors';
import { getBlogUrl } from '../../urlHelper';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
  faFacebook,
  faGithub,
  faDiscord,
} from '@fortawesome/free-brands-svg-icons';
library.add(faFacebook, faGithub, faDiscord);

@Component({
  components: {
    FontAwesomeIcon,
  },
})
export default class Footer extends Vue {
  @Prop() isLoggedIn!: boolean;
  @Prop() omegaUpLockDown!: boolean;

  T = T;
  ui = ui;
  reportAnIssueURL = reportAnIssueURL;

  get OmegaUpBlogURL(): string {
    // Use the key defined in blog-links-config.json
    return getBlogUrl('OmegaUpBlogURL');
  }

  get PrivacyPolicyURL(): string {
    return getBlogUrl('PrivacyPolicyURL');
  }

  get CodeofConductPolicyURL(): string {
    return getBlogUrl('CodeofConductPolicyURL');
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

@media (min-width: 1000px) {
  .slogan {
    max-width: 20rem;
  }
}

.column-title {
  margin-bottom: 12px;
  font-size: 16px;
  letter-spacing: 0;
  font-weight: 700;
}

.common-footer {
  display: block;
  width: 100%;
  margin-top: 32px !important;
  background-color: var(--footer-primary-color);
  border-top: 0.8px solid var(--footer-border-color);
  color: rgb(255, 255, 255);
  font-family:
    -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Inter, sans-serif;
  font-size: 16px;
  line-height: 24px;

  > .container-xl {
    max-width: 80rem;
    margin-right: auto;
    margin-left: auto;
  }

  .footer-navigation {
    display: grid !important;
    grid-template-columns: 1fr;
    gap: 32px;
    align-items: start;
    text-align: left;

    padding-top: clamp(40px, 5vw, 56px) !important;
    padding-right: clamp(20px, 3vw, 40px) !important;
    padding-bottom: clamp(32px, 4vw, 48px) !important;
    padding-left: clamp(20px, 3vw, 40px) !important;
  }

  .footer-navigation {
    .footer-brand {
      max-width: none;
      width: 100%;
      order: 0;
      grid-column: auto;
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      text-align: left;

      @media only screen and (max-width: 991px) {
        max-width: 100%;
      }

      .footer-logo {
        color: rgb(255, 255, 255);
        text-decoration: none;
      }

      .footer-logo-wordmark {
        display: block;
        font-size: 28px;
        font-weight: 700;
        letter-spacing: -0.06em;
        line-height: 1;
      }

      .footer-logo-omega {
        color: rgb(0, 0, 0);
      }

      .footer-logo-up {
        color: rgb(255, 255, 255);
      }

      .slogan {
        margin-top: 20px;
        margin-right: 0;
        margin-left: 0;
        max-width: 20rem;
        text-align: left;
        align-self: flex-start;
        font-size: 15px;
        font-weight: 600;
        letter-spacing: 0.03em;
        text-transform: uppercase;
      }

      .footer-sponsor {
        margin-top: 38px;

        .column-title {
          margin-bottom: 12px;
        }

        .sponsor-logo {
          width: 120px;
        }
      }
    }

    .footer-list-section {
      width: 100% !important;
      margin-right: 0 !important;
      margin-left: 0 !important;

      @media only screen and (min-width: 992px) {
        display: block;
      }

      ul {
        list-style-type: none;
        padding: 0;
        margin: 0;
        text-align: left;

        li {
          margin-top: 8px;
          padding: 0;

          a {
            text-decoration: none;
            color: rgb(255, 255, 255);

            &:hover {
              color: var(--footer-link-hover-color);
            }
          }
        }
      }

    }
  }

  a {
    text-decoration: none;
    color: rgb(255, 255, 255);

    &:hover {
      color: var(--footer-link-hover-color);
    }
  }

  .copy {
    background-color: var(--footer-primary-color);
    border-top: 0.8px solid var(--footer-divider-color);
    text-align: left;
    color: rgba(191, 219, 254, 0.8);

    .container-xl {
      max-width: 80rem;
      padding-right: clamp(20px, 3vw, 40px) !important;
      padding-left: clamp(20px, 3vw, 40px) !important;
    }

    a {
      color: rgba(191, 219, 254, 0.8);
    }

    .footer-legal-separator {
      padding-right: 8px;
      color: rgba(191, 219, 254, 0.8);
    }
  }
}

@media only screen and (min-width: 768px) {
  .common-footer .footer-navigation {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media only screen and (min-width: 992px) {
  .common-footer .footer-navigation {
    grid-template-columns: repeat(6, minmax(0, 1fr));
    column-gap: 40px;
    row-gap: 32px;

    .footer-brand {
      grid-column: span 2;
    }
  }
}

@media only screen and (max-width: 991px) {
  .common-footer {
    .footer-navigation {
      text-align: center;
    }

    .footer-brand {
      margin-right: auto;
      margin-left: auto;
      align-items: flex-start;
      text-align: left;
    }

    .footer-list-section {
      ul {
        text-align: center;
      }
    }

    .copy {
      text-align: center;
    }
  }
}
</style>
