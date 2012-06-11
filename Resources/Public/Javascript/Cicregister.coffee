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
		@element.bind "submit", (event) =>
			@submitForm(event)
			return false

	serializeForm: ->
		@element.serialize()

	submitFormError: (response) ->
		response.success

	submitFormSuccess: (response) ->
		return @showErrors(response) if response.hasErrors == true
		return @doRedirect(response) if response.redirect
		return @showResults(response)

	doRedirect: (response) ->
		document.location.href = '/' + response.redirect

	showResults: (response) ->
		@element.parents('.Cicregister:first').html(response.html)
		false

	showErrors: (response) ->
		@hideErrors()
		for field, errorDetails of response.errors.byProperty
			do(field, errorDetails) =>
				@showSingleError(field, errorDetails)
				if field == 'password'
	      	@showSingleError('confirmPassword', errorDetails)
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

	showLoading: ->
		$('#cicregister-submitButton').button('loading')

	hideLoading: ->
		$('#cicregister-submitButton').button('reset')

	submitForm: (event) ->

		result = false

		@showLoading()

		$.ajax @postURL,
			dataType: 'JSON'
			data: @serializeForm()
			success: (response) =>
				result = @submitFormSuccess(response)
				@hideLoading() if !response.redirect
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
	$('.cicregister-lightbox-trigger').colorbox({inline:true, scrolling: false, open: false, onOpen: ->
		$.each(forms, ->
			@.hideErrors()
		)
	});

