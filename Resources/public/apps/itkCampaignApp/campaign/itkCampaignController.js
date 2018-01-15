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
            $scope.getEntity('campaign', id).then(
                function (campaign) {
                    $scope.campaign = campaign;
                },
                function (err) {
                    // @TODO: Report error.
                }
            );
        }
        else {
            $scope.campaign = {
                title: 'Titel',
                description: '',
                schedule_from: null,
                schedule_to: null,
                channels: [],
                screens: [],
                groups: []
            };
        }
    }
]);
