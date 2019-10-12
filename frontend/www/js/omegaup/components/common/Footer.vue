<template>
  <div class="common-footer">
    <div class="footer-navigation">
      <div class="footer-brand">
        <img class="footer-logo" src="/media/logo-main-white.svg" />
        <div class="slogan">{{ T.frontPageFooter }}</div>
      </div>

      <div class="footer-list-section">
        <h4 class="section-title">{{ T.frontPageFooterSite }}</h4>
        <ul>
          <li><a href="/arena/">{{ T.navArena }}</a></li>
          <li><a href="/problem/">{{ T.navProblems }}</a></li>
          <li><a href="/rank/">{{ T.navRanking }}</a></li>
          <li><a href="/schools/">{{ T.navSchools }}</a></li>
          <li><a href="https://blog.omegaup.com" _target="blank">{{ T.navBlog }}</a></li>
          <li><a href="https://omegaup.com/preguntas/" _target="blank">{{ T.navQuestions }}</a></li>
        </ul>
      </div>

      <div class="footer-list-section">
        <h4 class="section-title">{{ T.frontPageFooterOrganization }}</h4>
        <ul>
          <li><a href="https://omegaup.org/#about">{{ T.frontPageFooterAboutUs }}</a></li>
          <li><a href="https://omegaup.org/#team">{{ T.frontPageFooterTeam }}</a></li>
        </ul>
      </div>

      <div class="footer-list-section">
        <h4 class="section-title">{{ T.frontPageDevelopers }}</h4>
        <ul>
          <li><a href="https://github.com/omegaup/omegaup/wiki/C%C3%B3mo-empezar-a-desarrollar" _target="blank">{{ T.frontPageFooterHelpUs }}</a></li>
          <li><a href="https://github.com/omegaup/omegaup" _target="blank">Github</a></li>
        </ul>
      </div>

      <div class="footer-list-section">
        <h4 class="section-title">{{ T.frontPageFooterContact }}</h4>
        <ul>
          <li><a href="mailto:hello@omegaup.com">hello@omegaup.com</a></li>
        </ul>

        <div class="social-icons">
          GITHUB / FACEBOOK
        </div>
      </div>
    </div>

    <div class="copy">
      <div class="copy-content">
        <div class="">
          Copyright © 2019 OmegaUp. All rights reserved.​
        </div>
        <div class="menu">
          <ul>
            <li><a href="">{{ T.frontPageFooterPrivacyPolicy }}</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">

import { Component, Vue } from 'vue-property-decorator';
import { T } from '../../omegaup.js';

@Component
export default class Footer extends Vue {
  T = T;
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