# Table of Contents
- [Ideas List](#ideas-list)
- [How to Ramp Up](#how-to-ramp-up)
- [Application Process](#application-process)
- [Communications](#communications)
- [Frequently Asked Questions](#frequently-asked-questions)

# Ideas List

> We encourage you to visit omegaup.org and omegaup.com to learn about our platform and features. And remember, this is an **Ideas List** we expect you complete most of the details in your proposal and you are also welcome to propose your own project idea. Don't hesitate to reach out for any questions or new ideas in our [Discord channel](https://discord.gg/gMEMX7Mrwe)!



## Query Optimization and Performance Benchmarking

**Brief Description**:

This project aims to improve the platform’s performance by optimizing SQL queries that currently have to do full table scans and implementing 2 integration tests: 

* One Integration test that analyzes the EXPLAIN of all queries to determine if it's efficient or not.
* One integration test that analyzes the performance of all queries by running them on a synthetic dataset with dimensions comparable to those of production. 

The goal is to ensure quick response times for frequent operations and ensure scalability under high-load scenarios. More details about the requirements can be found in [this doc](https://docs.google.com/document/d/1X_fAm97L6_v9P8_R0S_Lp7X6e7x7j7z-X5z5-x5z5z5).

**Current status**:

* Slow queries have been identified and documented in a [query tracker](https://docs.google.com/spreadsheets/d/1z5EZlGRY5MXUBYn5VoSX3c7Wqt14mI_iH-IY266bf_Y/edit?gid=0#gid=0).
* Progress is tracked in the master issue [#8277](https://github.com/omegaup/omegaup/issues/8277), which lists created and merged PRs.
* Key PRs: [#8450](https://github.com/omegaup/omegaup/pull/8450) (Script to populate database) and [#8423](https://github.com/omegaup/omegaup/pull/8423) (Inefficient query detection script).

**Expected results**:

Significant reduction in execution time for queries that have already been identified to be inefficient. A reproducible benchmarking system to evaluate performance improvements. Integration tests prevent introduction of new inefficient queries in the future.

**Preferred skills**:

* SQL query optimization
* MySQL
* Python
* PHP
* YML

**Possible mentor**:

[Ankitsinghsisodya](https://github.com/Ankitsinghsisodya), [pabo99](https://github.com/pabo99), [carlosabcs](https://github.com/carlosabcs)

**Estimated size of project:**

350 hours

**Skill level**:

Medium

## Performance Monitoring and Alerting Dashboard

**Brief Description**:

Define and implement meaningful performance metrics for the services that omegaUp runs, including: front end server, grader, gitserver, MySQL server. On top of that monitoring, some thresholds should be defined to alert us to our email and/or slack. Some basic monitoring and alerting is already in place, this project calls for a big improvement on top of that.

**Expected results**:

The monitoring and alerting allows us to catch and debug production issues before users start being significantly affected.

**Preferred skills**:

* New relic
* SQL
* PHP
* Python

**Possible mentor**:

[heduenas](https://github.com/heduenas), [iqbalcodes6602](https://github.com/iqbalcodes6602)

**Estimated size of project:**

350 hours

**Skill level**:

Medium



## Migrating from Vue 2 to Vue 3

**Brief Description**:

Vue.js 2 has officially reached its EOF (End of Life) in favor of Vue.js 3. We need to migrate in order to avoid security risk and dependency deprecation and to take advantage of Vue 3's improved performance.

**Expected results**:

omegaup.com runs fully on Vue.js 3 and has no dependency on Vue.js 2.

**Preferred skills**:

* Vue.js
* Typescript
* PHP
* REST APIs

**Possible mentor**:

[pabo99](https://github.com/pabo99), [iqbalcodes6602](https://github.com/iqbalcodes6602), [carlosabcs](https://github.com/carlosabcs)

**Estimated size of project:**

350 hours

**Skill level**:

Medium



## Migrating Bootstrap 4 to 5

**Brief Description**:

Migrate the UI to Bootstrap 5 to modernize the UI by replacing jQuery with Vanilla JavaScript, resulting in a lighter and faster codebase. This upgrade will enable easier theming through CSS variables and drop legacy IE support to embrace modern web standards. Ultimately, it reduces technical debt while providing a more responsive grid system tailored for high-resolution displays.

**Expected results**:

The omegaUp UI runs fully on bootstrap 5 and has no dependency on boostrap 4.

**Preferred skills**:

* Vue.js
* Typescript
* PHP
* REST APIs

**Possible mentor**:

[pabo99](https://github.com/pabo99), [iqbalcodes6602](https://github.com/iqbalcodes6602), [carlosabcs](https://github.com/carlosabcs)

**Estimated size of project:**

350 hours

**Skill level**:

Medium



## Cronjob Optimization

**Brief Description**:

We have a number of cronjobs responsible for things such as updating student/school rankings, awarding badges to students, etc. Over the time they have become inefficient, error prone and hard to debug. We want to make them more efficient, increase their test coverage and improve their debug-ability.

**Expected results**:

Cronjobs become much leaner, faster and easier to maintain.

**Preferred skills**:

* Python
* SQL query optimization
* PHP
* REST APIs

**Possible mentor**:

[Ankitsinghsisodya](https://github.com/Ankitsinghsisodya), [iqbalcodes6602](https://github.com/iqbalcodes6602), [carlosabcs](https://github.com/carlosabcs)

**Estimated size of project:**

350 hours

**Skill level**:

High



## Integrate Problem Creator

**Brief Description**:

A project from last year's GSoC introduced the Problem Creator, a visual editor that helps problem authors create and edit problems more easily. However, the Problem Creator isn't yet fully integrated with omegaUp. Currently, authors must write their problem in the Creator, download a .zip, and upload it manually. This project aims to streamline these workflows by fully integrating the Problem Creator with omegaUp's native create and edit features.

**Current Status**:

* A technical [pdesign doc](https://docs.google.com/document/d/1qpBwJQ6QIiIXgWpb_qa6OJ8KpJPcd11x3qKiefdgPAw/edit?tab=t.0) has been written for this project. 
* Completed backend changes:
  * #8470 CDP classes and validations, added core CDP classes and validation logic.
  * #8479 New method in ProblemDeployer to modify problem ZIPs, enables updating problem ZIP contents during deployment.
  * #8554 CDPBuilder fixes and unit test coverage, fixed issues in CdpBuilder and added a unit test to ensure correctness.
  * #8606 Language support in CDPBuilder, added multi-language support with priority based on languagePreference
  * #8613 Backend logic for editing cases and CDP, added server-side logic to support case editing and CDP updates.
  * Initial UI change completed: #8593 CasesForm and DeleteConfirmationForm components, introduced the base UI forms required for managing cases 
* Pending work (mostly UI-related)
  * #8471 Integrate CDP into the problem form and handle external ZIPs
  * #8492 UI implementation for the editing interface
  * #8595 UI integration: Edit view, CaseEdit, and Sidebar

**Expected results**:

A seamless end-to-end user experience where authors can create or edit problems directly via the Problem Creator without manual file transfers.

**Preferred skills**:

* Vue.js
* TypeScript
* PHP
* Integration Testing

**Possible mentor**:

[pabo99](https://github.com/pabo99), [heduenas](https://github.com/heduenas), Ankitsinghsisodya](https://github.com/Ankitsinghsisodya)

**Estimated size of project:**

175 hours

**Skill level**:

High

# How to Ramp Up

If you are interested spending this summer collaborating with us, first of all, we're honored that you are interested in our organization and we want to make the application process as smooth and enjoyable as possible for you. In order to familiarize yourself with [omegaUp.com](omegaup.com) and start collaborating with us please follow these steps:

 - Visit [omegaup.org](omegaup.org) to learn more about our work, our vision, and the people who are being benefited by our work.
 - Read [this article](http://www.ioinformatics.org/oi/pdf/v8_2014_169_178.pdf) published by our co-founders to learn about the architecture and overall design of our platform.
 - Read [this follow-up article](https://ioinformatics.org/journal/v18_2024_167_174_Duenas.pdf) to learn about the work done in more recent years in omegaUp.
 - Read our [technical documentation](https://github.com/omegaup/omegaup/tree/main/frontend/www/docs).

# Application Process

#### Our application process consists of three phases. If you want to participate with us this year, you must complete each of them in order.

### Phase One: Complete our test 

 - First, create an account at [omegaUp.com](https://omegaUp.com).
 - Join to our GSoC 2026 [omegaUp Test](https://omegaup.com/contest/gsoc2026). The test consists of 3 problems, you have to solve at least 2 of them in order to pass. **In the case of plagiarism, we will disqualify those applicants involved**, so please don't share your solutions with your fellow applicants.

### Phase Two: Familiarize yourself with our codebase

We ask that you complete phase one before you start working with our codebase.

 - Follow these [instructions](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/Development-Environment-Setup-Process.md) to set up your development environment.
 - Find yourself an interesting bug to solve from our [issue tracker](https://github.com/omegaup/omegaup/issues) (specially from our list of ["Good first issues"](https://github.com/omegaup/omegaup/labels/Good%20first%20issue), or reach out to the [Discord channel](https://discord.gg/gMEMX7Mrwe) asking for one and we will be happy to find a good fit for you. Most of the conversations in the issue tracker are in Spanish but feel free to switch the conversation to English on any issue. Alternatively, you can go and find bugs on omegaup.com yourself, then report them in our issue tracker, and then fix them.
 - Implement your fix and submit it for review. Once it's merged you can move onto the third phase.

### Phase Three: Writing your proposal

At this step we hope you are familiar with our development environment and code since that makes it easier to understand our project ideas. **We ask that you get at least three PR merged into one of the omegaUp repositories before working on a design for a specific project.**

 - Craft a design document for your project using [this template](https://docs.google.com/document/d/1_FKfpc2M3VLDVYqvT8ZgsgwIJ3zaZnyUVmSm-H3h6UQ/edit). If you want to work in more than one project, we ask that you mention that in your application but include only one design. This is to reduce the workload for reviewers. 
 - We also encourage you to **send us your draft proposal to review and give feedback**. Send the link of your draft through this form `https://forms.gle/TbbscnWA5B2ZWfJq7`. Make sure that anyone with the link can see and comment.
 - We will try to provide you with as much feedback as we can and as soon as we can. However, we will not provide feedback to candidates who have not successfully completed phases 1 or 2.
 - When you consider that your application is ready, don't forget to **send it to [Google](https://summerofcode.withgoogle.com/age-verification/student/?next=%2Fstudent-signup%2F)** because if you don't do it, you will not be able to be considered in GSoC 2026.

### Phase Four: Interview with the organization
After design documents are submitted, we will select a short list of candidates based on the first 3 phases and schedule phone interviews with them. The interview will consist of both behavioral and technical questions.

We will only consider candidates that completed all 4 phases of the application.

# Communications
## If you have questions about the [development environment](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/Quiero-desarrollar-en-omegaUp.md) or the [codebase](https://github.com/omegaup/omegaup) or how the GSoC application process works at omegaUp, please follow our [Getting Help page](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/How-to-Get-Help.md) to effectively getting your question answered.

**Our main communication medium with GSoC candidates is our [Discord channel](https://discord.gg/gMEMX7Mrwe). We invited you to join!**

# Frequently Asked Questions #
   * **The development environment installation script is throwing me an error.** Please follow our [Getting Help page](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/How-to-Get-Help.md) to effectively getting your issue resolved.
   * **Am I expected to speak Spanish?** Of course not. We try our best to be as inclusive as possible to non-Spanish volunteers. Please feel free to use English throughout our communication channels and in your code. We have also found Google Translate to do a decent job in translating the Spanish contents of our GitHub page, we advise you to use it to navigate our issue tracker, wiki, etc.
  * **How many spots will your organization have for GSoC 2026?** We will ask for 4 students this year, but there is no guarantee yet, we will know for sure until around mid-May 2026.
  * **How do you choose your students?** We will review each application that we receive and will choose our candidates based on three things:
    * Candidate's skill level. There are two good ways to show your skill level in your application: Through impactful pull requests sent to our repositories (this is the recommended way), or through previous experience. Make sure to include evidence of at least one of those in your application.
    * Candidate's work plan. We ask you to write a high-level design of your project following our [proposal template](https://docs.google.com/document/d/1_FKfpc2M3VLDVYqvT8ZgsgwIJ3zaZnyUVmSm-H3h6UQ/edit)
    * Cultural fit. We like people who promote inclusion in the organization and are proactively helping out peers. A good way to show help out other candidates when they ask questions on the [Discord channel](https://discord.gg/gMEMX7Mrwe).
* **Are there any sample applications for I can look at?** Two good samples are:
 * Carlos Cordova's [proposal from 2018](https://docs.google.com/document/d/1ZEnC33hW4WjZ1WcsDjEtuIeNPuvW62q_hBFjhFosLOI/edit#heading=h.30j0zll)
 * Vincent Fango's [proposal from 2018](https://docs.google.com/document/d/1ei3AV1ByLpONbTgO3Grnl8aVOIL2hwz48IxLmDyuOWA/edit#heading=h.gjdgxs). You can also watch Vincent's final project presentation: <br>
[![omegaUp dev environment installation on Windows](https://img.youtube.com/vi/cOnJ_5M1DFs/0.jpg)](https://www.youtube.com/watch?v=cOnJ_5M1DFs)
* **Can I propose a solution to multiple problems from the ideas list?** We ask that you include the design for only one project in your application. When you are in the coding phase and if you finish that project early, you are more than welcome to work on an additional project.
* **Are there more opportunities at omegaUp for me if I don't get selected for GSoC?** Of course, there are. We always welcome new volunteers who are interested in supporting our efforts. After results are released feel free to keep asking for work items.
