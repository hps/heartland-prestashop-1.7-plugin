(function (window, $) {
  var addHandler = window.GlobalPayments
      ? GlobalPayments.events.addHandler
      : function () { };

  $(document).ready(function () {
    secureSubmitPrepareFields();
    $("input[data-module-name='secureSubmit']").click(function () {
      $(".ps-shown-by-js").hide();
      $("#conditions-to-approve").hide();
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
    button.off("click").on("click", secureSubmitPrepareFields);
  }

  function secureSubmitPrepareFields(){
    // Create globalpayment configurations
    $('#conditions_to_approve[terms-and-conditions]').prop('checked', true);
    $("#secure-payment-field").attr("disabled",true);
    securesubmit_key = securesubmit_public_key;

    // GlobalPayments JS enabled payment fields
    GlobalPayments.configure({
      publicApiKey: securesubmit_key
    });

    const hps = GlobalPayments.ui.form({
      fields: {
        "card-number": {
          placeholder: "•••• •••• •••• ••••",
          target: "#securesubmitIframeCardNumber"
        },
        "card-expiration": {
          placeholder: "MM / YYYY",
          target: "#securesubmitIframeCardExpiration"
        },
        "card-cvv": {
          placeholder: "•••",
          target: "#securesubmitIframeCardCvv"
        },
        "submit": {
          target: "#submit_button",
          text: "Pay Now"
        }
      },
      styles: {
        'html' : {
          "-webkit-text-size-adjust": "100%"
        },
        'body' : {
          'width' : '100%'
        },
        '#secure-payment-field-wrapper' : {
          'position' : 'relative',
          'width' : '99%'
        },
        '#secure-payment-field' : {
          'background-color' : '#fff',
          'border'           : '1px solid #ccc',
          'display'          : 'block',
          'font-size'        : '14px',
          'height'           : '35px',
          'padding'          : '6px 12px',
          'width'            : '100%',
        },
        '#secure-payment-field-body' :{
          'width' : '99% !important',
          'position' : 'absolute'
        },
        '#secure-payment-field:focus' : {
          "border": "1px solid lightblue",
          "box-shadow": "0 1px 3px 0 #cecece",
          "outline": "none"
        },
        'button#secure-payment-field.submit' : {
          'width': 'unset',
          'flex': 'unset',
          'float': 'right',
          'color': '#fff',
          'background': '#2fb5d2',
          'cursor': 'pointer',
          'text-transform': 'uppercase',
          'font-weight': '600',
          'padding': '.5rem 1.25rem'
        },
        '#secure-payment-field[type=button]' : {
          "width": "100%"
        },
        '#secure-payment-field[type=button]:focus' : {
          "color": "#fff",
          "background": "#000000",
          "width": "100%"
        },
        '#secure-payment-field[type=button]:hover' : {
          "color": "#fff",
          "background": "#000000"
        },
        '.card-cvv' : {
          'background': 'transparent url(../../views/img/cvv1.png)',
          'background-size' : '63px 40px'
        },
        '.card-cvv.card-type-amex' : {
          'background': 'transparent url(../../views/img/ss-savedcards-amex@2x.png) no-repeat right top',
          'background-size' : '63px 40px'
        },
        '.card-number' : {
          'background': 'transparent url(../../views/img/ss-inputcard-blank@2x.png) no-repeat right',
          'background-size' : '55px 35px'
        },
        '.card-number.invalid.card-type-amex' : {
          'background': 'transparent url(../../views/img/ss-savedcards-amex@2x.png) no-repeat right',
          'background-position-y' : '-41px',
          'background-size' : '50px 90px'
        },
        '.card-number.invalid.card-type-discover' : {
          'background': 'transparent url(../../views/img/ss-saved-discover@2x.png) no-repeat right bottom',
          'background-position-y' : '-44px',
          'background-size' : '85px 90px'
        },
        '.card-number.invalid.card-type-jcb' : {
          'background': 'transparent url(../../views/img/ss-saved-jcb@2x.png) no-repeat right',
          'background-position-y' : '-44px',
          'background-size' : '55px 94px'
        },
        '.card-number.invalid.card-type-mastercard' : {
          'background': 'transparent url(../../views/img/ss-saved-mastercard.png) no-repeat right',
          'background-position-y' : '-41px',
          'background-size' : '82px 86px'
        },
        '.card-number.invalid.card-type-visa' : {
          'background': 'transparent url(../../views/img/ss-saved-visa@2x.png) no-repeat right',
          'background-position-y' : '-44px',
          'background-size' : '83px 88px',
        },
        '.card-number.valid.card-type-amex' : {
          'background': 'transparent url(../../views/img/ss-saved-discover@2x.png) no-repeat right top',
          'background-position-y' : '3px',
          'background-size' : '50px 90px',
        },
        '.card-number.valid.card-type-discover' : {
          'background': 'transparent url(../../views/img/ss-saved-discover@2x.png) no-repeat right top',
          'background-position-y' : '1px',
          'background-size' : '85px 90px'
        },
        '.card-number.valid.card-type-jcb' : {
          'background': 'transparent url(../../views/img/ss-saved-jcb@2x.png) no-repeat right top',
          'background-position-y' : '2px',
          'background-size' : '55px 94px'
        },
        '.card-number.valid.card-type-mastercard' : {
          'background': 'transparent url(../../views/img/images/ss-saved-mastercard.png) no-repeat right',
          'background-position-y' : '2px',
          'background-size' : '82px 86px'
        },
        '.card-number.valid.card-type-visa' : {
          'background': 'transparent url(../../views/img/ss-saved-visa@2x.png) no-repeat right top',
          'background-size' : '82px 86px'
        },
        '.card-number::-ms-clear' : {
          'display' : 'none'
        },
        'input[placeholder]' : {
          'letter-spacing' : '.5px',
        },
      }
    });

    hps.on("token-success", function(resp) {
      secureSubmitResponseHandler(resp);
    });

    hps.on("token-error", function(resp) {
      secureSubmitResponseHandler(resp);
    });


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
      $form.append("<input type='hidden' class='securesubmitToken' name='securesubmitToken' value='" + response.paymentReference + "'/>");
      $form.submit();
      document.getElementById('securesubmit-payment-form').submit();
    }
  }
})(window, window.jQuery);
