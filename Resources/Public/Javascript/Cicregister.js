(function() {
  var CicregisterForm;

  CicregisterForm = (function() {

    function CicregisterForm(element) {
      this.element = element;
      this.elementClasses = {
        inputWithError: 'f3-form-error'
      };
      this.postURL = '?type=1325527064&tx_cicregister_create[action]=create&tx_cicregister_create[format]=json';
      this.element = $(this.element);
      this.initEvents();
    }

    CicregisterForm.prototype.log = function(msg, label) {
      if (label == null) label = 'debug';
      console.log(msg, label);
      return false;
    };

    CicregisterForm.prototype.initEvents = function() {
      var _this = this;
      return this.element.bind("submit", function(event) {
        _this.submitForm(event);
        return false;
      });
    };

    CicregisterForm.prototype.serializeForm = function() {
      return this.element.serialize();
    };

    CicregisterForm.prototype.submitFormError = function(response) {
      return response.success;
    };

    CicregisterForm.prototype.submitFormSuccess = function(response) {
      if (response.hasErrors === true) return this.showErrors(response);
      if (response.redirect) return this.doRedirect(response);
      return this.showResults(response);
    };

    CicregisterForm.prototype.doRedirect = function(response) {
      return document.location.href = '/' + response.redirect;
    };

    CicregisterForm.prototype.showResults = function(response) {
      this.element.parents('.Cicregister:first').html(response.html);
      return false;
    };

    CicregisterForm.prototype.showErrors = function(response) {
      var errorDetails, field, _fn, _ref,
        _this = this;
      this.hideErrors();
      _ref = response.errors.byProperty;
      _fn = function(field, errorDetails) {
        _this.showSingleError(field, errorDetails);
        if (field === 'password') {
          return _this.showSingleError('confirmPassword', errorDetails);
        }
      };
      for (field in _ref) {
        errorDetails = _ref[field];
        _fn(field, errorDetails);
      }
      return $.colorbox.resize();
    };

    CicregisterForm.prototype.showSingleError = function(field, errorDetails) {
      var domLoc, errorDetail, errorWrapper, index;
      domLoc = $('#cicregister-' + field + '-errors');
      errorWrapper = $('<div class="message error">');
      $('#cicregister-' + field).addClass(this.elementClasses.inputWithError);
      for (index in errorDetails) {
        errorDetail = errorDetails[index];
        errorWrapper.append('<div>' + errorDetail.message + '</div>');
      }
      return domLoc.append(errorWrapper);
    };

    CicregisterForm.prototype.showMustValidate = function(response) {};

    CicregisterForm.prototype.showSucces = function(response) {};

    CicregisterForm.prototype.hideErrors = function() {
      var inputWithErrorClassName;
      inputWithErrorClassName = this.elementClasses.inputWithError;
      $('.' + this.elementClasses.inputWithError).each(function() {
        return $(this).removeClass(inputWithErrorClassName);
      });
      return this.element.find('.error').each(function() {
        return $(this).remove();
      });
    };

    CicregisterForm.prototype.showLoading = function() {
      return $('#cicregister-submitButton').button('loading');
    };

    CicregisterForm.prototype.hideLoading = function() {
      return $('#cicregister-submitButton').button('reset');
    };

    CicregisterForm.prototype.submitForm = function(event) {
      var result,
        _this = this;
      result = false;
      this.showLoading();
      $.ajax(this.postURL, {
        dataType: 'JSON',
        data: this.serializeForm(),
        success: function(response) {
          result = _this.submitFormSuccess(response);
          if (!response.redirect) return _this.hideLoading();
        },
        error: function(response) {
          return result = _this.submitFormError(response);
        }
      });
      return result;
    };

    return CicregisterForm;

  })();

  $(function() {
    var forms;
    forms = [];
    $('.CicregisterForm-New-Ajax').each(function() {
      return forms.push(new CicregisterForm(this));
    });
    $('.cicregister-lightbox-noJs').each(function() {
      return $(this).hide();
    });
    $('.cicregister-lightbox-trigger').each(function() {
      return $(this).show();
    });
    return $('.cicregister-lightbox-trigger').colorbox({
      inline: true,
      scrolling: false,
      open: false,
      onOpen: function() {
        return $.each(forms, function() {
          return this.hideErrors();
        });
      }
    });
  });

}).call(this);
