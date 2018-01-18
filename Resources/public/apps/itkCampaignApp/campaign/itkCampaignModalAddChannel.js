/**
 * @file
 * @TODO
 */

angular.module('itkCampaignApp').controller('ItkCampaignModalAddChannel', [
    'busService', '$scope', '$timeout', 'close', 'channels',
    function (busService, $scope, $timeout, close, channels) {
        'use strict';

        $scope.channels = channels;

        // Toggle select of channel.
        function toggleChannel(channel) {
            $timeout(function () {
                var index = null;

                channels.forEach(function (slideChannel, channelIndex) {
                    if (channel.id === slideChannel.id) {
                        index = channelIndex;
                    }
                });

                if (index !== null) {
                    channels.splice(index, 1);
                }
                else {
                    channels.push(channel);
                }
            });
        }

        // Register event listener for clickSlide.
        $scope.$on('channelOverview.clickChannel', function (event, channel) {
            toggleChannel(channel);
        });

        /**
         * Close the modal.
         */
        $scope.closeModal = function () {
            close(null);
        };
    }
]);
