/**
 * @file
 * @TODO
 */

angular.module('campaignApp').controller('CampaignModalAddScreenGroup', [
    'busService', '$scope', '$timeout', 'close', 'screenGroups', 'userService',
    function (busService, $scope, $timeout, close, screenGroups, userService) {
        'use strict';

        $scope.availableGroups = userService.getCurrentUser().groups;

        $scope.screenGroups = screenGroups;

        // Toggle select of screenGroup.
        function toggleScreenGroup (screenGroup) {
            $timeout(function () {
                var index = null;

                $scope.screenGroups.forEach(function (slideScreenGroup, screenGroupIndex) {
                    if (screenGroup.id === slideScreenGroup.id) {
                        index = screenGroupIndex;
                    }
                });

                if (index !== null) {
                    $scope.screenGroups.splice(index, 1);
                }
                else {
                    $scope.screenGroups.push(screenGroup);
                }
            });
        }

        /**
         * Returns true if groups is in selected groups array.
         *
         * @param groups
         * @returns {boolean}
         */
        $scope.groupSelected = function groupSelected (groups) {
            if (!$scope.screenGroups) {
                return false;
            }

            var res = false;

            $scope.screenGroups.forEach(function (element) {
                if (element.id === groups.id) {
                    res = true;
                }
            });

            return res;
        };

        /**
         * Handler for clickGroup.
         *
         * @param group
         */
        $scope.clickGroup = function clickGroup (group) {
            toggleScreenGroup(group);
        };

        /**
         * Close the modal.
         */
        $scope.closeModal = function () {
            close(null);
        };
    }
]);
