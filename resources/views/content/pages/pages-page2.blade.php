@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Profissionais')

@section('content')
<h2 class="text-center">Listagem de Profissionais</h2>
<a href="/cadastro-profissional" class="btn btn-primary">Adicionar Profissional</a>
<a href="/" type="button" class="btn btn-warning">Voltar</a>
<table class="table table-striped mt-4">
  <thead>
    <tr>
      <th>Nome</th>
      <th>Email</th>
      <th>Contato</th>
      <th>Especialidade</th>
      <th>Ações</th>
    </tr>
  </thead>
  <tbody>
    @foreach($profissionais as $profissional)
    <tr>
      <td>{{ $profissional->nome }}</td>
      <td>{{ $profissional->email }}</td>
      <td>{{ $profissional->contato }}</td>
      <td>{{ $profissional->especialidade }}</td>
      <td>
        <a href="/editar-profissional/{{ $profissional->id }}" class="btn btn-sm btn-warning">Editar</a>
        <form action="/deletar-profissional/{{ $profissional->id }}" method="GET" style="display:inline;">
          @csrf
          <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
        </form>
      </td>
    </tr>
    @endforeach
  </tbody>
</table>
@endsection