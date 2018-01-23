/**
 * @file
 * Controller for the campaign overview.
 */

// @TODO: Cleanup DI.
angular.module('itkCampaignApp').controller('ItkCampaignOverviewController', [
    'busService', '$scope', '$timeout', 'ModalService', '$routeParams', '$location', '$controller', '$filter', 'userService', '$q',
    function (busService, $scope, $timeout, ModalService, $routeParams, $location, $controller, $filter, userService, $q) {
        'use strict';

        $scope.loading = true;
        $scope.selectedCampaigns = {};
        $scope.sortOrder = true;
        $scope.search = {
            title: ''
        };

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

        function refreshCampaignList() {
            $scope.loading = true;

            // Load all accessible campaign entities.
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
        }

        refreshCampaignList();

        /**
         * Display help modal.
         */
        $scope.help = function () {
            busService.$emit('bodyService.addClass', 'is-locked');

            ModalService.showModal({
                templateUrl: 'bundles/itkcampaign/apps/itkCampaignApp/campaign-overview/modalHelp.html',
                controller: 'ItkCampaignModalBase'
            }).then(function (modal) {
                modal.close.then(function () {
                    busService.$emit('bodyService.removeClass', 'is-locked');
                });
            });
        };

        /**
         * Delete selected campaigns.
         */
        $scope.deleteCampaigns = function () {
            var selectedCampaigns = [];

            var campaignCheckboxes = angular.element('.js-campaign-checkbox');

            angular.forEach(campaignCheckboxes, function(element){
                var id = element.id;
                var checked = element.checked;

                if (id.indexOf('campaign-') !== -1) {
                    id = parseInt(id.split('campaign-')[1]);

                    if (checked) {
                        var campaign = $scope.campaigns.find(function (element) {
                            return element.id === id;
                        });

                        if (campaign) {
                            selectedCampaigns.push(campaign);
                        }
                    }
                }
            });

            // Prompt user for confirm.
            ModalService.showModal({
                templateUrl: 'bundles/itkcampaign/apps/itkCampaignApp/campaign-overview/itkCampaignOverviewModalDelete.html',
                controller: 'ItkCampaignOverviewModalDelete',
                inputs: {
                    campaigns: selectedCampaigns
                }
            }).then(function (modal) {
                modal.close.then(function (val) {
                    if (val === true) {
                        var promises = [];

                        // Delete selected.
                        for (var key in selectedCampaigns) {
                            if (selectedCampaigns.hasOwnProperty(key)) {
                                promises.push($scope.deleteEntity('campaign', selectedCampaigns[key]));
                            }
                        }

                        // Refresh list.
                        $q.all(promises).then(
                            function () {
                                refreshCampaignList();

                                busService.$emit('log.info', {
                                    cause: 200,
                                    timeout: 3000,
                                    msg: $translate('messages.campaign_delete_success')
                                });
                            },
                            function (err) {
                                busService.$emit('log.error', {
                                    cause: err.code,
                                    msg: $translate('messages.campaign_delete_error')
                                });
                            }
                        );
                    }

                    busService.$emit('bodyService.removeClass', 'is-locked');
                });
            });
        };

        /**
         * Flip sort order.
         */
        $scope.flipSortOrder = function () {
            $scope.sortOrder = !$scope.sortOrder;
        };

        /**
         * If less than all checkboxes are selected, select all.
         * Else select none.
         */
        $scope.clickAllCheckbox = function () {
            var campaignCheckboxes = angular.element('.js-campaign-checkbox');
            var checkedCampaigns = [];

            angular.forEach(campaignCheckboxes, function(element){
                var id = element.id;
                var checked = element.checked;

                if (id.indexOf('campaign-') !== -1) {
                    id = parseInt(id.split('campaign-')[1]);

                    if (checked) {
                        var campaign = $scope.campaigns.find(function (element) {
                            return element.id === id;
                        });

                        if (campaign) {
                            checkedCampaigns.push(campaign);
                        }
                    }
                }
            });

            if (checkedCampaigns.length === $scope.campaigns.length) {
                angular.forEach(campaignCheckboxes, function(element){
                    element.checked = false;
                });
            }
            else {
                angular.forEach(campaignCheckboxes, function(element){
                    element.checked = true;
                });
            }

        };
    }
]);
