(function ($) {
  $(document).ready(function () {
    bindHandler();
    $("input[data-module-name='secureSubmit']").click(function () {
      $(".ps-shown-by-js button").html("Pay Now");
    }).click();
    if ($("#failureModal").length) {
      $("#failureModal").modal("show").off("click");
    }
  });

  $(document).ajaxComplete(function () {
    setTimeout(bindHandler, 1000);
  });

  function bindHandler() {
    var button = $(".ps-shown-by-js button");
    button.off("click").on("click", secureSubmitFormHandler);
  }

  function secureSubmitFormHandler(event) {
    event.preventDefault();
    $("#securesubmit-ajax-loader").show();
    if ($("input[data-module-name='secureSubmit']").is(':checked')) {
      $(".ps-shown-by-js button").html("Pay Now");
      if ($('input.securesubmitToken').size() === 0) {
        var card = $('.securesubmit-card-number').val().replace(/\D/g, '');
        var cvc = $('.securesubmit-card-cvc').val();
        var month = '';
        var year = '';

        if (
          $('.securesubmit-card-expiry') &&
          $('.securesubmit-card-expiry').val()
        ) {
          var date = $('.securesubmit-card-expiry').val().split('/');

          if (date[0]) {
            month = date[0].trim();
          }

          if (date[1]) {
            year = date[1].trim();
          }
        }

        hps.tokenize({
          data: {
            public_key: securesubmit_public_key,
            number: card,
            cvc: cvc,
            exp_month: month,
            exp_year: year
          },
          success: secureSubmitResponseHandler,
          error: secureSubmitResponseHandler
        });

        return false;
      }
    } else {
      $(this).unbind('submit').submit(); // Do default behavior if Secure Submit isn't selected
    }

    return true;
  }

  function secureSubmitResponseHandler(response) {
    $("#securesubmit-ajax-loader").hide();
    var $form = $("form.securesubmit-payment-form");
    if (response.message) {
      $('<div class="secure-submit-error-message alert alert-danger shadow">' + response.message + '</div>').hide().appendTo('#securesubmit-payment-errors').fadeIn(300, function () {
        $(".secure-submit-error-message").first().css({ opacity: 0, transition: 'opacity 0.3s' }).slideUp(300, function () {
          $(this).remove();
        });
      });
      // $form.unblock(); // This failed so it's been commented out
    } else {
      // alert('[' + response.token_value + ']');
      $form.append("<input type='hidden' class='securesubmitToken' name='securesubmitToken' value='" + response.token_value + "'/>");
      $form.submit();
    }
  }
}(jQuery));
