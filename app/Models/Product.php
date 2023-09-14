<?php

namespace App\Models;

use App\Helpers\ProductFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Stem\LinguaStemRu;

class Product extends Model
{

    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'slug',
        'content',
        'image',
        'price',
        'new',
        'hit',
        'sale',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function baskets()
    {
        return $this->belongsToMany(Basket::class)->withPivot('quantity');
    }

    public function scopeCategoryProducts($builder, $id)
    {
        $category = new Category();
        $descendants = $category->getAllChildren($id);
        $descendants[] = $id;
        return $builder->whereIn('category_id', $descendants);
    }

    public function scopeFilterProducts($builder, $filters)
    {
        return $filters->apply($builder);
    }

    public function scopeSearch($query, $search)
    {
        // обрезаем поисковый запрос
        $search = iconv_substr($search, 0, 64);
        // удаляем все, кроме букв и цифр
        $search = preg_replace('#[^0-9a-zA-ZА-Яа-яёЁ]#u', ' ', $search);
        // сжимаем двойные пробелы
        $search = preg_replace('#\s+#u', ' ', $search);
        $search = trim($search);
        if (empty($search)) {
            return $query->whereNull('id'); // возвращаем пустой результат
        }
        // разбиваем поисковый запрос на отдельные слова
        $temp = explode(' ', $search);
        $words = [];
        $stemmer = new LinguaStemRu();
        foreach ($temp as $item) {
            if (iconv_strlen($item) > 3) {
                // получаем корень слова
                $words[] = $stemmer->stem_word($item);
            } else {
                $words[] = $item;
            }
        }
        $relevance = "(CASE WHEN products.name LIKE '%" . $words[0] . "%' THEN 2 ELSE 0 END)";
        $relevance .= " + (CASE WHEN products.content LIKE '%" . $words[0] . "%' THEN 1 ELSE 0 END)";
        $relevance .= " + (CASE WHEN categories.name LIKE '%" . $words[0] . "%' THEN 1 ELSE 0 END)";
        $relevance .= " + (CASE WHEN brands.name LIKE '%" . $words[0] . "%' THEN 2 ELSE 0 END)";
        for ($i = 1; $i < count($words); $i++) {
            $relevance .= " + (CASE WHEN products.name LIKE '%" . $words[$i] . "%' THEN 2 ELSE 0 END)";
            $relevance .= " + (CASE WHEN products.content LIKE '%" . $words[$i] . "%' THEN 1 ELSE 0 END)";
            $relevance .= " + (CASE WHEN categories.name LIKE '%" . $words[$i] . "%' THEN 1 ELSE 0 END)";
        }

        $query->join('categories', 'categories.id', '=', 'products.category_id')
            ->join('brands', 'brands.id', '=', 'products.brand_id')
            ->select('products.*', DB::raw($relevance . ' as relevance'))
            ->where('products.name', 'like', '%' . $words[0] . '%')
            ->orWhere('products.content', 'like', '%' . $words[0] . '%')
            ->orWhere('categories.name', 'like', '%' . $words[0] . '%')
            ->orWhere('brands.name', 'like', '%' . $words[0] . '%');
        for ($i = 1; $i < count($words); $i++) {
            $query = $query->orWhere('products.name', 'like', '%' . $words[$i] . '%');
            $query = $query->orWhere('products.content', 'like', '%' . $words[$i] . '%');
            $query = $query->orWhere('categories.name', 'like', '%' . $words[$i] . '%');
            $query = $query->orWhere('brands.name', 'like', '%' . $words[$i] . '%');
        }
        $query->orderBy('relevance', 'desc');
        return $query;
    }
}
