jQuery(document).ready(function($) {
    /*
     * Общие настройки ajax-запросов, отправка на сервер csrf-токена
     */
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    /*
     * Раскрытие и скрытие пунктов меню каталога в левой колонке
     */
    $('#catalog-sidebar > ul ul').hide();
    $('#catalog-sidebar .badge').on('click', function () {
        var $badge = $(this);
        var closed = $badge.siblings('ul') && !$badge.siblings('ul').is(':visible');

        $badge.siblings('ul').slideToggle('normal', function () {
            $badge.children('i').toggleClass('fa-plus fa-minus');
        });

    });
    /*
     * Получение данных профиля пользователя при оформлении заказа
     */
    $('form#profiles button[type="submit"]').hide();
    // при выборе профиля отправляем ajax-запрос, чтобы получить данные
    $('form#profiles select').change(function () {
        // если выбран элемент «Выберите профиль»
        if ($(this).val() == 0) {
            // очищаем все поля формы оформления заказа
            $('#checkout').trigger('reset');
            return;
        }
        var data = new FormData($('form#profiles')[0]);
        $.ajax({
            url: '/basket/profile',
            data: data,
            processData: false,
            contentType: false,
            type: 'POST',
            dataType: 'JSON',
            success: function (data) {
                if (data.profile === undefined) {
                    console.log('data undefined');
                }
                $('input[name="name"]').val(data.profile.name);
                $('input[name="email"]').val(data.profile.email);
                $('input[name="phone"]').val(data.profile.phone);
                $('input[name="address"]').val(data.profile.address);
                $('textarea[name="comment"]').val(data.profile.comment);
            },
            error: function (reject) {
                alert(reject.responseJSON.error);
            }
        });
    });
    /*
     * Добавление товара в корзину с помощью ajax-запроса без перезагрузки
     */
    $('form.add-to-basket').submit(function (e) {
        e.preventDefault();

        var $form = $(this);
        var formData = new FormData($form[0]);

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                var spinner = '<span class="spinner-border spinner-border-sm"></span>';
                $form.find('button').append(spinner);
            },
            success: function (response) {
                $form.find('.spinner-border').remove();
                var $topBasket = $('#top-basket');
                var $cartCounter = $topBasket.find('.cart-counter');

                if (response.positionsCount > 0) {
                    if (!$cartCounter.length) {
                        $cartCounter = $('<span class="cart-counter">');
                        $topBasket.find('a').append($cartCounter);
                    }

                    $topBasket.addClass('text-success');
                    $cartCounter.text('(' + response.positionsCount + ')');
                } else {
                    $topBasket.removeClass('text-success');
                    $cartCounter.remove();
                }
            },
            error: function (xhr, status, error) {
                console.log(error);
                alert('Произошла ошибка при добавлении товара в корзину. Пожалуйста, повторите попытку.');
            }
        });
    });
});
