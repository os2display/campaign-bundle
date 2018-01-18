/**
 * @file
 * Controller for the campaign create/edit.
 */

angular.module('itkCampaignApp').controller('ItkCampaignController', [
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

        var id = $routeParams.id;

        $scope.campaign = null;

        if (id) {
            // Load the entity else create a new.
            $scope.getEntity('campaign', {'id': id}).then(
                function (campaign) {
                    $scope.campaign = campaign;
                    // @TODO: Handle dates.
                },
                function (err) {
                    // @TODO: Report error.
                }
            );
        }
        else {
            $scope.campaign = {
                title: '',
                description: '',
                schedule_from: new Date().getDate(),
                schedule_to: new Date().getDate(),
                channels: [],
                screens: [],
                groups: []
            };
        }

        /**
         * Display modal to add channels.
         */
        $scope.addChannels = function () {
            busService.$emit('bodyService.addClass', 'is-locked');

            ModalService.showModal({
                templateUrl: "bundles/itkcampaign/apps/itkCampaignApp/campaign/itkCampaignModalAddChannel.html",
                controller: "ItkCampaignModalAddChannel",
                inputs: {
                    channels: $scope.campaign.channels
                }
            }).then(function (modal) {
                modal.close.then(function () {
                    busService.$emit('bodyService.removeClass', 'is-locked');
                });
            });
        };

        /**
         * Remove channel from campaign.
         * @param channel
         */
        $scope.removeChannel = function (channel) {
            var index = $scope.campaign.channels.indexOf(channel);

            if (index !== -1) {
                $scope.campaign.channels.splice(channel, 1);
            }
        };

        /**
         * Display modal to add screens.
         */
        $scope.addScreens = function () {
            busService.$emit('bodyService.addClass', 'is-locked');

            ModalService.showModal({
                templateUrl: "bundles/itkcampaign/apps/itkCampaignApp/campaign/itkCampaignModalAddScreen.html",
                controller: "ItkCampaignModalAddScreen",
                inputs: {
                    screens: $scope.campaign.screens
                }
            }).then(function (modal) {
                modal.close.then(function () {
                    busService.$emit('bodyService.removeClass', 'is-locked');
                });
            });
        };

        /**
         * Remove screen from campaign.
         * @param screen
         */
        $scope.removeScreen = function (screen) {
            var index = $scope.campaign.screens.indexOf(screen);

            if (index !== -1) {
                $scope.campaign.screens.splice(screen, 1);
            }
        };

        /**
         * Save the campaign.
         */
        $scope.save = function () {
            if ($scope.campaign.id === null) {
                $scope.createEntity('campaign', $scope.campaign).then(
                    function (data) {
                        console.log("success", data);
                    },
                    function (err) {
                        console.log(err);
                    }
                );
            }
            else {
                $scope.updateEntity('campaign', $scope.campaign).then(
                    function (data) {
                        console.log("success", data);
                    },
                    function (err) {
                        console.log(err);
                    }
                );
            }
        }
    }
]);
