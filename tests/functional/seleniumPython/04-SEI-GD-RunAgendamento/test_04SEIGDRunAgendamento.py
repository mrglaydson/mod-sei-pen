# Generated by Selenium IDE
import pytest
import os
import time
import json
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.action_chains import ActionChains
from selenium.webdriver.support import expected_conditions
from selenium.webdriver.support.wait import WebDriverWait
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.desired_capabilities import DesiredCapabilities

class Test04SEIGDRunAgendamento():
  def setup_method(self, method):
    if ("LOCAL" == os.environ["SELENIUMTEST_MODALIDADE"]):
        self.driver = webdriver.Chrome()
    else:
        self.driver = webdriver.Remote(command_executor=os.environ["SELENIUMTEST_SELENIUMHOST_URL"], desired_capabilities=DesiredCapabilities.CHROME)
    self.driver.implicitly_wait(5)
    self.vars = {}
  
  def teardown_method(self, method):
    self.driver.quit()
  
  def test_runAgendamento(self):
    self.driver.get(os.environ["SELENIUMTEST_SISTEMA_URL"]+"/sip/login.php?sigla_orgao_sistema="+os.environ["SELENIUMTEST_SISTEMA_ORGAO"]+"&sigla_sistema=SEI&infra_url=L3NlaS8=")
    self.driver.find_element(By.ID, "divUsuario").click()
    self.driver.find_element(By.ID, "txtUsuario").click()
    self.driver.find_element(By.ID, "txtUsuario").send_keys("teste")
    self.driver.find_element(By.ID, "pwdSenha").click()
    self.driver.find_element(By.ID, "pwdSenha").send_keys("teste")
    self.driver.find_element(By.ID, "Acessar").click()
    self.driver.find_element(By.XPATH, "//a[contains(.,\'Infra\')]").click()
    self.driver.find_element(By.LINK_TEXT, "Agendamentos").click()
    WebDriverWait(self.driver, 30).until(expected_conditions.visibility_of_element_located((By.XPATH, "//div[@id=\'divInfraAreaTabela\']/table/tbody/tr[contains(.,\'MdGdAgendamentoRN :: verificarTempoGuarda\')]//td[7]/a[2]")))
    self.driver.find_element(By.XPATH, "//div[@id=\'divInfraAreaTabela\']/table/tbody/tr[contains(.,\'MdGdAgendamentoRN :: verificarTempoGuarda\')]//td[7]/a[2]").click()
    self.driver.switch_to.alert.accept()
    WebDriverWait(self.driver, 30).until(expected_conditions.visibility_of_element_located((By.XPATH, "//div[@id=\'divInfraAreaTabela\']/table/tbody/tr[contains(.,\'MdGdAgendamentoRN :: verificarTempoGuarda\')]//td[6][text()=\"Sucesso\"]")))
  