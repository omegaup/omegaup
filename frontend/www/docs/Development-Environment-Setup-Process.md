## Development Environment
Install [`docker-compose`](https://docs.docker.com/compose/install/).

If you are running linux, after installing [`docker-compose`](https://docs.docker.com/compose/install/) run `sudo usermod -a -G docker $USER`, log out and log back in so you can start running docker commands.

## `git` Command Line Configuration 

Once docker is installed, fork [omegaup/omegaup](https://github.com/omegaup/omegaup) and clone your fork locally:

```shell
git clone --recurse-submodules https://github.com/<YOUR GITHUB USER>/omegaup
cd omegaup
```

After that's done, run the following on the root path:
```shell
git submodule update --init --recursive
```

If you are not well-versed with Git then you can read the [tutorial](https://github.com/shekhargulati/git-the-missing-tutorial).


## Bring up the docker container

Inside (`/omegaup`) run

```shell
docker-compose pull  # Only necessary the first time, or t
docker-compose up --no-build
```

after a few minutes (2-10 minutes), you should be able to access your local omegaUp instance [http://localhost:8001](http://localhost:8001). Normally the signal that indicates that the container is ready is that the previous command shows something similar to:

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

In order to open the console and run command inside the container you have to run:

```shell
docker exec -it omegaup_frontend_1 /bin/bash
```

## Solutions to Common Issues

If your browser keeps ching `http` to `https`, you can disable the security policies for `localhost`. [See this.](https://hmheng.medium.com/exclude-localhost-from-chrome-chromium-browsers-forced-https-redirection-642c8befa9b).

-------------------

When pushing changes to GitHub a series of errors can appear. Among the most common are the following:

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
error: failed to push some refs to 'https://github.com/user/omegaup'
```
This error indicates that MySQL is not installed. To fix it, install outside the container:
```shell
sudo apt-get install mysql-client
```

In case MySQL is already installed and you get the following error:

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
error: failed to push some refs to 'https://github.com/user/omegaup'
```
That means MySQL is not correctly configured. To fix that run the following script (outside the container as well):
```shell
cat > ~/.mysql.docker.cnf <<EOF
[client]
port=13306
host=127.0.0.1
protocol=tcp
user=root
password=omegaup
EOF
ln -sf ~/.mysql.docker.cnf .my.cnf
```

## Authentication

Once omegaup is running on your local environment, you can access `http://localhost:8001/` to se the website. Use the following credentials to log in:

* `omegaup` (password `omegaup`): Admin user.
* `user` (password `user`): Normal user.

You can create new users as well. It won't ask for email verification, so you can use any email address.