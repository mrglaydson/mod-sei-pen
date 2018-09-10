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
                echo "A versуo mais atual do mѓdulo estс instalada";
                break;
            default:
                echo "Versуo nуo encontrada!";
                break;
        }
    }

    protected function cadastrarModeloDocumento($strNome, $strValor) {
        $objMdGdModeloDocumentoDTO = new MdGdModeloDocumentoDTO();
        $objMdGdModeloDocumentoDTO->setStrNome($strNome);
        $objMdGdModeloDocumentoDTO->setStrValor($strValor);

        $objMdGdModeloDocumentoRN = new MdGdModeloDocumentoRN();
        $objMdGdModeloDocumentoRN->cadastrar($objMdGdModeloDocumentoDTO);
    }

    protected function cadastrarParametro($strNome, $strValor) {
        $objMdGdParametroDTO = new MdGdParametroDTO();
        $objMdGdParametroDTO->setStrNome($strNome);
        $objMdGdParametroDTO->setStrValor($strValor);

        $objMdGdParametroRN = new MdGdParametroRN();
        $objMdGdParametroRN->cadastrar($objMdGdParametroDTO);
    }

    protected function atualizarV100Conectado() {
        try {
            // Cria o parтmetro no sei com o nњmero da versуo
            $this->objInfraParametro->setValor(self::PARAMETRO_VERSAO, self::VERSAO_100);

            // Criaчуo da tabela de parтmetros
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_parametro (
                nome  ' . $this->objMetaBD->tipoTextoVariavel(100) . '  NOT NULL ,
                valor ' . $this->objMetaBD->tipoTextoVariavel(50) . ' 
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_parametro', 'pk_md_gd_parametro_nome', array('nome'));

            $this->cadastrarParametro('DESPACHO_ARQUIVAMENTO', '');
            $this->cadastrarParametro('DESPACHO_DESARQUIVAMENTO', '');
            $this->cadastrarParametro('TIPO_PROCEDIMENTO_LISTAGEM_ELIMINACAO', '');
            $this->cadastrarParametro('TIPO_DOCUMENTO_LISTAGEM_ELIMINACAO', '');

            // Criaчуo da tabela de justificativas arquivamento e desarquivamento
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_justificativa (
                id_justificativa ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                sta_tipo ' . $this->objMetaBD->tipoTextoFixo(1) . ' NOT NULL,
                nome ' . $this->objMetaBD->tipoTextoVariavel(255) . ' NOT NULL,
                descricao ' . $this->objMetaBD->tipoTextoGrande() . ' NOT NULL
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_justificativa', 'pk_md_gd_justificativa_id_justificativa', array('id_justificativa'));


            if (!$this->objInfraSequencia->verificarSequencia('md_gd_justificativa')) {
                $this->objInfraSequencia->criarSequencia('md_gd_justificativa', '1', '1', '9999999999');
            }

            // Criaчуo da tabela de arquivamentos
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_arquivamento (
                id_arquivamento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_procedimento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_usuario ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                id_unidade_corrente ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                id_unidade_intermediaria ' . $this->objMetaBD->tipoNumero() . ' ,
                id_despacho_arquivamento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_justificativa ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_lista_eliminacao ' . $this->objMetaBD->tipoNumeroGrande() . ' ,
                id_lista_recolhimento ' . $this->objMetaBD->tipoNumeroGrande() . ' ,
                dta_arquivamento ' . $this->objMetaBD->tipoDataHora() . ' NOT NULL,
                dt_guarda_corrente ' . $this->objMetaBD->tipoDataHora() . ' NOT NULL,
                dt_guarda_intermediaria ' . $this->objMetaBD->tipoDataHora() . ' NOT NULL,
                guarda_corrente ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                guarda_intermediaria ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                sta_guarda ' . $this->objMetaBD->tipoTextoFixo(1) . ' NOT NULL,
                sin_ativo ' . $this->objMetaBD->tipoTextoFixo(1) . ' NOT NULL,
                situacao ' . $this->objMetaBD->tipoTextoFixo(2) . ' NOT NULL,
                observacao_eliminacao ' . $this->objMetaBD->tipoTextoGrande() . ',
                observacao_recolhimento ' . $this->objMetaBD->tipoTextoGrande() . '
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_arquivamento', 'pk_md_gd_arquivamento_id_arquivamento', array('id_arquivamento'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_arquivamento_procedimento', 'md_gd_arquivamento', array('id_procedimento'), 'procedimento', array('id_procedimento'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_arquivamento_documento', 'md_gd_arquivamento', array('id_despacho_arquivamento'), 'documento', array('id_documento'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_arquivamento_md_gd_justificativa', 'md_gd_arquivamento', array('id_justificativa'), 'md_gd_justificativa', array('id_justificativa'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_arquivamento_usuario', 'md_gd_arquivamento', array('id_usuario'), 'usuario', array('id_usuario'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_arquivamento_unidade_corrente', 'md_gd_arquivamento', array('id_unidade_corrente'), 'unidade', array('id_unidade'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_arquivamento_unidade_intermediaria', 'md_gd_arquivamento', array('id_unidade_intermediaria'), 'unidade', array('id_unidade'));


            if (!$this->objInfraSequencia->verificarSequencia('md_gd_arquivamento')) {
                $this->objInfraSequencia->criarSequencia('md_gd_arquivamento', '1', '1', '9999999999');
            }

            // Cria a tabela de histѓrico de situaчѕes do arquivamento
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_historico_arquivamento (
                    id_historico_arquivamento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                    id_arquivamento ' . $this->objMetaBD->tipoNumeroGrande() . '  NOT NULL,
                    id_usuario ' . $this->objMetaBD->tipoNumero() . '  NOT NULL,
                    situacao ' . $this->objMetaBD->tipoTextoFixo(2) . '  NOT NULL,
                    dta_atualizacao ' . $this->objMetaBD->tipoDataHora() . '  NOT NULL
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_historico_arquivamento', 'pk_md_gd_historico_arquivamento_id_historico_arquivamento', array('id_historico_arquivamento'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_historico_arquivamento_usuario', 'md_gd_historico_arquivamento', array('id_usuario'), 'usuario', array('id_usuario'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_historico_arquivamento_md_gd_arquivamento', 'md_gd_historico_arquivamento', array('id_arquivamento'), 'md_gd_arquivamento', array('id_arquivamento'));


            if (!$this->objInfraSequencia->verificarSequencia('md_gd_historico_arquivamento')) {
                $this->objInfraSequencia->criarSequencia('md_gd_historico_arquivamento', '1', '1', '9999999999');
            }

            // Criaчуo da tabela de desarquivamento
            $this->objInfraBanco->executarSql(' CREATE TABLE md_gd_desarquivamento (
                id_desarquivamento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_arquivamento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_procedimento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_despacho_desarquivamento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_justificativa_desarquivamento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                dta_desarquivamento ' . $this->objMetaBD->tipoDataHora() . ' NOT NULL
            )');


            $this->objMetaBD->adicionarChavePrimaria('md_gd_desarquivamento', 'pk_gd_desarquivamento_id_arquivamento', array('id_desarquivamento'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_desarquivamento_md_gd_arquivamento', 'md_gd_desarquivamento', array('id_arquivamento'), 'md_gd_arquivamento', array('id_arquivamento'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_desarquivamento_procedimento', 'md_gd_desarquivamento', array('id_procedimento'), 'procedimento', array('id_procedimento'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_desarquivamento_documento', 'md_gd_desarquivamento', array('id_despacho_desarquivamento'), 'documento', array('id_documento'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_desarquivamento_md_gd_justificativa_desarquivamento', 'md_gd_desarquivamento', array('id_justificativa_desarquivamento'), 'md_gd_justificativa', array('id_justificativa'));


            if (!$this->objInfraSequencia->verificarSequencia('md_gd_desarquivamento')) {
                $this->objInfraSequencia->criarSequencia('md_gd_desarquivamento', '1', '1', '9999999999');
            }

            // Criaчуo da tabela de modelos de documento
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_modelo_documento (
                nome  ' . $this->objMetaBD->tipoTextoVariavel(100) . '  NOT NULL ,
                valor ' . $this->objMetaBD->tipoTextoGrande() . ' NOT NULL 
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_modelo_documento', 'pk_md_gd_modelo_documento_nome', array('nome'));

            $this->cadastrarModeloDocumento('MODELO_DESPACHO_ARQUIVAMENTO', ' ');
            $this->cadastrarModeloDocumento('MODELO_DESPACHO_DESARQUIVAMENTO', ' ');
            $this->cadastrarModeloDocumento('MODELO_LISTAGEM_ELIMINACAO', ' ');

            // Criaчуo da tabela de unidades de arquivamento
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_unidade_arquivamento (
                    id_unidade_arquivamento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                    id_unidade_origem ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                    id_unidade_destino ' . $this->objMetaBD->tipoNumero() . ' NOT NULL
              )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_unidade_arquivamento', 'pk_md_gd_justificativa_id_unidade_arquivamento', array('id_unidade_arquivamento'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_unidade_arquivamento_origem', 'md_gd_unidade_arquivamento', array('id_unidade_origem'), 'unidade', array('id_unidade'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_unidade_arquivamento_destino', 'md_gd_unidade_arquivamento', array('id_unidade_destino'), 'unidade', array('id_unidade'));


            if (!$this->objInfraSequencia->verificarSequencia('md_gd_unidade_arquivamento')) {
                $this->objInfraSequencia->criarSequencia('md_gd_unidade_arquivamento', '1', '1', '9999999999');
            }

            // Criaчуo da tabela da listagem de eliminaчуo
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_lista_eliminacao (
                id_lista_eliminacao ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_procedimento_eliminacao ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_documento_eliminacao ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                numero ' . $this->objMetaBD->tipoTextoVariavel(50) . ' NOT NULL,
                dth_emissao_listagem ' . $this->objMetaBD->tipoDataHora() . ' NOT NULL,
                ano_limite_inicio ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                ano_limite_fim ' . $this->objMetaBD->tipoNumero() . ' NOT NULL
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_lista_eliminacao', 'pk_md_gd_lista_eliminacao_id_lista_eliminacao', array('id_lista_eliminacao'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_lista_eliminacao_procedimento', 'md_gd_lista_eliminacao', array('id_procedimento_eliminacao'), 'procedimento', array('id_procedimento'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_lista_eliminacao_documento', 'md_gd_lista_eliminacao', array('id_documento_eliminacao'), 'documento', array('id_documento'));


            if (!$this->objInfraSequencia->verificarSequencia('md_gd_lista_eliminacao')) {
                $this->objInfraSequencia->criarSequencia('md_gd_lista_eliminacao', '1', '1', '9999999999');
            }

            // Cria a tabela ternсria entre a listagem de eliminaчуo e os procedimentos
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_lista_elim_procedimento (
                id_lista_eliminacao ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_procedimento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_lista_elim_procedimento', 'pk_md_gd_lista_elim_procedimento', array('id_lista_eliminacao', 'id_procedimento'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_lista_elim_procedimento_lista_eliminacao', 'md_gd_lista_elim_procedimento', array('id_lista_eliminacao'), 'md_gd_lista_eliminacao', array('id_lista_eliminacao'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_lista_elim_procedimento_procedimento', 'md_gd_lista_elim_procedimento', array('id_procedimento'), 'procedimento', array('id_procedimento'));


            // Criaчуo da tabela da listagem de recolhimento
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_lista_recolhimento (
                id_lista_recolhimento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                numero ' . $this->objMetaBD->tipoTextoVariavel(50) . ' NOT NULL,
                dth_emissao_listagem ' . $this->objMetaBD->tipoDataHora() . ' NOT NULL,
                ano_limite_inicio ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                ano_limite_fim ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                qtd_processos ' . $this->objMetaBD->tipoNumero() . ' NOT NULL 
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_lista_recolhimento', 'pk_md_gd_lista_recolhimento_id_lista_recolhimento', array('id_lista_recolhimento'));

            if (!$this->objInfraSequencia->verificarSequencia('md_gd_lista_recolhimento')) {
                $this->objInfraSequencia->criarSequencia('md_gd_lista_recolhimento', '1', '1', '9999999999');
            }

            // Cria a tabela ternсria entre a listagem de recolhimento
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_lista_recol_procedimento (
                id_lista_recolhimento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_procedimento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_lista_recol_procedimento', 'pk_md_gd_lista_recol_procedimento', array('id_lista_recolhimento', 'id_procedimento'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_lista_recol_procedimento_lista_recolhimento', 'md_gd_lista_recol_procedimento', array('id_lista_recolhimento'), 'md_gd_lista_recolhimento', array('id_lista_recolhimento'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_lista_recol_procedimento_procedimento', 'md_gd_lista_recol_procedimento', array('id_procedimento'), 'procedimento', array('id_procedimento'));


            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_arquivamento_lista_eliminacao', 'md_gd_arquivamento', array('id_lista_eliminacao'), 'md_gd_lista_eliminacao', array('id_lista_eliminacao'));
            $this->objMetaBD->adicionarChaveEstrangeira('fk_md_gd_arquivamento_lista_recolhimento', 'md_gd_arquivamento', array('id_lista_recolhimento'), 'md_gd_lista_recolhimento', array('id_lista_recolhimento'));

            // Cria a tabela que relaciona os documentos fэsicos eliminados
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_documento_fisico_elim (
                id_documento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_lista_eliminacao ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_documento_fisico_elim', 'pk_md_gd_documento_fisico_elim', array('id_documento', 'id_lista_eliminacao'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_documentos_fisico_elim_documento', 'md_gd_documento_fisico_elim', array('id_documento'), 'documento', array('id_documento'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_documentos_fisico_elim_lista_eliminacao', 'md_gd_documento_fisico_elim', array('id_lista_eliminacao'), 'md_gd_lista_eliminacao', array('id_lista_eliminacao'));

            // Cria a tabela que armazena as eliminaчѕes
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_eliminacao (
                id_eliminacao ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_usuario ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                id_unidade ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                id_lista_eliminacao ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                dth_eliminacao ' . $this->objMetaBD->tipoDataHora() . ' NOT NULL
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_eliminacao', 'pk_md_gd_eliminacao', array('id_eliminacao'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_eliminacao_usuario', 'md_gd_eliminacao', array('id_usuario'), 'usuario', array('id_usuario'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_eliminacao_unidade', 'md_gd_eliminacao', array('id_unidade'), 'unidade', array('id_unidade'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_eliminacao_lista_eliminacao', 'md_gd_eliminacao', array('id_lista_eliminacao'), 'md_gd_lista_eliminacao', array('id_lista_eliminacao'));

            // Cria a tabela que armazena os recolhimentos
            $this->objInfraBanco->executarSql('CREATE TABLE md_gd_recolhimento (
                id_recolhimento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_usuario ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                id_unidade ' . $this->objMetaBD->tipoNumero() . ' NOT NULL,
                id_lista_recolhimento ' . $this->objMetaBD->tipoNumeroGrande() . ' NOT NULL,
                dth_recolhimento ' . $this->objMetaBD->tipoDataHora() . ' NOT NULL
            )');

            $this->objMetaBD->adicionarChavePrimaria('md_gd_recolhimento', 'pk_md_gd_recolhimento', array('id_recolhimento'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_recolhimento_usuario', 'md_gd_recolhimento', array('id_usuario'), 'usuario', array('id_usuario'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_recolhimento_unidade', 'md_gd_recolhimento', array('id_unidade'), 'unidade', array('id_unidade'));
            $this->objMetaBD->adicionarChaveEstrangeira('md_gd_recolhimento_lista_recolhimento', 'md_gd_recolhimento', array('id_lista_recolhimento'), 'md_gd_lista_recolhimento', array('id_lista_recolhimento'));
        } catch (Exception $ex) {
            throw new InfraException('Erro ao atualizar a versуo 1.0.0 do mѓdulo de gestуo documetal', $ex);
        }
    }

}

?>