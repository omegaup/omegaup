# I Want to Develop in omegaUp  

Thank you for your interest in contributing to omegaUp! This guide provides an overview of the development environment and the project structure. 

If you're new to omegaUp, we recommend:  
  1. Visiting [omegaUp.com](https://omegaup.com/), creating an account, and solving a few problems.  
  2. Exploring [omegaup.org](https://omegaup.org/) to learn more about our organization and areas of work.

## Contents  

- [Development Environment](#development-environment)  
- [Architecture (Overview)](#architecture-overview)  
- [Code Structure](#code-structure)  
- [Design Decisions](#design-decisions)  

## Development Environment  

The first step is setting up the development environment. We use Docker for this, and the main operating systems used are Windows and Ubuntu. It can run on macOS, but additional configurations are needed.  

- [Development Environment Installation](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/Development-Environment-Setup-Process.md).  

## Architecture (Overview)  

Below is a high-level overview of omegaUp components _(temporary codenames are used)_:  

 
- **[Frontend](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/Frontend.md)**: A collection of controllers (in the MVC model) that manage site interactions, including problem and contest administration, user management, rankings, solved and pending problems, the scoreboard, and more. _Frontend_ communicates with _Backend_ to compile and run programs. Written in PHP+MySQL.  
- **Backend**: The evaluation subsystem, written in Go.  
  - **[Grader](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/Grader.md)**: Responsible for maintaining the submission queue, sending them to one or more _Runners_, receiving responses, and determining a verdict.  
  - **[Runner](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/Runner.md)**: A decentralized, asynchronous system for compiling and executing programs. Other systems communicate with _Runner_ through a RESTful API. It knows how to compile, execute, and pass input to user-submitted programs and verify their correctness. It is essentially a distributed and user-friendly frontend for _Minijail_.  
  - **Minijail**: A fork of the Linux sandbox used in Chrome OS. It can execute code in C, C++, Perl, Python, Ruby, Java, and Karel. Written in C.  

For more details, you can refer to two papers published in the IOI journal:  

- Luis Héctor CHÁVEZ, Alan GONZÁLEZ, Joemmanuel PONCE.  
  [omegaUp: Cloud-Based Contest Management System and Training Platform in the Mexican Olympiad in Informatics](http://ioinformatics.org/oi/pdf/v8_2014_169_178.pdf).  
- Luis Héctor CHÁVEZ.  
  [libinteractive: A Better Way to Write Interactive Tasks](https://ioinformatics.org/journal/v9_2015_3_14.pdf).  

## Code Structure  

The code is located in `/opt/omegaup`. The development installation includes two pre-configured accounts: 
| Username  | Password  | Role        |
|-----------|----------|------------|  
| `omegaup` | `omegaup` | Administrator |  
| `user`    | `user`    | Regular user |

These are the main directories actively used in development:  

- **[frontend/database](https://github.com/omegaup/omegaup/tree/main/frontend/database)**: Contains the main SQL file for constructing the database schema, along with all SQL files added when modifying the database.  
- **[frontend/server/src](https://github.com/omegaup/omegaup/tree/main/frontend/server/src)**: Contains all PHP classes and server-related folders, including:  
  - **[frontend/server/src/DAO](https://github.com/omegaup/omegaup/tree/main/frontend/server/src/DAO)**: Data Access Object (DAO) classes for interacting with the database, storing, and retrieving object instances via MySQL queries.  
  - **[frontend/server/src/DAO/VO](https://github.com/omegaup/omegaup/tree/main/frontend/server/src/DAO/VO)**: Value Object (VO) classes used to construct various objects needed in omegaUp. These are auto-generated and do not need manual modification.  
  - **[frontend/server/src/DAO/Base](https://github.com/omegaup/omegaup/tree/main/frontend/server/src/DAO/Base)**: Base objects containing core methods for database interaction, such as creating, updating, deleting, and retrieving records for each DAO object. These are also auto-generated.  
  - **[frontend/server/src/Controllers](https://github.com/omegaup/omegaup/tree/main/frontend/server/src/Controllers)**: API controller classes that use DAOs to retrieve and manipulate requested information.  
- **[frontend/tests](https://github.com/omegaup/omegaup/tree/main/frontend/tests)**: Contains PHP unit test classes for controllers and Python tests for user interface interactions.  
- **[frontend/www](https://github.com/omegaup/omegaup/tree/main/frontend/www)**: Contains TypeScript and Vue.js files related to the frontend. TypeScript files handle API calls to controllers and process received data. Vue.js files build components that display information to users using HTML and CSS. Each Vue component includes unit tests to check proper visibility, event emission, and expected behavior.  
- **[frontend/templates](https://github.com/omegaup/omegaup/tree/main/frontend/templates)**: Contains internationalization files for English, Spanish, and Portuguese, as well as the main template loading libraries used across all views (e.g., Bootstrap).  

### You may find these useful:  

- [Coding Guidelines](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/Coding-guidelines-(English-version).md).  
- [Useful Development Commands](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/Useful-Commands-for-Development.md).  

## Design Decisions  

- **Encryption for everything**: All communication with omegaUp and its subsystems must be encrypted, both client-to-server and between components. This prevents cheating in contests (e.g., packet sniffing in a programming competition) and mitigates attacks like Firesheep.  
- **OAuth & Identity Management**: We support federated authentication to minimize password use. Users can link multiple identities (e.g., a student’s school email with an external OpenID account). 
- **Programming Languages**:  
  - Backend: Go/C  
  - Frontend: PHP+MySQL  
  - UX: Vue.js+TypeScript  

### Next steps

| Topic | Description |  
| ---------------------------- | ------------------------------------------------------------ |  
| [How to Start Developing](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/How-to-Make-a-Pull-Request.md) | Git configuration and how to submit a PR. |  
| [Architecture](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/Architecture.md) | Software architecture of omegaUp.com. |  
| [Release and Deployment](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/Release-&-deployment.md) | How and when deployments occur. |  
