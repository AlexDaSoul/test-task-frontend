App.module 'ProfileInfoApp', (module) ->

    # add module view { template, initialize }
    class ProfileInfoView extends Marionette.ItemView
        template: '#profile-info-tmpl'

        initialize: (options) ->
            # update view after save edit form
            @listenTo options.model, 'change', () =>
                @render()
                return
            return


    # add moidule controller { initialize}
    class ProfileInfoController extends Marionette.Controller

        initialize: (options) ->
            options.region.show( options.view ) # render
            return

    # init module
    module.addInitializer( () ->

        view = new ProfileInfoView {
            model: App.model
        }

        @controller = new ProfileInfoController {
            region: App.infoRegion
            model: App.model
            view: view
        }

        return
    )
    return