# Teste do Módulo de Gestão Documental

## Orientações

- Os testes rodam em 3 modalidades:
	- **Local:** precisa ter instalado localmente o pytest e também o driver seleniumcrhome na versão adequada. Nessa modalidade você pode visualizar o teste rodando em seu browser
	- **Standalone:** (default) roda os testes usando conteineres
	- **Remote:** caso queira rodar os testes em ambiente externo e usando um grid selenium

- os testes seguem uma sequencia de tempo e uma ordem já definida. Eles não rodam independentes, pois o seguinte evetualmente precisa do trabalho realizado anteriormente

- **IMPORTANTE!** A medida que os testes vão executando, o script tentará alterar a data do sistema portanto:
	- verifique se vc tem permissão de sudo - o script vai tentar executar ``` sudo date -s ``` sendo assim coloque o seu user com permissão para executar comandos sudo
	- verifique se vc desligou o sincronizador de datas da sua vm. Não raro, ao alterar a data de uma vm em poucos minutos ela volta para a data original pois há algum processo em background fazendo isso. (ex: sudo systemctl stop vboxad.service ou sudo systemctl stop vboxadd-service.service, etc)
	- como vamos manipular a data rode o projeto em uma vm separada isolada, caso contrário vai dar problema no seu SO original

- caso opte por rodar o ``` make tests-functional ```, antes disso todo o ambiente deve ter a data setada para 2021-12-22 7AM; então cria-se o ambiente do Super; e só entao rode o teste. Existe um target no makefile ``` make tests-functional-loop ```  que já faz td isso automaticamente caso seu usuário tenha permissão

- o teste usa o arquivo .testselenium.env que é criado automaticamente caso não exista. É esse arquivo quem diz como o teste rodará (local, remoto, etc)

- antes de rodar o teste:
	-  clone o projeto do zero
	- rode o make up e depois o make install 
	- verifique as mensagens e resolva qualquer orientação
	- tente acessar a pagina inicial do sistema pelo http://localhost:8000
	- se tudo certo, basta rodar ``` make tests-functional-loop ```

- esse target ``` make tests-functional-loop ``` faz todo o trabalho necessário para os testes: ajusta a data, destroi e cria o ambiente. Caso aconteça algum erro durante os testes ele volta do início do processo e roda novamente. Vc pode definir a quantidade de retrys no campo SELENIUMTEST_RETRYTESTS do arquivo .testselenium.env

