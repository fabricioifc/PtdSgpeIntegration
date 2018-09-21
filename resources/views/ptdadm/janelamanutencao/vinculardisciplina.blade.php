@extends('layouts.master')
@section('head')
    <meta name="ptdtoken" content="{{ csrf_token() }}">
    <meta name="ptdurlbase" content="{{ url("/") }}/">
@endsection
@section('title','Vincular Disciplinas a Período')

@section('content')
    <div class='alert alert-success well-sm'>

        <a href="{!!url("/coordenador-curso/vincular-disciplinas-sgpe/periodo/$janela->id") !!}" 
            class='btn btn-sm btn-success' 
            style='width: 30%;' 
            title='Carregar as disciplinas ofertadas no sistema gerenciador de planos de ensino.'>
                Vincular Disciplinas SGPE
        </a>
    </div>
        <div class="col-md-4">
            <h2>Período selecionado: {{$janela->ano}}/{{$janela->semestre}}</h2>
            <form action="{!! url('coordenador-curso/vincular-disciplinas/save') !!}" method="post">
                {!! csrf_field() !!}
                <input type="hidden" name="janela_manutencao_id" value="{{$janela->id}}">
                <label for="curso">Curso</label>
                <select id="curso" name="curso_id" class="form-control">
                    @foreach($cursos as $curso)
                        <option value="{{$curso->id}}">{{$curso->modalidade->descricao}} - {{$curso->nome}}</option>
                    @endforeach
                </select>
                <label for="disciplina">Disciplina</label>
                <select id="disciplina" name="disciplina_id" class="form-control">
                </select>
                <label for="professor">Professor</label>
                <select id="professor" name="professor_id" class="form-control">
                    @foreach($professores as $professor)
                        <option value="{{$professor->id}}">{{$professor->nome}}</option>
                    @endforeach
                </select>
                <br>
                <button type="submit" class="btn btn-success">Salvar</button>
            </form>
        </div>
    <div class="col-md-8">
        <h2>Disciplinas Vinculadas</h2>
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

@endsection


@section('javascript')
    <script src="{!! asset('jquery-ui-1.11.4/jquery-ui.js') !!}"></script>
    <script src="{!! asset('bower_components/datatables/media/js/jquery.dataTables.min.js') !!}"></script>
    <script src="{!! asset('js/components/url.js') !!}"></script>
    <script type="text/javascript">
        $("#curso").change(function () {
            $("#disciplina").empty()
                var disciplinas = URLget("{!! url("ajax/disciplina/curso/") !!}/"+this.value);
                Promise.resolve(disciplinas)
                    .then(function (data) {
                        result = jQuery.parseJSON(data);
                        var select = document.getElementById('disciplina');
                        while(select.hasChildNodes()){
                            select.removeChild(select.lastChild);
                        }
                        options = result.forEach( function(item){
                            var option = document.createElement("option");
                            option.value = item.id;
                            option.text = item.componente_curricular + ' | Ano/Semestre:' + item.ano + 'º';
                            select.add(option);
                        });
                    })
                    .catch(function(error) {
                        window.alert('Um erro ocorreu: ' + error)
                    });
        }).change();

        //function para o uso de jqueryDatatables
        $(document).ready(function () {
            $("#curso").change(function () {
            $("#disciplina").empty()
                var disciplinas = URLget("{!! url("ajax/disciplina/curso/") !!}/"+this.value);
                Promise.resolve(disciplinas)
                    .then(function (data) {
                        result = jQuery.parseJSON(data);
                        var select = document.getElementById('disciplina');
                        while(select.hasChildNodes()){
                            select.removeChild(select.lastChild);
                        }
                        options = result.forEach( function(item){
                            var option = document.createElement("option");
                            option.value = item.id;
                            option.text = item.componente_curricular + ' | Ano/Semestre:' + item.ano + 'º';
                            select.add(option);
                        });
                    })
                    .catch(function(error) {
                        window.alert('Um erro ocorreu: ' + error)
                    });
        }).change();

            $('#tabelaInfo').DataTable({
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
@endsection