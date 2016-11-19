# add profile page application with edit
App = new Marionette.Application()

App.addRegions {
    infoRegion: '#profile-info-container'
    editRegion: '#edit-form-container'
}

App.profileModel = Backbone.Model.extend {
    defaults: JSON.parse $('#user-model').text()
}

App.model = new App.profileModel()

App.start()
