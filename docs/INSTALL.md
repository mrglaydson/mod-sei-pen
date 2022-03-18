# Manual de Instalação do Módulo de Gestão Documental

O objetivo deste documento é descrever os procedimentos para realização da INSTALAÇÃO INICIAL do Módulo de Gestão Documental (**mod-gestao-documental**) no Sistema Eletrônico de Informações (SEI).

**ATENÇÃO: Caso o módulo já se encontre instalado em uma versão anterior, siga as instruções detalhadas de atualização no documento ATUALIZACAO.md presente no arquivo de distribuição do módulo (mod-gestao-documental-VERSAO.zip)**

O módulo **mod-gestao-documental** é o responsável adicionar novas funcionalidades ao SEI para realizar o gerenciamento do ciclo de vida dos documentos públicos segunda regras de gestão documental.

Este documento está estruturado nas seguintes seções:

1. **[Instalação](#instalação)**:
   Procedimentos destinados à Equipe Técnica responsáveis pela instalação do módulo nos servidores web e atualização do banco de dados.

2. **[Suporte](#suporte)**:
   Canais de comunicação para resolver problemas ou tirar dúvidas sobre o módulo.

3. **[Problemas Conhecidos](#problemas-conhecidos)**:
   Descrição de problemas ou comportamentos conhecidos e seus respectivos procedimentos para correção.

---

## 1. INSTALAÇÃO

Esta seção descreve os passos necessários para **INSTALAÇÃO** do **```**mod-gestao-documental**```**.  
Todos os itens descritos nesta seção são destinados à equipe de tecnologia da informação, responsáveis pela procedimentos técnicos de instalação e manutenção da infraestrutura do SEI.

### Pré-requisitos

**SEI versão 4.0.3 ou superior instalada**;

Usuário de acesso ao banco de dados do SEI e SIP com permissões para criar novas estruturas no banco de dados

### Procedimentos:

### 1.1 Fazer backup dos bancos de dados do SEI, SIP e dos arquivos de configuração do sistema.

Todos os procedimentos de manutenção do sistema devem ser precedidos de backup completo de todo o sistema a fim de possibilitar a sua recuperação em caso de falha. A rotina de instalação descrita abaixo atualiza tanto o banco de dados, como os arquivos pré-instalados do módulo e, por isto, todas estas informações precisam ser resguardadas.

---

### 1.2. Baixar o arquivo de distribuição do **mod-gestao-documental**

Necessário realizar o _download_ do pacote de distribuição do módulo **mod-gestao-documental** para instalação ou atualização do sistema SEI. O pacote de distribuição consiste em um arquivo zip com a denominação **mod-gestao-documental-VERSAO**.zip e sua última versão pode ser encontrada em https://github.com/spbgovbr/mod-gestao-documental/releases

---

### 1.3. Descompactar o pacote de instalação e atualizar os arquivos do sistema

Após realizar a descompactação do arquivo zip de instalação, será criada uma pasta contendo a seguinte estrutura:

```
/**mod-sei-loginunico**-VERSAO
    /sei              # Arquivos do módulo posicionados corretamente dentro da estrutura do SEI
    /sip              # Arquivos do módulo posicionados corretamente dentro da estrutura do SIP
    INSTALACAO.md     # Instruções de instalação do **mod-gestao-documental**
    ATUALIZACAO.md    # Instruções de atualização do **mod-gestao-documental**
    NOTAS_VERSAO.MD   # Registros de novidades, melhorias e correções desta versão
```

Importante enfatizar que os arquivos contidos dentro dos diretórios `sei` e `sip` não substituem nenhum código-fonte original do sistema. Eles apenas posicionam os arquivos do módulos nas pastas corretas de scripts, configurações e pasta de módulos, todos posicionados dentro de um diretório específico denominado loginunico para deixar claro quais scripts fazem parte do módulo.

Os diretórios `sei` e `sip` descompactados acima devem ser mesclados com os diretórios originais através de uma cópia simples dos arquivos.

Observação: O termo VERSAO deve ser substituído nas instruções abaixo pelo número da versão do módulo que está sendo instalado.

```
$ cp /tmp/**mod-gestao-documental**-VERSAO.zip <DIRETÓRIO RAIZ DE INSTALAÇÃO DO SEI E SIP>
$ cd <DIRETÓRIO RAIZ DE INSTALAÇÃO DO SEI E SIP>
$ unzip **mod-gestao-documental**-VERSAO.zip
```

---

### 1.4. Habilitar módulo **mod-gestao-documental** no arquivo de configuração do SEI

Esta etapa é padrão para a instalação de qualquer módulo no SEI para que ele possa ser carregado junto com o sistema. Edite o arquivo **sei/config/ConfiguracaoSEI.php** para adicionar a referência ao módulo na chave **[Modulos]** abaixo da chave **[SEI]**:

```php
'SEI' => array(
    'URL' => ...,
    'Producao' => ...,
    'RepositorioArquivos' => ...,
    'Modulos' => array('MdGestaoDocumentalIntegracao' => 'gestao-documental'),
    ),
```

Adicionar a referência ao módulo LoginUnico na array da chave 'Modulos' indicada acima:

```php
'Modulos' => array('MdGestaoDocumentalIntegracao' => 'gestao-documental'),
```

---

### 1.5. Configurar os parâmetros do módulo

A instalação da nova versão do **mod-gestao-documental** cria um arquivo de configuração específico para o módulo dentro da pasta de configuração do SEI (**<DIRETÓRIO RAIZ DE INSTALAÇÃO DO SEI>/sei/config/gestao-documental/**).

O arquivo de configuração padrão criado **ConfiguracaoModAssinaturaAvancada.exemplo.php** vem com o sufixo **exemplo** justamente para não substituir o arquivo principal contendo as configurações vigentes do módulo.

Caso não exista o arquivo principal de configurações do módulo criado em **<DIRETÓRIO RAIZ DE INSTALAÇÃO DO SEI E SIP>/sei/config/gestao-documental/ConfiguracaoModAssinaturaAvancada.php**, renomeie o arquivo de exemplo para iniciar a parametrização da integração.

```
cd <DIRETÓRIO RAIZ DE INSTALAÇÃO DO SEI>/sei/config/gestao-documental/
mv ConfiguracaoModAssinaturaAvancada.exemplo.php ConfiguracaoModAssinaturaAvancada.php
```

Altere o arquivo de configuração específico do módulo em **<DIRETÓRIO RAIZ DE INSTALAÇÃO DO SEI E SIP>/sei/config/gestao-documental/ConfiguracaoModAssinaturaAvancada.php** e defina as configurações do módulo, conforme apresentado abaixo:

- **parametro**
  Descrição da função do parâmetro, exemplo de valores possíveis e mudança de comportamento causada no sistema.

---

### 1.6. Atualizar a base de dados do SIP com as tabelas do **mod-gestao-documental**

A atualização realizada no SIP não cria nenhuma tabela específica para o módulo, apenas é aplicada a criarção os recursos, permissões e menus de sistema utilizados pelo **mod-gestao-documental**. Todos os novos recursos criados possuem o prefixo **md_assinavc\_** para fácil localização pelas funcionalidades de gerenciamento de recursos do SIP.

O script de atualização da base de dados do SIP fica localizado em `<DIRETÓRIO RAIZ DE INSTALAÇÃO DO SEI E SIP>/sip/scripts/mod-gestao-documental/sip_atualizar_versao_modulo_documental.php`

```bash
$ php -c /etc/php.ini <DIRETÓRIO RAIZ DE INSTALAÇÃO DO SEI E SIP>/sip/scripts/mod-gestao-documental/sip_atualizar_versao_modulo_documental.php
```

---

### 1.7. Atualizar a base de dados do SEI com as tabelas do **mod-gestao-documental**

Nesta etapa é instalado/atualizado as tabelas de banco de dados vinculadas do **mod-gestao-documental**. Todas estas tabelas possuem o prefixo **md_assin_avc\*** para organização e fácil localização no banco de dados.

O script de atualização da base de dados do SIP fica localizado em `<DIRETÓRIO RAIZ DE INSTALAÇÃO DO SEI E SIP>/sei/scripts/mod-gestao-documental/sei_atualizar_versao_modulo_documental.php`

```bash
$ php -c /etc/php.ini <DIRETÓRIO RAIZ DE INSTALAÇÃO DO SEI E SIP>/sei/scripts/mod-gestao-documental/sei_atualizar_versao_modulo_documental.php
```

---
---

## 2. PROBLEMAS CONHECIDOS

### 2.1. Problema com ...

---
---

## 3. SUPORTE

Em caso de dúvidas ou problemas durante o procedimento de atualização, favor entrar em conta pelos canais de atendimento disponibilizados na Central de Atendimento do Processo Eletrônico Nacional, que conta com uma equipe para avaliar e responder esta questão de forma mais rápida possível.

Para mais informações, contate a equipe responsável por meio dos seguintes canais:

- [Portal de Atendimento (PEN): Canal de Atendimento](https://portaldeservicos.economia.gov.br) - Módulo do Barramento
- Telefone: 0800 978 9005
