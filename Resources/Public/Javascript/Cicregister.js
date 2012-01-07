(function() {
  var CicregisterForm;

  CicregisterForm = (function() {

    function CicregisterForm(element) {
      this.element = element;
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
        return _this.submitForm(event);
      });
    };

    CicregisterForm.prototype.serializeForm = function() {
      return this.element.serialize();
    };

    CicregisterForm.prototype.submitFormError = function(response) {
      this.log(response, 'error');
      return response.success;
    };

    CicregisterForm.prototype.submitFormSuccess = function(response) {
      this.log(response, 'success');
      return response.success;
    };

    CicregisterForm.prototype.submitForm = function(event) {
      var result,
        _this = this;
      result = false;
      $.ajax(this.postURL, {
        dataType: 'JSON',
        data: this.serializeForm(),
        success: function(response) {
          return result = _this.submitFormSuccess(response);
        },
        error: function(response) {
          return result = _this.submitFormError(response);
        }
      });
      this.log(result);
      return result;
    };

    return CicregisterForm;

  })();

  $(function() {
    var forms;
    forms = [];
    return $('.CicregisterForm-New').each(function() {
      return forms.push(new CicregisterForm(this));
    });
  });

}).call(this);
