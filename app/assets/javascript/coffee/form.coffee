App.module 'ProfileFormApp', (module) ->

    # add module view { template, events, ui, send, cancell, onBeforeSend }
    class ProfileFormView extends Marionette.ItemView
        template: '#edit-form-tmpl'

        events: {
            'click .save-button': 'send' # send ajax form
            'click .cancell-button': 'cancell' # close modal
        }

        ui: { # form fields list
            userName: '[name=userName]'
            userEmail: '[name=userEmail]'
            password: '[name=password]'
            repeatPassword: '[name=repeatPassword]'
            siteUrl: '[name=siteUrl]'
            userPhone: '[name=userPhone]'
            userBirthday: '[name=userBirthday]'
            userGender: '[name=userGender]'
            userAbout: '[name=userAbout]'
            userSkill: '[name=userSkill]'
        }

        send:  (event) ->
            event.preventDefault()
            @onBeforeSend()
            @trigger 'sendForm', { data: @ui } # trigger sendForm event for controller
            return

        cancell: ->
            $('.alert, .error-message').remove()
            return

        onBeforeSend: ->
            $('.save-button').addClass('hide')
            $('.sk-fading-circle').removeClass('hide')
            $('.alert, .error-message').remove()
            return


    # add module cotntroller { initialize, sendForm,  }
    class ProfileFormController extends Marionette.Controller

        initialize: (options) ->
            options.region.show( options.view ) # render

            @model = options.model

            @listenTo options.view, 'sendForm', (form) =>
                @sendForm form.data # listen sendForm event
                return

        sendForm: (data) ->

            _self = @

            birthday = data.userBirthday.val().split '-' # split value for userBirthday { year, month, day }

            formData = {
                _token: @model.get('_csrf') # add csrf field
                userName: data.userName.val()
                userEmail: data.userEmail.val()
                password: {
                    first: data.password.val()
                    second: data.repeatPassword.val() # get repeat password
                }
                siteUrl: data.siteUrl.val()
                userPhone: data.userPhone.val()
                userBirthday: {
                    year: birthday[0]
                    month: birthday[1]
                    day: birthday[2]
                }
                userGender: _.find(data.userGender, { checked: true }).value # get checked radiobutton
                userAbout: data.userAbout.val()
                userSkill: data.userSkill.val()
            }

            $.ajax {
                type: 'POST'
                url: 'process-form'
                dataType: 'json'
                data: { 'user': formData }
                success: (json) ->
                    $('.save-button').removeClass('hide')
                    $('.sk-fading-circle').addClass('hide')

                    # add message
                    messageTemplate = _.template($('#messages-tmpl').html()) { message: 'Profile Updated!', type: 'alert alert-success' }
                    $('.form-title').after messageTemplate

                    # update model
                    _self.model.set {
                        userName: json['[userName]'].value,
                        userEmail: json['[userEmail]'].value,
                        siteUrl: json['[siteUrl]'].value,
                        userPhone: json['[userPhone]'].value,
                        userBirthday: "#{json['[userBirthday][year]'].value}-#{json['[userBirthday][month]'].value}-#{json['[userBirthday][day]'].value}",
                        userGender: json['[userGender]'].value,
                        userAbout: json['[userAbout]'].value,
                        userSkill: json['[userSkill]'].value
                    }

                    return
                error: (jqXHR, exception) ->
                    $('.save-button').removeClass('hide')
                    $('.sk-fading-circle').addClass('hide')

                    # errors list
                    if (jqXHR.status == 0)
                        window.alert('Not connect.\n Verify Network.')
                    else if (jqXHR.status == 400)
                        json = JSON.parse jqXHR.responseText

                        # add message
                        messageTemplate = _.template($('#messages-tmpl').html()) { message: json.message, type: 'alert alert-danger' }
                        $('.form-title').after messageTemplate

                        # iterate errors data for view messages
                        _.each json.errors.children, (field, key) ->

                            # message under unput field
                            if field.errors
                                errorTemplate = _.template($('#validate-errors-tmpl').html()) { errors: field.errors }
                                if (key == 'userGender')
                                    $('#' + key).append errorTemplate
                                else
                                    $('#' + key).after errorTemplate
                                return

                            # childrens properties of fields
                            if field.children
                                if field.children.year && field.children.year.errors
                                    errorTemplate = _.template($('#validate-errors-tmpl').html()) { errors: field.children.year.errors }
                                    $('#userBirthday').after errorTemplate

                                if field.children.month && field.children.month.errors
                                    errorTemplate = _.template($('#validate-errors-tmpl').html()) { errors: field.children.month.errors }
                                    $('#userBirthday').after errorTemplate

                                if field.children.day && field.children.day.errors
                                    errorTemplate = _.template($('#validate-errors-tmpl').html()) { errors: field.children.day.errors }
                                    $('#userBirthday').after errorTemplate

                                if field.children.first && field.children.first.errors
                                    errorTemplate = _.template($('#validate-errors-tmpl').html()) { errors: field.children.first.errors }
                                    $('#password').after errorTemplate

                                if field.children.second && field.children.second.errors
                                    errorTemplate = _.template($('#validate-errors-tmpl').html()) { errors: field.children.second.errors }
                                    $('#repeatPassword').after errorTemplate

                        return
                    else if (jqXHR.status == 404) # other errors
                        window.alert('Requested page not found. [404]')
                    else if (jqXHR.status == 500)
                        window.alert('Internal Server Error [500].')
                    else if (exception == 'parsererror')
                        window.alert('parsererror')
                    else if (exception == 'timeout')
                        window.alert('Time out error.')
                    else if (exception == 'abort')
                        window.alert('Ajax request aborted.')
                    else
                        window.alert('Uncaught Error.\n' + jqXHR.responseText)
                    return
            }
            return


    # init module
    module.addInitializer( () ->

        view = new ProfileFormView {
            model: App.model
        }

        @controller = new ProfileFormController {
            region: App.editRegion
            model: App.model
            view: view
        }

        return
    )
    return