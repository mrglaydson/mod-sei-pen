# Generated by Selenium IDE
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

class Test0200SEIGDCadArquivaProc():
  def setup_method(self, method):
    self.driver = webdriver.Remote(command_executor='http://seleniumhub:4444/wd/hub', desired_capabilities=DesiredCapabilities.CHROME)
    self.vars = {}
  
  def teardown_method(self, method):
    self.driver.quit()
  
  def wait_for_window(self, timeout = 2):
    time.sleep(round(timeout / 1000))
    wh_now = self.driver.window_handles
    wh_then = self.vars["window_handles"]
    if len(wh_now) > len(wh_then):
      return set(wh_now).difference(set(wh_then)).pop()
  
  def test_cadastrarProcessos(self):
    self.driver.get("http://sei.gd.nuvem.gov.br/sip/login.php?sigla_orgao_sistema=ME&sigla_sistema=SEI&infra_url=L3NlaS8=")
    WebDriverWait(self.driver, 30000).until(expected_conditions.visibility_of_element_located((By.ID, "txtUsuario")))
    self.driver.find_element(By.ID, "txtUsuario").click()
    self.driver.find_element(By.ID, "txtUsuario").send_keys("arquivista01")
    WebDriverWait(self.driver, 30000).until(expected_conditions.visibility_of_element_located((By.ID, "pwdSenha")))
    self.driver.find_element(By.ID, "pwdSenha").click()
    self.driver.find_element(By.ID, "pwdSenha").send_keys("arquivista01")
    self.driver.find_element(By.ID, "sbmLogin").click()
    WebDriverWait(self.driver, 30000).until(expected_conditions.visibility_of_element_located((By.LINK_TEXT, "Iniciar Processo")))
    self.driver.find_element(By.LINK_TEXT, "Iniciar Processo").click()
    self.driver.find_element(By.LINK_TEXT, "Acesso à Informação: Demanda do e-SIC").click()
    self.driver.find_element(By.ID, "txtDescricao").send_keys("teste arquivo")
    self.driver.find_element(By.ID, "optPublico").click()
    self.driver.find_element(By.CSS_SELECTOR, "#divInfraBarraComandosInferior > #btnSalvar > .infraTeclaAtalho").click()
    self.driver.switch_to.frame(1)
    WebDriverWait(self.driver, 30000).until(expected_conditions.visibility_of_element_located((By.CSS_SELECTOR, ".botaoSEI:nth-child(1) > .infraCorBarraSistema")))
    self.driver.find_element(By.CSS_SELECTOR, ".botaoSEI:nth-child(1) > .infraCorBarraSistema").click()
    WebDriverWait(self.driver, 30000).until(expected_conditions.visibility_of_element_located((By.LINK_TEXT, "Despacho")))
    self.driver.find_element(By.LINK_TEXT, "Despacho").click()
    WebDriverWait(self.driver, 30000).until(expected_conditions.visibility_of_element_located((By.ID, "optPublico")))
    self.driver.find_element(By.ID, "optPublico").click()
    self.vars["window_handles"] = self.driver.window_handles
    self.driver.find_element(By.ID, "btnSalvar").click()
    self.vars["win3408"] = self.wait_for_window(10000)
    self.vars["root"] = self.driver.current_window_handle
    self.driver.switch_to.window(self.vars["win3408"])
    self.driver.close()
    self.driver.switch_to.window(self.vars["root"])
    self.driver.switch_to.frame(0)
    self.driver.switch_to.default_content()
    self.driver.switch_to.frame(1)
    self.driver.switch_to.window(self.vars["root"])
    self.driver.switch_to.frame(0)
    self.driver.switch_to.default_content()
    self.driver.switch_to.frame(0)
    self.driver.find_element(By.XPATH, "//span").click()
    self.driver.switch_to.default_content()
    self.driver.switch_to.frame(1)
    WebDriverWait(self.driver, 30000).until(expected_conditions.visibility_of_element_located((By.CSS_SELECTOR, ".botaoSEI:nth-child(23) > .infraCorBarraSistema")))
    self.driver.find_element(By.CSS_SELECTOR, ".botaoSEI:nth-child(23) > .infraCorBarraSistema").click()
    self.driver.switch_to.window(self.vars["root"])
    self.driver.switch_to.frame(0)
    self.driver.switch_to.default_content()
    self.driver.switch_to.frame(1)
    print(str("ponto11"))
    WebDriverWait(self.driver, 30000).until(expected_conditions.visibility_of_element_located((By.ID, "selJustificativa")))
    self.driver.find_element(By.ID, "selJustificativa").click()
    print(str("Ponto33"))
    dropdown = self.driver.find_element(By.ID, "selJustificativa")
    dropdown.find_element(By.XPATH, "//option[. = 'Justificativa de Arquivamento 01']").click()
    print(str("Ponto2"))
    self.driver.find_element(By.ID, "selJustificativa").click()
    self.driver.find_element(By.ID, "selCargoFuncao").click()
    dropdown = self.driver.find_element(By.ID, "selCargoFuncao")
    dropdown.find_element(By.XPATH, "//option[. = 'Assessor(a)']").click()
    self.driver.find_element(By.ID, "selCargoFuncao").click()
    self.driver.find_element(By.ID, "pwdSenha").click()
    self.driver.find_element(By.ID, "pwdSenha").send_keys("arquivista01")
    self.driver.find_element(By.ID, "sbmSalvar").click()
    time.sleep(5)
    WebDriverWait(self.driver, 30000).until(expected_conditions.visibility_of_element_located((By.XPATH, "//*[@id=\"divArvoreAcoes\"]/a[11]/img")))
    self.driver.find_element(By.XPATH, "//*[@id=\"divArvoreAcoes\"]/a[11]/img").click()
    WebDriverWait(self.driver, 30000).until(expected_conditions.visibility_of_element_located((By.ID, "selJustificativa")))
    dropdown = self.driver.find_element(By.ID, "selJustificativa")
    dropdown.find_element(By.XPATH, "//option[. = 'Justificativa de Desarquivamento 01']").click()
    self.driver.find_element(By.ID, "selJustificativa").click()
    self.driver.find_element(By.CSS_SELECTOR, "body").click()
    dropdown = self.driver.find_element(By.ID, "selCargoFuncao")
    dropdown.find_element(By.XPATH, "//option[. = 'Gestor de Contrato']").click()
    self.driver.find_element(By.ID, "selCargoFuncao").click()
    self.driver.find_element(By.ID, "pwdSenha").click()
    self.driver.find_element(By.ID, "pwdSenha").send_keys("arquivista01")
    self.driver.find_element(By.ID, "sbmSalvar").click()
    time.sleep(5)
  