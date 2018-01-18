/**
 * @file
 * @TODO
 */

angular.module('itkCampaignApp').controller('ItkCampaignModalAddScreen', [
    'busService', '$scope', '$timeout', 'close', 'screens',
    function (busService, $scope, $timeout, close, screens) {
        'use strict';

        $scope.screens = screens;

        // Toggle select of screen.
        function toggleScreen(screen) {
            $timeout(function () {
                var index = null;

                screens.forEach(function (slideScreen, screenIndex) {
                    if (screen.id === slideScreen.id) {
                        index = screenIndex;
                    }
                });

                if (index !== null) {
                    screens.splice(index, 1);
                }
                else {
                    screens.push(screen);
                }
            });
        }

        // Register event listener for clickSlide.
        $scope.$on('screenOverview.clickScreen', function (event, screen) {
            toggleScreen(screen);
        });

        /**
         * Close the modal.
         */
        $scope.closeModal = function () {
            close(null);
        };
    }
]);
