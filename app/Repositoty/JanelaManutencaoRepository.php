<?php
/**
 * Created by PhpStorm.
 * User: raiska
 * Date: 11/6/17
 * Time: 7:46 PM
 */

namespace App\Repository;

use App\Model\Disciplina;
use App\Model\JanelaManutencao;
use App\Model\Pessoa;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class JanelaManutencaoRepository extends EloquentRepository
{

    public function __construct(JanelaManutencao $model)
    {
        parent::__construct($model);
    }

    public function getAllPtd()
    {
        return $this->model->where('ria','=',false)->get();
    }

    public function getAllPtdOrderBy(array $fields,$order = 'asc')
    {
        $query = $this->model
            ->where('ria','=',false);
        foreach($fields as $field)
         {
             $query->orderBy($field,$order);
         }
        return $query->get();
    }

    public function getAllRia()
    {
        return $this->model->where('ria','=',true)->get();
    }

    public function ptdPorPeriodo($ano,$semestre)
    {
        return $this->model->where('ano','=',$ano)
            ->where('semestre','=',$semestre)
            ->where('ria','=',false)
            ->first();
    }

    public function janelasPorAno($ano)
    {
        return $this->model->where('ano','=',$ano)->where('ria','=',false)->get();
    }

    public function vincularPessoa($id,Disciplina $disciplina,Pessoa $pessoa)
    {
        $janela = $this->getById($id);
        $outraJanela = $janela->pegaJanelaOutroSemestrePtd();
        if($disciplina->oferta == "anual") {
            try {
                if (!empty($outraJanela)) {
                    $outraJanela->disciplinas()->attach([$disciplina->id => ['pessoa_id'=>$pessoa->id]]);
                }
            } catch (QueryException $exception) {}
        }
        try{
            return $janela->disciplinas()->attach([$disciplina->id => ['pessoa_id'=>$pessoa->id]]);
        }catch (QueryException $exception)
        {
            return false;
        }
    }

    public function vinculadasPorCoordenador(JanelaManutencao $janelaManutencao,Pessoa $pessoa,$cursos = [])
    {
        $cursos = $cursos->pluck('id')->flatten();
        $vinculadas = $janelaManutencao->disciplinas()
            ->with(['professorJanela'=>function ($q){
            $q->orderBy('nome');
        }])
            ->groupBy('disciplina_id')
            ->where('pessoa_id','>',0)
            ->with(['curso'=>function($q){
                $q->with('modalidade');
            }]);
        if(Gate::allows('adminDisciplinas')) return $vinculadas->get();
        return $vinculadas
            ->whereIn('curso_id',$cursos)
            ->get();
    }

    public function vinculadasPorJanela(JanelaManutencao $janelaManutencao){
        return DB::table('disciplina_janela_manutencao')
            ->select('disciplina.*','pessoa.nome as pessoa','curso.nome as curso','modalidade.sigla as modalidade')
            ->join('disciplina','disciplina.id','=','disciplina_janela_manutencao.disciplina_id')
            ->join('curso','curso.id','=','disciplina.curso_id')
            ->join('modalidade','modalidade.id','=','curso.modalidade_id')
            ->join('pessoa','pessoa.id','=','disciplina_janela_manutencao.pessoa_id')
            ->where('disciplina_janela_manutencao.janela_manutencao_id','=',$janelaManutencao->id)
            ->orderBy('modalidade')->orderBy('curso')->orderBy('disciplina.componente_curricular')
            ->get();
    }

    public function desvincularDisciplina(Disciplina $disciplina, Pessoa $pessoa, JanelaManutencao $janelaManutencao)
    {
        $ids = [$janelaManutencao->id];
        if($disciplina->anual);
            try {
                $outraJanela = $janelaManutencao->pegaJanelaOutroSemestrePtd();
                if (!empty($outraJanela)) {
                    $ids[] = $outraJanela->id;
                }
            } catch (QueryException $exception) {}
        return DB::table('disciplina_janela_manutencao')
            ->select('*')
            ->where('disciplina_id', '=', $disciplina->id)
            ->whereIn('janela_manutencao_id', $ids)
            ->where('pessoa_id','=',$pessoa->id)
            ->delete();
    }

}