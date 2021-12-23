
# Manual de Instala��o do M�dulo de Gest�o Documental do SEI

Esse manual tem como objetivo orientar a instala��o e configura��o inicial do m�dulo de gest�o documental do SEI para sua utiliza��o nas vers�es do SEI suportadas.  

Este manual est� estruturado nas seguintes se��es:

 1. **Instala��o**
Procedimentos de instala��o do m�dulo nos servidores de aplica��o e atualiza��o do banco de dados.
 
## Instala��o

### Pr�-requisitos
 - **SEI vers�o 3.0.11 ou superior instalada.**
 - Usu�rio de acesso ao banco de dados do SEI e SIP com  permiss�es para criar novas estruturas no banco de dados.
  
### Procedimentos

1. Fazer backup dos banco de dados do SEI e SIP e dos arquivos de configura��o do sistema.

2.  Configurar m�dulo de gest�o documental no arquivo de configura��o do SEI

    Editar o arquivo **sei/config/ConfiguracaoSEI.php**, tomando o cuidado de usar editor que n�o altere o charset ISO 5589-1 do arquivo, para adicionar a refer�ncia ao m�dulo PEN na chave **[Modulos]** abaixo da chave **[SEI]**:    

        'SEI' => array(
            'URL' => 'http://[servidor sei]/sei',
            'Producao' => true,
            'RepositorioArquivos' => '/var/sei/arquivos',
            'Modulos' => array('MdGestaoDocumentalIntegracao' => 'mod-gestao-documental'),
            ),

    Adicionar a refer�ncia ao m�dulo PEN na array da chave 'Modulos' indicada acima:
            
        'Modulos' => array('MdGestaoDocumentalIntegracao' => 'mod-gestao-documental')

3.  Mover o diret�rio de arquivos do m�dulo "mod-sei-gestao-documental" para o diret�rio sei/web/modulos/

3. Mover o arquivo de instala��o do m�dulo no SEI **scripts/sei/sei-gestao-documental-atualizar.php** para a pasta **sei/scripts**. Lembre-se de mover, e n�o copiar, por quest�es de seguran�a e padroniza��o.

4. Mover o arquivo de instala��o do m�dulo no SEI **scripts/sip/sip-gestao-documental-atualizar.php** para a pasta **sip/scripts**. Lembre-se de mover, e n�o copiar, por quest�es de seguran�a e padroniza��o.

5 Executar o script **sip-gestao-documental-atualizar.php** para atualizar o banco de dados do SIP para o funcionamento do m�dulo:

        # php -c /etc/php.ini [DIRETORIO_RAIZ_INSTALA��O]/sip/scripts/sip-gestao-documental-atualizar.php.php

6. Executar o script **sei-gestao-documental-atualizar.php** para inser��o de dados no banco do SEI referente ao m�dulo.

        # php -c /etc/php.ini [DIRETORIO_RAIZ_INSTALA��O]/sei/scripts/sei-gestao-documental-atualizar.php

7. Ap�s a instala��o do m�dulo, o usu�rio de manuten��o dever� ser alterado para outro contendo apenas as permiss�es de leitura e escrita no banco de dados.
