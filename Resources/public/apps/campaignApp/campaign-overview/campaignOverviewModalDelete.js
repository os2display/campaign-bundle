/**
 * @file
 * @TODO
 */

angular.module('campaignApp').controller('CampaignOverviewModalDelete', [
    'busService', '$scope', '$timeout', 'close', 'campaigns',
    function (busService, $scope, $timeout, close, campaigns) {
        'use strict';

        $scope.campaigns = campaigns;

        /**
         * Close with result true.
         */
        $scope.confirm = function confirm() {
            close(true);
        };

        /**
         * Close the modal.
         */
        $scope.closeModal = function () {
            close(null);
        };
    }
]);
