/**
 * @file
 * @TODO
 */

angular.module('campaignApp').controller('CampaignModalBase', [
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
