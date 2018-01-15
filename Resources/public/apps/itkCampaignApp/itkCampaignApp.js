/**
 * @file
 * Sets up the Campaign App.
 */

// Create module and configure routing and translations.
angular.module('itkCampaignApp').config(['$routeProvider', '$translateProvider', function ($routeProvider, $translateProvider) {
    'use strict';

    var appUrl = 'bundles/itkcampaign/apps/itkCampaignApp/';

    // Set up translations.
    $translateProvider
    .useSanitizeValueStrategy('escape')
    .useStaticFilesLoader({
        prefix: appUrl + 'translations/locale-',
        suffix: '.json'
    })
    .preferredLanguage('da')
    .fallbackLanguage('da')
    .forceAsyncReload(true);

    // Register routes
    $routeProvider
    // Dashboard
    .when('/campaign', {
        templateUrl: appUrl + 'campaign-overview.html' + '?' + window.config.version
    })
    .when('/campaign/create', {
        controller: 'ItkCampaignController',
        templateUrl: appUrl + 'campaign/campaign.html' + '?' + window.config.version
    })
    .when('/campaign/{id}', {
        templateUrl: appUrl + 'campaign/campaign.html' + '?' + window.config.version
    });
}]);

// Setup the app.
// Register menu items.
angular.module('itkCampaignApp').service('itkCampaignAppSetup', [
    'busService', 'userService', '$translate',
    function (busService, userService, $translate) {
        'use strict';

        // Register listener for requests for Main Menu items
        busService.$on('menuApp.requestMainMenuItems', function requestMainMenuItems(event, args) {
            if (userService.hasRole('ROLE_ADMIN')) {
                busService.$emit('menuApp.returnMainMenuItems', [
                    {
                        title: $translate.instant('menu.campaign'),
                        route: '/#/campaign',
                        activeFilter: '/campaign',
                        icon: 'picture_in_picture',
                        weight: 6
                    }
                ]);
            }
        });

        // Listen for sub menu requests
        busService.$on('menuApp.requestSubMenuItems', function (event, data) {
            var items = [];

            if (userService.hasRole('ROLE_ADMIN')) {
                items.push({
                    title: $translate.instant('menu.campaign_create'),
                    path: '#/campaign/create',
                    activeFilter: '/campaign/create',
                    group: 'left',
                    weight: 1
                });
            }

            busService.$emit('menuApp.returnSubMenuItems', [
                {
                    mainMenuItem: 'campaign',
                    items: items
                }
            ]);
        });
    }
]);

// Start the service.
angular.module('itkCampaignApp').run(['itkCampaignAppSetup', angular.noop]);
