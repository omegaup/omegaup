### Interested in applying to GSoC 2019? checkout our [2019 Ideas List](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/Google-Summer-of-Code-2019-Ideas-List.md) ###

## Table of Contents ##
* [Project Ideas List](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/Google-Summer-of-Code-2018-Ideas-List.md#ideas-list)
* [How to Ramp Up](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/Google-Summer-of-Code-2018-Ideas-List.md#how-to-ramp-up)
* [Our Mailing List and Slack Channel](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/Google-Summer-of-Code-2018-Ideas-List.md#our-mailing-list-and-slack-channel)
* [How to Write Your Application](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/Google-Summer-of-Code-2018-Ideas-List.md#how-to-write-your-application)
* [FAQs](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/Google-Summer-of-Code-2018-Ideas-List.md#frequently-asked-questions)

# Ideas List #

## Quality Contents: Add quality and difficulty measurement for contests

**Brief description**:

Recently we introduced the capability of gathering feedback from users about a problem right after they solve it. Explicitly, we ask for quality, difficulty and topics related to the task. We aggregate that feedback every night and show a measurement of quality and difficulty as well as a list of tags for each problem to users looking at the [problem list](https://omegaup.com/problem/?query=&language=all&order_by=difficulty&mode=desc). In this project we want to the same kind of feedback about contests and show aggregations of that data to users looking for [contests](https://omegaup.com/arena/) to participate in.

**Expected results**:

Users participating in contests should be asked for feedback about the contest and users looking for a contest to participate in should see quality, difficulty and topic tags attached to each contest.

**Preferred skills**:
  * PHP
  * SQL
  * Python

**Possible mentor**:

[heduenas](https://github.com/heduenas)

**Skill level**:

Medium

## Quality Contents: Show quality and difficulty histograms

**Brief description**:

Recently we introduced the capability of gathering feedback from users about a problem right after they solve it. Explicitly, we ask for quality (a number between 1 and 5), difficulty (another number between 1 and 5) and topics related to the task. We aggregate that feedback every night and show a measurement of quality and difficulty as well as a list of tags for each problem to users looking at the [problem list](https://omegaup.com/problem/?query=&language=all&order_by=difficulty&mode=desc). The measurements of quality and difficulty are computed by taking a bayesian average of the data gathered from the users, we need to also compute and show the % of people who voted for each of the 5 possible values.

**Expected results**:

Users browsing the [problem list](https://omegaup.com/problem/?query=&language=all&order_by=difficulty&mode=desc) will see a histogram of the votes that the problem received for quality and for difficulty.

**Preferred skills**:
  * PHP
  * SQL
  * Python

**Possible mentor**:

[heduenas](https://github.com/heduenas)

**Skill level**:

Easy

## Problem suggestions

**Brief description**:

It is difficult for any person to wade through the thousands of problems available to solve in the platform. It would be very helpful to have a way to suggest what problems to solve next. Two potential use cases would be to suggest problems appropriate for a persons' skill level for students that are trying to learn new skills by themselves, and for teachers to be able to request problems that are similar in nature to another in order to refresh their courses after each school term.

**Expected results**:

Build a service that given a set of problems (either the problems that a particular user has solved, or a set of problems that a teacher wants to be similar), and a corpus of available problems, and data about what users have solved what problems, can return a small set of next problems to try.

**Preferred skills**:
  * PHP
  * SQL
  * Python
  * Machine Learning

**Possible mentor**:

[heduenas](https://github.com/heduenas)

**Skill level**:

Medium

## Plagiarism detector

**Brief description**:

Whenever there is an online contest/programming course, there is the risk of participants cheating by sharing their solution amongst themselves. This is currently done in a very ad-hoc way (by manually inspecting all submissions that have similar scores) and taking into account which students get along with which other students, which does not scale. It would be very beneficial to have a report of similarity that is generated at the end of each contest/course. This can be achieved by calling a service such as [MOSS](https://theory.stanford.edu/~aiken/moss/).

**Expected results**:

Build a service that manages a queue of plagiarism analysis requests. Each request is a set of code submissions that will be uploaded to the plagiarism analysis service. Once the analysis is finished, it should be transformed in a way that can be presented to the user.

**Preferred skills**:
  * PHP
  * SQL
  * Python

**Possible mentor**:

[heduenas](https://github.com/heduenas)

**Skill level**:

Easy / medium

## Ghost mode for contests

**Brief description**:

Students seeking to participate in competitions (such as state or national informatics olympiads) oftentimes replay the previous versions of that same competition at omegaUp.com as part of their training (that is, they solve exactly the same problems and under the same time constraints, etc). We want to offer a more realistic to contest replay by also showing a replay of the scoreboard exactly as recorded during the original competition.

Given a contest that has already finished, you should be able to select it and create a new contest based on it. Once the contest starts, a virtual timer will start running. The scoreboard of this new contest will merge the current standings with the standings of the original contest that corresponds with the virtual timer.

The name "Ghost mode" was chosen due to its similarity with the feature found in [Mario Kart](https://www.mariowiki.com/Ghost_(Mario_Kart)).

**Expected results**:

Students replaying any past competition will be able to see a replay of the scoreboard exactly as recorded during the original competition. This can be done mostly on the client-side to avoid having to update the database and/or invalidate caches.

**Preferred skills**:
  * JavaScript
  * PHP
  * SQL

**Possible mentor**:

[heduenas](https://github.com/heduenas)

**Skill level**:

Easy

# How To Ramp Up #
If you are interested spending this summer collaborating with us, first of all, we're honored that you are interested in our organization and we want to make the application process as smooth and enjoyable as possible for you. In order to familiarize yourself with omegaUp.com and start working with our codebase please follow these steps:
* Send us an email at `googlesummerofcode@omegaup.com` introducing yourself, expressing your interest and your prior experience in computer programming, we would like to meet you.
* Subscribe to our [mailing list](https://groups.google.com/forum/#!forum/omegaup-soporte).
* Join our [slack channel](https://omegaup-slack.herokuapp.com/) (token is "GSOC2019")
* Visit [omegaup.org](https://omegaup.org/) to learn more about our work, our vision, and the people who are being benefited by our work.
* Visit [omegaup.com](https://omegaup.com/), create an account and read a couple of our [problems in English](https://omegaup.com/problem/?query=&language=en&order_by=quality&mode=desc) to get a feel of our main product.
* Read [this article](http://www.ioinformatics.org/oi/pdf/v8_2014_169_178.pdf) published by our co-founders to learn about the architecture and design of our platform.
* Follow these [instructions](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/Development-Environment-Setup-Process.md) to set up your development environment.
* Find yourself an interesting bug to solve from our [issue tracker](https://github.com/omegaup/omegaup/issues), or reach out to the mailing list asking for one and we will be happy to find a good fit for you. We look forward to your pull request. Most of the conversations in the issue tracker are in Spanish, but feel free to switch the conversation to English on any issue. Alternatively, you can go and find bugs on [omegaup.com](https://omegaup.com) yourself, then report them in our [issue tracker](https://github.com/omegaup/omegaup/issues), and then fix them.
* Coming soon: Ramp up information for specific project ideas.

# Our Mailing List and Slack Channel #
## We ask you to read this entire document carefully. If you still have any question, first make sure it has not been answered in FAQs, in our mailing list or slack channel.

The [mailing list](https://groups.google.com/forum/#!forum/omegaup-soporte) and [slack channel](https://omegaup-slack.herokuapp.com/) is designed to answer any type of question related to omegaUp and participating in Google Summer of Code. For now, these are the only spaces where your questions will be answered. Join us! The token for our slack channel is "GSOC2018".

When you subscribe to our mailing list make sure to subscribe for notifications. [Here](http://www.youtube.com/watch?v=CnyO5XoTD4A&t=2m2s) is how to do that.

# How to Write Your Application #
Before start writing your proposal, we hope you are familiar with our development environment and code since that makes it easier to understand our project ideas. Craft a design document for your project using [this template](https://docs.google.com/document/d/1_FKfpc2M3VLDVYqvT8ZgsgwIJ3zaZnyUVmSm-H3h6UQ/edit). If you want to work in two projects, write one proposal with one design followed by the other. 

We also encourage you to **send us your draft proposal to review and give feedback**. Send the link of your draft to `googlesummerofcode@omegaup.com`. Make sure that the subject says *Proposal* and that anyone with the link can see and comment. 

When you consider that your application is ready, don't forget to **send it to [Google](https://summerofcode.withgoogle.com/age-verification/student/?next=%2Fstudent-signup%2F)** because if you don't do it, you will not be able to be considered in GSoC 2018.

# Frequently Asked Questions #
* **The development environment installation script is throwing me an error.** Please file a bug to the [deploy repository](https://github.com/omegaup/deploy/issues) and make sure to include your reproduction steps and the error you are getting.
* **Am I expected to speak Spanish?** Not at all. We try our best to be as inclusive as possible to non-Spanish volunteers. Please feel free to use English everywhere in our communication channels and in your code. We have also found Google Translate to do a decent job in assisting with translations of the things we have written in the past.
* **Do you have a mailing list where I can ask more questions?** Yes! Please join our [mailing list](https://groups.google.com/forum/#!forum/omegaup-soporte). You can also ask questions on our 
* **How do you choose your students?** We will review each application that we receive and will choose one or two (depending on how many good proposals and mentors we find) based on two things:
    * Candidate's skill level. There are two good ways to show your skill level in your application: Through impactful pull requests sent to our repositories (this is the recommended way), or through previous experience. Make sure to include evidence of at least one of those in your application.
    * Candidate's work plan. We ask you to write a high level design of your project following our [proposal template](https://docs.google.com/document/d/1_FKfpc2M3VLDVYqvT8ZgsgwIJ3zaZnyUVmSm-H3h6UQ/edit)
* **Can I propose solution to multiple problems from the ideas list?** Of course! We actually expect and encourage candidates to solve one of the problems marked as "easy" and then move onto a more challenging one, both during the solution proposal phase and during the the coding phase.
* **Are there more opportunities for me if I don't get selected for GSoC?** Of course there are. We always welcome new volunteers who are interested in supporting our efforts. Around the time when proposal submission ends, we will announce a plan to onboard those of you how are interested in becoming volunteers.