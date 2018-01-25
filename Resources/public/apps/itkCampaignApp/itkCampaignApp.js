/**
 * @file
 * Sets up the Campaign App.
 */

// Create module and configure routing and translations.
angular.module('itkCampaignApp').config([
    '$routeProvider', '$translateProvider', function ($routeProvider, $translateProvider) {
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
            .when('/campaign', {
                controller: 'ItkCampaignOverviewController',
                templateUrl: appUrl + 'campaign-overview/campaign-overview.html' + '?' + window.config.version
            })
            .when('/campaign/create', {
                controller: 'ItkCampaignController',
                templateUrl: appUrl + 'campaign/campaign.html' + '?' + window.config.version
            })
            .when('/campaign/:id', {
                controller: 'ItkCampaignController',
                templateUrl: appUrl + 'campaign/campaign.html' + '?' + window.config.version
            });
    }
]);

// Setup the app.
// Register menu items.
angular.module('itkCampaignApp').service('itkCampaignAppSetup', [
    'busService', 'userService', '$translate', '$filter',
    function (busService, userService, $translate, $filter) {
        'use strict';

        // Register listener for requests for Main Menu items
        busService.$on('menuApp.requestMainMenuItems', function requestMainMenuItems (event, args) {
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

        busService.$on('itkHeader.list.element.requestItems', function (event, data) {
            if (data.type === 'screen') {
                var apiData = data.entity.api_data;

                if (apiData && apiData.active_campaigns && apiData.active_campaigns.length > 0) {
                    var message = apiData.active_campaigns.reduce(function (sum, campaign) {
                        return sum + '<p>' + campaign.title + ': ' + campaign.schedule_from + ' - ' + campaign.schedule_to + '</p>';
                    }, '');

                    var iconSource = 'bundles/os2displayadmin/images/icons/exclamation-icon.png';

                    var html =
                        '<div class="itk-campaign-info">' +
                        '  <div tooltips tooltip-template="' + message + '" tooltip-side="top">' +
                        '    <img class="itk-campaign-info--icon" src="' + iconSource + '" title="">' +
                        '  </div>' +
                        '</div>';

                    busService.$emit(data.returnEvent, {
                        html: html,
                        type: 'warning'
                    });
                }
            }
        });

        busService.$on('itkHeader.entity.requestItems', function (event, data) {
            if (data.type === 'screen') {
                var apiData = data.entity.api_data;

                if (apiData && apiData.active_campaigns && apiData.active_campaigns.length > 0) {
                    var message = apiData.active_campaigns.reduce(function (sum, campaign) {
                        return sum + '<p>' + $translate.instant('messages.active_campaign') + ' ' + $filter('date')(campaign.schedule_to, 'medium') + ' (' + $translate.instant('messages.active_campaign_see') + '<a href="/#/campaign/' + campaign.id + '">' + campaign.title + '</a>)</p>';
                    }, '');

                    var html =
                        '<div class="message itk-campaign-info--message">' +
                        '  <div class="message--inner is-info">' +
                        '    <div class="message--content">' +
                        message +
                        '    </div>' +
                        '  </div>' +
                        '</div>';

                    busService.$emit(data.returnEvent, {
                        html: html,
                        type: 'warning'
                    });
                }
            }
        });
    }
]);

// Start the service.
angular.module('itkCampaignApp').run(['itkCampaignAppSetup', angular.noop]);
