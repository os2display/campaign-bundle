/**
 * @file
 * Controller for the campaign create/edit.
 */

angular.module('itkCampaignApp').controller('ItkCampaignController', [
    'busService', '$scope', '$timeout', 'ModalService', '$routeParams', '$location', '$controller', '$filter',
    function (busService, $scope, $timeout, ModalService, $routeParams, $location, $controller, $filter) {
        'use strict';

        $scope.loading = true;

        // Extend Os2Display/AdminBundle: BaseApiController.
        $controller('BaseApiController', {$scope: $scope});

        // Get translation filter.
        var $translate = $filter('translate');

        // Check role.
        // @TODO: Replace with new CAMPAIGN role.
        if (!$scope.requireRole('ROLE_CAMPAIGN_ADMIN')) {
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
                    $scope.campaign = convertCampaignDatesToTimestamps(campaign);
                },
                function (err) {
                    $location.path('/#/campaign');
                    busService.$emit('log.error', {
                        cause: err.code,
                        msg: $translate('messages.campaign_load_error')
                    });
                }
            ).then(
                function () {
                    $scope.loading = false;
                }
            );
        }
        else {
            var now = new Date();
            now.setMilliseconds(0);
            now.setSeconds(0);
            now.setMinutes(0);
            now = parseInt(now / 1000);

            $scope.campaign = {
                title: '',
                description: '',
                schedule_from: now,
                schedule_to: now + 24 * 60 * 60,
                channels: [],
                screens: [],
                screen_groups: [],
                groups: []
            };

            $scope.loading = false;
        }

        /**
         * Display modal to add channels.
         */
        $scope.addChannels = function () {
            if (!$scope.campaign.channels) {
                $scope.campaign.channels = [];
            }

            busService.$emit('bodyService.addClass', 'is-locked');

            ModalService.showModal({
                templateUrl: 'bundles/itkcampaign/apps/itkCampaignApp/campaign/itkCampaignModalAddChannel.html',
                controller: 'ItkCampaignModalAddChannel',
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
            if (!$scope.campaign.screens) {
                $scope.campaign.screens = [];
            }

            busService.$emit('bodyService.addClass', 'is-locked');

            ModalService.showModal({
                templateUrl: 'bundles/itkcampaign/apps/itkCampaignApp/campaign/itkCampaignModalAddScreen.html',
                controller: 'ItkCampaignModalAddScreen',
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
         * Display modal to add screenGroups.
         */
        $scope.addScreenGroups = function () {
            if (!$scope.campaign.screen_groups) {
                $scope.campaign.screen_groups = [];
            }

            busService.$emit('bodyService.addClass', 'is-locked');

            ModalService.showModal({
                templateUrl: 'bundles/itkcampaign/apps/itkCampaignApp/campaign/itkCampaignModalAddScreenGroup.html',
                controller: 'ItkCampaignModalAddScreenGroup',
                inputs: {
                    screenGroups: $scope.campaign.screen_groups
                }
            }).then(function (modal) {
                modal.close.then(function () {
                    busService.$emit('bodyService.removeClass', 'is-locked');
                });
            });
        };

        /**
         * Remove screenGroup from campaign.
         * @param screenGroup
         */
        $scope.removeScreenGroup = function (screenGroup) {
            var index = $scope.campaign.screen_groups.indexOf(screenGroup);

            if (index !== -1) {
                $scope.campaign.screen_groups.splice(screenGroup, 1);
            }
        };

        function convertCampaignDatesToTimestamps (campaign) {
            campaign.schedule_from = parseInt(new Date(campaign.schedule_from) / 1000);
            campaign.schedule_to = parseInt(new Date(campaign.schedule_to) / 1000);

            return campaign;
        }

        function convertCampaignDatesToUTC (campaign) {
            campaign.schedule_from = new Date(campaign.schedule_from * 1000).toISOString();
            campaign.schedule_to = new Date(campaign.schedule_to * 1000).toISOString();

            return campaign;
        }

        /**
         * Save the campaign.
         */
        $scope.save = function () {
            if (!$scope.campaign.title) {
                busService.$emit('log.error', {
                    cause: 400,
                    msg: $translate('messages.campaign_created_error_no_title')
                });

                return;
            }

            $scope.loading = true;

            var campaign = angular.copy($scope.campaign);

            campaign = convertCampaignDatesToUTC(campaign);

            if ($scope.campaign.id === undefined) {
                $scope.createEntity('campaign', campaign).then(
                    function () {
                        busService.$emit('log.info', {
                            cause: 200,
                            timeout: 3000,
                            msg: $translate('messages.campaign_created')
                        });

                        $location.path('/campaign');
                    },
                    function (err) {
                        var message = $translate('messages.campaign_created_error');

                        if (err.code === 409) {
                            message = $translate('messages.campaign_created_error_duplicate')
                        }

                        busService.$emit('log.error', {
                            cause: err.code,
                            msg: message
                        });
                    }
                ).then(
                    function() {
                        $scope.loading = false;
                        window.scrollTo(0, 0);
                    }
                );
            }
            else {
                $scope.updateEntity('campaign', campaign).then(
                    function () {
                        busService.$emit('log.info', {
                            cause: 200,
                            timeout: 3000,
                            msg: $translate('messages.campaign_updated')
                        });

                        $location.path('/campaign');
                    },
                    function (err) {
                        var message = $translate('messages.campaign_updated_error');

                        if (err.code === 409) {
                            message = $translate('messages.campaign_updated_error_duplicate')
                        }

                        busService.$emit('log.error', {
                            cause: err.code,
                            msg: message
                        });
                    }
                ).then(
                    function() {
                        $scope.loading = false;
                        window.scrollTo(0, 0);
                    }
                );
            }
        };
    }
]);
