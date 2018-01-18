/**
 * @file
 * Controller for the campaign overview.
 */

// @TODO: Cleanup DI.
angular.module('itkCampaignApp').controller('ItkCampaignOverviewController', [
    'busService', '$scope', '$timeout', 'ModalService', '$routeParams', '$location', '$controller', '$filter', 'userService',
    function (busService, $scope, $timeout, ModalService, $routeParams, $location, $controller, $filter, userService) {
        'use strict';

        // Extend Os2Display/AdminBundle: BaseApiController.
        $controller('BaseApiController', {$scope: $scope});

        // Get translation filter.
        var $translate = $filter('translate');

        // Check role.
        // @TODO: Replace with new CAMPAIGN role.
        if (!$scope.requireRole('ROLE_ADMIN')) {
            busService.$emit('log.error', {
                timeout: 5000,
                cause: 403,
                msg: $translate('common.error.forbidden')
            });

            $location.path('/');
            return;
        }

        $scope.getEntities('campaign').then(
            function (campaigns) {
                $scope.campaigns = campaigns;

                var now = parseInt(new Date() / 1000);

                for (var key in $scope.campaigns) {
                    var campaign = $scope.campaigns[key];
                    var schedule_from = parseInt(new Date(campaign.schedule_from) / 1000);
                    var schedule_to = parseInt(new Date(campaign.schedule_to) / 1000);

                    if (schedule_from <= now && schedule_to > now) {
                        campaign.status = 'active';
                    }
                    else {
                        if (schedule_to < now) {
                            campaign.status = 'expired';
                        }
                        else {
                            campaign.status = 'future';
                        }
                    }
                }
            },
            function (err) {
                console.error(err);
            }
        )
    }
]);
