<?php

class PenMapUnidadesFixture
{
    private static $contexto;

    public function __construct(string $contexto)
    {
        self::$contexto = $contexto;
    }

    protected function inicializarObjInfraIBanco()
    {
        return \BancoSEI::getInstance();
    }

    public function cadastrar($dados = [])
    {
        $bancoOrgaoA = new DatabaseUtils(self::$contexto);
        $bancoOrgaoA->execute(
            "INSERT INTO md_pen_unidade (id_unidade, id_unidade_rh, sigla_unidade_rh, nome_unidade_rh) ".
            "VALUES(?, ?, ?, ?)",
            array(110000001, $dados['id'], $dados['sigla'], $dados['nome'])
        );
    }

    public function deletar($dados = []): void
    {
        $bancoOrgaoA = new DatabaseUtils(self::$contexto);
        $bancoOrgaoA->execute(
            "DELETE FROM md_pen_unidade WHERE id_unidade = ? and id_unidade_rh = ?",
            array(110000001, $dados['id'])
        );
    }
}