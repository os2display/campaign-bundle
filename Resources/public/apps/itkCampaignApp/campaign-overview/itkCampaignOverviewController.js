/**
 * @file
 * Controller for the campaign overview.
 */

// @TODO: Cleanup DI.
angular.module('itkCampaignApp').controller('ItkCampaignOverviewController', [
    'busService', '$scope', '$timeout', 'ModalService', '$routeParams', '$location', '$controller', '$filter', 'userService',
    function (busService, $scope, $timeout, ModalService, $routeParams, $location, $controller, $filter, userService) {
        'use strict';

        $scope.loading = true;

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

        $scope.search = {
            title: ''
        };

        $scope.getEntities('campaign').then(
            function (campaigns) {
                $scope.campaigns = campaigns;

                var now = parseInt(new Date() / 1000);

                for (var key in $scope.campaigns) {
                    if ($scope.campaigns.hasOwnProperty(key)) {
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
                }
            },
            function (err) {
                busService.$emit('log.error', {
                    cause: err.code,
                    msg: $translate('common.error.could_not_load_results')
                });
            }
        ).then(function () {
            $scope.loading = false;
        });

        $scope.help = function () {
            // Display help modal.
        };

        $scope.deleteCampaigns = function () {
            // Get selected campaigns.

            // Prompt user for confirm.

            // Delete selected.

            // Refresh list.
        };

        $scope.flipSortOrder = function () {
            // Flip sort order.

            // Make arrow go up/down.
        };

        $scope.clickAllCheckbox = function () {
            // Find all checkboxes.

            // If at least one is filled, deselect all.

            // Else select all.
        };
    }
]);
