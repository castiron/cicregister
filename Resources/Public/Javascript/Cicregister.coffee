class CicregisterForm

	constructor: (@element) ->
		@elementClasses =
			inputWithError: 'f3-form-error'
		@postURL = '?type=1325527064&tx_cicregister_create[action]=create&tx_cicregister_create[format]=json'
		@element = $(@element)
		@initEvents()

	log: (msg, label = 'debug') ->
		console.log(msg, label)
		false

	initEvents: ->
		@element.bind "submit", (event) => @submitForm(event)

	serializeForm: ->
		@element.serialize()

	submitFormError: (response) ->
		response.success

	submitFormSuccess: (response) ->
		return @showErrors(response) if response.hasErrors == true
		return @doRedirect(response) if response.redirect
		return @showResults(response)

	doRedirect: (response) ->
		window.location = response.redirect

	showResults: (response) ->
		@element.parents('.Cicregister:first').html(response.html)
		false

	showErrors: (response) ->
		for field, errorDetails of response.errors.byProperty
			@showSingleError(field, errorDetails)
		$.colorbox.resize()

	showSingleError: (field, errorDetails) ->
		domLoc = $('#cicregister-' + field + '-errors')
		errorWrapper = $('<div class="message error">')
		$('#cicregister-' + field).addClass(@elementClasses.inputWithError)
		for index, errorDetail of errorDetails
			errorWrapper.append('<div>' + errorDetail.message + '</div>')
		domLoc.append(errorWrapper);

	showMustValidate: (response) ->

	showSucces: (response) ->

	hideErrors: ->
		inputWithErrorClassName = @elementClasses.inputWithError
		$('.' + @elementClasses.inputWithError).each( ->
			$(@).removeClass(inputWithErrorClassName)
		)
		@element.find('.error').each( ->
			$(this).remove()
		)

	submitForm: (event) ->

		@hideErrors()
		result = false

		$.ajax @postURL,
			dataType: 'JSON'
			data: @serializeForm()
			success: (response) =>
				result = @submitFormSuccess(response)
			error:(response) =>
				result = @submitFormError(response)
		result

$ ->
	forms = []
	$('.CicregisterForm-New-Ajax').each( ->
		forms.push(new CicregisterForm(this))
	)

	$('.cicregister-lightbox-noJs').each( -> $(this).hide(); )
	$('.cicregister-lightbox-trigger').each(-> $(this).show();)
	$('.cicregister-lightbox-trigger').colorbox({inline:true, scrolling: false, open: false});

