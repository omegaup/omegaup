<template>
  <div class="common-footer">
    <div class="footer-navigation">
      <div class="footer-brand">
        <img class="footer-logo" src="/media/logo-main-white.svg" />
        <div class="slogan">
          {{ T.frontPageFooter }}
        </div>
      </div>
      <div class="footer-list-section">
        <h4 class="section-title">{{ T.frontPageFooterSite }}</h4>
        <ul>
          <li>
            <a href="/arena/">{{ T.navContests }}</a>
          </li>
          <li>
            <a href="/course/">{{ T.navCourses }} </a>
          </li>
          <li>
            <a href="/problem/">{{ T.navProblems }}</a>
          </li>
          <li>
            <a href="/rank/">{{ T.navRanking }}</a>
          </li>
          <li>
            <a href="https://blog.omegaup.com">{{ T.navBlog }}</a>
          </li>
        </ul>
      </div>
      <div class="footer-list-section">
        <h4 class="section-title">{{ T.frontPageFooterOrganization }}</h4>
        <ul>
          <li>
            <a href="https://omegaup.org/#about">{{
              T.frontPageFooterAboutUs
            }}</a>
          </li>
          <li>
            <a href="https://omegaup.org/#team">{{ T.frontPageFooterTeam }}</a>
          </li>
        </ul>
      </div>
      <div class="footer-list-section">
        <h4 class="section-title">{{ T.frontPageDevelopers }}</h4>
        <ul>
          <li>
            <a
              href="https://github.com/omegaup/omegaup/wiki/C%C3%B3mo-empezar-a-desarrollar"
              >{{ T.frontPageFooterHelpUs }}</a
            >
          </li>
          <li>
            <a href="https://github.com/omegaup/omegaup">GitHub</a>
          </li>
          <li>
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
      <div class="footer-list-section">
        <h4 class="section-title">{{ T.frontPageFooterContact }}</h4>
        <ul>
          <li>
            <a href="mailto:hello@omegaup.com">hello@omegaup.com</a>
          </li>
        </ul>
        <div class="social-icons">
          <a href="https://github.com/omegaup/omegaup/">GitHub</a> |
          <a href="https://www.facebook.com/omegaup/">Facebook</a>
        </div>
      </div>
    </div>
    <div class="copy">
      <div class="copy-content">
        <div class="">
          {{ T.wordsCopyright }}
        </div>
        <div class="menu">
          <ul>
            <li>
              <a
                href="https://blog.omegaup.com/codigo-de-conducta-en-omegaup/"
                >{{ T.frontPageFooterCodeConduct }}</a
              >
            </li>
            <li>
              <a href="https://blog.omegaup.com/privacy-policy/">{{
                T.frontPageFooterPrivacyPolicy
              }}</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Component, Vue, Prop } from 'vue-property-decorator';
import T from '../../lang';
import { reportAnIssueURL } from '../../errors';

@Component
export default class Footer extends Vue {
  @Prop() isLoggedIn!: boolean;
  @Prop() omegaUpLockDown!: boolean;

  T = T;
  reportAnIssueURL = reportAnIssueURL;
}
</script>

<style lang="scss">
@import '../../../../sass/main.scss';

.common-footer {
  background-color: $omegaup-primary--darker;
  color: $omegaup-white;
  font-weight: 500;

  .footer-navigation {
    width: 80%;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    padding-top: 20px;
    padding-bottom: 20px;

    @media only screen and (max-width: 767px) {
      display: table;
    }
    .footer-brand {
      max-width: 200px;

      @media only screen and (max-width: 767px) {
        max-width: 100%;
        margin-bottom: 40px;
      }

      .footer-logo {
        width: 120px;
        display: block;
        margin: 0 auto;
        margin-top: -40px;
      }

      .slogan {
        margin-top: -40px;
        text-align: center;
        text-transform: uppercase;
      }
    }

    .footer-list-section {
      @media only screen and (max-width: 767px) {
        width: 50%;
        display: inline-grid;
        margin-bottom: 20px;
      }

      .section-title {
        margin: 0 auto;
        font-weight: 700;
        text-align: center;
      }

      ul {
        list-style-type: none;
        padding: 0;
        margin: 0 auto;
        text-align: center;

        li {
          margin-top: 8px;
          padding: 0;

          a {
            text-decoration: none;
            color: white;

            &:hover {
              color: gray;
            }
          }
        }
      }

      .social-icons {
        margin: 0 auto;
      }
    }
  }

  .copy {
    background-color: $omegaup-primary--darkest;

    .copy-content {
      width: 80%;
      margin: 0 auto;
      display: flex;
      justify-content: space-between;
      padding-top: 20px;
      padding-bottom: 20px;

      .menu {
        ul {
          margin: 0;
          li {
            list-style-type: none;

            a {
              text-decoration: none;
              color: white;

              &:hover {
                color: gray;
              }
            }
          }
        }
      }
    }
  }
}
</style>
