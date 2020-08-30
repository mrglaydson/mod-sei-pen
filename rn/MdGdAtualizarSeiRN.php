<?

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdGdAtualizarSeiRN extends InfraRN {

    protected $objInfraBanco;
    protected $objMetaBD;
    protected $objInfraSequencia;
    protected $objInfraParametro;

    const PARAMETRO_VERSAO = 'VERSAO_MODULO_GESTAO_DOCUMENTAL';
    const VERSAO_100 = '1.0.0';

    public function __construct() {
        ini_set('max_execution_time', '0');
        ini_set('memory_limit', '-1');

        session_start();

        SessaoSEI::getInstance(false);

        InfraDebug::getInstance()->setBolLigado(false);
        InfraDebug::getInstance()->setBolDebugInfra(false);
        InfraDebug::getInstance()->setBolEcho(true);
        InfraDebug::getInstance()->limpar();

        $this->objInfraBanco = $this->inicializarObjInfraIBanco();
        $this->objMetaBD = $this->inicializarObjMetaBD();
        $this->objInfraSequencia = $this->inicializarObjInfraSequencia();
        $this->objInfraParametro = $this->inicializarObjInfraParametro();
    }

    protected function inicializarObjInfraIBanco() {
        return BancoSEI::getInstance();
    }

    protected function inicializarObjMetaBD() {
        $this->objInfraBanco->abrirConexao();
        return new InfraMetaBD($this->objInfraBanco);
    }

    protected function inicializarObjInfraSequencia() {
        return new InfraSequencia($this->objInfraBanco);
    }

    protected function inicializarObjInfraParametro() {
        return new InfraParametro($this->objInfraBanco);
    }

    public function atualizar() {

        if (!$this->objInfraParametro->isSetValor(self::PARAMETRO_VERSAO)) {
            $this->atualizarV100Conectado();
        }

        $strVersao = $this->objInfraParametro->getValor(self::PARAMETRO_VERSAO);

        switch ($strVersao) {
            case self::VERSAO_100:
                echo "A versão mais atual do mdulo est instalada";
                break;
            default:
                echo "versão no encontrada!";
                break;
        }
    }

    /**
     * Atualiza para a primeira versão do mdulo
     */
    protected function atualizarV100Conectado() {
        try {

            // Cria o parâmetro no sei com o número da versão
            $this->objInfraParametro->setValor(self::PARAMETRO_VERSAO, self::VERSAO_100);

            // Criação da tabela de parâmetros
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_parametro (
                nome  ' . $this->objMetaBD->tipoTextoVariavel(100) . '  NOT NULL ,
                valor ' . $this->objMetaBD->tipoTextoVariavel(50) . '  NULL
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_parametro', 'pk_md_gd_parametro_nome', array('nome'));
            
            // Cadastra os parâmetros
            $this->cadastrarParametros();
            
            // Criação da tabela de justificativas arquivamento e desarquivamento
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_justificativa (
                id_justificativa ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                sta_tipo ' . $this->objMetaBD->tipoTextoFixo(1) . ' NOT NULL,
                nome ' . $this->objMetaBD->tipoTextoVariavel(255) . ' NOT NULL,
                descricao ' . $this->objMetaBD->tipoTextoGrande() . ' NOT NULL
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_justificativa', 'pk_md_gd_id_justificativa', array('id_justificativa'));

            if (!$this->objInfraSequencia->verificarSequencia('md_gd_justificativa')) {
                $this->objInfraSequencia->criarSequencia('md_gd_justificativa', '1', '1', '9999999999');
            }

            // Criação da tabela de arquivamentos
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_arquivamento (
                id_arquivamento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_procedimento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_usuario ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                id_unidade_corrente ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                id_unidade_intermediaria ' . $this->objMetaBD->tipoNumero() . ' NULL ,
                id_despacho_arquivamento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_justificativa ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_lista_eliminacao ' . $this->objMetaBD->tipoNumeroGrande() . ' NULL ,
                id_lista_recolhimento ' . $this->objMetaBD->tipoNumeroGrande() . ' NULL,
                dta_arquivamento ' . $this->objMetaBD->tipoDataHora() . ' NOT NULL,
                dta_guarda_corrente ' . $this->objMetaBD->tipoDataHora() . ' NOT NULL,
                dta_guarda_intermediaria ' . $this->objMetaBD->tipoDataHora() . ' NOT NULL,
                guarda_corrente ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                guarda_intermediaria ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                sta_guarda ' . $this->objMetaBD->tipoTextoFixo(2) . ' NOT NULL,
                sta_situacao ' . $this->objMetaBD->tipoTextoFixo(2) . ' NOT NULL,
                sta_destinacao_final ' .$this->objMetaBD->tipoTextoFixo(1). ' NOT NULL,
                sin_condicionante ' .$this->objMetaBD->tipoTextoFixo(1). ' NOT NULL,
                sin_ativo ' . $this->objMetaBD->tipoTextoFixo(1) . ' NOT NULL,
                observacao_eliminacao ' . $this->objMetaBD->tipoTextoGrande() . ' NULL,
                observacao_recolhimento ' . $this->objMetaBD->tipoTextoGrande() . ' NULL
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_arquivamento', 'pk_md_gd_id_arquivamento', array('id_arquivamento'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_arq_procedimento', 'md_gd_arquivamento', array('id_procedimento'), 'procedimento', array('id_procedimento'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_arq_documento', 'md_gd_arquivamento', array('id_despacho_arquivamento'), 'documento', array('id_documento'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_arq_justificativa', 'md_gd_arquivamento', array('id_justificativa'), 'md_gd_justificativa', array('id_justificativa'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_arq_usuario', 'md_gd_arquivamento', array('id_usuario'), 'usuario', array('id_usuario'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_arq_un_corrente', 'md_gd_arquivamento', array('id_unidade_corrente'), 'unidade', array('id_unidade'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_arq_un_intermediaria', 'md_gd_arquivamento', array('id_unidade_intermediaria'), 'unidade', array('id_unidade'));


            if (!$this->objInfraSequencia->verificarSequencia('md_gd_arquivamento')) {
                $this->objInfraSequencia->criarSequencia('md_gd_arquivamento', '1', '1', '9999999999');
            }

            // Cria a tabela de histórico do arquivamento
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_arquivamento_historico (
                id_arquivamento_historico ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_arquivamento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_usuario ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                id_unidade ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                sta_situacao_antiga ' . $this->objMetaBD->tipoTextoFixo(2) . ' NULL,
                sta_situacao_atual ' . $this->objMetaBD->tipoTextoFixo(2) . ' NOT NULL,
                descricao ' . $this->objMetaBD->tipoTextoVariavel(255) . ' NOT NULL,
                dth_historico ' . $this->objMetaBD->tipoDataHora() . ' NOT NULL
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_arquivamento_historico', 'pk_md_gd_id_arq_historico', array('id_arquivamento_historico'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_hist_arquivamento', 'md_gd_arquivamento_historico', array('id_arquivamento'), 'md_gd_arquivamento', array('id_arquivamento'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_hist_usuario', 'md_gd_arquivamento_historico', array('id_usuario'), 'usuario', array('id_usuario'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_hist_unidade', 'md_gd_arquivamento_historico', array('id_unidade'), 'unidade', array('id_unidade'));

            if (!$this->objInfraSequencia->verificarSequencia('md_gd_arquivamento_historico')) {
                $this->objInfraSequencia->criarSequencia('md_gd_arquivamento_historico', '1', '1', '9999999999');
            }

            // Cria a tabela de anotaes das pendências de arquivamento
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_anotacao_pendencia (
                id_anotacao_pendencia ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_procedimento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                anotacao ' . $this->objMetaBD->tipoTextoGrande() . ' NULL
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_anotacao_pendencia', 'pk_md_gd_id_anotacao_pendencia', array('id_anotacao_pendencia'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_an_id_procedimento', 'md_gd_anotacao_pendencia', array('id_procedimento'), 'procedimento', array('id_procedimento'));

            if (!$this->objInfraSequencia->verificarSequencia('md_gd_anotacao_pendencia')) {
                $this->objInfraSequencia->criarSequencia('md_gd_anotacao_pendencia', '1', '1', '9999999999');
            }

            // Criação da tabela de desarquivamento
            $this->objInfraBanco->executarSql(' CREATE TABLE md_gd_desarquivamento (
                id_desarquivamento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_arquivamento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_procedimento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_despacho_desarquivamento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_justificativa ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                dta_desarquivamento ' . $this->objMetaBD->tipoDataHora() . ' NOT NULL
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_desarquivamento', 'pk_gd_id_desarquivamento', array('id_desarquivamento'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_desarq_arquivamento', 'md_gd_desarquivamento', array('id_arquivamento'), 'md_gd_arquivamento', array('id_arquivamento'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_desarq_procedimento', 'md_gd_desarquivamento', array('id_procedimento'), 'procedimento', array('id_procedimento'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_desarq_documento', 'md_gd_desarquivamento', array('id_despacho_desarquivamento'), 'documento', array('id_documento'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_desarq_justificativa', 'md_gd_desarquivamento', array('id_justificativa'), 'md_gd_justificativa', array('id_justificativa'));


            if (!$this->objInfraSequencia->verificarSequencia('md_gd_desarquivamento')) {
                $this->objInfraSequencia->criarSequencia('md_gd_desarquivamento', '1', '1', '9999999999');
            }

            // Criação da tabela de modelos de documento
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_modelo_documento (
                nome  ' . $this->objMetaBD->tipoTextoVariavel(100) . '  NOT NULL ,
                valor ' . $this->objMetaBD->tipoTextoGrande() . ' NOT NULL 
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_modelo_documento', 'pk_md_gd_modelo_documento_nome', array('nome'));
            
            // Cadastra os modelos padres
            $this->cadastrarModelos();

            // Criação da tabela de unidades de arquivamento
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_unidade_arquivamento (
                    id_unidade_arquivamento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                    id_unidade_origem ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                    id_unidade_destino ' . $this->objMetaBD->tipoNumero() . ' NOT NULL
              )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_unidade_arquivamento', 'pk_md_gd_id_unidade_arq', array('id_unidade_arquivamento'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_unidade_origem', 'md_gd_unidade_arquivamento', array('id_unidade_origem'), 'unidade', array('id_unidade'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_unidade_destino', 'md_gd_unidade_arquivamento', array('id_unidade_destino'), 'unidade', array('id_unidade'));


            if (!$this->objInfraSequencia->verificarSequencia('md_gd_unidade_arquivamento')) {
                $this->objInfraSequencia->criarSequencia('md_gd_unidade_arquivamento', '1', '1', '9999999999');
            }

            // Criação da tabela da listagem de Eliminação
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_lista_eliminacao (
                id_lista_eliminacao ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_procedimento_eliminacao ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_documento_eliminacao ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                numero ' . $this->objMetaBD->tipoTextoVariavel(50) . ' NOT NULL,
                qtd_processos ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                sin_documentos_fisicos ' . $this->objMetaBD->tipoTextoFixo(1) . ' NOT NULL,
                dth_emissao_listagem ' . $this->objMetaBD->tipoDataHora() . ' NOT NULL,
                ano_limite_inicio ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                ano_limite_fim ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                situacao ' . $this->objMetaBD->tipoTextoFixo(2) . ' NOT NULL
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_lista_eliminacao', 'pk_md_gd_id_lista_eliminacao', array('id_lista_eliminacao'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_list_eli_procedimento', 'md_gd_lista_eliminacao', array('id_procedimento_eliminacao'), 'procedimento', array('id_procedimento'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_list_eli_documento', 'md_gd_lista_eliminacao', array('id_documento_eliminacao'), 'documento', array('id_documento'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_arquivamento_gd_list_eli', 'md_gd_arquivamento', array('id_lista_eliminacao'), 'md_gd_lista_eliminacao', array('id_lista_eliminacao'));

            if (!$this->objInfraSequencia->verificarSequencia('md_gd_lista_eliminacao')) {
                $this->objInfraSequencia->criarSequencia('md_gd_lista_eliminacao', '1', '1', '9999999999');
            }

            // Cria a tabela ternária entre a listagem de Eliminação e os procedimentos
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_lista_elim_procedimento (
                id_lista_eliminacao ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_procedimento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_lista_elim_procedimento', 'pk_md_gd_list_eli_procedimento', array('id_lista_eliminacao', 'id_procedimento'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_list_eli_proc_lista_elim', 'md_gd_lista_elim_procedimento', array('id_lista_eliminacao'), 'md_gd_lista_eliminacao', array('id_lista_eliminacao'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_list_eli_proc_proc', 'md_gd_lista_elim_procedimento', array('id_procedimento'), 'procedimento', array('id_procedimento'));

            // Cria a tabela que relaciona os documentos físicos eliminados
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_documento_fisico_elim (
                id_documento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_lista_eliminacao ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_documento_fisico_elim', 'pk_md_gd_documento_fisico_elim', array('id_documento', 'id_lista_eliminacao'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_doc_fisico_elim_doc', 'md_gd_documento_fisico_elim', array('id_documento'), 'documento', array('id_documento'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_doc_fisico_elim_list_eli', 'md_gd_documento_fisico_elim', array('id_lista_eliminacao'), 'md_gd_lista_eliminacao', array('id_lista_eliminacao'));


            // Criação da tabela da listagem de recolhimento
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_lista_recolhimento (
                id_lista_recolhimento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                numero ' . $this->objMetaBD->tipoTextoVariavel(50) . ' NOT NULL,
                dth_emissao_listagem ' . $this->objMetaBD->tipoDataHora() . ' NOT NULL,
                ano_limite_inicio ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                ano_limite_fim ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                qtd_processos ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                sin_documentos_fisicos ' . $this->objMetaBD->tipoTextoFixo(1) . ' NOT NULL,
                situacao ' . $this->objMetaBD->tipoTextoFixo(2) . ' NOT NULL

            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_lista_recolhimento', 'pk_md_gd_id_lista_recolhimento', array('id_lista_recolhimento'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_arquivamento_list_rec', 'md_gd_arquivamento', array('id_lista_recolhimento'), 'md_gd_lista_recolhimento', array('id_lista_recolhimento'));

            if (!$this->objInfraSequencia->verificarSequencia('md_gd_lista_recolhimento')) {
                $this->objInfraSequencia->criarSequencia('md_gd_lista_recolhimento', '1', '1', '9999999999');
            }

            // Cria a tabela ternária entre a listagem de recolhimento e procedimento
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_lista_recol_procedimento (
                id_lista_recolhimento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_procedimento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_lista_recol_procedimento', 'pk_md_gd_list_rec_procedimento', array('id_lista_recolhimento', 'id_procedimento'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_list_rec_proc_list_rec', 'md_gd_lista_recol_procedimento', array('id_lista_recolhimento'), 'md_gd_lista_recolhimento', array('id_lista_recolhimento'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_list_rec_proc_proc', 'md_gd_lista_recol_procedimento', array('id_procedimento'), 'procedimento', array('id_procedimento'));

            // Cria a tabela de relacionamento de documentos físicos recolhidos
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_documento_fisico_recol (
                id_documento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_lista_recolhimento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_documento_fisico_recol', 'pk_md_gd_documento_fisico_rec', array('id_documento', 'id_lista_recolhimento'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_doc_fisico_rec_documento', 'md_gd_documento_fisico_recol', array('id_documento'), 'documento', array('id_documento'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_doc_fisico_rec_list_rec', 'md_gd_documento_fisico_recol', array('id_lista_recolhimento'), 'md_gd_lista_recolhimento', array('id_lista_recolhimento'));

            // Cria a tabela que armazena as eliminaes
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_eliminacao (
                id_eliminacao ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_usuario ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                assinante ' . $this->objMetaBD->tipoTextoVariavel(255) . ' NOT NULL,
                id_lista_eliminacao ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_unidade ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                id_veiculo_publicacao ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                id_secao_imprensa_nacional ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                pagina ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                dth_data_imprensa ' . $this->objMetaBD->tipoDataHora() . ' NOT NULL,
                dth_eliminacao ' . $this->objMetaBD->tipoDataHora() . ' NOT NULL
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_eliminacao', 'pk_md_gd_eliminacao', array('id_eliminacao'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_elim_usuario', 'md_gd_eliminacao', array('id_usuario'), 'usuario', array('id_usuario'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_elim_unidade', 'md_gd_eliminacao', array('id_unidade'), 'unidade', array('id_unidade'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_elim_lista_eliminacao', 'md_gd_eliminacao', array('id_lista_eliminacao'), 'md_gd_lista_eliminacao', array('id_lista_eliminacao'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_elim_veiculo', 'md_gd_eliminacao', array('id_veiculo_publicacao'), 'veiculo_publicacao', array('id_veiculo_publicacao'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_elim_secao', 'md_gd_eliminacao', array('id_secao_imprensa_nacional'), 'secao_imprensa_nacional', array('id_secao_imprensa_nacional'));

            if (!$this->objInfraSequencia->verificarSequencia('md_gd_eliminacao')) {
                $this->objInfraSequencia->criarSequencia('md_gd_eliminacao', '1', '1', '9999999999');
            }

            // Cria a tabela que armazena os recolhimentos
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_recolhimento (
                id_recolhimento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_usuario ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                id_unidade ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                id_lista_recolhimento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                dth_recolhimento ' . $this->objMetaBD->tipoDataHora() . ' NOT NULL
            )');

            if (!$this->objInfraSequencia->verificarSequencia('md_gd_recolhimento')) {
                $this->objInfraSequencia->criarSequencia('md_gd_recolhimento', '1', '1', '9999999999');
            }

            $this->objMetaBD->adicionarChavePrimaria('md_gd_recolhimento', 'pk_md_gd_recolhimento', array('id_recolhimento'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_rec_usuario', 'md_gd_recolhimento', array('id_usuario'), 'usuario', array('id_usuario'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_rec_unidade', 'md_gd_recolhimento', array('id_unidade'), 'unidade', array('id_unidade'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_rec_lista_recolhimento', 'md_gd_recolhimento', array('id_lista_recolhimento'), 'md_gd_lista_recolhimento', array('id_lista_recolhimento'));
     
            // Adiciona o agendamento
            $objInfraAgendamentoDTO = new InfraAgendamentoTarefaDTO();
            $objInfraAgendamentoDTO->setStrDescricao('Arquivamento em fase intermediária');
            $objInfraAgendamentoDTO->setStrComando('MdGdAgendamentoRN::verificarTempoGuarda');
            $objInfraAgendamentoDTO->setStrStaPeriodicidadeExecucao('D');
            $objInfraAgendamentoDTO->setStrPeriodicidadeComplemento('0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23');
            $objInfraAgendamentoDTO->setStrSinAtivo('S');
            $objInfraAgendamentoDTO->setStrSinSucesso('S');
    
            $objAtividadeBD = new  AgendamentoBD(BancoSEI::getInstance());
            $objAtividadeBD->cadastrar($objInfraAgendamentoDTO);
            
        } catch (Exception $ex) {
            throw new InfraException('Erro ao atualizar a versão 1.0.0 do mdulo de gestão documental', $ex);
        }
    }


    /**
     * MTODOS AUXILIARES DA INSTALAO
     */

     /**
      * Cadastra os parâmetros de configurao padres do módulo
      *
      * @return void
      */
    protected function cadastrarParametros(){
        $despachoArquivamento = $this->cadastrarTipoDocumento(1, 'Termo de Arquivamento', 'Termo automático para arquivamento de processos no sistema', SerieRN::$TA_INTERNO, 34, SerieRN::$TN_SEQUENCIAL_UNIDADE);
        $despachoDesarquivamento = $this->cadastrarTipoDocumento(1, 'Termo de Desarquivamento', 'Termo automático para desarquivamento de processos no sistema', SerieRN::$TA_INTERNO, 34, SerieRN::$TN_SEQUENCIAL_UNIDADE);
        $tipoProcedimentoListagemEliminacao = $this->cadastrarTipoProcedimento('Processo de listagem de eliminação', 'Processo de listagem de eliminação');
        $tipoDocumentoListagemEliminacao = $this->cadastrarTipoDocumento(1, 'Termo de Listagem de Eliminação', 'Termo automático para listagem de eliminação de processos no sistema', SerieRN::$TA_INTERNO, 34, SerieRN::$TN_SEQUENCIAL_UNIDADE);
        $tipoDocumentoEliminacao = $this->cadastrarTipoDocumento(1, 'Termo de Eliminação', 'Termo automático para eliminação de processos no sistema', SerieRN::$TA_INTERNO, 34, SerieRN::$TN_SEQUENCIAL_UNIDADE);;
        $tipoProcedimentoEliminacao = $this->cadastrarTipoProcedimento('Processo de Eliminação', 'Processo de eliminação');

        $this->cadastrarParametro('DESPACHO_ARQUIVAMENTO', $despachoArquivamento);
        $this->cadastrarParametro('DESPACHO_DESARQUIVAMENTO', $despachoDesarquivamento);
        $this->cadastrarParametro('TIPO_PROCEDIMENTO_LISTAGEM_ELIMINACAO', $tipoProcedimentoListagemEliminacao);
        $this->cadastrarParametro('TIPO_DOCUMENTO_LISTAGEM_ELIMINACAO', $tipoDocumentoListagemEliminacao);
        $this->cadastrarParametro('TIPO_DOCUMENTO_ELIMINACAO', $tipoDocumentoEliminacao);
        $this->cadastrarParametro('TIPO_PROCEDIMENTO_ELIMINACAO', $tipoProcedimentoEliminacao);

    }

    /**
     * Cadastra um parâmetro no mdulo
     *
     * @param string $strNome
     * @param string $strValor
     * @return void
     */
    protected function cadastrarParametro($strNome, $strValor) {
        $objMdGdParametroDTO = new MdGdParametroDTO();
        $objMdGdParametroDTO->setStrNome($strNome);
        $objMdGdParametroDTO->setStrValor($strValor);

        $objMdGdParametroRN = new MdGdParametroRN();
        $objMdGdParametroRN->cadastrar($objMdGdParametroDTO);
    }

    /**
     * Cadastra os modelos de documento padres do mdulo
     *
     * @return void
     */
    public function cadastrarModelos(){
        $strModeloDespachoArquivamento = '<table summary="Tabela de Variáveis Disponíveis" width="99%">
                <tbody>
                    <tr>
                        <td>@motivo@</td>
                        <td>Motivo do arquivamento</td>
                    </tr>
                    <tr>
                        <td>@data_arquivamento@</td>
                        <td>Data do arquivamento</td>
                    </tr>
                    <tr>
                        <td>@responsavel_arquivamento@</td>
                        <td>Respons&aacute;vel pelo arquivamento</td>
                    </tr>
                </tbody>
            </table>
        ';

        $strModeloDespachoDesarquivamento = '<table summary="Tabela de Variveis Disponveis" width="99%">
                <tbody>
                    <tr>
                        <td>@motivo@</td>
                        <td>Motivo do desarquivamento</td>
                    </tr>
                    <tr>
                        <td>@data_desarquivamento@</td>
                        <td>Data do desarquivamento</td>
                    </tr>
                    <tr>
                        <td>@responsavel_desarquivamento@</td>
                        <td>Respons&aacute;vel pelo desarquivamento</td>
                    </tr>
                </tbody>
            </table>
        ';

        $strModeloListagemEliminacao = '<table summary="Tabela de Variveis Disponveis" width="99%">
                <tbody>
                    <tr>
                        <td>@unidade@</td>
                        <td>Unidade geradora</td>
                    </tr>
                    <tr>
                        <td>@numero_listagem@</td>
                        <td>N&uacute;mero da listagem</td>
                    </tr>
                    <tr>
                        <td>@folha@</td>
                        <td>N&uacute;mero de folhas</td>
                    </tr>
                    <tr>
                        <td>@tabela@</td>
                        <td>Tabela de detalhamento da listagem</td>
                    </tr>
                    <tr>
                        <td>@mensuracao_total@</td>
                        <td>Mensura&ccedil;&atilde;o total</td>
                    </tr>
                    <tr>
                        <td>@datas_limites_gerais@</td>
                        <td>Datas limite do arquivamento</td>
                    </tr>
                </tbody>
            </table>
            ';

        $strModeloDocumentoEliminacao = '<table summary="Tabela de Variveis Disponveis" width="99%">
                    <tbody>
                        <tr>
                            <td>@unidade@</td>
                            <td>Unidade geradora</td>
                        </tr>
                        <tr>
                            <td>@data_eliminacao@</td>
                            <td>Data do arquivamento</td>
                        </tr>
                        <tr>
                            <td>@responsavel_eliminacao@</td>
                            <td>Respons&aacute;vel pelo arquivamento</td>
                        </tr>
                    </tbody>
                </table>
                ';

        $this->cadastrarModeloDocumento('MODELO_DESPACHO_ARQUIVAMENTO', $strModeloDespachoArquivamento);
        $this->cadastrarModeloDocumento('MODELO_DESPACHO_DESARQUIVAMENTO', $strModeloDespachoDesarquivamento);
        $this->cadastrarModeloDocumento('MODELO_LISTAGEM_ELIMINACAO', $strModeloListagemEliminacao);
        $this->cadastrarModeloDocumento('MODELO_DOCUMENTO_ELIMINACAO', $strModeloDocumentoEliminacao);
    }

    /**
     * Cadastra um modelo de documento
     *
     * @param string $strNome
     * @param string $strValor
     * @return void
     */
    protected function cadastrarModeloDocumento($strNome, $strValor) {
        $objMdGdModeloDocumentoDTO = new MdGdModeloDocumentoDTO();
        $objMdGdModeloDocumentoDTO->setStrNome($strNome);
        $objMdGdModeloDocumentoDTO->setStrValor($strValor);

        $objMdGdModeloDocumentoRN = new MdGdModeloDocumentoRN();
        $objMdGdModeloDocumentoRN->cadastrar($objMdGdModeloDocumentoDTO);
    }
    
    /**
     * Mtodo simplificado para cadastro de um tipo de documento
     *
     * @param integer $numIdGrupoSerie
     * @param string $strNome
     * @param string $strDescricao
     * @param string $strStaAplicabilidade
     * @param integer $numIdModelo
     * @param string $strStaNumeracao
     * @return integer
     */
    protected function cadastrarTipoDocumento($numIdGrupoSerie, $strNome, $strDescricao, $strStaAplicabilidade, $numIdModelo, $strStaNumeracao){
        $objSerieDTO = new SerieDTO();
        $objSerieDTO->setNumIdSerie(null);
        $objSerieDTO->setNumIdGrupoSerie($numIdGrupoSerie);
        $objSerieDTO->setStrNome($strNome);
        $objSerieDTO->setStrDescricao($strDescricao);
        $objSerieDTO->setStrStaAplicabilidade($strStaAplicabilidade);
        $objSerieDTO->setNumIdModelo($numIdModelo);        
        $objSerieDTO->setNumIdModeloEdoc(null);
        $objSerieDTO->setNumIdTipoFormulario(null);
        $objSerieDTO->setStrStaNumeracao($strStaNumeracao);
        $objSerieDTO->setStrSinAssinaturaPublicacao('S');
        $objSerieDTO->setStrSinInteressado('N');
        $objSerieDTO->setStrSinDestinatario('N');
        $objSerieDTO->setStrSinInterno('N');
        $objSerieDTO->setStrSinAtivo('S');
        $objSerieDTO->setArrObjRelSerieAssuntoDTO(array());
        $objSerieDTO->setArrObjSerieRestricaoDTO(array());
        $objSerieDTO->setArrObjRelSerieVeiculoPublicacaoDTO(array());

        $objSerieRN = new SerieRN();
        $objSerieDTO = $objSerieRN->cadastrarRN0642($objSerieDTO);
        return $objSerieDTO->getNumIdSerie();
    }

    /**
     * Mtodo simplificado para cadastro de um tipo de procedimento
     *
     * @param string $strNome
     * @param string $strDescricao
     * @return integer
     */
    protected function cadastrarTipoProcedimento($strNome, $strDescricao){
        $objTipoProcedimentoDTO = new TipoProcedimentoDTO();
        $objTipoProcedimentoDTO->setArrObjRelTipoProcedimentoAssuntoDTO(array());
        $objTipoProcedimentoDTO->setArrObjTipoProcedRestricaoDTO(array());
        $objTipoProcedimentoDTO->setNumIdTipoProcedimento(null);
        $objTipoProcedimentoDTO->setStrNome($strNome);
        $objTipoProcedimentoDTO->setStrDescricao($strDescricao);
        $objTipoProcedimentoDTO->setStrStaGrauSigiloSugestao(null);
        $objTipoProcedimentoDTO->setNumIdHipoteseLegalSugestao(null);
        $objTipoProcedimentoDTO->setStrSinInterno('N');
        $objTipoProcedimentoDTO->setStrSinOuvidoria('N');
        $objTipoProcedimentoDTO->setStrSinIndividual('N');

        // Nveis de acesso permitidos
        $arrObjNivelAcessoPermitidoDTO = array();
        
        $objNivelAcessoPermitidoDTO = new NivelAcessoPermitidoDTO();
        $objNivelAcessoPermitidoDTO->setStrStaNivelAcesso(ProtocoloRN::$NA_PUBLICO);
        $arrObjNivelAcessoPermitidoDTO[] = $objNivelAcessoPermitidoDTO; 

        $objNivelAcessoPermitidoDTO = new NivelAcessoPermitidoDTO();
        $objNivelAcessoPermitidoDTO->setStrStaNivelAcesso(ProtocoloRN::$NA_RESTRITO);
        $arrObjNivelAcessoPermitidoDTO[] = $objNivelAcessoPermitidoDTO; 

        $objTipoProcedimentoDTO->setArrObjNivelAcessoPermitidoDTO($arrObjNivelAcessoPermitidoDTO);

        $objTipoProcedimentoDTO->setStrStaNivelAcessoSugestao(ProtocoloRN::$NA_PUBLICO);
        $objTipoProcedimentoDTO->setStrSinAtivo('S');

        $objTipoProcedimentoRN = new TipoProcedimentoRN();
        $objTipoProcedimentoDTO = $objTipoProcedimentoRN->cadastrarRN0265($objTipoProcedimentoDTO); 
        return $objTipoProcedimentoDTO->getNumIdTipoProcedimento();
    }


}

?>