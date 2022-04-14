pipeline {
    agent {
      node {
        label 'SUPERGD'
      }
    }
    
    parameters { 
        choice(
            name: 'database',
            choices: "mysql\noracle\nsqlserver",                
            description: 'Qual o banco de dados' )
        string(
            name: 'urlGit',
            defaultValue:"https://github.com/spbgovbr/mod-gestao-documental.git",
            description: "Url do git onde se encontra o módulo")
        string(
            name: 'credentialGit',
            defaultValue:"githubcred",
            description: "Jenkins Credencial do git onde se encontra o módulo")
	      string(
	          name: 'branchGit',
	          defaultValue:"Reconciliacao",
	          description: "Branch/Versao do git onde se encontra módulo")
	      string(
	          name: 'sourceSuperLocation',
	          defaultValue:"~/sei/FontesSEIcopia",
	          description: "Localizacao do fonte do Super no servidor onde vai rodar o job")
	      string(
	          name: 'qtdTentativas',
	          defaultValue:"5",
	          description: "Quantidade de tentativas caso o teste falhe")
          
    }
    
    stages {

        stage('Inicializar Job'){
            steps {
                
                script{                    
                    DATABASE = params.database
                    GITURL = params.urlGit
					          GITCRED = params.credentialGit
					          GITBRANCH = params.branchGit
                    VMCRED = params.credentialVm
                    SUPERLOCATION = params.sourceSuperLocation
                    QTDTENTATIVAS = params.qtdTentativas
                    
                    if ( env.BUILD_NUMBER == '1' ){
                        currentBuild.result = 'ABORTED'
                        warning('Informe os valores de parametro iniciais. Caso eles n tenham aparecido faça login novamente')
                    }

                }

                sh """
                echo ${WORKSPACE}
                
                """
            }
        }
        
        stage('Checkout'){
            
            steps {
                
              sh """
              git config http.sslVerify false
              """
                
                git branch: GITBRANCH,
                    credentialsId: GITCRED,
                    url: GITURL
                
                sh """
                
                ls -l
				
                """
            }
        }
        
        stage('Build Env - Run Tests'){
        
            steps {
                
                
                sh """
                ls
                rm -rf .env
                rm -rf .testselenium.env
                
                make base="${DATABASE}" config
                make .testselenium.env
                
                sed -i "s|export SELENIUMTEST_RETRYTESTS=5|export SELENIUMTEST_RETRYTESTS=${QTDTENTATIVAS}|" .testselenium.env             
                sed -i "s|SEI_PATH=../../../../|SEI_PATH=${SUPERLOCATION}|" .env
                
                if [ "${DATABASE}" == "oracle" ]; then
                    
                    sed -i "s|export SELENIUMTEST_BACKUP=false|export SELENIUMTEST_BACKUP=true|" .testselenium.env      
                    
                fi
                
                make MSGORIENTACAO=n tests-functional-loop
                
                
                
                """  
              
            }        
        }
    }
}