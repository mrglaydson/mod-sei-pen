#!/bin/bash

#ATENCAO ANTES DE RODAR DO ZERO, O AMBIENTE DEVE TER SIDO CONSTRUIDO E ESTAR NA DATA 2021-12-22 7AM, LEIA O README DOS TESTES

set -e


DIR=$( dirname "${BASH_SOURCE[0]}" )

source $DIR/utils/carga-functions.sh


rodar_teste 01-Configs 

rodar_teste 02.00-SEI-GD-CadArquivaProc 

rodar_teste 02.10-SEI-GD-CadProcEventoInst 

rodar_teste 02.20-SEI-GD-CadProcEventoInst-Aut

rodar_teste 02.50-SEI-GD-DuplicaMassa
rodar_teste 02.50-SEI-GD-DuplicaMassa
rodar_teste 02.50-SEI-GD-DuplicaMassa
rodar_teste 02.50-SEI-GD-DuplicaMassa
rodar_teste 02.50-SEI-GD-DuplicaMassa
rodar_teste 02.50-SEI-GD-DuplicaMassa
rodar_teste 02.70-SEI-GD-DuplicaMassa2
rodar_teste 02.70-SEI-GD-DuplicaMassa2
rodar_teste 02.70-SEI-GD-DuplicaMassa2
rodar_teste 02.70-SEI-GD-DuplicaMassa2
rodar_teste 02.70-SEI-GD-DuplicaMassa2
rodar_teste 02.70-SEI-GD-DuplicaMassa2

rodar_teste 02.30-SEI-GD-CadProcAcessoInf
rodar_teste 02.40-SEI-GD-CadProcAcessoInf-Aut

rodar_teste 02.50-SEI-GD-DuplicaMassa
rodar_teste 02.50-SEI-GD-DuplicaMassa
rodar_teste 02.50-SEI-GD-DuplicaMassa
rodar_teste 02.50-SEI-GD-DuplicaMassa
rodar_teste 02.50-SEI-GD-DuplicaMassa
rodar_teste 02.50-SEI-GD-DuplicaMassa
rodar_teste 02.70-SEI-GD-DuplicaMassa2
rodar_teste 02.70-SEI-GD-DuplicaMassa2
rodar_teste 02.70-SEI-GD-DuplicaMassa2
rodar_teste 02.70-SEI-GD-DuplicaMassa2
rodar_teste 02.70-SEI-GD-DuplicaMassa2
rodar_teste 02.70-SEI-GD-DuplicaMassa2


if [ "$SELENIUMTEST_BACKUP" == "true" ]; then
    backup "dmpPosFlood1.dmp"
fi


rodar_teste 02.60-SEI-GD-ConcluirMassa

rodar_teste 03-SEI-GD-PendenciasArq

if [ "$SELENIUMTEST_BACKUP" == "true" ]; then
    backup "dmpPos03.dmp"
fi
  

# FLOOD 2

rodar_teste 02.15-SEI-GD-CadProcEventoInst

rodar_teste 02.75-SEI-GD-DuplicaMassa2
rodar_teste 02.75-SEI-GD-DuplicaMassa2
rodar_teste 02.75-SEI-GD-DuplicaMassa2
rodar_teste 02.75-SEI-GD-DuplicaMassa2
rodar_teste 02.75-SEI-GD-DuplicaMassa2

rodar_teste 02.25-SEI-GD-CadProcEventoInst-Aut

rodar_teste 02.75-SEI-GD-DuplicaMassa2
rodar_teste 02.75-SEI-GD-DuplicaMassa2
rodar_teste 02.75-SEI-GD-DuplicaMassa2
rodar_teste 02.75-SEI-GD-DuplicaMassa2
rodar_teste 02.75-SEI-GD-DuplicaMassa2

rodar_teste 02.35-SEI-GD-CadProcAcessoInf

rodar_teste 02.75-SEI-GD-DuplicaMassa2
rodar_teste 02.75-SEI-GD-DuplicaMassa2
rodar_teste 02.75-SEI-GD-DuplicaMassa2
rodar_teste 02.75-SEI-GD-DuplicaMassa2
rodar_teste 02.75-SEI-GD-DuplicaMassa2

rodar_teste 02.45-SEI-GD-CadProcAcessoInf-Aut

rodar_teste 02.75-SEI-GD-DuplicaMassa2
rodar_teste 02.75-SEI-GD-DuplicaMassa2
rodar_teste 02.75-SEI-GD-DuplicaMassa2
rodar_teste 02.75-SEI-GD-DuplicaMassa2
rodar_teste 02.75-SEI-GD-DuplicaMassa2

backup "dmpPosFlood2.dmp"

data_alterar "2022-12-24 09:00:00"

standalone_destruir

db_restart

rodar_teste 04-SEI-GD-RunAgendamento

rodar_teste 03.10-SEI-GD-PendenciasArq


data_alterar "2024-03-01 09:30:00"

db_restart

standalone_destruir

rodar_teste 04-SEI-GD-RunAgendamento/

rodar_teste 05-SEI-GD-Avaliacao

rodar_teste 01.50-Configs-SegundaEtapa/

rodar_teste 06-SEI-GD-ListagemEliminacao

# pular
# rodar_teste 06.10-SEI-GD-ListagemEliminacao

rodar_teste 02.85-SEI-GD-ConcluirMassa2

rodar_teste 13.00-ArquivamentoLegado


backup "dmpPos13.dmp"


data_alterar "2025-03-06 11:00:00"

standalone_destruir

db_restart

# pular
# rodar_teste 06.20-SEI-GD-VerificarPrazos/

data_alterar "2026-03-11 12:00:00"

standalone_destruir

db_restart

rodar_teste 04-SEI-GD-RunAgendamento/

# pular
# rodar_teste 06.30-SEI-GD-VerificarPrazos/


data_alterar "2036-12-22 13:00:00"

standalone_destruir

db_restart

rodar_teste 04-SEI-GD-RunAgendamento/

rodar_teste 07-SEI-GD-Avaliacao-Recolhimento/

rodar_teste 08-SEI-GD-ListagemRecolhimento/

# o 9 pula
#rodar_teste 09-SEI-GD-EliminarProcsDocs


rodar_teste 11.00-SEI-GD-AvaliacaoMassa 


backup "dmpPos11.dmp"


data_alterar "2037-12-22 15:00:00"

standalone_destruir

db_restart

rodar_teste 04-SEI-GD-RunAgendamento/

rodar_teste 11.01-SEI-GD-AvaliacaoMassa2/

rodar_teste 12.00-ListagensMassa

rodar_teste 14.00-ArquivamentoUnidadesNaoMapeadas

rodar_teste 15.00-ArquivamentoUnidadesDuplas/

rodar_teste 16.00-ArquivamentoAssuntosMultiplos

rodar_teste 01.60-ConfigMultiorgaos


atualizar_base_login_multiorgao


rodar_teste 17.00-Multiorgaos/

rodar_teste 18.00-Anexado

rodar_teste 19.00-Relacionado/