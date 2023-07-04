<?php

namespace App\Http\Requests;

use App\Rules\CategoryParent;
use Illuminate\Foundation\Http\FormRequest;

class CategoryCatalogRequest extends CatalogRequest
{

    /**
     * С какой сущностью сейчас работаем (категория каталога)
     * @var array
     */
    protected $entity = [
        'name' => 'category',
        'table' => 'categories'
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
     * Объединяет дефолтные правила и правила, специфичные для категории
     * для проверки данных при добавлении новой категории
     */
    protected function createItem()
    {
        $rules = [
            'parent_id' => [
                'required',
                'regex:~^[0-9]+$~',
            ],
        ];
        return array_merge(parent::createItem(), $rules);
    }

    /**
     * Объединяет дефолтные правила и правила, специфичные для категории
     * для проверки данных при обновлении существующей категории
     */
    protected function updateItem()
    {
        // получаем объект модели категории из маршрута: admin/category/{category}
        $model = $this->route('category');
        $rules = [
            'parent_id' => [
                'required',
                'regex:~^[0-9]+$~',
                // задаем правило, чтобы категорию нельзя было поместить внутрь себя
                new CategoryParent($model)
            ],
        ];
        return array_merge(parent::updateItem(), $rules);
    }
}
