
# Manual de Instalação do Módulo de Gestão Documental do SEI

Esse manual tem como objetivo orientar a instalação e configuração inicial do módulo de gestão documental do SEI para sua utilização nas versões do SEI suportadas.  

Este manual está estruturado nas seguintes seções:

 1. **Instalação**
Procedimentos de instalação do módulo nos servidores de aplicação e atualização do banco de dados.
 
## Instalação

### Pré-requisitos
 - **SEI versão 3.0.11 ou superior instalada.**
 - Usuário de acesso ao banco de dados do SEI e SIP com  permissões para criar novas estruturas no banco de dados.
  
### Procedimentos

1. Fazer backup dos banco de dados do SEI e SIP e dos arquivos de configuração do sistema.

2.  Configurar módulo de gestão documental no arquivo de configuração do SEI

    Editar o arquivo **sei/config/ConfiguracaoSEI.php**, tomando o cuidado de usar editor que não altere o charset ISO 5589-1 do arquivo, para adicionar a referência ao módulo PEN na chave **[Modulos]** abaixo da chave **[SEI]**:    

        'SEI' => array(
            'URL' => 'http://[servidor sei]/sei',
            'Producao' => true,
            'RepositorioArquivos' => '/var/sei/arquivos',
            'Modulos' => array('MdGestaoDocumentalIntegracao' => 'sei-mod-gestao-documental'),
            ),

    Adicionar a referência ao módulo PEN na array da chave 'Modulos' indicada acima:
            
        'Modulos' => array('MdGestaoDocumentalIntegracao' => 'sei-mod-gestao-documental')

3.  Mover o diretório de arquivos do módulo "mod-sei-gestao-documental" para o diretório sei/web/modulos/

3. Mover o arquivo de instalação do módulo no SEI **scripts/sei/sei-gestao-documental-atualizar.php** para a pasta **sei/scripts**. Lembre-se de mover, e não copiar, por questões de segurança e padronização.

4. Mover o arquivo de instalação do módulo no SEI **scripts/sip/sip-gestao-documental-atualizar.php** para a pasta **sip/scripts**. Lembre-se de mover, e não copiar, por questões de segurança e padronização.

5 Executar o script **sip-gestao-documental-atualizar.php** para atualizar o banco de dados do SIP para o funcionamento do módulo:

        # php -c /etc/php.ini [DIRETORIO_RAIZ_INSTALAÇÃO]/sip/scripts/sip-gestao-documental-atualizar.php.php

6. Executar o script **sei-gestao-documental-atualizar.php** para inserção de dados no banco do SEI referente ao módulo.

        # php -c /etc/php.ini [DIRETORIO_RAIZ_INSTALAÇÃO]/sei/scripts/sei-gestao-documental-atualizar.php

7. Após a instalação do módulo, o usuário de manutenção deverá ser alterado para outro contendo apenas as permissões de leitura e escrita no banco de dados.
