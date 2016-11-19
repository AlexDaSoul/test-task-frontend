$(document).on 'ready', ->
    $('.equal-height').responsiveEqualHeightGrid();
    $('.datepicker').bootstrapMaterialDatePicker({ weekStart : 0, time: false });

    userPassword = document.getElementById('password')
    userRepeatPassword = document.getElementById('repeatPassword')

    validatePassword = ->
        if userPassword.value != userRepeatPassword.value
            userRepeatPassword.setCustomValidity('Passwords Don\'t Match')
        else
            userRepeatPassword.setCustomValidity('')
        return

    $(userPassword).on 'change', ->
        validatePassword()
        return

    $(userRepeatPassword).on 'change', ->
        validatePassword()
        return