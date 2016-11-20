(function() {
  var App,
    extend = function(child, parent) { for (var key in parent) { if (hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; },
    hasProp = {}.hasOwnProperty;

  App = new Marionette.Application();

  App.addRegions({
    infoRegion: '#profile-info-container',
    editRegion: '#edit-form-container'
  });

  App.profileModel = Backbone.Model.extend({
    defaults: JSON.parse($('#user-model').text())
  });

  App.model = new App.profileModel();

  App.start();

  App.module('ProfileFormApp', function(module) {
    var ProfileFormController, ProfileFormView;
    ProfileFormView = (function(superClass) {
      extend(ProfileFormView, superClass);

      function ProfileFormView() {
        return ProfileFormView.__super__.constructor.apply(this, arguments);
      }

      ProfileFormView.prototype.template = '#edit-form-tmpl';

      ProfileFormView.prototype.events = {
        'click .save-button': 'send',
        'click .cancell-button': 'cancell'
      };

      ProfileFormView.prototype.ui = {
        userName: '[name=userName]',
        userEmail: '[name=userEmail]',
        password: '[name=password]',
        repeatPassword: '[name=repeatPassword]',
        siteUrl: '[name=siteUrl]',
        userPhone: '[name=userPhone]',
        userBirthday: '[name=userBirthday]',
        userGender: '[name=userGender]',
        userAbout: '[name=userAbout]',
        userSkill: '[name=userSkill]'
      };

      ProfileFormView.prototype.send = function(event) {
        event.preventDefault();
        this.onBeforeSend();
        this.trigger('sendForm', {
          data: this.ui
        });
      };

      ProfileFormView.prototype.cancell = function() {
        $('.alert, .error-message').remove();
      };

      ProfileFormView.prototype.onBeforeSend = function() {
        $('.save-button').addClass('hide');
        $('.sk-fading-circle').removeClass('hide');
        $('.alert, .error-message').remove();
      };

      return ProfileFormView;

    })(Marionette.ItemView);
    ProfileFormController = (function(superClass) {
      extend(ProfileFormController, superClass);

      function ProfileFormController() {
        return ProfileFormController.__super__.constructor.apply(this, arguments);
      }

      ProfileFormController.prototype.initialize = function(options) {
        options.region.show(options.view);
        this.model = options.model;
        return this.listenTo(options.view, 'sendForm', (function(_this) {
          return function(form) {
            _this.sendForm(form.data);
          };
        })(this));
      };

      ProfileFormController.prototype.sendForm = function(data) {
        var _self, birthday, formData;
        _self = this;
        birthday = data.userBirthday.val().split('-');
        formData = {
          _token: this.model.get('_csrf'),
          userName: data.userName.val(),
          userEmail: data.userEmail.val(),
          password: {
            first: data.password.val(),
            second: data.repeatPassword.val()
          },
          siteUrl: data.siteUrl.val(),
          userPhone: data.userPhone.val(),
          userBirthday: {
            year: birthday[0],
            month: birthday[1],
            day: birthday[2]
          },
          userGender: _.find(data.userGender, {
            checked: true
          }).value,
          userAbout: data.userAbout.val(),
          userSkill: data.userSkill.val()
        };
        $.ajax({
          type: 'POST',
          url: 'process-form',
          dataType: 'json',
          data: {
            'user': formData
          },
          success: function(json) {
            var messageTemplate;
            $('.save-button').removeClass('hide');
            $('.sk-fading-circle').addClass('hide');
            messageTemplate = _.template($('#messages-tmpl').html())({
              message: 'Profile Updated!',
              type: 'alert alert-success'
            });
            $('.form-title').after(messageTemplate);
            _self.model.set({
              userName: json['[userName]'].value,
              userEmail: json['[userEmail]'].value,
              siteUrl: json['[siteUrl]'].value,
              userPhone: json['[userPhone]'].value,
              userBirthday: json['[userBirthday][year]'].value + "-" + json['[userBirthday][month]'].value + "-" + json['[userBirthday][day]'].value,
              userGender: json['[userGender]'].value,
              userAbout: json['[userAbout]'].value,
              userSkill: json['[userSkill]'].value
            });
          },
          error: function(jqXHR, exception) {
            var json, messageTemplate;
            $('.save-button').removeClass('hide');
            $('.sk-fading-circle').addClass('hide');
            if (jqXHR.status === 0) {
              window.alert('Not connect.\n Verify Network.');
            } else if (jqXHR.status === 400) {
              json = JSON.parse(jqXHR.responseText);
              messageTemplate = _.template($('#messages-tmpl').html())({
                message: json.message,
                type: 'alert alert-danger'
              });
              $('.form-title').after(messageTemplate);
              _.each(json.errors.children, function(field, key) {
                var errorTemplate;
                if (field.errors) {
                  errorTemplate = _.template($('#validate-errors-tmpl').html())({
                    errors: field.errors
                  });
                  if (key === 'userGender') {
                    $('#' + key).append(errorTemplate);
                  } else {
                    $('#' + key).after(errorTemplate);
                  }
                  return;
                }
                if (field.children) {
                  if (field.children.year && field.children.year.errors) {
                    errorTemplate = _.template($('#validate-errors-tmpl').html())({
                      errors: field.children.year.errors
                    });
                    $('#userBirthday').after(errorTemplate);
                  }
                  if (field.children.month && field.children.month.errors) {
                    errorTemplate = _.template($('#validate-errors-tmpl').html())({
                      errors: field.children.month.errors
                    });
                    $('#userBirthday').after(errorTemplate);
                  }
                  if (field.children.day && field.children.day.errors) {
                    errorTemplate = _.template($('#validate-errors-tmpl').html())({
                      errors: field.children.day.errors
                    });
                    $('#userBirthday').after(errorTemplate);
                  }
                  if (field.children.first && field.children.first.errors) {
                    errorTemplate = _.template($('#validate-errors-tmpl').html())({
                      errors: field.children.first.errors
                    });
                    $('#password').after(errorTemplate);
                  }
                  if (field.children.second && field.children.second.errors) {
                    errorTemplate = _.template($('#validate-errors-tmpl').html())({
                      errors: field.children.second.errors
                    });
                    return $('#repeatPassword').after(errorTemplate);
                  }
                }
              });
              return;
            } else if (jqXHR.status === 404) {
              window.alert('Requested page not found. [404]');
            } else if (jqXHR.status === 500) {
              window.alert('Internal Server Error [500].');
            } else if (exception === 'parsererror') {
              window.alert('parsererror');
            } else if (exception === 'timeout') {
              window.alert('Time out error.');
            } else if (exception === 'abort') {
              window.alert('Ajax request aborted.');
            } else {
              window.alert('Uncaught Error.\n' + jqXHR.responseText);
            }
          }
        });
      };

      return ProfileFormController;

    })(Marionette.Controller);
    module.addInitializer(function() {
      var view;
      view = new ProfileFormView({
        model: App.model
      });
      this.controller = new ProfileFormController({
        region: App.editRegion,
        model: App.model,
        view: view
      });
    });
  });

  App.module('ProfileInfoApp', function(module) {
    var ProfileInfoController, ProfileInfoView;
    ProfileInfoView = (function(superClass) {
      extend(ProfileInfoView, superClass);

      function ProfileInfoView() {
        return ProfileInfoView.__super__.constructor.apply(this, arguments);
      }

      ProfileInfoView.prototype.template = '#profile-info-tmpl';

      ProfileInfoView.prototype.initialize = function(options) {
        this.listenTo(options.model, 'change', (function(_this) {
          return function() {
            _this.render();
          };
        })(this));
      };

      return ProfileInfoView;

    })(Marionette.ItemView);
    ProfileInfoController = (function(superClass) {
      extend(ProfileInfoController, superClass);

      function ProfileInfoController() {
        return ProfileInfoController.__super__.constructor.apply(this, arguments);
      }

      ProfileInfoController.prototype.initialize = function(options) {
        options.region.show(options.view);
      };

      return ProfileInfoController;

    })(Marionette.Controller);
    module.addInitializer(function() {
      var view;
      view = new ProfileInfoView({
        model: App.model
      });
      this.controller = new ProfileInfoController({
        region: App.infoRegion,
        model: App.model,
        view: view
      });
    });
  });

  $(document).on('ready', function() {
    var userPassword, userRepeatPassword, validatePassword;
    $('.equal-height').responsiveEqualHeightGrid();
    $('.datepicker').bootstrapMaterialDatePicker({
      weekStart: 0,
      time: false
    });
    userPassword = document.getElementById('password');
    userRepeatPassword = document.getElementById('repeatPassword');
    validatePassword = function() {
      if (userPassword.value !== userRepeatPassword.value) {
        userRepeatPassword.setCustomValidity('Passwords Don\'t Match');
      } else {
        userRepeatPassword.setCustomValidity('');
      }
    };
    $(userPassword).on('change', function() {
      validatePassword();
    });
    return $(userRepeatPassword).on('change', function() {
      validatePassword();
    });
  });

}).call(this);

//# sourceMappingURL=bundle.js.map
