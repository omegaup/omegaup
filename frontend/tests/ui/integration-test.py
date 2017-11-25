import argparse
import hashlib
import os
import os.path
import time

from selenium import webdriver
from selenium.webdriver.firefox.options import Options
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC


class OmegaUpTest:
  def __init__(self, driver, url):
    self.driver = driver
    self.url = url
    self.driver.implicitly_wait(10)

  def resource(self, resource):
    return os.path.normpath(os.path.join(os.getcwd(), '../resources/', resource))

  def home(self):
    self.driver.get(self.url)

  def login(self, username, password):
    login_link = self.driver.find_element_by_partial_link_text('Log in')
    login_link.click()

    username_input = self.driver.find_element_by_name('user')
    username_input.send_keys('omegaup')
    password_input = self.driver.find_element_by_name('pass')
    password_input.send_keys('omegaup')
    username_input.submit()

  def create_user(self):
    login_link = self.driver.find_element_by_partial_link_text('Sign up')
    login_link.click()

    # Data setup
    self.username = hashlib.md5('username-%s' % time.time()).hexdigest()
    self.email = hashlib.md5('email-%s' % time.time()).hexdigest() + '@email.com'
    self.password = hashlib.md5('password').hexdigest()

    # UI setup
    username_input = self.driver.find_element_by_name('reg_username')
    email_input = self.driver.find_element_by_name('reg_email')
    password1_input = self.driver.find_element_by_name('reg_pass')
    password2_input = self.driver.find_element_by_name('reg_pass2')

    # Submit form
    username_input.send_keys(self.username)
    email_input.send_keys(self.email)
    password1_input.send_keys(self.password)
    password2_input.send_keys(self.password)
    username_input.submit()

    username_label = self.driver.find_element_by_class_name('username')
    assert username_label.text == self.username

  def create_problem(self):
    nav_problems = self.driver.find_element_by_css_selector('#nav-problems')
    nav_problems.click()
    nav_links = self.driver.find_elements_by_css_selector('#nav-problems a')
    nav_link = None
    for link in nav_links:
      if link.get_attribute('innerText') == 'Create a problem':
        nav_link = link
        break
    assert nav_link
    nav_link.click()

    self.problem_alias = hashlib.md5('problem-%s' % time.time()).hexdigest()
    self.driver.find_element_by_name('title').send_keys(self.problem_alias)
    # Alias should be set automatically
    self.driver.find_element_by_name('source').send_keys('test')
    contents_element = self.driver.find_element_by_name('problem_contents')
    contents_element.send_keys(self.resource('triangulos.zip'))
    contents_element.submit()

    self.driver.find_element_by_xpath('//span[text()="%s"]' % ('Edit problem %s' % self.problem_alias))

  def open_problem(self):
    self.driver.find_element_by_link_text('Go to problem').click()

  def create_run(self):
    self.driver.find_element_by_link_text('New submission').click()
    self.driver.find_element_by_css_selector('input[type="file"]').send_keys(self.resource('solution.cpp'))
    self.driver.find_element_by_css_selector('#submit input[type="submit"]').click()

  def verify_run(self):
    status = self.driver.find_element_by_css_selector('td.status span')
    assert status.get_attribute('innerText') == 'new', 'Status could not be found'

  def create_contest(self):
    # Navigate to create contest
    nav_contests = self.driver.find_element_by_css_selector('#nav-contests')
    nav_contests.click()
    nav_links = self.driver.find_elements_by_css_selector('#nav-contests a')
    nav_link = None
    for link in nav_links:
      if link.get_attribute('innerText') == 'Create a new contest':
        nav_link = link
        break
    assert nav_link
    nav_link.click()

    # Fill in form elements
    self.contest_alias = hashlib.md5('contest-%s' % time.time()).hexdigest()
    self.contest_description = 'Contest description[%s]' % self.contest_alias
    title_element = self.driver.find_element_by_name('title')
    title_element.send_keys(self.contest_alias)
    self.driver.find_element_by_name('alias').send_keys(self.contest_alias)
    self.driver.find_element_by_name('description').send_keys(self.contest_description)
    # TODO: Set date properly
    title_element.submit()

  def add_problem_to_contest(self):
    # We should be in the problem selection form now
    problem_element = self.driver.find_element_by_name('problems')
    problem_element.send_keys(self.problem_alias)
    problem_element.submit()

  def go_to_contest(self):
    self.driver.find_element_by_link_text('Arena').click()
    self.driver.find_element_by_link_text(self.contest_alias).click()

  def open_contest(self):
    description_element = self.driver.find_element_by_css_selector('#description')
    assert description_element.get_attribute('innerText') == self.contest_description
    self.driver.find_element_by_css_selector('button[type="submit"]').click()

  def verify_contest_summary(self):
    assert self.driver.find_element_by_link_text(self.username), \
           "Should be able to find the username as the organizer"
    

  def open_contest_problem(self):
    self.driver.find_element_by_link_text('A. %s' % self.problem_alias).click()

  def create_run_in_contest(self):
    self.create_run()

  def verify_run_in_contest(self):
    self.verify_run()

  def run(self):
    self.home()
    #self.login('omegaup', 'omegaup')
    self.create_user()
    self.create_problem()
    self.open_problem()
    self.create_run()
    self.verify_run()
    self.create_contest()
    self.add_problem_to_contest()
    self.go_to_contest()
    self.open_contest()
    self.verify_contest_summary()
    self.open_contest_problem()
    self.create_run_in_contest()
    self.verify_run_in_contest()


def main():
  parser = argparse.ArgumentParser(description='Process some integers.')
  parser.add_argument('--url', help='the entry point URL',
                      default='http://localhost:8080/')
  parser.add_argument('--browser', help='the browser to use: chrome|firefox',
                      default='chrome')
  args = parser.parse_args()
  print(args)

  if args.browser == 'chrome':
    driver = webdriver.Chrome()
  elif args.browser == 'firefox':
    options = Options()
    options.add_argument('-headless')
    driver = webdriver.Firefox(firefox_options=options)
  else:
    raise 'Must specify which browser to use'

  try:
    test_case = OmegaUpTest(driver, args.url)
    test_case.run()
  finally:
    driver.quit()


if __name__ == "__main__":
  main()