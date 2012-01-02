(function ($) {

	var model = {
		element: false,
		data: false,

		init : function( options ) {
			// setup model element and data
			model.element = $(this);
			data = model.element.data('Cicregister');
			if(!data) {
				model.element.data('Cicregister', {
					errors: {}
				})
			}
			model.data = data;

			// bind events
			model.element.bind('submit.Cicregister', model.onSubmit);

			return model.element
		},

		onSubmit : function ( ) {
			url = '?type=1325527064';
			data = model.element.serialize();

			$.ajax({
				url : url,
				dataType : 'json',
				data : data,
				success: function() {
					console.log(data);
				}
			});
			return false;
		}
	}

	$.fn.Cicregister = function () {
		return model.init.apply(this, arguments);
	};
})(jQuery);

$('#Cicregister-New').Cicregister({foo: 'barr'});