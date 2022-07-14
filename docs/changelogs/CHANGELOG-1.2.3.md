# NOTAS DE VERSÃO MOD-GESTAO-DOCUMENTAL (versão 1.2.3)

Este documento descreve as principais mudanças aplicadas nesta versão do módulo de Gestão Documental.

Para maiores informações sobre os procedimentos de instalação ou atualização, acesse os seguintes documentos localizados no pacote de distribuição mod-gestao-documental-VERSAO.zip:

* **INSTALACAO.md** - Procedimento de instalação e configuração do módulo
* **ATUALIZACAO.md** - Procedimento específicos para atualização de uma versão anterior


## Lista de Melhorias e Correções de Problemas

#### [Issue #175] Ajustes no módulo parte 1

Pendências de Arquivamento - Mudar o nome do campo de "assunto" para "código de classificação" e apresentar apenas o código numérico. Ao passar o cursor deve ser mostrada a descrição completa.

No Arquivo da Unidade ao invés do filtro se chamar “Justificativa”, deve se chamar “Justificativa de Arquivamento”
Gestão Documental > Listagens de Recolhimento - Alterar o filtro "Datas-limites de:" para "Datas-limite de:"

Ao tentar EXCLUIR uma unidade de arquivamento o nome da Unidade de Arquivo vem como “undefined”. O texto correto da mensagem deve ser: Confirma exclusão da Unidade de Arquivamento “nome da unidade de arquivamento”?

No “Arquivo da Unidade” e na “Avaliação de Processos” a segunda caixa do filtro de data deve apresentar como texto apenas o termo "Até:"

Na tela de Avaliação de Processos ao clicar no ícone “Preparar Listagem de Eliminação” está sendo apresentada a mensagem “Confirma o envio do processo “00000.000000/0000-00” para eliminação?”. Essa mensagem deve ser substituída por “Confirma o envio do processo “00000.000000/0000-00” para preparação da listagem?”.

Em todo o módulo, onde constar “datas-limites” alterar para “datas-limite”

#### [Issue #177] Ajustes no módulo parte 2

"Os perfis devem vir configurados já na instalação ou num script de configuração."
"Incluir a base legal no modelo do documento de arquivamento e desarquivamento. Incluir um espaço ao final do documento para distanciar um pouco do rodapé. "

Mudar o nome do campo de "assunto" para "código de classificação" e apresentar apenas o código numérico. Ao passar o cursor deve ser mostrada a descrição completa. Colocar a coluna TIPO depois da coluna CÓDIGO DE CLASSIFICAÇÃO. Acrescentar a coluna DATA DE DESTINAÇÃO que deve ficar no lugar de DATA DE ARQUIVAMENTO.

Mudar o nome do campo de "assunto" para "código de classificação" e apresentar apenas o código numérico. Ao passar o cursor deve ser mostrada a descrição completa. Na coluna "Observação do Código" mostrar apenas os primeiros 100 caracteres, para otimizar espaço em tela. Apresentar barra de rolagem que possibilite a leitura do restante do texto.

Mudar o nome do campo de "assunto" para "código de classificação" e apresentar apenas o código numérico. Ao passar o cursor deve ser mostrada a descrição completa. Na coluna "Observação do Código" mostrar apenas os primeiros 100 caracteres, para otimizar espaço em tela. Apresentar barra de rolagem que possibilite a leitura do restante do texto.

Quando um processo é devolvido para correção ele aparece na tela do arquivo da unidade, mas não é possível identificar que é para correção. Seria interessante marcar a linha com uma cor diferente para dar destaque e mostrar a mensagem de devolução em algum campo.

A tela de Avaliação de Processos possui o filtro “Data de Destinação” mas na parte de baixo da tela é apresentada a coluna “Data de Arquivamento”. Deve ser apresentada a coluna com a data de destinação do processo e não a data do arquivamento.

Em todo o módulo alterar o label do ícone de arquivamento de "Concluir e Arquivar Processo" para "Arquivar Processo". Alterar também o título da página para "Arquivar Processo"