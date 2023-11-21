<?php

use PHPUnit\Extensions\Selenium2TestCase\Keys as Keys;

class PaginaCadastroMapEnvioCompDigitais extends PaginaTeste
{
    /**
     * M�todo contrutor
     *
     * @return void
     */
    public function __construct($test)
    {
        parent::__construct($test);
    }

    /**
     * Description
     * @return void
     */
    public function novo()
    {
        $this->test->byId("btnNovo")->click();
    }

    /**
     * Criar novo mapeamento
     * Chama sele��o de repositorios
     * Chama sele��o de unidade
     * Chama bot�o salvar
     *
     * @return void
     */
    public function setarParametros($estrutura, $unidade)
    {
        $this->selectRepositorio($estrutura);
        $this->selectUnidade($unidade);
    }

    /**
     * Seleciona reposit�rio por sigla
     *
     * @param string $siglaRepositorio
     * @return string
     */
    private function selectRepositorio($siglaRepositorio)
    {
        $this->repositorioSelect = $this->test->select($this->test->byId('selRepositorioEstruturas'));

        if(isset($siglaRepositorio)){
            $this->repositorioSelect->selectOptionByLabel($siglaRepositorio);
        }

        return $this->test->byId('selRepositorioEstruturas')->value();
    }

    /**
     * Seleciona unidade por nome
     *
     * @param string $nomeUnidade
     * @param ?string  $hierarquia
     * @return string
     */
    private function selectUnidade($nomeUnidade, $hierarquia = null)
    {
        $this->unidadeInput = $this->test->byId('txtUnidade');
        $this->unidadeInput->clear();
        $this->unidadeInput->value($nomeUnidade);
        $this->test->keys(Keys::ENTER);
        $this->test->waitUntil(function($testCase) use($hierarquia) {
            $bolExisteAlerta=null;
            $nomeUnidade = $testCase->byId('txtUnidade')->value();
            if(!empty($hierarquia)){
                $nomeUnidade .= ' - ' . $hierarquia;
            }

            try{
                $bolExisteAlerta=$this->alertTextAndClose();
                if($bolExisteAlerta!=null)$this->test->keys(Keys::ENTER);
            }catch(Exception $e){}

            $testCase->byPartialLinkText($nomeUnidade)->click();
            return true;
        }, PEN_WAIT_TIMEOUT);

        return $this->unidadeInput->value();
    }

    /**
     * Description
     * @return void
     */
    public function salvar()
    {
        $this->test->byId("btnSalvar")->click();
    }

    /**
     * Seleciona bot�o editar da primeira linha de tabela
     * 
     * @return void
     */
    public function editar()
    {
        $this->test->byXPath("(//img[@title='Alterar Mapeamento'])[1]")->click();
    }

    /**
     * Selecionar primeira checkbox de exclus�o
     * Seleciona bot�o excluir
     * Seleciona bot�o de confirma��o
     *  
     * @return void
     */
    public function selecionarExcluir()
    {
        $this->test->byXPath("(//label[@for='chkInfraItem0'])[1]")->click();
        $this->test->byId("btnExcluir")->click();
        $this->test->acceptAlert();
    }

    /**
     * Selecionar primeira checkbox de exclus�o
     * Seleciona bot�o excluir
     * Seleciona bot�o de confirma��o
     *  
     * @return void
     */
    public function selecionarPesquisa($textoPesquisa)
    {
        $this->test->byId('txtNomeEstrutura')->clear();
        $this->test->byId('txtNomeEstrutura')->value($textoPesquisa);
        $this->test->byId("btnPesquisar")->click();
    }

    /**
     * Selecionar primeira checkbox de exclus�o
     * Seleciona bot�o excluir
     * Seleciona bot�o de confirma��o
     *  
     * @return void
     */
    public function selecionarImprimir()
    {
        $this->test->byId("lnkInfraCheck")->click();
        // $this->test->byId("btnImprimir")->click();
    }

    /**
     * Buscar item de tabela por nome
     *
     * @param string $origem
     * @return string|null
     */
    public function buscarNome($nome)
    {
        try {
            $orgaoOrigem = $this->test->byXPath("//td[contains(.,'" . $nome . "')]")->text();
            return !empty($nome) && !is_null($nome) ?
                $nome : 
                null;
        } catch (Exception $ex) {
            return null;
        }
    }

    /**
     * Buscar mensagem de alerta da p�gina
     *
     * @return string
     */
    public function buscarMensagemAlerta()
    {
        $alerta = $this->test->byXPath("(//div[@id='divInfraMsg0'])[1]");
        return !empty($alerta->text()) ? $alerta->text() : "";
    }
}
