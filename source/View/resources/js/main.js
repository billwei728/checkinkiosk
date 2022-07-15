

let notification = (message, bSuccess) => {
    var notifyClass = '';

    switch (bSuccess) {
        case 0:
            notifyClass = 'error';
            break;
        case 2:
            notifyClass = 'info';
            break;
        case 3:
            notifyClass = 'warn';
            break;
        default:
            notifyClass = 'success';
    }

    $.notify(message, { className: notifyClass, position: 'bottom right' });
}

let onSubmitRequest = (params, isMainPage) => {
    $.ajax({
        type: 'POST',
        url: 'index.php',
        data: params,
        beforeSend: function () {
            if (! $('.loader').is(':visible')) {
                $('.loader').fadeIn('slow');
            }
        },
        success: function (data) {
            const result = JSON.parse(data);

            if (! result.success) {
                notification(result.errMsg, 0);
                return;
            }

            if (! isMainPage) {
                onPostSubmitRequest(result);
            } else {
                onPostPageLoad(result);
            }
        },
        complete: function () {
            $('.loader').fadeOut('slow');
        },
        error: function (xhr, status, error) {
            notification(xhr.responseText, 0);
        }
    });
}

(function () {
    'use strict'

    var forms = document.querySelectorAll('.needs-validation')

    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }

            form.classList.add('was-validated')
        }, false)
    })
})();