## Content

[También disponible en Español](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/C%C3%B3mo-empezar-a-desarrollar.md)

- [Installing Development Environment](#installing-development-environment-docker)
- [Codebase structure](#codebase-structure)
- [How To Update Your Copy of omegaUp](#how-to-update-your-copy-of-omegaup)
- [How To Make Changes To The Code](#how-to-make-changes-to-the-code)
- [The Web App Is Not Showing My Changes!](#the-web-app-is-not-showing-my-changes-)
- [Troubleshooting](#troubleshooting)

Before starting, if you are not confident using Git, we recommend you read [this tutorial](https://github.com/shekhargulati/git-the-missing-tutorial) 

## Videotutorial
[![Videotutorial](http://img.youtube.com/vi/H1PG4Dvje88/0.jpg)](http://www.youtube.com/watch?v=H1PG4Dvje88 "OmegaUp Localhost Setup Video Tutorial")

## Installing Development Environment

### Prerequisites:
* Install the [docker engine](https://docs.docker.com/engine/install/ubuntu/#install-using-the-repository).
* Install [docker compose 2](https://docs.docker.com/compose/install/linux/#install-the-plugin-manually) or if you have already installed `docker compose 1`  you can migrate using the following [instructions](https://docs.docker.com/compose/install/linux/#install-using-the-repository) .

After installing docker, fork the [omegaup/omegaup](https://github.com/omegaup/omegaup) repository, clone it to an empty directory

```shell
git clone --recurse-submodules https://github.com/YOURUSERNAME/omegaup
cd omegaup
```

Once you have cloned the repository, inside the directory run:

```
git submodule update --init --recursive
```

and then, in the same directory (`omegaup/`) run:

```shell
docker-compose pull  # only needed the first time, or when the following command complains.
docker-compose up --no-build
```

after some time (2-10 minutes), you should be able to access omegaUp via [http://localhost:8001](http://localhost:8001). Normally what signals when the container is ready is an output similar to

```
frontend_1     | Child frontend:
frontend_1     |        1550 modules
frontend_1     |     Child HtmlWebpackCompiler:
frontend_1     |            1 module
frontend_1     | Child style:
frontend_1     |        1 module
frontend_1     |     Child extract-text-webpack-plugin node_modules/extract-text-webpack-plugin/dist node_modules/css-loader/dist/cjs.js!node_modules/sass-loader/dist/cjs.js!frontend/www/sass/main.scss:
frontend_1     |            2 modules
frontend_1     | Child grader:
frontend_1     |        1131 modules
frontend_1     |     Child vs/editor/editor:
frontend_1     |            36 modules
frontend_1     |     Child vs/language/css/cssWorker:
frontend_1     |            67 modules
frontend_1     |     Child vs/language/html/htmlWorker:
frontend_1     |            61 modules
frontend_1     |     Child vs/language/json/jsonWorker:
frontend_1     |            60 modules
frontend_1     |     Child vs/language/typescript/tsWorker:
frontend_1     |            41 modules
```

To run commands inside the container, you can run

```shell
docker-compose exec frontend /bin/bash
```

to open a commandline console.

## Codebase structure

omegaUp code can be found at `/opt/omegaup` inside the contianer. The dev installation has two user accounts preconfigured by default: `omegaup` (admin) y `user` (normal user). Their passwords `omegaup` and `user`, respectively.

These are the directories that we are actively using in the development:

* [frontend/server/controllers](https://github.com/omegaup/omegaup/tree/main/frontend/server/controllers): The controllers do the business logic and expose the server API.
* [frontend/server/libs](https://github.com/omegaup/omegaup/tree/main/frontend/server/libs): Libraries and utilities.
* [frontend/server/libs/dao](https://github.com/omegaup/omegaup/tree/main/frontend/server/libs/dao): Data Access Objects [DAO] and Value Objects [VO]. Classes used to represent database schemes and facilitate their use by the controllers.
* [frontend/templates](https://github.com/omegaup/omegaup/tree/main/frontend/templates): Smarty templates used to generate the HTML that is displayed to users. Also here are the internationalization files for English, Spanish and Portuguese.
* [frontend/www](https://github.com/omegaup/omegaup/tree/master/frontend/www): The complete contents of the Internet page.

For more details, see [here](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/Frontend.md).

## How To Update Your Copy of omegaUp

Before you start making changes to the code, you should update the code to its latest version, that can be done by following [these steps](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/How-to-Update-Your-Local-Copy-of-omegaup-Before-Making-Changes.md).

## How To Make Changes To The Code

When you have made changes that you wish to propose to omegaUp repository, follow [these steps](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/How-to-Make-a-Pull-Request-(English.md)).

## The Web App Is Not Showing My Changes!

You need to run `cd /opt/omegaup && yarn run dev`.

## Troubleshooting

Here we list some common problems that people encounter while installing the dev environment:

If your browser changes http by https protocol, you can disable the security policies for localhost. [More info...](https://hmheng.medium.com/exclude-localhost-from-chrome-chromium-browsers-forced-https-redirection-642c8befa9b).

-------------------

If you encounter any problems not covered in this section, please file an issue at [https://github.com/omegaup/deploy/issues](https://github.com/omegaup/deploy/issues) with your reproduction steps and the error message you are getting.

-------------------

When running scripts, there may be some errors. The most common look like MySQL-related issues:

```shell
Traceback (most recent call last):
  File "/home/ubuntu/dev/omegaup/stuff/policy-tool.py", line 124, in <module>
    main()
  File "/home/ubuntu/dev/omegaup/stuff/policy-tool.py", line 120, in main
    args.func(args, auth)
  File "/home/ubuntu/dev/omegaup/stuff/policy-tool.py", line 66, in validate
    for statement_type, git_object_id in _missing(args, auth):
  File "/home/ubuntu/dev/omegaup/stuff/policy-tool.py", line 53, in _missing
    if int(database_utils.mysql(
  File "/home/ubuntu/dev/omegaup/stuff/database_utils.py", line 75, in mysql
    return subprocess.check_output(args, universal_newlines=True)
  File "/usr/lib/python3.8/subprocess.py", line 411, in check_output
    return run(*popenargs, stdout=PIPE, timeout=timeout, check=True,
  File "/usr/lib/python3.8/subprocess.py", line 489, in run
    with Popen(*popenargs, **kwargs) as process:
  File "/usr/lib/python3.8/subprocess.py", line 854, in __init__
    self._execute_child(args, executable, preexec_fn, close_fds,
  File "/usr/lib/python3.8/subprocess.py", line 1702, in _execute_child
    raise child_exception_type(errno_num, err_msg, err_filename)
FileNotFoundError: [Errno 2] No such file or directory: '/usr/bin/mysql'
```

or


```shell
mysql: [Warning] Using a password on the command line interface can be insecure.
ERROR 2002 (HY000): Can't connect to local MySQL server through socket '/var/run/mysqld/mysqld.sock' (2)
Traceback (most recent call last):
  File "/home/ubuntu/dev/omegaup/stuff/policy-tool.py", line 124, in <module>
    main()
  File "/home/ubuntu/dev/omegaup/stuff/policy-tool.py", line 120, in main
    args.func(args, auth)
  File "/home/ubuntu/dev/omegaup/stuff/policy-tool.py", line 66, in validate
    for statement_type, git_object_id in _missing(args, auth):
  File "/home/ubuntu/dev/omegaup/stuff/policy-tool.py", line 53, in _missing
    if int(database_utils.mysql(
  File "/home/ubuntu/dev/omegaup/stuff/database_utils.py", line 75, in mysql
    return subprocess.check_output(args, universal_newlines=True)
  File "/usr/lib/python3.8/subprocess.py", line 411, in check_output
    return run(*popenargs, stdout=PIPE, timeout=timeout, check=True,
  File "/usr/lib/python3.8/subprocess.py", line 512, in run
    raise CalledProcessError(retcode, process.args,
subprocess.CalledProcessError: Command '['/usr/bin/mysql', '--user=root', '--password=omegaup', 'omegaup', '-NBe', 'SELECT COUNT(*) FROM `PrivacyStatements` WHERE `type` = "contest_optional_consent" AND `git_object_id` = "534d173d57e3814174ac02cc25f92e4253829d9c";']' returned non-zero exit status 1.
```

This indicates the script was accidentally run outside the container. In order to fix this, you should be running the command inside the container.

## Authentication

Once omegaUp is running in your container, and you can access to http://localhost:8001/ to visit the website, you can login with:

* `omegaup` (password `omegaup`): User with sysadmin privileges.
* `user` (password `user`): User with regular privileges.

There are a huge list of users we use in tests:

| User | Password |
| -- | -- |
| test_user_0 | test_user_0 |
| test_user_1 | test_user_1 |
| test_user_2 | test_user_2 |
| test_user_3 | test_user_3 |
| test_user_4 | test_user_4 |
| test_user_5 | test_user_5 |
| test_user_6 | test_user_6 |
| test_user_7 | test_user_7 |
| test_user_8 | test_user_8 |
| test_user_9 | test_user_9 |
| course_test_user_0 | course_test_user_0 |
| course_test_user_1 | course_test_user_1 |
| course_test_user_2 | course_test_user_2 |

Feel free to create as much users as you need to test your changes. In development mode,the email verification is disabled, so you can use dummy emails.