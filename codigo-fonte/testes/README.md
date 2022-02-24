# Teste do Módulo de Gestão Documental

## Orientações

- Os testes foram escritos tendo como base o ambiente sei-docker - https://github.com/spbgovbr/sei-docker, mas vc pode usar qualquer ambiente que use a base inicial de referencia do poder executivo. Caso use ambiente com módulos instalados poderá haver alguma incompatibilidade no teste na hora de clicar em menus
A base que vc for usar tem q ter sido criada antes de 2021-12-22 7AM

- para o sei3 foi usado o SeleniumIDE, para o sei4 apenas adaptado os codigos em python, portanto desconsiderar os arquivos .side e considerar apenas os arquivos nas respectivas pastas exportedpython (\<cenario\>/exportedpython/)

- O ambiente deve ter sido criado e estar na data com a vm em: 2021-12-22 7AM

- Caso use o sei-docker verifique as orientações iniciais na seção abaixo

- As datas do teste precisam ser manipuladas, portanto vc vai precisar iniciar o teste com uma data e ir adiantando o relógio após determinados cenários

- consulte o script cargacompleto.sh para ver o andamento correto dos cenários bem como os momentos para adiantar a data - esse é o fluxo que deve ser seguido

- o script é apenas uma sugestão de execução e para mostrar a ordem do fluxo, dependendo do seu ambiente terá que fazer adaptações

- o ideal nas primeiras vezes enquanto vc não entender todo o cenário, é rodar um por um na sequência

- o teste, quase todo, foi escrito para que os cenarios posteriores dependam do anterior, portanto qualquer intervenção que atinja as asserções pode gerar um erro em cenários posteriores. Constantemente ele conta processos, prazos, etc. Caso determinado processo não tenha sido criado ou algum cenário rodou duas vezes quando era pra rodar apenas uma, pode gerar problema nas asserções posteriores

- caso vc atue no ambiente por fora, sem ser o teste poderá contaminar e falhar o teste, por ex, se vc entrar com um usuario e mudar de unidade o teste pode falhar pq não iniciou na unidade esperada. Outras ações podem gerar erro, como criar processos qd o teste estiver rodando ou antes do ciclo terminar

- aconselhável ir fazendo backups da base após alguns cenários, assim vc não precisa voltar td caso apareçam erros. Observar a data em que está o ambiente para restaurar o backup na data correta

- o script de sugestão está fazendo backups de tempos em tempos do mysql e caso esteja usando o sei-docker, verificar isso antes de usar o script e se for o caso comente ou adapte

- Caso o teste falhe em determinado momento, verifique se foi um falso positivo, por ex: é comum o teste dizer q não encontrou determinado menu para clicar, as vezes o menu esta lá mas ainda nao deu tempo dele aparecer na hora da asserção, por ex. Nesse caso volte o último backup válido e continue o teste daquele ponto. Ou inicie do zero

- O teste precisa das seguintes variáveis para que o python selenium execute corretamente:
	- export selOrgao=ME
	- export local_do_teste=LOCAL
	- export base_url=http://localhost
	- respectivamente acima, sao Orgao do seu ambiente; teste executando localmente na mesma maquina do ambiente; url para o selenium chegar no sei, não informar / no final

- antes de rodar o cenário 17 fazer um update na base de dados para permitir o login sem ldap:
```
update sip.orgao set sin_autenticar='N'
```
- o script sugerido já faz isso automaticamente para o sei-docker e mysql


## SEI-docker

- Caso opte pelo sei-docker basta seguir as orientações abaixo além das descritas no restante desse documento

- O ambiente deve ser criado na data inicial caso contrario haverá erros inesperados

- Altere o envlocal.env, setando as variaveis respectivas, para:
    - desligar o JOD (coloque false)
    - usar http (default https, coloque http)
    - instalar o modulo gestao documental (default false, coloque true)

- Posicione o código fonte do sei no local correto exigido pelo sei-docker 

- Antes de voltar a data suba o ambiente uma vez para baixar os conteineres

- Derrube todo o ambiente e limpe os volumes

```
make clear && make apagar_volumes
``` 
- O comando acima só funciona se vc já tiver subido pelo menos uma vez o ambiente


- Volte a data para 22/12/2021 e depois suba o ambiente

``` 
make setup && make logs_app
``` 

- quando o ambiente estiver no ar dispare os testes, o log do app vai dizer se pode usar



