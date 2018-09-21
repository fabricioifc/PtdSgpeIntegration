@extends('layouts.master')
@section('head')
    <meta name="ptdtoken" content="{{ csrf_token() }}">
    <meta name="ptdurlbase" content="{{ url("/") }}/">
@endsection
@section('title','Vincular Disciplinas a Período via SGPE')

@section('content')

  <p>
    <a href="{{url("/coordenador-curso/vincular-disciplinas-sgpe/periodo/$janela->id") }}" class='btn btn-sm btn-default'>Voltar</a>
  </p>
  <div class='alert alert-info well-sm'>
    <dl class='dl-horizontal' style='margin-bottom: 0px;'>
        <dt>Período selecionado</dt>
        <dd>{{$janela->ano}}/{{$janela->semestre}}</dd>
        <dt>Curso selecionado</dt>
        <dd>{{$curso->nome}}</dd>
    </dl>
  </div>

  @if($sgpe_error != null)
    <div class='alert alert-danger well-sm'>
      <small><strong>Não foi possível conectar-se ao sistema SGPE. Por favor, entre em contato com o suporte técnico.</strong></small>
      <p>{{$sgpe_error}}</p>
    </div>
    
  @else
  <div class='row'>
    <table class='table table-striped table-condensed' id="oferta_table" cellspacing="0" width="100%" role="grid">
    <thead>
        <tr>
        <th>Ano</th>
        <th>Semestre</th>
        <th>Grade</th>
        <th>Turma</th>
        <th>Curso</th>
        <th>Mais Detalhes</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>{{$oferta->year}}</strong></td>
            <td><strong>{{$oferta->semestre}}</strong></td>
            <td>{{$oferta->grid->year}}</td>
            <td>{{$oferta->turma}}</td>
            <td>{{$oferta->course->name}}</td>
            <td><a href="{{$oferta->url}}" target='_blank'>Detalhes</a></td>
        </tr>
    </tbody>
    </table>
</div>

<div class='row'>
    <form action="{!! url('coordenador-curso/vincular-disciplinas-sgpe/save') !!}" method="post">
        {!! csrf_field() !!}
        <input type="hidden" name="janela_manutencao_id" value="{{$janela->id}}">
        <div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>Disciplinas Ofertadas no Sitema SGPE</h3>
            </div>
            <div class='panel-body'>
                <table class='table table-striped table-condensed' id="disciplinas_table" cellspacing="0" width="100%" role="grid">
                    <thead>
                        <tr>
                            <th>Disciplina SGPE</th>
                            <th>Disciplina PTD</th>
                            <th>Professor SGPE</th>
                            <th>Professor PTD</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($oferta->offer_disciplines as $od)
                            <input type="hidden" name="disciplina_sgpe_id[]" value="{{$od->id}}">
                            <tr>
                                <td><strong>{{$od->discipline->sigla}} - {{$od->discipline->title}}</strong></td>
                                <td style='width: 30%;'>
                                    <select id="disciplina{{$od->id}}" name="disciplina_id[]" class="form-control">
                                        <option value="">Selecione a Disciplina</option>
                                        @foreach($disciplinas as $disciplina)
                                            @if(!empty($od->discipline->title) && $od->discipline->title == $disciplina->componente_curricular)
                                                <option value="{{$disciplina->id}}" selected="true">
                                                  {{$disciplina->componente_curricular}}[{{$disciplina->sigla}}] | Ano/Semestre: {{$disciplina->ano}}º
                                                </option>
                                            @else
                                                <option value="{{$disciplina->id}}">
                                                  {{$disciplina->componente_curricular}}[{{$disciplina->sigla}}] | Ano/Semestre: {{$disciplina->ano}}º
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </td>
                                <td><strong>{{$od->name or null}}</strong></td>
                                <td style='width: 20%;'>
                                    <select id="professor{{$od->id}}" name="professor_id[]" class="form-control">
                                        <option value="">Selecione o Professor</option>
                                        @foreach($professores as $professor)
                                            @if(!empty($od->name) && $od->name == $professor->nome)
                                                <option value="{{$professor->id}}" selected="true">
                                                    {{$professor->nome}}
                                                </option>
                                            @else
                                                <option value="{{$professor->id}}">{{$professor->nome}}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class='panel-footer'>
                <button type="submit" class="btn btn-success">
                    <i class='fa fa-save'></i>
                    Salvar
                </button>
                <span class='alert alert-warning pull-right well-sm' style='font-size: 12px;'><i class='fa fa-warning'></i>
                    Ao salvar, as disciplinas já vinculadas nesta janela ({{$janela->ano}}) serão desconsideradas.
                </span>
            </div>
        </div>
    </form>
</div>
  @endif

  <div class="row">
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
                            @endforeach
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
      </div>
    </div>
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
  </script>
@endsection



@section('css')
    <link href="{!! asset('jquery-ui-1.11.4/jquery-ui.theme.css') !!}" rel="stylesheet">
    <link href="{!! asset('bower_components/datatables/media/css/jquery.dataTables.min.css') !!}" rel="stylesheet">
    <style type='text/css'>
        .table > tbody > tr > td {
            vertical-align: middle;
        }
    </style>
@endsection