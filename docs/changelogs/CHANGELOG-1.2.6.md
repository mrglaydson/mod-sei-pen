# NOTAS DE VERSÃO MOD-GESTAO-DOCUMENTAL (versão 1.2.6)

Este documento descreve as principais mudanças aplicadas nesta versão do módulo de Gestão Documental.

## Compatibilidade de versões
* O módulo é compatível com as seguintes versões do **SUPER.BR**:
    * 4.0.0.0 até 4.0.3.4

Para maiores informações sobre os procedimentos de instalação ou atualização, acesse os seguintes documentos localizados no pacote de distribuição mod-gestao-documental-VERSAO.zip:

* **INSTALACAO.md** - Procedimento de instalação e configuração do módulo
* **ATUALIZACAO.md** - Procedimento específicos para atualização de uma versão anterior


## Lista de Melhorias e Correções de Problemas

#### [Issue #181] Adicionar o recurso

Adicionar o recurso "gd_lista_eliminacao_procedimento_adicionar" no perfil GD Avaliação.

#### [Issue #184] Ajustes na descrição e inclusão de novos recursos

Ajustes na descrição dos perfis GD Arquivamento e GD Avaliação;
Inclusão do recurso gd_listar_eliminacao_anotar no script de instalação;
Substituição de alguns ícones.

#### [Issue #186] Melhorias no módulo

Na tela de Pendências de Arquivamento trocar o ícone de Reabrir processo do SEI 3 pelo ícone de Reabrir processo do SEI 4.0;
Nos processos arquivados com a funcionalidade "legado" apresentar apenas a data do arquivamento informada. Não apresentar o horário;
Na tela de Preparação da Listagem de Eliminação e na tela de Preparação da Listagem de Recolhimento no lugar da coluna Data de Arquivamento apresentar a coluna Data de Destinação, a exemplo que como ocorre da tela de Avaliação de Processos;
Ao clicar em Visualizar Listagem de Eliminação e Visualizar Listagem de Recolhimento está sendo apresentada uma tela que se chama "Visualizar Listagem de Eliminação". Alterar o nome dessa tela para "Visualizar Processos da Listagem de Eliminação". Nessa mesma tela;
Na listagem de eliminação/recolhimento que é gerada no processo há fontes diferentes. Ajustar para calibri;.

#### [Issue #188] Ajustes no módulo

Nas colunas com título "Processo" alterar para "Nº do Processo", seguindo o que foi feito na coluna Tipo, que foi alterado para Tipo de Processo.
Remover o filtro "Destinação Final" pois nessa tela não é mais apresentada a destinação final dos processos e o filtro está ficando vazio
Lembrar de remover o horário da coluna "Data de Arquivamento". Deixar apenas a data mesmo
Alterar o botão SALVAR para ASSINAR a fim de manter o padrão com o resto do SUPER
Alterar o nome "Dados do Arquivamento" para "Dados do Desarquivamento"
Alterar o nome do campo "Motivo" para "Justificativa" para manter o padrão com o resto do módulo
Alterar o nome do campo "Processos:" para "Processo(s):" para padronizar as telas de arquivamento e desarquivamento
Alterar o nome do campo "Processo:" para "Processo(s):" para padronizar as telas de arquivamento e desarquivamento
Ajustes nos testes

#### [Issue #189] Erros no módulo

Arquivamento de processo: Erro ao arquivar processo com retorno programado

Processo(s) não pode(m) ser aquivado(s) pois possui(em) retorno programado.
Alterar a mensagem de crítica que aparece no arquivamento legado Processo(s) não pode(m) ser arquivado(s) pois foi realizada ação na árvore do processo posterior à data de arquivamento informada.
Na tela de Avaliação de Processos apresentar a checkbox que permite selecionar vários processos e realizar uma ação para todos eles.

Na tela "Visualizar Listagem de Recolhimento", que será alterada para "Visualizar Processos da Listagem de Recolhimento" está dando erro ao clicar em "Gerar PDF": Tanto na tela de "Visualizar Processos da Listagem de Eliminação" quanto "Visualizar Processos da Listagem de Recolhimento" ao clicar em Gerar PDF usar como modelo o documento anexo, com brasão, cabeçalho e a relação dos processo contendo justificativa de arquivamento e demais informações

Correção no modelo de listagem de eliminação de documentos
Correção na tabela da listagem de recolhimento de documentos
Correção na geração dos pdfs do listagem de eliminação e recolhimento