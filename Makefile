.PHONY: .env help clean build all install restart down destroy up config

include .env
include .modulo.env

# Parâmetros de configuração
base = mysql

MODULO_NOME = gestao-documental
MODULO_PASTAS_CONFIG = mod-$(MODULO_NOME)
MODULO_PASTA_NOME = $(notdir $(shell pwd))
VERSAO_MODULO := $(shell grep 'const VERSAO_MODULO' ./src/MdGestaoDocumentalIntegracao.php | cut -d'"' -f2)
SEI_SCRIPTS_DIR = dist/sei/scripts/$(MODULO_PASTAS_CONFIG)
SEI_CONFIG_DIR = dist/sei/config/$(MODULO_PASTAS_CONFIG)
SEI_MODULO_DIR = dist/sei/web/modulos/$(MODULO_NOME)
SIP_SCRIPTS_DIR = dist/sip/scripts/$(MODULO_PASTAS_CONFIG)

ARQUIVO_CONFIG_SEI=$(SEI_PATH)/sei/config/ConfiguracaoSEI.php
ARQUIVO_ENV_ASSINATURA=.modulo.env
MODULO_COMPACTADO = mod-$(MODULO_NOME)-$(VERSAO_MODULO).zip
CMD_INSTALACAO_SEI = echo -ne '$(SEI_DATABASE_USER)\n$(SEI_DATABASE_PASSWORD)\n' | php sei_atualizar_versao_modulo_documental.php
CMD_INSTALACAO_SIP = echo -ne '$(SIP_DATABASE_USER)\n$(SIP_DATABASE_PASSWORD)\n' | php sip_atualizar_versao_modulo_documental.php

RED=\033[0;31m
NC=\033[0m
YELLOW=\033[1;33m

MENSAGEM_AVISO_MODULO = $(RED)[ATENÇÃO]:$(NC)$(YELLOW) Necessário configurar a chave de configuração do módulo no arquivo de configuração do SEI (ConfiguracaoSEI.php) $(NC)\n               $(YELLOW)'Modulos' => array('MdGestaoDocumentalIntegracao' => 'gestao-documental') $(NC)
MENSAGEM_AVISO_ENV = $(RED)[ATENÇÃO]:$(NC)$(YELLOW) Configurar parâmetros de autenticação do ambiente de testes do módulo de Gestão Documental no arquivo .modulo.env $(NC)

all: clean build

build: 
	@mkdir -p $(SEI_SCRIPTS_DIR)
	@mkdir -p $(SEI_CONFIG_DIR)
	@mkdir -p $(SEI_MODULO_DIR)
	@mkdir -p $(SIP_SCRIPTS_DIR)
	@cp -Rf src/* $(SEI_MODULO_DIR)/
	@cp docs/INSTALL.md dist/INSTALACAO.md
	@cp docs/UPGRADE.md dist/ATUALIZACAO.md
	@cp docs/changelogs/CHANGELOG-$(VERSAO_MODULO).md dist/NOTAS_VERSAO.md
	@mv $(SEI_MODULO_DIR)/scripts/sei_atualizar_versao_modulo_documental.php $(SEI_SCRIPTS_DIR)/
	@mv $(SEI_MODULO_DIR)/scripts/sip_atualizar_versao_modulo_documental.php $(SIP_SCRIPTS_DIR)/
	@mv $(SEI_MODULO_DIR)/config/ConfiguracaoMdGestaoDocumental.exemplo.php $(SEI_CONFIG_DIR)/
	@rm -rf $(SEI_MODULO_DIR)/config
	@rm -rf $(SEI_MODULO_DIR)/scripts
	@cd dist/ && zip -r $(MODULO_COMPACTADO) INSTALACAO.md ATUALIZACAO.md NOTAS_VERSAO.md sei/ sip/	
	@rm -rf dist/sei dist/sip dist/INSTALACAO.md dist/ATUALIZACAO.md
	@echo "Construção do pacote de distribuição finalizada com sucesso"

clean:
	@rm -rf dist
	@echo "Limpeza do diretório de distribuição do realizada com sucesso"


.modulo.env:
	cp -n envs/modulo.env .modulo.env


install:
	docker-compose exec -w /opt/sei/scripts/$(MODULO_PASTAS_CONFIG) httpd bash -c "$(CMD_INSTALACAO_SEI)"; true
	docker-compose exec -w /opt/sip/scripts/$(MODULO_PASTAS_CONFIG) httpd bash -c "$(CMD_INSTALACAO_SIP)"; true
	@echo ""
	@echo "==================================================================================================="
	@if ! grep -q MdGestaoDocumentalIntegracao "$(ARQUIVO_CONFIG_SEI)" ; then echo '$(MENSAGEM_AVISO_MODULO)\n'; fi
	@echo ""
	@echo "Fim da instalação do módulo"


up: .modulo.env
	@if [ ! -f ".env" ]; then cp envs/$(base).env .env; fi
	docker-compose up -d

config:
	@cp -f envs/$(base).env .env
	@echo "Ambiente configurado para utilizar a base de dados $(base). (base=[mysql|oracle|sqlserver])"

down: 
	docker-compose down


restart: down up


destroy: 
	docker-compose down --volumes

