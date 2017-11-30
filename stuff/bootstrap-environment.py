#!/usr/bin/python3

'''
A tool to run an import script to populate the database with objects.
'''


import argparse
import grp
import json
import logging
import os
import requests
import shutil
import subprocess
import time


OMEGAUP_ROOT = os.path.abspath(os.path.join(__file__, '..', '..'))
OMEGAUP_RUNTIME_ROOT = '/var/lib/omegaup'


class ScopedFiles:
  '''
  A RAII wrapper over a map of POST names to filenames. Upon entering, it
  creates a mapping from POST names to Python file objects, which are closed on
  exit.
  '''
  def __init__(self, files):
    self.__files = files
    self.files = None

  def __enter__(self):
    if self.__files:
      self.files = {}
      for name, filename in self.__files.items():
        self.files[name] = open(os.path.join(OMEGAUP_ROOT, filename), 'rb')
    return self

  def __exit__(self, exception_type, exception_value, traceback):
    if self.files:
      for _, f in self.files.items():
        f.close()


class Session:
  '''
  A context manager that represents an omegaUp user session.

  Within the context, API requests can be performed as the user.
  '''
  def __init__(self, args, username, password):
    self.jar = requests.cookies.RequestsCookieJar()
    self.url = args.root_url.rstrip('/')
    result = self.request('/user/login/',
                          {'usernameOrEmail': username, 'password': password})
    assert result['status'] == 'ok'

  def __enter__(self):
    return self

  def __exit__(self, exception_type, exception_value, traceback):
    pass

  def request(self, api, data=None, files=None):
    logging.debug('Requesting %s: %s', api, data)
    opened_files = None
    if data:
      with ScopedFiles(files) as f:
        r = requests.post(self.url + '/api' + api, files=f.files,
                          data=data, cookies=self.jar)
    else:
      r = requests.get(self.url + '/api' + api, cookies=self.jar)
    for name, value in r.cookies.items():
      self.jar[name] = value
    if r.status_code == 404:
      return None
    result = r.json()
    logging.debug('Result: %s', result)
    return result


def _run_script(path, args, now):
  '''Runs a single script specified in |path|'''
  with open(path, 'r') as f:
    script = json.load(f)

  for session in script:
    with Session(args, session['username'], session['password']) as s:
      for request in session['requests']:
        # First try to see if the resource has already been created.
        if request['api'] == '/problem/create':
          if s.request('/problem/details/',
                       {'problem_alias': request['params']['alias']}):
            logging.warn('Problem %s exists, skipping',
                         request['params']['alias'])
            continue
        elif request['api'] == '/contest/create':
          if s.request('/contest/adminDetails/',
                       {'contest_alias': request['params']['alias']}):
            logging.warn('Contest %s exists, skipping',
                         request['params']['alias'])
            continue
        # Date parameters need some special handling
        for k, v in request['params'].items():
          if type(v) == str and v.startswith('$NOW$'):
            # Replace $NOW$ with the current timestamp, adding an optional
            # number of seconds.
            tokens = v.split('+')
            timestamp = now
            if len(tokens) == 2:
              timestamp += int(tokens[1])
            v = int(timestamp)
            request['params'][k] = v
        result = s.request(
            request['api'], data=request['params'],
            files=(request['files'] if 'files' in request else None))
        assert result['status'] == 'ok', request


def main():
  parser = argparse.ArgumentParser()
  parser.add_argument('--root-url', type=str, default='http://localhost')
  parser.add_argument('--verbose', action='store_true')
  parser.add_argument('--purge', action='store_true',
      help='Also purges and re-creates the database')
  parser.add_argument('--mysql-config-file', default=None,
      help='.my.cnf file that stores credentials')
  parser.add_argument('--username', default=None, help='MySQL username')
  parser.add_argument('--password', default=None, help='MySQL password')
  parser.add_argument('scripts', metavar='SCRIPT', type=str, nargs='*',
      default=[os.path.join(OMEGAUP_ROOT, 'stuff/bootstrap.json')],
      help='The JSON script with requests to pre-populate the database')
  args = parser.parse_args()
  now = time.time()

  if args.verbose:
    logging.getLogger().setLevel('DEBUG')

  if args.purge:
    logging.info('Purging old problems')
    # Removing directories requires the user to be in the 'www-data' group.
    can_delete = 'www-data' in (grp.getgrgid(grid).gr_name for grid in
                                os.getgroups())
    for subdir in ('problems.git', 'problems'):
      for alias in os.listdir(os.path.join(OMEGAUP_RUNTIME_ROOT,
                                           subdir)):
        path = os.path.join(OMEGAUP_RUNTIME_ROOT, subdir, alias)
        logging.debug('Removing %s', path)
        if can_delete:
          shutil.rmtree(path)
        else:
          subprocess.check_call(['/usr/bin/sudo', '/bin/rm', '-rf', path])
    logging.info('Purging database')
    db_migrate_args = [os.path.join(OMEGAUP_ROOT, 'stuff/db-migrate.py')]
    for name, value in [('--username', args.username),
                        ('--password', args.password),
                        ('--mysql-config-file', args.mysql_config_file)]:
      if value is not None:
        db_migrate_args.extend([name, value])
    subprocess.check_call(db_migrate_args + ['purge'])
    subprocess.check_call(db_migrate_args + ['migrate', '--development-environment'])

  for path in args.scripts:
    _run_script(path, args, now)

if __name__ == '__main__':
  main()


# vim: expandtab shiftwidth=2 tabstop=2
