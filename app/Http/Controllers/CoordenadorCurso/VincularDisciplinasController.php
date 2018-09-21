<?php
/**
 * Created by PhpStorm.
 * User: raiska
 * Date: 11/6/17
 * Time: 6:43 PM
 */

namespace App\Http\Controllers\CoordenadorCurso;

use App\Http\Controllers\Controller;
use App\Model\Repository\JanelaManutencaoRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
use App\Util\SgpeClient;

/**
 * @Middleware("auth")
 */
class VincularDisciplinasController extends Controller
{

    private $janelaRepository;
    private $disciplinaRepository;

    public function __construct()
    {
        if (Gate::denies('coordenadorCurso')) {
            abort(403);
        }
        $this->janelaRepository = app('App\Repository\JanelaManutencaoRepository');
        $this->disciplinaRepository = app('App\Repository\DisciplinaRepository');
        $this->pessoaRepository = app('App\Repository\PessoaRepository');
        $this->cursoRepository = app('App\Repository\CursoRepository');
    }

/**
* @Get("/coordenador-curso/vincular-disciplinas")
 * Lista os períodos (janelas) para PTD com opção de vincular disciplinas
*/
public function index()
{
    $janelas = $this->janelaRepository->getAllPtd();
    return view('ptdadm.janelamanutencao.list',compact('janelas'));
}


    /**
     * @Get("/coordenador-curso/vincular-disciplinas/periodo/{id}", where={"id": "[0-9]+"})
     * @return \Illuminate\Http\Response
     */
    public function vincularDisciplinasPorJanela($id)
    {
        $janela = $this->janelaRepository->getById($id);
        $professores = $this->pessoaRepository->professoresAtivos();
        $cursos = $this->cursoRepository->getAtivosPorCoordenador(Auth::user());
        $vinculadas = $this->janelaRepository->vinculadasPorCoordenador($janela,Auth::user(),$cursos);
        return view('ptdadm.janelamanutencao.vinculardisciplina',
            compact('janela','professores','cursos','vinculadas'));
    }

    /**
     * @Post("/coordenador-curso/vincular-disciplinas/save")
     *
     */
    public function salvarBloco(\Illuminate\Http\Request $request)
    {
        $janela = $request->input('janela_manutencao_id');
        $disciplina = $this->disciplinaRepository->getById($request->input('disciplina_id'));
        $pessoa = $this->pessoaRepository->getById($request->input('professor_id'));
        $this->janelaRepository->vincularPessoa($janela,$disciplina,$pessoa);
        return redirect("coordenador-curso/vincular-disciplinas/periodo/$janela")
            ->with('success','O professor foi vinculado com sucesso!');
    }


     /**
     * @Get("/coordenador-curso/vincular-disciplinas/{disciplina_id}/periodo/{janela_id}/professor/{professor_id}/excluir")
     */
    public function destroy($disciplina_id, $janela_id,$professor_id)
    {
        $disciplina = $this->disciplinaRepository->getById($disciplina_id);
        $janela = $this->janelaRepository->getById($janela_id);
        $professor = $this->pessoaRepository->getById($professor_id);
        $this->janelaRepository->desvincularDisciplina($disciplina,$professor,$janela);
        return redirect("/coordenador-curso/vincular-disciplinas/periodo/$janela_id");
    }

}