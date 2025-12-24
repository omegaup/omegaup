Implementing a badge is quite simple, just follow these steps:

1. Create an alias for the badge. This alias must be unique and must not exceed 32 characters.

2. Create a folder in [`/frontend/badges/`](https://github.com/omegaup/omegaup/tree/master/frontend/badges), with a name matching the alias of the badge to be implemented. From now on, this folder will be referred to as `badgeFolder`.

3. If the badge has a custom icon, the `SVG` file should be added to `badgeFolder`, named `icon.svg`.

4. Create a file called `query.sql` in `badgeFolder`. This file must contain the `SQL (MySQL)` statement that selects the `user_id`s of users who should receive the proposed badge. To follow this logic, it's necessary to understand the [omegaUp database schema](https://github.com/omegaup/omegaup/blob/master/frontend/database/schema.sql).

5. Create the [`localizations.json`](https://github.com/omegaup/omegaup/blob/master/frontend/badges/legacyUser/localizations.json) file inside `badgeFolder`. This must contain the translations of the badge's name and description in Spanish (es), English (en), and Portuguese (pt). Remember that the maximum length for a badge name is **50 characters**.

6. To load the translations from `localizations.json` into the corresponding files, you need to run the script: `./stuff/lint.sh`.

7. A `test.json` file must be created inside `badgeFolder`. This will specify through the `testType` field how the badge's unit tests will be executed:

   - `"testType": "apicall"` Consists of using the controller APIs to create relevant data (problems, users, contests, runs, etc). To do this, create an "actions" field with an array of all actions to be executed, which can be:

        - `changeTime`: allows modifying the system date.
        - `apicalls`: allows calling a specific API, also setting the username and password of the calling user and the parameters to be passed. The APIs are all those public static functions with the api prefix within each of the controllers located in [this folder](https://github.com/omegaup/omegaup/tree/master/frontend/server/src/Controllers).
        - `scripts`: allows executing an omegaUp cron script (`aggregate_feedback.py`, `assign_badges.py`, `update_ranks.py`). These scripts are located in [this folder](https://github.com/omegaup/omegaup/tree/master/stuff/cron).

        In this type of test, you must add an `expectedResults` field at the end, which should contain the usernames of those users who will receive the badge.

        Example:

      - https://github.com/omegaup/omegaup/blob/master/frontend/badges/coderOfTheMonth/test.json

   - `"testType": "phpunit"` Consists of creating a unit test with filename equal to `badge alias + "Test.php"`. This file should be saved in the [badges test folder](https://github.com/omegaup/omegaup/tree/master/frontend/tests/badges) and should follow the classic structure of unit tests already implemented in omegaUp, it can even make use of the available [factories](https://github.com/omegaup/omegaup/tree/master/frontend/tests/factories).
       
        Examples:

     - https://github.com/omegaup/omegaup/blob/master/frontend/badges/100solvedProblems/test.json
     - https://github.com/omegaup/omegaup/blob/master/frontend/tests/badges/Badge_100solvedProblemsTest.php

    Each option has its advantages and disadvantages. We suggest using phpunit for badges with many identical API calls; in any other case, it's better to use apicalls.

8. Finally, you need to run the tests to verify that the implemented badge meets the specified criteria and that the proposed query and unit test execute successfully. For this, you can use any of the following scripts:
   - `./vendor/bin/phpunit --bootstrap frontend/tests/bootstrap.php --configuration frontend/tests/phpunit.xml frontend/tests/ --debug`
   - `./vendor/bin/phpunit --bootstrap frontend/tests/bootstrap.php --configuration frontend/tests/phpunit.xml frontend/tests/badges/ --debug`
   - `./stuff/runtests.sh`

9. If no errors are thrown, you can now submit your Pull Request to add the new badge!

Here are some Pull Requests already submitted for creating badges that you can use as a guide:
- [Contest Administrator](https://github.com/omegaup/omegaup/pull/2602/files)
- [Virtual Contest Administrator](https://github.com/omegaup/omegaup/pull/2603/files)

If you have any questions, don't hesitate to contact us :)