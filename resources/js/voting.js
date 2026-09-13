import $ from 'jquery';

window.$ = $;
window.jQuery = $;

const ezPlusReady = import('ez-plus').catch(() => null);

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

$.ajaxSetup({
    headers: {
        Accept: 'application/json',
        ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
    },
});

$(function () {
    const $page = $('#voting-page');
    const $model = $('#voting-model');
    const $status = $('#voting-status');
    const $buttons = $('[data-side]');
    const $images = {
        left: $('#voting-left-image'),
        right: $('#voting-right-image'),
    };
    let currentPair = null;
    // Для игнорирования устаревших колбэков сети, изображений и зума после выбора более новой модели.
    let requestToken = 0;
    let state = 'initial';

    const setState = (nextState, message) => {
        state = nextState;
        $page.attr('data-state', nextState);
        $buttons.prop('disabled', nextState !== 'ready');
        $model.prop('disabled', nextState === 'loading_models' || nextState === 'submitting');

        if (message) {
            $status.text(message);
        }
    };

    const errorMessage = (response) => {
        const errors = response?.responseJSON?.errors;

        if (errors) {
            const first = Object.values(errors).flat()[0];

            if (typeof first === 'string') {
                return first;
            }
        }

        return response?.responseJSON?.message ?? 'Не удалось выполнить запрос. Проверьте подключение и повторите попытку.';
    };

    const clearImages = () => {
        Object.values($images).forEach(($image) => {
            const zoom = $.data($image[0], 'ezPlus');

            $image.off('.votingPair').off('.ezpspace').off('mouseenter mouseleave');
            zoom?.destroy();
            $(`#${$image.attr('id')}-zoomContainer`).remove();
            $image.removeData('ezPlus');
            $image.removeAttr('src').attr('alt', '').prop('hidden', true);
        });
    };

    const enableZoom = async (token) => {
        await ezPlusReady;

        if (token !== requestToken || typeof $.fn.ezPlus !== 'function') {
            return;
        }

        const useContainedLens = window.matchMedia('(max-width: 1023px)').matches;

        Object.entries($images).forEach(([side, $image]) => {
            if (token !== requestToken || !$image.attr('src')) {
                return;
            }

            $image.ezPlus({
                tint: true,
                tintColour: '#0ea5e9',
                tintOpacity: 0.35,
                ...(useContainedLens
                    ? {
                        zoomType: 'lens',
                        lensShape: 'square',
                        lensSize: 180,
                        containLensZoom: true,
                    }
                    : {
                        zoomWindowPosition: side === 'left' ? 1 : 11,
                        zoomWindowOffsetX: 12,
                    }),
                zoomWindowWidth: 320,
                zoomWindowHeight: 240,
                zoomWindowFadeIn: 150,
                zoomWindowFadeOut: 150,
            });
        });
    };

    const renderPair = (pair, token) => {
        currentPair = pair;

        clearImages();
        let loadedImages = 0;
        let failedToLoad = false;

        const initializeZoomWhenReady = () => {
            if (token !== requestToken || currentPair !== pair) {
                return;
            }

            loadedImages += 1;

            if (loadedImages === Object.keys($images).length) {
                enableZoom(token);
                setState('ready', 'Выберите фотографию, которая нравится больше.');
            }
        };

        const showPhotoLoadingError = () => {
            if (token !== requestToken || currentPair !== pair || failedToLoad) {
                return;
            }

            failedToLoad = true;
            currentPair = null;
            clearImages();
            setState('error', 'Не удалось загрузить одну из фотографий. Выберите модель и повторите попытку.');
        };

        for (const side of ['left', 'right']) {
            const photo = pair[side];
            const $image = $images[side];

            $image
                .one('load.votingPair', initializeZoomWhenReady)
                .one('error.votingPair', showPhotoLoadingError)
                .attr('src', photo.url)
                .attr('alt', `${pair.model}: ${side === 'left' ? 'левая' : 'правая'} фотография`)
                .prop('hidden', false);
        }
    };

    const loadPair = () => {
        const model = $model.val();
        const token = ++requestToken;

        currentPair = null;
        clearImages();

        if (!model) {
            setState('initial', 'Выберите модель автомобиля.');

            return;
        }

        setState('loading_pair', 'Загружаем следующую пару фотографий.');

        $.getJSON('/voting/pair', { model })
            .done((response) => {
                if (token !== requestToken || model !== $model.val()) {
                    return;
                }

                if (response.data === null) {
                    setState('empty', 'Для выбранной модели недостаточно фотографий для голосования.');

                    return;
                }

                renderPair(response.data, token);
            })
            .fail((response) => {
                if (token !== requestToken || model !== $model.val()) {
                    return;
                }

                setState('error', errorMessage(response));
            });
    };

    const loadModels = () => {
        setState('loading_models', 'Загружаем доступные модели.');

        $.getJSON('/voting/models')
            .done((response) => {
                $model.empty().append($('<option>', { value: '', text: 'Выберите модель' }));

                response.data.forEach((model) => {
                    $model.append($('<option>', { value: model.key, text: model.label }));
                });

                if (response.data.length === 0) {
                    $model.prop('disabled', true);
                    setState('empty', 'Нет моделей с двумя фотографиями для голосования.');

                    return;
                }

                setState('initial', 'Выберите модель автомобиля.');
            })
            .fail((response) => setState('error', errorMessage(response)));
    };

    $model.on('change', loadPair);

    $buttons.on('click', function () {
        if (state !== 'ready' || currentPair === null) {
            return;
        }

        const side = $(this).data('side');
        const winner = currentPair[side];
        const loser = currentPair[side === 'left' ? 'right' : 'left'];
        const model = currentPair.model;

        setState('submitting', 'Сохраняем ваш голос.');

        $.ajax({
            url: '/voting/votes',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                model,
                winner_photo_id: winner.id,
                loser_photo_id: loser.id,
            }),
        })
            .done(loadPair)
            .fail((response) => setState('ready', errorMessage(response)));
    });

    loadModels();
});
