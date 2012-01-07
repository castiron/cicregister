class CicregisterForm

	constructor: (@element) ->
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
		@log(response, 'error')
		response.success

	submitFormSuccess: (response) ->
		@log(response, 'success')
		response.success

	submitForm: (event) ->
		result = false

		$.ajax @postURL,
			dataType: 'JSON'
			data: @serializeForm()
			success: (response) =>
				result = @submitFormSuccess(response)
			error:(response) =>
				result = @submitFormError(response)
		@log(result)
		result

$ ->
	forms = []
	$('.CicregisterForm-New').each( ->
		forms.push(new CicregisterForm(this))
	)
