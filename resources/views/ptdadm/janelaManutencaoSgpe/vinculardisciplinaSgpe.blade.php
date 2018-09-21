@extends('layouts.master')
@section('head')
    <meta name="ptdtoken" content="{{ csrf_token() }}">
    <meta name="ptdurlbase" content="{{ url("/") }}/">
@endsection
@section('title','Vincular Disciplinas a Período via SGPE')

@section('content')

  <div class=row>
    <p><a href="{{url("/coordenador-curso/vincular-disciplinas/periodo/$janela->id") }}" class='btn btn-sm btn-default'>Voltar</a></p>
  </div>
  <div class='row'>
    <div class='alert alert-info well-sm clearfix'>
        <h5 class='col-md-6'>Período selecionado: <strong>{{$janela->ano}}/{{$janela->semestre}}</strong></h5>
        <div class='col-md-6'>
            <select id="sgpe_curso_id" name="sgpe_curso_id" class="form-control" required='true' style='display: inline;' autofocus>
                <option value="">Selecione o Curso</option>
                @foreach($cursos as $curso)
                  <option value="{{$curso->id}}">{{$curso->modalidade->descricao}} - {{$curso->nome}}</option>
                @endforeach
            </select>
        </div>
    </div>
  </div>

<div class='row'>
  @if($sgpe_error != null)
    <div class='alert alert-danger well-sm'>
      <small><strong>Não foi possível conectar-se ao sistema SGPE. Por favor, entre em contato com o suporte técnico.</strong></small>
      <p>{{$sgpe_error}}</p>
    </div>
  @elseif(empty($ofertas))
    <div class='alert alert-danger well-sm'>
        Nenhuma disciplina ofertada para <strong>{{$janela->ano}}</strong> no sistema SGPE.
    </div>
  @else
    <div class='panel panel-default'>
      <div class='panel-heading'>
        <h3 class='panel-title'>Ofertas Disponíveis no Sitema SGPE para <strong>{{$janela->ano}}</strong></h3>
      </div>
      <div class='panel-body'>
        <table class='table table-striped table-condensed' id="ofertas_table" cellspacing="0" width="100%" role="grid">
        <thead>
            <tr>
            <th>Ano</th>
            <th>Semestre</th>
            <th>Grade</th>
            <th>Turma</th>
            <th>Curso</th>
            <th>Mais Detalhes</th>
            <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ofertas as $oferta)
            <tr>
                <td><strong>{{$oferta->year}}</strong></td>
                <td><strong>{{$oferta->semestre}}</strong></td>
                <td>{{$oferta->grid->year}}</td>
                <td>{{$oferta->turma}}</td>
                <td>{{$oferta->course->name}}</td>
                <td><a href="{{$oferta->url}}" target='_blank'>Detalhes</a></td>
                <td class='oferta_url'>
                <a href="{{url("/coordenador-curso/vincular-disciplinas-sgpe/periodo/$janela->id/sgpe_offer/$oferta->id/curso") }}" class='btn btn-sm btn-success'>Vincular Disciplinas</a>
                </td>
            </tr>
            @endforeach
        </tbody>
        </table>
      </div>
    </div>

      <div class='panel panel-default'>
      <div class='panel-heading'>
        <h3 class='panel-title'>Disciplinas Vinculadas</h3>
      </div>
      <div class='panel-body'>
        <table class="table table-striped table-hover" id="tabelaInfo" cellspacing="0" width="100%" role="grid">
            <thead>
                <tr>
                    <th>Curso</th>
                    <th>Disciplina</th>
                    <th>Ano/Semestre</th>
                    <th>Carga Horária</th>
                    <th>Professores</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vinculadas as $vinculada)  
                    <tr>
                        <td>{{$vinculada->curso->modalidade->sigla}} | {{$vinculada->curso->nome}}</td>
                        <td>{{$vinculada->componente_curricular}}</td>
                        <td>{{$vinculada->ano}}</td>
                        <td>{{$vinculada->carga_horaria}}</td>
                        <td>
                            @foreach($vinculada->professorJanela()->where('janela_manutencao_id','=',$janela->id)->get() as $professor)
                                {{$professor->nome}}
                                <a href="/coordenador-curso/vincular-disciplinas/{{$vinculada->id}}/periodo/{{$janela->id}}/professor/{{$professor->id}}/excluir">
                                    <i class="fa fa-trash" arial-hidden="true"></i>
                                </a>
                                <br>
                            @endforeach
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
      </div>
    </div>
  @endif
</div>
@endsection


@section('javascript')
  <script src="{!! asset('jquery-ui-1.11.4/jquery-ui.js') !!}"></script>
  <script src="{!! asset('bower_components/datatables/media/js/jquery.dataTables.min.js') !!}"></script>
  <script src="{!! asset('js/components/url.js') !!}"></script>
  <script type="text/javascript">
      //function para o uso de jqueryDatatables
      $(document).ready(function () {
          $('#ofertas_table').DataTable({
              "language": {
                  "paginate": {
                      "first": "Primeira",
                      "last": "Última",
                      "previous": "Página Anterior",
                      "next": "Próxima Página",
                      "zeroRecords": "Não foram encontrados dados",
                  },
                  "infoFiltered": " - Filtrado de _MAX_ registros",
                  "info": "Mostrando página _PAGE_ de _PAGES_ ( _MAX_ itens )",
                  "search": "Buscar:",
                  "lengthMenu": "Exibir _MENU_ itens",
              },
              "pagingType": "full_numbers",
              renderer: "bootstrap"
          });
      });

      // Adicionar o código do curso como parâmetro da chamada HTTP GET
      $(document).on('click', 'table#ofertas_table tbody tr td.oferta_url a', function(e) {
          var curso_selecionado = $('#sgpe_curso_id').val();

          // Não pode prosseguir se não selecionar o curso
          if (curso_selecionado == '') {
            alert("Selecione o Curso");
            e.preventDefault();
            return false;    
          }

          var link = $(this).attr('href');
          link = link + "/" + curso_selecionado;
          $(this).attr('href', link);
      });
  </script>
@endsection



@section('css')
    <link href="{!! asset('jquery-ui-1.11.4/jquery-ui.theme.css') !!}" rel="stylesheet">
    <link href="{!! asset('bower_components/datatables/media/css/jquery.dataTables.min.css') !!}" rel="stylesheet">
@endsection