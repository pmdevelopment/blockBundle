var blockBootBox = function () {
    "use strict";

    return {
        /**
         * Form
         *
         * @param element
         * @param onSuccess
         */
        form: function (element, onSuccess) {
            var _self = this;
            $.get($(element).data('href'), {}, function (result) {
                _self.formCreate(element, result, onSuccess);
            });
        },
        /**
         * Form Create
         *
         * @param element
         * @param content
         * @param onSuccess
         */
        formCreate: function (element, content, onSuccess) {
            var _self = this, title, size;

            if ($(element).attr('title')) {
                title = $(element).attr('title');
            } else {
                title = $(element).text();
            }

            if ($(element).data('size')) {
                size = $(element).data('size');
            } else {
                size = null;
            }
            bootbox.dialog({
                className: "bootbox-form-dialog",
                title: title,
                size: size,
                message: content,
                buttons: {
                    save: {
                        label: "Speichern",
                        className: "btn-success",
                        callback: function () {
                            var form = $('.bootbox-form-dialog form')[0];
                            var formData = new FormData(form);
                            console.log($(element).data('href'));
                            $.ajax({
                                url: $(element).data('href'),
                                data: formData,
                                type: "POST",
                                contentType: false,
                                processData: false,
                                success: function (result) {
                                    onSuccess(result);
                                }
                            });
                        },
                        close: {
                            label: "Abbrechen",
                            className: "btn-warning"
                        }
                    }
                }
            }).on('shown.bs.modal', function () {
                var elements = $('.bootbox-form-dialog textarea');
                $.each(elements, function (no) {
                    var el = this;
                    var mde = new SimpleMDE({
                        element: el,
                        forceSync: true,
                        spellChecker: false
                    });
                });
            });

        },
        /**
         * Init
         */
        init: function () {
            var _self = this;
            var textblock = $('.textblock');
            var stringblock = $('.stringblock');
            var imageblock = $('.imageblock');
            var buttonblock = $('.buttonblock');
            var addentity = $('.addEntityButtonBlock');

            textblock.on('dblclick taphold', function () {
                var currentBlock = this;
                _self.form(currentBlock, function (result) {
                    $(currentBlock).html(result);
                    bootbox.hideAll();
                });

                return false;
            }).removeClass('disabled');

            stringblock.on('dblclick taphold', function () {
                var currentBlock = this;
                _self.form(currentBlock, function (result) {
                    $(currentBlock).text(result);
                    bootbox.hideAll();
                });

                return false;
            });

            imageblock.on('dblclick taphold', function () {
                _self.form(this, function (result) {
                    bootbox.hideAll();
                    location.reload();
                });

                return false;
            }).removeClass('disabled');

            buttonblock.click(function (ev) {
                $("." + $(this).data('slug')).first().dblclick();
            });

            addentity.click(function () {
                var currentBlock = this;
                _self.form(this, function (result) {
                    bootbox.hideAll();
                    location.reload();
                });
            });
        }
    };

}();

$(document).ready(function () {
    blockBootBox.init();
});