# Generated by Selenium IDE
import os
import pytest
import time
import json
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.action_chains import ActionChains
from selenium.webdriver.support import expected_conditions
from selenium.webdriver.support.wait import WebDriverWait
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.desired_capabilities import DesiredCapabilities

class Test1200ListagensMassa():
  def setup_method(self, method):
    if ("LOCAL" == os.environ["SELENIUMTEST_MODALIDADE"]):
      self.driver = webdriver.Chrome()
    else:
      self.driver = webdriver.Remote(command_executor=os.environ["SELENIUMTEST_SELENIUMHOST_URL"], desired_capabilities=DesiredCapabilities.CHROME)
    
    if ((not 'maximizar_screen' in os.environ) or os.environ['maximizar_screen'] == 'true'):
        self.driver.maximize_window()
    
    self.driver.implicitly_wait(10)
    self.vars = {}
  
  def teardown_method(self, method):
    self.driver.quit()
  
  def test_1210GerarListagem(self):
    self.driver.get(os.environ["SELENIUMTEST_SISTEMA_URL"]+"/sip/login.php?sigla_orgao_sistema="+os.environ["SELENIUMTEST_SISTEMA_ORGAO"]+"&sigla_sistema=SEI")
    self.driver.find_element(By.ID, "divUsuario").click()
    self.driver.find_element(By.ID, "txtUsuario").click()
    self.driver.find_element(By.ID, "txtUsuario").send_keys("arquivista02")
    self.driver.find_element(By.ID, "pwdSenha").click()
    self.driver.find_element(By.ID, "pwdSenha").send_keys("arquivista02")
    self.driver.find_element(By.ID, "Acessar").click()
    self.driver.find_element(By.XPATH, "//span[text()='Avaliação de Processos']/../../../../a").click()
    self.driver.find_element(By.XPATH, "//span[.='Listagens de Eliminação']/..").click()
    self.driver.find_element(By.LINK_TEXT, "Preparação da Listagem").click()
    self.driver.find_element(By.ID, "imgInfraCheck").click()
    time.sleep(2)
    self.driver.find_element(By.XPATH, "//input[@id='chkInfraItem0']/..").click()
    self.driver.find_element(By.ID, "btnGerarListagem").click()
    self.driver.find_element(By.ID, "divInfraAreaTabela").click()
    self.driver.find_element(By.ID, "divInfraAreaDados").click()
    self.driver.find_element(By.CSS_SELECTOR, ".infraCaption").click()
    assert self.driver.find_element(By.CSS_SELECTOR, ".infraCaption").text == "Lista de Processos para Eliminação (1 registro):"
    #self.driver.find_element(By.XPATH, "//span[text()='Avaliação de Processos']/../../../../a").click()
    #self.driver.find_element(By.XPATH, "//span[.='Listagens de Eliminação']/..").click()
    self.driver.find_element(By.LINK_TEXT, "Gestão das Listagens").click()
    self.driver.find_element(By.CSS_SELECTOR, ".infraCaption").click()
    assert self.driver.find_element(By.CSS_SELECTOR, ".infraCaption").text == "Lista de Processos de Eliminação (3 registros):"
    #self.driver.find_element(By.XPATH, "//div[@id=\'divInfraAreaTabela\']/table/tbody/tr[2]/td[3]").click()
    assert self.driver.find_element(By.XPATH, "//a[ .= '99994.000001/2037-76' ]").text == "99994.000001/2037-76"
    self.driver.find_element(By.XPATH, "//*[@id=\"divInfraAreaTabela\"]/table/tbody/tr[2]/td[9]/a[1]").click()
    self.driver.find_element(By.CSS_SELECTOR, ".infraCaption").click()
    assert self.driver.find_element(By.CSS_SELECTOR, ".infraCaption").text == "Lista de Processos (29 registros):"
    self.driver.find_element(By.ID, "btnCancelar").click()
  
  def test_1210GerarListagemRecolhimento(self):
    self.driver.get(os.environ["SELENIUMTEST_SISTEMA_URL"]+"/sip/login.php?sigla_orgao_sistema="+os.environ["SELENIUMTEST_SISTEMA_ORGAO"]+"&sigla_sistema=SEI")
    self.driver.find_element(By.ID, "divUsuario").click()
    self.driver.find_element(By.ID, "txtUsuario").click()
    self.driver.find_element(By.ID, "txtUsuario").send_keys("arquivista02")
    self.driver.find_element(By.ID, "pwdSenha").click()
    self.driver.find_element(By.ID, "pwdSenha").send_keys("arquivista02")
    self.driver.find_element(By.ID, "Acessar").click()
    self.driver.find_element(By.XPATH, "//span[text()='Avaliação de Processos']/../../../../a").click()
    self.driver.find_element(By.XPATH, "//span[.='Listagens de Recolhimento']/..").click()
    self.driver.find_element(By.LINK_TEXT, "Preparação da Listagem").click()
    self.driver.find_element(By.ID, "imgInfraCheck").click()
    time.sleep(2)
    self.driver.find_element(By.XPATH, "//input[@id='chkInfraItem0']/..").click()
    self.driver.find_element(By.ID, "btnGerarListagem").click()
    self.driver.find_element(By.ID, "divInfraAreaTabela").click()
    self.driver.find_element(By.ID, "divInfraAreaDados").click()
    self.driver.find_element(By.CSS_SELECTOR, ".infraCaption").click()
    assert self.driver.find_element(By.CSS_SELECTOR, ".infraCaption").text == "Lista de Processos para Recolhimento (1 registro):"
    #self.driver.find_element(By.XPATH, "//span[text()='Avaliação de Processos']/../../../../a").click()
    #self.driver.find_element(By.XPATH, "//span[.='Listagens de Recolhimento']/..").click()
    self.driver.find_element(By.LINK_TEXT, "Gestão das Listagens").click()
    self.driver.find_element(By.CSS_SELECTOR, ".infraCaption").click()
    assert self.driver.find_element(By.CSS_SELECTOR, ".infraCaption").text == "Lista de Processos de Recolhimento (3 registros):"
    self.driver.find_element(By.XPATH, "//*[@id=\"divInfraAreaTabela\"]/table/tbody/tr[4]/td[9]/a[1]").click()
    self.driver.find_element(By.CSS_SELECTOR, ".infraCaption").click()
    assert self.driver.find_element(By.CSS_SELECTOR, ".infraCaption").text == "Lista de Processos (39 registros):"
    self.driver.find_element(By.ID, "btnCancelar").click()
  