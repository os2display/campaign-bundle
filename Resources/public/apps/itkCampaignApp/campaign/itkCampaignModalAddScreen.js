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
            console.log($scope.screens);
            console.log(screen);

            $timeout(function () {
                var index = null;

                $scope.screens.forEach(function (slideScreen, screenIndex) {
                    if (screen.id === slideScreen.id) {
                        index = screenIndex;
                    }
                });

                if (index !== null) {
                    $scope.screens.splice(index, 1);
                }
                else {
                    console.log("push");
                    $scope.screens.push(screen);
                }
            });
        }

        // Register event listener for clickSlide.
        $scope.$on('itkScreenList.clickScreen', function (event, screen) {
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
