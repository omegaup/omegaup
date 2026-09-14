#!/usr/bin/python3
# -*- coding: utf-8 -*-
# type: ignore

'''Run Selenium end-to-end tests.'''

import os.path
import re

from selenium.common.exceptions import TimeoutException
from selenium.webdriver.common.by import By

_DEFAULT_TIMEOUT = 5  # seconds
_ROOT = os.path.abspath(os.path.join(__file__, '..', '..'))
_EMPTY_WORLD = '''
<ejecucion>
  <condiciones instruccionesMaximasAEjecutar="10000000" longitudStack="65000"></condiciones>
  <mundos>
    <mundo nombre="mundo_0" ancho="100" alto="100">
    </mundo>
  </mundos>
  <programas tipoEjecucion="CONTINUA" intruccionesCambioContexto="1" milisegundosParaPasoAutomatico="0">
    <programa nombre="p1" ruta="{$2$}" mundoDeEjecucion="mundo_0" xKarel="1" yKarel="1" direccionKarel="NORTE" mochilaKarel="0"></programa>
  </programas>
</ejecucion>'''  # noqa


def _init(driver, world, code, delay=1):
    '''Replaces the current program code with |code|.'''

    driver.browser.execute_script('window.state.init(%r, %r);' % (world, code))
    driver.browser.find_element(By.ID, 'retraso_txt').clear()
    driver.browser.find_element(By.ID, 'retraso_txt').send_keys(str(delay))


def _clean_state(driver):
    '''Cleans the state of the runtime.'''

    driver.browser.find_element(By.ID, 'worldclean').click()
    driver.browser.execute_script('window.state.cleanLog();')


def _wait_for_log_message(driver, message):
    '''Waits until |message| is posted to the log.'''

    try:
        driver.wait.until(lambda _: re.search(
            r'^.*%s.*$' % re.escape(message),
            driver.browser.find_element(By.CSS_SELECTOR, '#mensajes>p').text))
    except TimeoutException as timeout:
        timeout.msg = driver.browser.find_element(By.ID, 'mensajes').text
        raise timeout


def test_smoke(driver):
    '''Tests basic functionality.'''

    driver.browser.get(driver.url('index.html'))
    _init(driver, _EMPTY_WORLD, '''iniciar-programa
inicia-ejecucion
    avanza;
    apagate;
termina-ejecucion
finalizar-programa''')

    # Initial conditions.
    _clean_state(driver)
    driver.assert_script_equal('window.state.mundo.i', 1)
    driver.assert_script_equal('window.state.mundo.j', 1)

    # Compile.
    _clean_state(driver)
    driver.browser.find_element(By.ID, 'compilar').click()
    _wait_for_log_message(driver, 'Programa compilado (sintaxis pascal)',)

    # Execute.
    _clean_state(driver)
    driver.browser.find_element(By.ID, 'ejecutar').click()
    _wait_for_log_message(driver, 'Ejecución terminada')
    driver.assert_script_equal('window.state.mundo.i', 2)
    driver.assert_script_equal('window.state.mundo.j', 1)

    # Step-by-step.
    _clean_state(driver)
    driver.browser.find_element(By.ID, 'paso').click()
    driver.browser.find_element(By.ID, 'paso').click()
    driver.browser.find_element(By.ID, 'paso').click()
    _wait_for_log_message(driver, 'Ejecución terminada',)
    driver.assert_script_equal('window.state.mundo.i', 2)
    driver.assert_script_equal('window.state.mundo.j', 1)

    # See-the-future.
    _clean_state(driver)
    driver.browser.find_element(By.ID, 'futuro').click()
    _wait_for_log_message(driver, 'Ejecución terminada',)
    driver.assert_script_equal('window.state.mundo.i', 2)
    driver.assert_script_equal('window.state.mundo.j', 1)

    # Restore everything.
    _clean_state(driver)
    driver.assert_script_equal('window.state.mundo.i', 1)
    driver.assert_script_equal('window.state.mundo.j', 1)
