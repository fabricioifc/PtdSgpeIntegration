<?php
/**
 * Created by Fabricio Bizotto.
 * User: fabricio.bizotto
 * Date: 12/09/2018
 * Time: 8:42 AM
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
class VincularDisciplinasSgpeController extends Controller
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
     * @Get("/coordenador-curso/vincular-disciplinas-sgpe/periodo/{id}", where={"id": "[0-9]+"})
     * @return \Illuminate\Http\Response
     */
    public function vincularDisciplinasPorJanelaSgpe($id) {
        $sgpe_error = null;
        $ofertas = [];
        
        $janela = $this->janelaRepository->getById($id);
        $professores = $this->pessoaRepository->professoresAtivos();
        $cursos = $this->cursoRepository->getAtivosPorCoordenador(Auth::user());
        $vinculadas = $this->janelaRepository->vinculadasPorCoordenador($janela,Auth::user(),$cursos);

        $sgpe = new SgpeClient();
        $ofertas = $sgpe->buscarOfertas($janela->ano);
        
        return view('ptdadm.janelaManutencaoSgpe.vinculardisciplinaSgpe',
            compact('janela','professores','cursos','vinculadas','ofertas', 'sgpe_error'));
    }

    /**
     * @Get("/coordenador-curso/vincular-disciplinas-sgpe/periodo/{idjanela}/sgpe_offer/{idoffer}/curso/{idcurso}", where={"idjanela": "[0-9]+"})
     * @return \Illuminate\Http\Response
     */
    public function vincularDisciplinasPorJanelaSgpeOferta($idjanela, $idoffer, $idcurso) {
        $sgpe_error = null;

        $curso = $this->cursoRepository->getById($idcurso);
        
        $janela = $this->janelaRepository->getById($idjanela);
        $professores = $this->pessoaRepository->professoresAtivos();
        $cursos = $this->cursoRepository->getAtivosPorCoordenador(Auth::user());
        $disciplinas = $this->disciplinaRepository->porCurso($curso);
        $vinculadas = $this->janelaRepository->vinculadasPorCoordenador($janela,Auth::user(),$cursos);

        $sgpe = new SgpeClient();
        $oferta = $sgpe->buscarOfertaPorId($idoffer);
        return view('ptdadm.janelaManutencaoSgpe.vinculardisciplinaSgpeOferta',
            compact('janela','professores','cursos','disciplinas','vinculadas','oferta', 'sgpe_error','curso'));
    }

        /**
     * @Post("/coordenador-curso/vincular-disciplinas-sgpe/save")
     *
     */
    public function salvarBlocoSgpe(\Illuminate\Http\Request $request) {
        try {
            $janela_id = $request->input('janela_manutencao_id');
            $janela = $this->janelaRepository->getById($janela_id);
            $disciplinas_sgpe_id = $request->input('disciplina_sgpe_id');
            $disciplinas_id = $request->input('disciplina_id');
            $professores_id = $request->input('professor_id');

            // Percorre a lista de disciplina e professores selecionados.
            // Desvincula e vincula novamente
            for ($i=0; $i < count($disciplinas_sgpe_id) ; $i++) { 
                if (!empty($disciplinas_id[$i]) && !empty($professores_id[$i])) {
                    $disciplina = $this->disciplinaRepository->getById($disciplinas_id[$i]);
                    $pessoa = $this->pessoaRepository->getById($professores_id[$i]);
                    $this->janelaRepository->desvincularDisciplina($disciplina, $pessoa, $janela);
                    $this->janelaRepository->vincularPessoa($janela_id,$disciplina,$pessoa);
                }
            }
            return redirect("coordenador-curso/vincular-disciplinas-sgpe/periodo/$janela_id")
                ->with('success','Os professores foram vinculados com sucesso!');
        } catch(Exception $ex) {
            return redirect("coordenador-curso/vincular-disciplinas-sgpe/periodo/$janela_id")
            ->with('error','Ocorreu um erro ao vincular as disciplinas. Por favor, entre em contato com o suporte técnico.');
        }
    }
}