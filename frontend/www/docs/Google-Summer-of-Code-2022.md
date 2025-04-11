# Table of Contents
- [Ideas List](#ideas-list)
  * [Code Reviews](#code-reviews)
  * [Optimize omegaUp.com for Mobile](#optimize-omegaupcom-for-mobile)
  * [Accounts for Children](#accounts-for-children)
  * [SQL Problem Support](#sql-problem-support)
  * [Plagiarism Detector](#plagiarism-detector)
- [How to Ramp Up](#how-to-ramp-up)
- [Application Process](#application-process)
    + [Phase One: Complete our test](#phase-one--complete-our-test)
    + [Phase Two: Familiarize yourself with our codebase](#phase-two--familiarize-yourself-with-our-codebase)
    + [Phase Three: Writing your proposal](#phase-three--writing-your-proposal)
- [Communications](#communications)
- [Frequently Asked Questions](#frequently-asked-questions)

# Ideas List

> We encourage you to visit omegaup.com to learn about our platform and features. And remember, this is an **Ideas List** we expect you complete most of the details in your proposal. Don't hesitate to reach out for any questions or new ideas in our [Discord channel](https://discord.gg/aj8r5M5W)!


## Code Reviews

**Brief Description**:

When students make a submission to a code assignment, their teacher and classmates should be able to send them feedback on their code line by line (similar to how github's code reviews work).

**Expected results**:

Build a functionality for teachers to do codereviews on their students' submissions. As a stretch goal, classmates should also be able to write feedback after the deadline of the assignment is past.

**Preferred skills**:
  * Vue.js
  * Typescript
  * PHP
  * SQL

**Possible mentor**:

[carlosabcs](https://github.com/carlosabcs), [tvanessa](https://github.com/tvanessa)

**Estimated size of project:**

175 hours

**Skill level**:

Low to medium

## Optimize omegaUp.com for Mobile

**Brief Description**:

There are many students who do not have access to a computer but they do have access to a mobile phone, most of them a low to mid end. Currently, our website consumes too much resources (bandwidth, RAM, CPU) to properly work on such devices. We should make a mobile version of omegaUp.com that works great on mobiles.

**Expected results**:
When students visit omegaUp.com from a mobile phone it loads quickly and they are able to do their learning without much overhead.

**Preferred skills**:
  * Vue.js
  * Typescript
  * PHP

**Possible mentor**:

[heduenas](https://github.com/heduenas), [pabo99](https://github.com/pabo99)

**Estimated size of project:**

175 hours

**Skill level**:

Medium

## Accounts for Children

**Brief Description**:

Currently, students under 13 years old cannot use omegaUp since first we need to meet a series of regulations. We will introduce a special type of restricted account for children that allows them to consume curated content and be invited to courses by their school teachers.

**Expected results**:
Children under 13 years old can sign up at omegaup.com and can safely learn computer science both on their own or assisted by their teachers. 

**Preferred skills**:
  * PHP
  * MySQL
  * Vue.js
  * Typescript

**Possible mentor**:

[tvanessa](https://github.com/tvanessa), [carlosabcs](https://github.com/carlosabcs)

**Estimated size of project:**

350 hours

**Skill level**:

Medium

## SQL Problem Support

**Brief Description**:

We have received the request to support SQL learning by many people, both students and teachers. The idea is to implement a sandboxed environment that can run user-sent SQL statements and verify the result produced by them.

**Expected results**:
Students can learn SQL by submitting SQL solutions to problems, and teachers can create custom data base questions that can be solved by a SQL query or set of queries.  

**Preferred skills**:
  * Go
  * Vue.js
  * Typescript
  * PHP
  * SQLite

**Possible mentor**:

[heduenas](https://github.com/heduenas), [carlosabcs](https://github.com/carlosabcs)

**Estimated size of project:**

350 hours

**Skill level**:

High

## Plagiarism Detector

**Brief Description**:

Whenever there is an online contest/programming course, there is the risk of participants cheating by sharing their solution amongst themselves. This is currently done in a very ad-hoc way (by manually inspecting all submissions that have similar scores) and taking into account which students get along with which other students, which does not scale. It would be very beneficial to have a report of similarity that is generated at the end of each contest/course. This can be achieved by calling a service such as [MOSS](https://theory.stanford.edu/~aiken/moss/).

**Expected results**:

Build a service that manages a queue of plagiarism analysis requests in courses and contests. Each request is a set of code submissions that will be uploaded to the plagiarism analysis service. Once the analysis is finished, it should be transformed in a way that can be presented to the user.

**Preferred skills**:
  * PHP
  * SQL
  * Golang
  * Vue.js

**Possible mentor**:

[carlosabcs](https://github.com/carlosabcs), [pabo99](https://github.com/pabo99)

**Estimated size of project:**

350 hours

**Skill level**:

Medium


# How to Ramp Up

If you are interested spending this summer collaborating with us, first of all, we're honored that you are interested in our organization and we want to make the application process as smooth and enjoyable as possible for you. In order to familiarize yourself with [omegaUp.com](omegaup.com) and start collaborating with us please follow these steps:

 - Visit [omegaup.org](omegaup.org) to learn more about our work, our vision, and the people who are being benefited by our work.
 - Read [this article](http://www.ioinformatics.org/oi/pdf/v8_2014_169_178.pdf) published by our co-founders to learn about the architecture and overall design of our platform.

# Application Process

#### Our application process consists of three phases. If you want to participate with us this year, you must complete each of them in order.

### Phase One: Complete our test 

 - First, create an account at [omegaUp.com](https://omegaUp.com).
 - Join to our [GSoC 2022 omegaUp Test](https://omegaup.com/arena/gsoc2022e). The test consists of three problems, you have to solve any two of them in order to pass. In the case of plagiarism, we will disqualify those applicants involved.

### Phase Two: Familiarize yourself with our codebase

We ask that you complete phase one before you start working with our codebase.

 - Follow these [instructions](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/Development-Environment-Setup-Process.md) to set up your development environment.
 - Find yourself an interesting bug to solve from our [issue tracker](https://github.com/omegaup/omegaup/issues) (specially from our list of ["Good first issues"](https://github.com/omegaup/omegaup/labels/Good%20first%20issue), or reach out to the [Discord channel](https://discord.gg/aj8r5M5W) asking for one and we will be happy to find a good fit for you. Most of the conversations in the issue tracker are in Spanish but feel free to switch the conversation to English on any issue. Alternatively, you can go and find bugs on omegaup.com yourself, then report them in our issue tracker, and then fix them.
 - Implement your fix and submit it for review. Once it's merged you can move onto the third phase.

### Phase Three: Writing your proposal

At this step we hope you are familiar with our development environment and code since that makes it easier to understand our project ideas. **We ask that you get at least one PR merged into one of the omegaUp repositories before working on a design for a specific project.**

 - Craft a design document for your project using [this template](https://docs.google.com/document/d/1_FKfpc2M3VLDVYqvT8ZgsgwIJ3zaZnyUVmSm-H3h6UQ/edit). If you want to work in more than one project, we ask that you mention that in your application but include only one design. This is to reduce the workload for reviewers. 
 - We also encourage you to **send us your draft proposal to review and give feedback**. Send the link of your draft through this form `https://forms.gle/XRCU4MS9oAJCXuk3A`. Make sure that anyone with the link can see and comment.
 - We will try to provide you with as much feedback as we can and as soon as we can. However, we will not provide feedback to candidates who have not successfully completed phases 1 or 2.
 - When you consider that your application is ready, don't forget to **send it to [Google](https://summerofcode.withgoogle.com/age-verification/student/?next=%2Fstudent-signup%2F)** because if you don't do it, you will not be able to be considered in GSoC 2022.

We will only consider candidates that completed all 3 phases of the application.

# Communications
## If you have questions about the [development environment](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/I-Want-to-Develop-in-omegaUp.md) or the [codebase](https://github.com/omegaup/omegaup) or how the GSoC application process works at omegaUp, please follow our [Getting Help page](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/How-to-Get-Help.md) to effectively getting your question answered.

**Our main communication medium with GSoC candidates is our [Discord channel](https://discord.gg/aj8r5M5W). We invited you to join!**

# Frequently Asked Questions #
   * **The development environment installation script is throwing me an error.** Please follow our [Getting Help page](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/How-to-Get-Help.md) to effectively getting your issue resolved.
   * **Am I expected to speak Spanish?** Of course not. We try our best to be as inclusive as possible to non-Spanish volunteers. Please feel free to use English throughout our communication channels and in your code. We have also found Google Translate to do a decent job in translating the Spanish contents of our GitHub page, we advise you to use it to navigate our issue tracker, wiki, etc.
  * **How many spots will your organization have for GSoC 2022?** We will ask for 2 students this year, but there is no guarantee yet, we will know for sure until around mid-May 2022.
  * **How do you choose your students?** We will review each application that we receive and will choose our candidates based on three things:
    * Candidate's skill level. There are two good ways to show your skill level in your application: Through impactful pull requests sent to our repositories (this is the recommended way), or through previous experience. Make sure to include evidence of at least one of those in your application.
    * Candidate's work plan. We ask you to write a high-level design of your project following our [proposal template](https://docs.google.com/document/d/1_FKfpc2M3VLDVYqvT8ZgsgwIJ3zaZnyUVmSm-H3h6UQ/edit)
    * Cultural fit. We like people who promote inclusion in the organization and are proactively helping out peers. A good way to show help out other candidates when they ask questions on the [Discord channel](https://discord.gg/aj8r5M5W).
* **Are there any sample applications for I can look at?** Two good samples are:
 * Carlos Cordova's [proposal from 2018](https://docs.google.com/document/d/1ZEnC33hW4WjZ1WcsDjEtuIeNPuvW62q_hBFjhFosLOI/edit#heading=h.30j0zll)
 * 
    Vincent Fango's [proposal from 2018](https://docs.google.com/document/d/1ei3AV1ByLpONbTgO3Grnl8aVOIL2hwz48IxLmDyuOWA/edit#heading=h.gjdgxs). You can also watch Vincent's final project presentation: <br>
[![omegaUp dev environment installation on Windows](https://img.youtube.com/vi/cOnJ_5M1DFs/0.jpg)](https://www.youtube.com/watch?v=cOnJ_5M1DFs)
* **Can I propose a solution to multiple problems from the ideas list?** This year we ask that you include the design for only one project in your application. When you are in the coding phase and if you finish that project early, you are more than welcome to work on an additional project.
* **Are there more opportunities at omegaUp for me if I don't get selected for GSoC?** Of course, there are. We always welcome new volunteers who are interested in supporting our efforts. Around the time when results are released, we will announce a plan to onboard those of you how are interested in becoming volunteers.

