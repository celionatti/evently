<?php

declare(strict_types=1);

namespace App\Models;

use Trees\Database\Model\Model;
use Trees\Database\Interface\ModelInterface;
use Trees\Database\QueryBuilder\QueryBuilder;

class Setting extends Model implements ModelInterface
{
    protected string $table = 'settings';

    protected array $fillable = [
        'id',
        'key',
        'value',
        'type',
        'category',
        'description',
        'is_editable'
    ];

    protected array $casts = [
        'is_editable' => 'boolean'
    ];

    protected array $hidden = [];

    public function rules()
    {
        return [
            'key' => 'required|string|max:100',
            'value' => 'nullable',
            'type' => 'required|string|in:string,integer,boolean,email,url,json,text',
            'category' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
            'is_editable' => 'boolean'
        ];
    }

    public function applySearch(QueryBuilder $query, string $searchTerm): void
    {
        $query->whereRaw(
            '(key LIKE :search OR description LIKE :search)',
            ['search' => "%$searchTerm%"]
        );
    }
}
