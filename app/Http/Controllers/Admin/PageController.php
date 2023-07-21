<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PageController extends Controller {
    /**
     * Показывает список всех страниц
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function index() {
        $pages = Page::all();
        return view('admin.page.index', compact('pages'));
    }

    /**
     * Показывает форму для создания страницы
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function create() {
        $parents = Page::where('parent_id', 0)->get();
        return view('admin.page.create', compact('parents'));
    }

    /**
     * Сохраняет новую страницу в базу данных
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function store(Request $request) {
        $this->validate($request, [
            'name' => 'required|max:100',
            'parent_id' => 'required|regex:~^[0-9]+$~',
            'slug' => 'required|max:100|unique:pages|regex:~^[-_a-z0-9]+$~i',
            'content' => 'required',
        ]);
        $page = Page::create($request->all());
        return redirect()
            ->route('admin.page.show', ['page' => $page->id])
            ->with('success', 'Новая страница успешно создана');
    }

    /**
     * Показывает информацию о странице сайта
     *
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function show(Page $page) {
        return view('admin.page.show', compact('page'));
    }

    /**
     * Показывает форму для редактирования страницы
     *
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function edit(Page $page) {
        $parents = Page::where('parent_id', 0)->get();
        return view('admin.page.edit', compact('page', 'parents'));
    }

    /**
     * Обновляет страницу (запись в таблице БД)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function update(Request $request, Page $page) {
        $this->validate($request, [
            'name' => 'required|max:100',
            'parent_id' => 'required|regex:~^[0-9]+$~|not_in:'.$page->id,
            'slug' => 'required|max:100|unique:pages,slug,'.$page->id.',id|regex:~^[-_a-z0-9]+$~i',
            'content' => 'required',
        ]);
        $page->update($request->all());
        return redirect()
            ->route('admin.page.show', ['page' => $page->id])
            ->with('success', 'Страница была успешно отредактирована');
    }

    /**
     * Загружает изображение, которое было добавлено в wysiwyg-редакторе и
     * возвращает ссылку на него, чтобы в редакторе вставить <img src="…"/>
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    public function uploadImage(Request $request) {
        $this->validate($request, ['image' => [
            'mimes:jpeg,jpg,png',
            'max:5000' // 5 Мбайт
        ]]);
        $path = $request->file('image')->store('page', 'public');
        $url = Storage::disk('public')->url($path);
        return response()->json(['image' => $url]);
    }

    /**
     * Удаляет изображение, которое было удалено в wysiwyg-редакторе
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    public function removeImage(Request $request) {
        // $path = /storage/page/CW2xtBYIcXDx7d3oJRCLZoZtIhaSFWAS8Qa7WFL3.png
        $path = parse_url($request->image, PHP_URL_PATH);
        $path = str_replace('/storage/', '', $path);
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            return 'Изображение было удалено';
        }
        return 'Не удалось удалить изображение';
    }

    /**
     * Удаляет изображения, которые связаны со страницей
     *
     * @param  string $content
     * @return void
     */
    private function removeImages($content) {
        $dom = new \DomDocument();
        $dom->loadHtml($content);
        $images = $dom->getElementsByTagName('img');
        foreach ($images as $img) {
            $src = $img->getAttribute('src');
            $pattern = '~/storage/page/([0-9a-f]{32}\.(jpeg|png|gif))~';
            if (preg_match($pattern, $src, $match)) {
                $name = $match[1];
                if (Storage::disk('public')->exists('page/' . $name)) {
                    Storage::disk('public')->delete('page/' . $name);
                }
            }
        }
    }

    /**
     * Удаляет страницу (запись в таблице БД)
     *
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function destroy(Page $page) {
        if ($page->children->count()) {
            return back()->withErrors('Нельзя удалить страницу, у которой есть дочерние');
        }
        $this->removeImages($page->content);
        $page->delete();
        return redirect()
            ->route('admin.page.index')
            ->with('success', 'Страница сайта успешно удалена');
    }
}
