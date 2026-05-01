<?php

namespace App\Services;

use Illuminate\Support\Collection;

/*
 * SearchService — autocomplete reutilizável para qualquer Model Eloquent.
 *
 * Centraliza a lógica de busca LIKE encadeada para evitar duplicação nos controllers.
 * Uso: app(SearchService::class)->autocomplete(Paciente::class, $q, ['nome', 'matricula'])
 */
class SearchService
{
    /*
     * Busca registros de um Model por múltiplos campos com LIKE.
     *
     * @param  class-string  $model   Classe Eloquent (ex: Paciente::class)
     * @param  string        $query   Termo digitado pelo usuário
     * @param  array         $fields  Campos a pesquisar (OR entre eles)
     * @param  array         $select  Colunas a retornar
     * @param  int           $limit   Máximo de resultados
     * @return Collection
     */
    public function autocomplete(
        string $model,
        string $query,
        array  $fields,
        array  $select = ['id', 'nome'],
        int    $limit  = 10
    ): Collection {
        // Mínimo de 2 caracteres para evitar buscas varrendo toda a tabela
        if (mb_strlen(trim($query)) < 2) {
            return collect();
        }

        $termo = trim($query);

        return $model::where(function ($q) use ($fields, $termo) {
            foreach ($fields as $i => $field) {
                $method = $i === 0 ? 'where' : 'orWhere';
                $q->$method($field, 'like', '%' . $termo . '%');
            }
        })
        ->limit($limit)
        ->get($select);
    }
}
