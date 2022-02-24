#!/bin/bash

#ATENCAO ANTES DE RODAR DO ZERO, O AMBIENTE DEVE TER SIDO CONSTRUIDO E ESTAR NA DATA 2021-12-22 7AM, LEIA O README DOS TESTES

set -e


paralelizar(){
  
  pids=""
  i=1
  for p in $@; do
      
      pytest -x --tb=short seleniumIDE/$p/exportedpython/ &
      pids[$i]=$!
      
      i=$(($i+1))
  done
  
  for pid in ${pids[*]}; do
      wait $pid
  done

}


pytest -x --tb=short seleniumIDE/01-Configs/exportedpython 

pytest -x --tb=short seleniumIDE/02.00-SEI-GD-CadArquivaProc/exportedpython 

paralelizar 02.10-SEI-GD-CadProcEventoInst 02.20-SEI-GD-CadProcEventoInst-Aut

#x6
paralelizar 02.50-SEI-GD-DuplicaMassa 02.50-SEI-GD-DuplicaMassa
paralelizar 02.50-SEI-GD-DuplicaMassa 02.50-SEI-GD-DuplicaMassa
paralelizar 02.50-SEI-GD-DuplicaMassa 02.50-SEI-GD-DuplicaMassa
paralelizar 02.70-SEI-GD-DuplicaMassa2 02.70-SEI-GD-DuplicaMassa2
paralelizar 02.70-SEI-GD-DuplicaMassa2 02.70-SEI-GD-DuplicaMassa2
paralelizar 02.70-SEI-GD-DuplicaMassa2 02.70-SEI-GD-DuplicaMassa2

paralelizar 02.30-SEI-GD-CadProcAcessoInf 02.40-SEI-GD-CadProcAcessoInf-Aut

#x6
paralelizar 02.50-SEI-GD-DuplicaMassa 02.50-SEI-GD-DuplicaMassa
paralelizar 02.50-SEI-GD-DuplicaMassa 02.50-SEI-GD-DuplicaMassa
paralelizar 02.50-SEI-GD-DuplicaMassa 02.50-SEI-GD-DuplicaMassa
paralelizar 02.70-SEI-GD-DuplicaMassa2 02.70-SEI-GD-DuplicaMassa2
paralelizar 02.70-SEI-GD-DuplicaMassa2 02.70-SEI-GD-DuplicaMassa2
paralelizar 02.70-SEI-GD-DuplicaMassa2 02.70-SEI-GD-DuplicaMassa2

docker exec -it docker-compose_db_1 bash -c "mysqldump -proot --databases sei sip > dmpPosFlood1.dmp"


pytest -x --tb=short seleniumIDE/02.60-SEI-GD-ConcluirMassa/exportedpython

pytest -x --tb=short seleniumIDE/03-SEI-GD-PendenciasArq/exportedpython


docker exec -it docker-compose_db_1 bash -c "mysqldump -proot --databases sei sip > dmpPos03.dmp"

# FLOOD 2


pytest -x --tb=short seleniumIDE/02.15-SEI-GD-CadProcEventoInst/exportedpython

paralelizar 02.75-SEI-GD-DuplicaMassa2 02.75-SEI-GD-DuplicaMassa2 02.75-SEI-GD-DuplicaMassa2
paralelizar 02.75-SEI-GD-DuplicaMassa2 02.75-SEI-GD-DuplicaMassa2

pytest -x --tb=short seleniumIDE/02.25-SEI-GD-CadProcEventoInst-Aut/exportedpython

paralelizar 02.75-SEI-GD-DuplicaMassa2 02.75-SEI-GD-DuplicaMassa2 02.75-SEI-GD-DuplicaMassa2
paralelizar 02.75-SEI-GD-DuplicaMassa2 02.75-SEI-GD-DuplicaMassa2

pytest -x --tb=short seleniumIDE/02.35-SEI-GD-CadProcAcessoInf/exportedpython

paralelizar 02.75-SEI-GD-DuplicaMassa2 02.75-SEI-GD-DuplicaMassa2 02.75-SEI-GD-DuplicaMassa2
paralelizar 02.75-SEI-GD-DuplicaMassa2 02.75-SEI-GD-DuplicaMassa2

pytest -x --tb=short seleniumIDE/02.45-SEI-GD-CadProcAcessoInf-Aut/exportedpython

paralelizar 02.75-SEI-GD-DuplicaMassa2 02.75-SEI-GD-DuplicaMassa2 02.75-SEI-GD-DuplicaMassa2
paralelizar 02.75-SEI-GD-DuplicaMassa2 02.75-SEI-GD-DuplicaMassa2

docker exec -it docker-compose_db_1 bash -c "mysqldump -proot --databases sei sip > dmpPosFlood2.dmp"

a=$(date | sed "s| -03||g")
b=$(echo -n $a | tail -c 8)
sudo date -s "2022-12-24 ${b}"
sleep 5

pytest -x --tb=short seleniumIDE/04-SEI-GD-RunAgendamento/exportedpython/test_04SEIGDRunAgendamento4.py

pytest -x --tb=short seleniumIDE/03.10-SEI-GD-PendenciasArq/exportedpython

a=$(date | sed "s| -03||g")
b=$(echo -n $a | tail -c 8)
sudo date -s "2024-03-01  ${b}"
sleep 5

pytest -x --tb=short seleniumIDE/04-SEI-GD-RunAgendamento/exportedpython/test_04SEIGDRunAgendamento4.py


pytest -x --tb=short seleniumIDE/05-SEI-GD-Avaliacao/exportedpython/test_05SEIGDAvaliacao4.py

pytest -x --tb=short seleniumIDE/01.50-Configs-SegundaEtapa/exportedpython/test_00ConfigSIP4.py
pytest -x --tb=short seleniumIDE/01.50-Configs-SegundaEtapa/exportedpython/test_01ConfigSEI4.py

pytest -x --tb=short seleniumIDE/06-SEI-GD-ListagemEliminacao/exportedpython

pytest -x --tb=short seleniumIDE/06.10-SEI-GD-ListagemEliminacao/exportedpython

pytest -x --tb=short seleniumIDE/02.85-SEI-GD-ConcluirMassa2/exportedpython

pytest -x --tb=short seleniumIDE/13.00-ArquivamentoLegado/exportedpython

docker exec -it docker-compose_db_1 bash -c "mysqldump -proot --databases sei sip > dmpPos13.dmp"

a=$(date | sed "s| -03||g")
b=$(echo -n $a | tail -c 8)
sudo date -s "2025-03-06 ${b}"
sleep 5

pytest -x --tb=short seleniumIDE/06.20-SEI-GD-VerificarPrazos/exportedpython/test_0620SEIGDVerificarPrazos4.py

a=$(date | sed "s| -03||g")
b=$(echo -n $a | tail -c 8)
sudo date -s "2026-03-11 ${b}"
sleep 5

pytest -x --tb=short seleniumIDE/04-SEI-GD-RunAgendamento/exportedpython/test_04SEIGDRunAgendamento4.py

pytest -x --tb=short seleniumIDE/06.30-SEI-GD-VerificarPrazos/exportedpython/test_0630SEIGDVerificarPrazos4.py


a=$(date | sed "s| -03||g")
b=$(echo -n $a | tail -c 8)
sudo date -s "2036-12-22 ${b}"
sleep 5

pytest -x --tb=short seleniumIDE/04-SEI-GD-RunAgendamento/exportedpython/test_04SEIGDRunAgendamento4.py


pytest -x --tb=short seleniumIDE/07-SEI-GD-Avaliacao-Recolhimento/exportedpython/

pytest -x --tb=short seleniumIDE/08-SEI-GD-ListagemRecolhimento/exportedpython/

# o 9 pula
#pytest -x --tb=short seleniumIDE/09-SEI-GD-EliminarProcsDocs/exportedpython


pytest -x --tb=short seleniumIDE/11.00-SEI-GD-AvaliacaoMassa/exportedpython 

docker exec -it docker-compose_db_1 bash -c "mysqldump -proot --databases sei sip > dmpPos11.dmp"

a=$(date | sed "s| -03||g")
b=$(echo -n $a | tail -c 8)
sudo date -s "2037-12-22 ${b}"
sleep 5

pytest -x --tb=short seleniumIDE/04-SEI-GD-RunAgendamento/exportedpython/test_04SEIGDRunAgendamento4.py

pytest -x --tb=short seleniumIDE/11.01-SEI-GD-AvaliacaoMassa2/exportedpython/

pytest -x --tb=short seleniumIDE/12.00-ListagensMassa/exportedpython

pytest -x --tb=short seleniumIDE/14.00-ArquivamentoUnidadesNaoMapeadas/exportedpython

pytest -x --tb=short seleniumIDE/15.00-ArquivamentoUnidadesDuplas/exportedpython/

pytest -x --tb=short seleniumIDE/16.00-ArquivamentoAssuntosMultiplos/exportedpython

pytest -x --tb=short seleniumIDE/01.60-ConfigMultiorgaos/exportedpython

docker exec -it docker-compose_db_1 bash -c "mysql -proot -e \"update sip.orgao set sin_autenticar='N';\""

pytest -x --tb=short seleniumIDE/17.00-Multiorgaos/exportedpython/test_1700Multiorgaos4.py

pytest -x --tb=short seleniumIDE/18.00-Anexado/exportedpython

pytest -x --tb=short seleniumIDE/19.00-Relacionado/exportedpython/