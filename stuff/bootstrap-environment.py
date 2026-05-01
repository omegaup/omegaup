#!/usr/bin/env python3
# pylint: disable=invalid-name
# This program is intended to be invoked from the console, not to be used as a
# module.
'''
A tool to run an import script to populate the database with objects.
'''

import argparse
import errno
import grp
import json
import logging
import os
import shutil
import subprocess
import time
import sys

from typing import Any, BinaryIO, Dict, Mapping, ItemsView, Optional

from dataset_generator.generate_synthetic_dataset import seed_synthetic

import requests

OMEGAUP_ROOT = os.path.abspath(os.path.join(__file__, '..', '..'))
sys.path.insert(0, os.path.join(OMEGAUP_ROOT, "stuff"))
OMEGAUP_RUNTIME_ROOT = '/var/lib/omegaup'


class ScopedFiles:
    '''
    A RAII wrapper over a map of POST names to filenames. Upon entering, it
    creates a mapping from POST names to Python file objects, which are closed
    on exit.
    '''
    def __init__(self, files: Optional[Mapping[str, str]]):
        self.__files = files
        self.files: Optional[Dict[str, BinaryIO]] = None

    def __enter__(self) -> 'ScopedFiles':
        if self.__files:
            self.files = {}
            for name, filename in self.__files.items():
                self.files[name] = open(os.path.join(OMEGAUP_ROOT, filename),
                                        'rb')
        return self

    def __exit__(self, *exc_info: Any) -> None:
        if self.files:
            for _, f in self.files.items():
                f.close()


class Session:
    '''
    A context manager that represents an omegaUp user session.

    Within the context, API requests can be performed as the user.
    '''
    def __init__(
            self,
            args: argparse.Namespace,
            username: Optional[str],
            password: Optional[str],
            token: Optional[str],
    ):
        # This is a false positive.
        # pylint: disable=abstract-class-instantiated
        self.jar = requests.cookies.RequestsCookieJar()
        self.url = args.root_url.rstrip('/')
        if token is not None:
            self.token: Optional[str] = token
        else:
            self.token = None
            request: Dict[str, Any] = {
                'api': '/user/login',
                'params': {
                    'usernameOrEmail': username,
                    'password': password,
                }
            }
            result = self.request(request['api'], request['params'])
            assert result and result['status'] == 'ok', (request, result)

    def __enter__(self) -> 'Session':
        return self

    def __exit__(self, *exc_info: Any) -> None:
        pass

    def request(
            self,
            api: str,
            data: Optional[Dict[str, str]] = None,
            files: Optional[Mapping[str, str]] = None,
    ) -> Optional[Dict[str, Any]]:
        '''Performs an API request.'''
        logging.debug('Requesting %s: %s', api, data)
        headers = {}
        if self.token is not None:
            headers['Authorization'] = f'token {self.token}'
        if data:
            with ScopedFiles(files) as f:
                req = requests.post(f'{self.url}/api{api}',
                                    files=f.files,
                                    data=data,
                                    cookies=self.jar,
                                    headers=headers,
                                    timeout=(3, 9))
        else:
            req = requests.get(f'{self.url}/api{api}',
                               cookies=self.jar,
                               headers=headers,
                               timeout=(3, 9))
        cookies: ItemsView[str, str] = req.cookies.items()  # type: ignore
        for name, value in cookies:
            self.jar[name] = value
        if req.status_code == 404:
            return None
        try:
            result: Dict[str, Any] = req.json()
        except:  # noqa: bare-except
            logging.exception('Failed to parse json: %s', req.text)
            raise
        logging.debug('Result: %s', result)
        return result


def _does_resource_exist(s: Session, request: Mapping[str, Any]) -> bool:
    '''Returns whether a resource already exist.'''
    api_endpoint = request['api'].lower()
    if not api_endpoint.endswith('/'):
        api_endpoint += '/'
    if api_endpoint == '/problem/create/':
        if s.request('/problem/details/',
                     {'problem_alias': request['params']['problem_alias']}):
            logging.warning('Problem %s exists, skipping',
                            request['params']['problem_alias'])
            return True
    if api_endpoint == '/contest/create/':
        if s.request('/contest/adminDetails/',
                     {'contest_alias': request['params']['alias']}):
            logging.warning('Contest %s exists, skipping',
                            request['params']['alias'])
            return True
    if api_endpoint == '/course/create/':
        if s.request('/course/adminDetails/',
                     {'alias': request['params']['alias']}):
            logging.warning('Course %s exists, skipping',
                            request['params']['alias'])
            return True
    if api_endpoint == '/course/createassignment/':
        if s.request(
                '/course/assignmentDetails/', {
                    'course': request['params']['course_alias'],
                    'assignment': request['params']['alias']
                }):
            logging.warning('Assignment %s exists, skipping',
                            request['params']['alias'])
            return True
    if api_endpoint == '/user/create/':
        if s.request('/user/profile/',
                     {'username': request['params']['username']}):
            logging.warning('User %s exists, skipping',
                            request['params']['username'])
            return True
    return False


def _process_one_request(s: Session, request: Mapping[str, Any],
                         now: float) -> None:
    '''Invokes a single request specified in |request|.'''
    if _does_resource_exist(s, request):
        return
    # Date parameters need some special handling
    for key, val in request['params'].items():
        if isinstance(val, str) and val.startswith('$NOW$'):
            # Replace $NOW$ with the current timestamp, adding an
            # optional number of seconds.
            tokens = val.split('+')
            timestamp = now
            if len(tokens) == 2:
                timestamp += int(tokens[1])
            val = int(timestamp)
            request['params'][key] = val
    logging.info('invoking one request %r', request)
    result = s.request(
        request['api'],
        data=request['params'],
        files=(request['files'] if 'files' in request else None))
    fail_ok = 'fail_ok' in request and request['fail_ok']
    status = 'error'
    if result and 'status' in result:
        status = result['status']
    if status != 'ok':
        if fail_ok:
            logging.warning('Request %r failed, continuing. '
                            'Result is %r', request, result)
        else:
            assert status == 'ok', (request, result)


def _run_script(path: str, args: argparse.Namespace, now: float) -> None:
    '''Runs a single script specified in |path|'''
    with open(path, 'r', encoding='utf-8') as f:
        script = json.load(f)

    for session in script:
        logging.info('running one session...')
        with Session(args,
                     session.get('username'),
                     session.get('password'),
                     token=session.get('token')) as s:
            for request in session['requests']:
                _process_one_request(s, request, now)


def _purge_old_problems() -> None:
    logging.info('Purging old problems')
    # Removing directories requires the user to be in the 'www-data' group.
    can_delete = 'www-data' in (grp.getgrgid(grid).gr_name
                                for grid in os.getgroups())
    problems_root = os.path.join(OMEGAUP_RUNTIME_ROOT, 'problems.git')
    if not os.path.isdir(OMEGAUP_RUNTIME_ROOT):
        raise RuntimeError('Please run this script inside the VM / container'
                           ) from FileNotFoundError(errno.ENOENT,
                                                    os.strerror(errno.ENOENT),
                                                    OMEGAUP_RUNTIME_ROOT)
    if not os.path.isdir(problems_root):
        return
    for alias in os.listdir(problems_root):
        path = os.path.join(problems_root, alias)
        logging.debug('Removing %s', path)
        if can_delete:
            shutil.rmtree(path)
        else:
            subprocess.check_call(['/usr/bin/sudo', '/bin/rm', '-rf', path])


def _purge_old_submissions() -> None:
    logging.info('Purging old submissions')
    # Removing directories requires the user to be in the 'www-data' group.
    can_delete = 'www-data' in (grp.getgrgid(grid).gr_name
                                for grid in os.getgroups())
    submissions_root = os.path.join(OMEGAUP_RUNTIME_ROOT, 'submissions')
    for root, _, filenames in os.walk(submissions_root):
        for filename in filenames:
            path = os.path.join(root, filename)
            logging.debug('Removing %s', path)
            if can_delete:
                os.unlink(path)
            else:
                subprocess.check_call(
                    ['/usr/bin/sudo', '/usr/bin/unlink', path])


def _main() -> None:
    '''Main entrypoint.'''

    parser = argparse.ArgumentParser()
    parser.add_argument('--root-url',
                        type=str,
                        default='http://localhost:8001/')
    parser.add_argument('--verbose', action='store_true')
    parser.add_argument('--purge',
                        action='store_true',
                        help='Also purges and re-creates the database')
    parser.add_argument('--mysql-config-file',
                        default=None,
                        help='.my.cnf file that stores credentials')
    parser.add_argument('--username', default=None, help='MySQL username')
    parser.add_argument('--password', default=None, help='MySQL password')
    parser.add_argument(
        'scripts',
        metavar='SCRIPT',
        type=str,
        nargs='*',
        default=[os.path.join(OMEGAUP_ROOT, 'stuff/bootstrap.json')],
        help=('The JSON script with requests to '
              'pre-populate the database'))
    parser.add_argument("--seed-mode",
                        choices=["none", "static", "synthetic"],
                        default=os.getenv("SEED_MODE", "none"),
                        help="Data seeding mode.")
    parser.add_argument("--seed-config",
                        default=os.getenv(
                            "SEEDER_CONFIG",
                            "stuff/dataset_generator/settings.yml"),
                        help="Path to synthetic seeder YAML config.")
    parser.add_argument("--seed-env",
                        choices=["development", "testing"],
                        default=os.getenv("SEEDER_ENV", "testing"),
                        help="Synthetic seeder environment for counts.")
    parser.add_argument("--ou-username", default=os.getenv("OMEGAUP_USERNAME"))
    parser.add_argument("--ou-password", default=os.getenv("OMEGAUP_PASSWORD"))
    parser.add_argument("--ou-token", default=os.getenv("OMEGAUP_TOKEN"))
    args = parser.parse_args()
    now = time.time()

    if args.verbose:
        logging.getLogger().setLevel('DEBUG')

    if args.purge:
        _purge_old_problems()
        _purge_old_submissions()

        db_migrate_args = [
            os.path.join(OMEGAUP_ROOT, 'stuff/db-migrate.py'),
            '--kill-blocking-connections',
        ]
        for name, value in [('--username', args.username),
                            ('--password', args.password),
                            ('--mysql-config-file', args.mysql_config_file)]:
            if value is not None:
                db_migrate_args.extend([name, value])
        if args.verbose:
            db_migrate_args.append('--verbose')
        logging.info('Purging database...')
        subprocess.check_call(db_migrate_args + ['purge'])
        logging.info('Migrating database...')
        subprocess.check_call(db_migrate_args +
                              ['migrate', '--development-environment'])

    if args.seed_mode == "synthetic":
        seed_synthetic(
            Session,
            root=OMEGAUP_ROOT,
            config_path=args.seed_config,
            seeder_env=args.seed_env,
            session_args=args,
            ou_username=args.ou_username,
            ou_password=args.ou_password,
            ou_token=args.ou_token,
        )
        return

    for path in args.scripts:
        logging.info('Running script %s...', path)
        _run_script(path, args, now)


if __name__ == '__main__':
    _main()
