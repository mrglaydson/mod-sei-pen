# NOTAS DE VERSÃO MOD-GESTAO-DOCUMENTAL (versão 1.1.0)

Este documento descreve as principais mudanças aplicadas nesta versão do módulo de Gestão Documental.

Para maiores informações sobre os procedimentos de instalação ou atualização, acesse os seguintes documentos localizados no pacote de distribuição mod-gestao-documental-VERSAO.zip:

* **INSTALACAO.md** - Procedimento de instalação e configuração do módulo
* **ATUALIZACAO.md** - Procedimento específicos para atualização de uma versão anterior


## Lista de Melhorias e Correções de Problemas

#### [Issue #56] Remover o ícone de "Reabrir processo" nos processos arquivados

Remover o ícone de "Reabrir processo" que aparece ao clicar nos documentos de um processo arquivado (talvez seja necessário ponto de extensão)

#### [Issue #98] Diversas Alterações de Texto em Justificativas de Arquivamento e Desarquivamento

Alterar o nome do menu para "Justificativas de Arquivamento / Desarquivamento".
Alterar o título da tela para "Nova Justificativa de Arquivamento / Desarquivamento".
Alterar o título da tela para "Alterar Justificativa de Arquivamento / Desarquivamento".
Alterar o título da tela para "Consultar Justificativa de Arquivamento / Desarquivamento".

#### [Issue #101] Atualizar o modelo do "Documento de Arquivamento" e do "Documento de Desarquivamento"

Atualizar o modelo do "Documento de Arquivamento" e do "Documento de Desarquivamento"

#### [Issue #103] Alterar o Nome da Lista e Alterar Diferentes Títulos

Alterar o nome da lista para "Lista de Unidades de Arquivamento"
Alterar o título da tela para "Nova Unidade de Arquivamento"
Alterar o título da tela para "Consultar Unidade de Arquivamento"
Alterar o título da tela para "Alterar Unidade de Arquivamento"

#### [Issue #105] Ao tentar arquivar um processo aberto em mais de uma unidade (por meio da tela de controle de processos) está sendo apresentada mensagem de crítica do sistema

Ao tentar arquivar um processo aberto em mais de uma unidade (por meio da tela de controle de processos) está sendo apresentada mensagem de crítica do sistema em um fundo cinza (estranho). Ao clicar em "OK" é aberta a tela de arquivamento. A mensagem de crítica apresentada deve ser: "O(s) processo(s) x, y e z está(ão) aberto(s) em outra(s) unidade(s)." Depois deve ser mostrada a tela de "Controle de Processos".

#### [Issue #107] Remover o texto "Dados para assinatura do despacho de arquivamento" que aparece abaixo de "Dados da Assinatura"

Remover o texto "Dados para assinatura do despacho de arquivamento" que aparece abaixo de "Dados da Assinatura".

#### [Issue #108] Alterar a palavra "Processos" para "Processo". Alterar o termo "Motivo" para "Justificativa"

Alterar a palavra "Processos" para "Processo". Alterar o termo "Motivo" para "Justificativa" a fim de manter o padrão com as demais telas

#### [Issue #110] Remover a coluna "Item" e a coluna "Anotações"

Remover a coluna "Item" e a coluna "Anotações".

#### [Issue #111] Remover o ícone "Realizar Anotação"

Remover o ícone "Realizar Anotação"

#### [Issue 113] Inserir dois pontos (:) no filtro "Até"

Inserir dois pontos (:) no filtro "Até"

#### [Issue #117] Inserir dois pontos (:) no filtro "Até". Remover o filtro "Destinação Final". Alterar o label do filtro "De"

Inserir dois pontos (:) no filtro "Até". Remover o filtro "Destinação Final". Alterar o label do filtro "De" para "Data de Arquivamento de".

#### [Issue #128] Alterar o filtro "De:" para "Data de Destinação De:"

Alterar o filtro "De:" para "Data de Destinação De:" (esse filtro deve ter relação com a coluna "Data de Destinação").

#### [Issue #130] 

Alterar o nome da coluna "Observações" para "Observação do Assunto"

#### [Issue #137] Alterar o título da relação de processos de "Lista de Processos Arquivados" para "Lista de Processos para Avaliação"

Alterar o título da relação de processos de "Lista de Processos Arquivados" para "Lista de Processos para Avaliação".

#### [Issue #155] Arquivar/desarquivar processos em lote

Ao arquivar/desarquivar processos em lote o campo "Processo" está tão pequeno que apresenta apenas um linha (só dá pra ver um processo).