# NOTAS DE VERSÃO MOD-GESTAO-DOCUMENTAL (versão 1.2.7)

Este documento descreve as principais mudanças aplicadas nesta versão do módulo de Gestão Documental.

## Compatibilidade de versões
* O módulo é compatível com as seguintes versões do **SUPER.BR**:
    * 4.0.0.0 até 4.0.3.4

Para maiores informações sobre os procedimentos de instalação ou atualização, acesse os seguintes documentos localizados no pacote de distribuição mod-gestao-documental-VERSAO.zip:

* **INSTALACAO.md** - Procedimento de instalação e configuração do módulo
* **ATUALIZACAO.md** - Procedimento específicos para atualização de uma versão anterior


## Lista de Melhorias e Correções de Problemas

#### [Issue #192] Correção de erro ao tentar arquivar processo

O módulo não deixa arquivar um processo que esteve com retorno programado e já não está mais. Ele continua sinalizando que o processo não pode ser arquivado por possuir retorno programado.