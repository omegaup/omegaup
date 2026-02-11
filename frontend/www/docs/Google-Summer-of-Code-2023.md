# Table of Contents
- [Ideas List](#ideas-list)
  * [Code Reviews](#code-reviews)
  * [Optimize omegaUp.com for Mobile](#optimize-omegaupcom-for-mobile)
  * [Accounts for Children](#accounts-for-children)
  * [SQL Problem Support](#sql-problem-support)
  * [Plagiarism Detector](#plagiarism-detector)
- [How to Ramp Up](#how-to-ramp-up)
- [Application Process](#application-process)
- [Communications](#communications)
- [Frequently Asked Questions](#frequently-asked-questions)

# Ideas List

> We encourage you to visit omegaup.com to learn about our platform and features. And remember, this is an **Ideas List** we expect you complete most of the details in your proposal. Don't hesitate to reach out for any questions or new ideas in our [Discord channel](https://discord.gg/gMEMX7Mrwe)!


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

[heduenas](https://github.com/heduenas), [pabo99](https://github.com/pabo99)

**Estimated size of project:**

350 hours

**Skill level**:

Medium

## Cypress Migration

**Brief Description**:

We have a number of end-to-end tests that use the Selenium framework. They have become slow, flaky and expensive to maintain. We have started writing new tests in the Cypress framework which yields us much better results and is much easier to use. This project consists of migrating all existing Selenium tests to Cypress and adding new test coverage using Cypress.

**Expected results**:

End-to-end tests run on the Cypress framework and have good performance. Allowing omegaUp developers to be more productive and have greater satisfaction with the development process, leading to a better end product for our users.

**Preferred skills**:
* Typescript
* End-to-end web testing frameworks
* Vue.js
* PHP

**Possible mentor**:

[pabo99](https://github.com/pabo99), [carlosabcs](https://github.com/heduenas)

**Estimated size of project:**

350 hours

**Skill level**:

Low to Medium

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

[carlosabcs](https://github.com/carlosabcs), [tvanessa](https://github.com/tvanessa)

**Estimated size of project:**

175 hours

**Skill level**:

Medium

## Plagiarism Detector

**Brief Description**:

Whenever there is an online contest/programming course, there is the risk of participants cheating by sharing their solution amongst themselves. This is currently done in a very ad-hoc way (by manually inspecting all submissions that have similar scores) and taking into account which students get along with which other students, which does not scale. It would be very beneficial to have a report of similarity that is generated at the end of each contest/course. This can be achieved by calling a service such as[ MOSS](https://theory.stanford.edu/~aiken/moss/).

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

High

## Automatic Certificate Generation

**Brief Description**:

Hundreds of contests and courses are hosted by omegaUp each year, many of which award certificates to students. Contest/course organizers currently have to generate their certificates manually outside of omegaup. This project consists of adding the ability for admins to effortlessly generate certificates for massive contests/courses. Those certificates should include a QR code that will be used to verify that authenticity of the document.

**Expected results**:

Contest/course admins are able to effortlessly generate certificates for students participating int heir contests/courses. Students are notified whenever they are awarded a new certificate, they are able to download it anytime as PDF and the certificate contains a QR to verify its authenticity.

**Preferred skills**:
* Python
* PHP
* SQL
* Python
* RabbitMQ
* Vue.js

**Possible mentor**:

[pabo99](https://github.com/pabo99), [heduenas](https://github.com/heduenas)

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
 - Join to our [GSoC 2023 omegaUp Test](https://omegaup.com/arena/gsoc2023). The test consists of three problems, you have to solve at least 3 (out of 4) of them in order to pass. **In the case of plagiarism, we will disqualify those applicants involved**, so please don't share your solutions with your fellow applicants.

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

