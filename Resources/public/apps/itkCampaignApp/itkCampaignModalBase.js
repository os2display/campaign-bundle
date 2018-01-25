/**
 * @file
 * @TODO
 */

angular.module('itkCampaignApp').controller('ItkCampaignModalBase', [
    'busService', '$scope', '$timeout', 'close',
    function (busService, $scope, $timeout, close) {
        'use strict';

        /**
         * Close the modal.
         */
        $scope.closeModal = function () {
            close(null);
        };
    }
]);
