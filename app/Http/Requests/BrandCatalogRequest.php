<?php

namespace App\Http\Requests;

namespace App\Http\Requests;

class BrandCatalogRequest extends CatalogRequest
{

    /**
     * С какой сущностью сейчас работаем (бренд каталога)
     * @var array
     */
    protected $entity = [
        'name' => 'brand',
        'table' => 'brands'
    ];

    public function authorize()
    {
        return parent::authorize();
    }

    public function rules()
    {
        return parent::rules();
    }

    /**
     * Объединяет дефолтные правила и правила, специфичные для бренда
     * для проверки данных при добавлении нового бренда
     */
    protected function createItem()
    {
        $rules = [];
        return array_merge(parent::createItem(), $rules);
    }

    /**
     * Объединяет дефолтные правила и правила, специфичные для бренда
     * для проверки данных при обновлении существующего бренда
     */
    protected function updateItem()
    {
        $rules = [];
        return array_merge(parent::updateItem(), $rules);
    }
}
