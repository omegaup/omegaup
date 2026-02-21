# Table of Contents
- [Ideas List](#ideas-list)
- [How to Ramp Up](#how-to-ramp-up)
- [Application Process](#application-process)
- [Communications](#communications)
- [Frequently Asked Questions](#frequently-asked-questions)

# Ideas List

> We encourage you to visit omegaup.org and omegaup.com to learn about our platform and features. And remember, this is an **Ideas List** we expect you complete most of the details in your proposal and you are also welcome to propose your own project idea. Don't hesitate to reach out for any questions or new ideas in our [Discord channel](https://discord.gg/gMEMX7Mrwe)!

## Migrate Problem Creator to Vue.js + Typescript

**Brief Description**:

Migrate the [Problem Creator](https://github.com/Mau-MD/Omegaup-CDP) to Vue.js + Typescript so it can be integrated into omegaUp.com rather than remain as an external website.

**Expected results**:

Users can use the Problem Creator without having to go to an external website to create a `.zip` zip to upload to omegaup.com.

**Preferred skills**:
* Vue.js
* React
* Typescript
* PHP

**Possible mentor**:

[carlosabcs](https://github.com/carlosabcs), [pabo99](https://github.com/pabo99)

**Estimated size of project:**

350 hours

**Skill level**:

Medium

## Public Courses on github

**Brief Description**:

omegaUp offers many [public courses](https://omegaup.com/course/) in Spanish open to everyone. They have been solely managed by omegaUp staff but we want to be able to manage them through github so anyone can suggest improvements to the content (through pull requests). The Mexican Olympiad in Informatics already [does this](https://github.com/ComiteMexicanoDeInformatica/Curso-OMI/blob/main/.github/workflows/continuous-integration.yaml) on a public course that they offer through omegaUp. We need to replicate what they have on our courses.

**Expected results**:

The content of public courses offered by omegaUp is managed through github and anybody is able to propose improvements through pull requests.

**Preferred skills**:
* git/github
* Python
* Continuous Integration
* REST APIs

**Possible mentor**:

[heduenas](https://github.com/heduenas), [tvanessa](https://github.com/tvanessa)

**Estimated size of project:**

175 hours

**Skill level**:

Medium to Advanced

## Cronjob Optimization

**Brief Description**:

We have a number of [cronjobs](https://github.com/omegaup/omegaup/tree/main/stuff/cron) responsible for things such as updating student/school rankings, awarding badges to students, etc. Over the time they have become inefficient, error prone and hard to debug. We want to make them more efficient, increase their test coverage and improve their debug-ability.

**Expected results**:

Cronjobs become much leaner, faster and easier to maintain.

**Preferred skills**:
* Python
* MySQL
* PHP
* Unit and Integration Testing

**Possible mentor**:

[pabo99](https://github.com/pabo99), [tvanessa](https://github.com/tvanessa)

**Estimated size of project:**

350 hours

**Skill level**:

Medium

## Code Coverage Measurement for End-to-end Tests

**Brief Description**:

We recently migrated our integration tests written in [Cypress](https://www.cypress.io/). We use [codecov](https://about.codecov.io/) to measure and enforce test coverage, however our codecov setup right now only takes into account unit tests and not end-to-end tests. In this project we want codecov to also measure cypress test coverage so we can enforce minimum levels of coverage.

**Expected results**:

Codecov reports cypress test coverage enabling coverage levels to be monitored and minimum levels of coverage to be enforced.

**Preferred skills**:
* Integration testing
* Typescript
* PHP

**Possible mentor**:

[pabo99](https://github.com/pabo99), [heduenas](https://github.com/heduenas)

**Estimated size of project:**

90 hours

**Skill level**:

Medium

## AI Teaching Assistant

**Brief Description**:

We recently added the role of (human) Teaching Assistant, which has the capability of providing code-reviews to students and answer questions asked by students. In this project we want to create a bot that can answer technical questions and perform code reviews both proactively and upon request. This will help tighten the feedback loop for students so they can grow more rapidly.

**Expected results**:

omegaUp has an AI Teaching Assistant bot that can answer technical questions and perform code reviews, proactively and upon request.

**Preferred skills**:
* Python
* PHP
* MySQL
* LLM Prompt Engineering
* REST APIs

**Possible mentor**:

[carlosabcs](https://github.com/carlosabcs), [heduenas](https://github.com/heduenas)

**Estimated size of project:**

90 hours

**Skill level**:

Medium

## Migrate our Online IDE to Vue.js + Typescript

**Brief Description**:

We have an [online IDE](https://omegaup.com/grader/ephemeral/) that we currently embed into our [problem](https://omegaup.com/arena/problem/Watermel/) pages. However, the IDE is currently embedded but not fully integrated, the reason being that it is not written in Vue.js + typescript as the rest of the web platform. As such we cannot do important things such as load the user's latest submission.

**Expected results**:

Our online IDE is turned into a Vue.js component that is fully integrated into the problem pages of omegaup.com

**Preferred skills**:
* Typscript
* Vue.js
* PHP

**Possible mentor**:

[pabo99](https://github.com/pabo99), [tvanessa](https://github.com/tvanessa)

**Estimated size of project:**

350 hours

**Skill level**:

High

# How to Ramp Up

If you are interested spending this summer collaborating with us, first of all, we're honored that you are interested in our organization and we want to make the application process as smooth and enjoyable as possible for you. In order to familiarize yourself with [omegaUp.com](omegaup.com) and start collaborating with us please follow these steps:

 - Visit [omegaup.org](omegaup.org) to learn more about our work, our vision, and the people who are being benefited by our work.
 - Read [this article](http://www.ioinformatics.org/oi/pdf/v8_2014_169_178.pdf) published by our co-founders to learn about the architecture and overall design of our platform.

# Application Process

#### Our application process consists of three phases. If you want to participate with us this year, you must complete each of them in order.

### Phase One: Complete our test 

 - First, create an account at [omegaUp.com](https://omegaUp.com).
 - Join to our GSoC 2024 [omegaUp Test](https://omegaup.com/arena/gsoc2024-ext). The test consists of 3 problems, you have to solve at least 2 of them in order to pass. **In the case of plagiarism, we will disqualify those applicants involved**, so please don't share your solutions with your fellow applicants.

### Phase Two: Familiarize yourself with our codebase

We ask that you complete phase one before you start working with our codebase.

 - Follow these [instructions](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/Development-Environment-Setup-Process.md) to set up your development environment.
 - Find yourself an interesting bug to solve from our [issue tracker](https://github.com/omegaup/omegaup/issues) (specially from our list of ["Good first issues"](https://github.com/omegaup/omegaup/labels/Good%20first%20issue), or reach out to the [Discord channel](https://discord.gg/gMEMX7Mrwe) asking for one and we will be happy to find a good fit for you. Most of the conversations in the issue tracker are in Spanish but feel free to switch the conversation to English on any issue. Alternatively, you can go and find bugs on omegaup.com yourself, then report them in our issue tracker, and then fix them.
 - Implement your fix and submit it for review. Once it's merged you can move onto the third phase.

### Phase Three: Writing your proposal

At this step we hope you are familiar with our development environment and code since that makes it easier to understand our project ideas. **We ask that you get at least one PR merged into one of the omegaUp repositories before working on a design for a specific project.**

 - Craft a design document for your project using [this template](https://docs.google.com/document/d/1_FKfpc2M3VLDVYqvT8ZgsgwIJ3zaZnyUVmSm-H3h6UQ/edit). If you want to work in more than one project, we ask that you mention that in your application but include only one design. This is to reduce the workload for reviewers. 
 - We also encourage you to **send us your draft proposal to review and give feedback**. Send the link of your draft through this form `https://forms.gle/TbbscnWA5B2ZWfJq7`. Make sure that anyone with the link can see and comment.
 - We will try to provide you with as much feedback as we can and as soon as we can. However, we will not provide feedback to candidates who have not successfully completed phases 1 or 2.
 - When you consider that your application is ready, don't forget to **send it to [Google](https://summerofcode.withgoogle.com/age-verification/student/?next=%2Fstudent-signup%2F)** because if you don't do it, you will not be able to be considered in GSoC 2023.

### Phase Four: Interview with the organization
After design documents are submitted, we will select a short list of candidates based on the first 3 phases and schedule phone interviews with them. The interview will consist of both behavioral and technical questions.

We will only consider candidates that completed all 4 phases of the application.

# Communications
## If you have questions about the [development environment](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/I-Want-to-Develop-in-omegaUp.md) or the [codebase](https://github.com/omegaup/omegaup) or how the GSoC application process works at omegaUp, please follow our [Getting Help page](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/How-to-Get-Help.md) to effectively getting your question answered.

**Our main communication medium with GSoC candidates is our [Discord channel](https://discord.gg/gMEMX7Mrwe). We invited you to join!**

# Frequently Asked Questions #
   * **The development environment installation script is throwing me an error.** Please follow our [Getting Help page](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/How-to-Get-Help.md) to effectively getting your issue resolved.
   * **Am I expected to speak Spanish?** Of course not. We try our best to be as inclusive as possible to non-Spanish volunteers. Please feel free to use English throughout our communication channels and in your code. We have also found Google Translate to do a decent job in translating the Spanish contents of our GitHub page, we advise you to use it to navigate our issue tracker, wiki, etc.
  * **How many spots will your organization have for GSoC 2023?** We will ask for 3 students this year, but there is no guarantee yet, we will know for sure until around mid-May 2023.
  * **How do you choose your students?** We will review each application that we receive and will choose our candidates based on three things:
    * Candidate's skill level. There are two good ways to show your skill level in your application: Through impactful pull requests sent to our repositories (this is the recommended way), or through previous experience. Make sure to include evidence of at least one of those in your application.
    * Candidate's work plan. We ask you to write a high-level design of your project following our [proposal template](https://docs.google.com/document/d/1_FKfpc2M3VLDVYqvT8ZgsgwIJ3zaZnyUVmSm-H3h6UQ/edit)
    * Cultural fit. We like people who promote inclusion in the organization and are proactively helping out peers. A good way to show help out other candidates when they ask questions on the [Discord channel](https://discord.gg/gMEMX7Mrwe).
* **Are there any sample applications for I can look at?** Two good samples are:
 * Carlos Cordova's [proposal from 2018](https://docs.google.com/document/d/1ZEnC33hW4WjZ1WcsDjEtuIeNPuvW62q_hBFjhFosLOI/edit#heading=h.30j0zll)
 * 
    Vincent Fango's [proposal from 2018](https://docs.google.com/document/d/1ei3AV1ByLpONbTgO3Grnl8aVOIL2hwz48IxLmDyuOWA/edit#heading=h.gjdgxs). You can also watch Vincent's final project presentation: <br>
[![omegaUp dev environment installation on Windows](https://img.youtube.com/vi/cOnJ_5M1DFs/0.jpg)](https://www.youtube.com/watch?v=cOnJ_5M1DFs)
* **Can I propose a solution to multiple problems from the ideas list?** This year we ask that you include the design for only one project in your application. When you are in the coding phase and if you finish that project early, you are more than welcome to work on an additional project.
* **Are there more opportunities at omegaUp for me if I don't get selected for GSoC?** Of course, there are. We always welcome new volunteers who are interested in supporting our efforts. Around the time when results are released, we will announce a plan to onboard those of you how are interested in becoming volunteers.

