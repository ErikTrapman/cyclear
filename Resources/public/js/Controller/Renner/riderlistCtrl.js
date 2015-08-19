angular.module('Cyclear').controller('riderlistCtrl', function ($scope, $http, $filter, $timeout, $route, $routeParams) {

    $scope.initialLoad = false;
    $scope.loaded = false;

    $scope.filter = {};
    $scope.filter.name = null;
    $scope.filter.excludeWithTeam = false;

    $scope.riders = [];

    $scope.pagination = {};
    $scope.pagination.currentPage = 1;
    // keep this in sync with getPages() to prevent a new watch begin triggered on pagination-changes
    $scope.pagination.options = [2, 3, 4, 5, 6];
    $scope.data;
    $scope.pages;

    var getRiders = function (initial) {
        var routeparams = {_format: 'json', seizoen: seizoenSlug };
        if (!initial) {
            routeparams.filter = $scope.filter.name;
            routeparams.excludeWithTeam = $scope.filter.excludeWithTeam;
            routeparams.page = $scope.pagination.currentPage;
        }
        $http.get(Routing.generate('rider_index', routeparams))
            .success(function (data) {
                $scope.riders = data.items;
                $scope.data = data;
                $scope.getPages();
                $scope.initialLoad = true;
            }).error(function (reason, status) {

            });
    }

    /** Bootstrap **/
    getRiders(true);

    var timer;
    $scope.$watch('[filter, pagination]', function (newValue, oldValue) {
        if (!$scope.initialLoad) {
            return;
        }
        // do we need to reset the pagination?
        if (newValue[0].name != oldValue[0].name) {
            // looks like this does not trigger a new watch?
            $scope.pagination.currentPage = 1;
        }

        // still time requests even though we cancel them later on through Angular.
        if (timer) {
            $timeout.cancel(timer);
        }
        if (newValue) {
            timer = $timeout(function () {
                getRiders(false);
            }, 500);
        } else if (!newValue && oldValue) {
            timer = $timeout(function () {
                getRiders(false);
            }, 500);
        }
    }, true);


    $scope.getPages = function () {
        var pages = [];
        for (var x = 1; x <= Math.ceil($scope.data.total_count / $scope.data.num_items_per_page); x++) {
            pages.push(x);
        }
        $scope.pages = pages;
        var max = 6;
        var i = 1;
        $scope.pagination.options = [];
        if ($scope.pagination.currentPage > 1) {
            max = 5;
            if ($scope.pagination.currentPage > 2) {
                $scope.pagination.options.push($scope.pagination.currentPage - 1);
                max = 4;
            }
            $scope.pagination.options.push($scope.pagination.currentPage);
        }
        while (i < pages.length) {
            $scope.pagination.options.push(i + $scope.pagination.currentPage);
            i++;
            if (i >= max) {
                break;
            }
        }
        return pages;
    }

    $scope.setPage = function (pageNr) {
        if (pageNr < 1) {
            return;
        }
        $scope.pagination.currentPage = parseInt(pageNr, 10);
    }

    $scope.getUrl = function (slug) {
        return Routing.generate('renner_show', {seizoen: seizoenSlug, renner: slug});
    }

});
