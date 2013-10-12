angular.module('Cyclear', ['d3']).directive('teamGraph', ['d3Service', function(d3Service) {
        return {
            restrict: 'E',
            scope: true,
            templateUrl: '/bundles/cycleargame/views/team-graph/base.html',
            replace: true,
            link: function($scope, iElement, iAttr) {

            },
            controller: function($scope, $http) {
                $scope.teams = [];
                $scope.data = [];
                $scope.selected = [];
                var maxSelected = 1;
                var finishedLoading = false;

                $http.get("/api/seasons/voorjaar-2013/teams.json").success(function(data) {
                    for (i in data) {
                        $scope.teams[i] = data[i];
                    }
                });

                $scope.setSelect = function(id) {
                    var index = $scope.selected.indexOf(id);
                    if (index > -1) {
                        $scope.selected.splice(index, 1);
                    } else {
                        $scope.selected.push(id);
                    }
                }

                d3Service.d3().then(function(d3) {

                    var margin = {top: 20, right: 80, bottom: 30, left: 50},
                    width = 800 - margin.left - margin.right,
                            height = 500 - margin.top - margin.bottom;

                    var x = d3.time.scale().range([0, width]);
                    var y = d3.scale.linear().range([height, 0]);
                    var parseDate = d3.time.format("%Y%m%d").parse;

                    var color = d3.scale.category10();

                    var xAxis = d3.svg.axis()
                            .scale(x)
                            .orient("bottom");

                    var yAxis = d3.svg.axis()
                            .scale(y)
                            .orient("left");

                    var line = d3.svg.line().interpolate("basis").x(function(d) {
                        return x(d.date);
                    })
                            .y(function(d) {
                        return y(d.points);
                    });
                    var linecolors = ['red', 'green', 'yellow', 'blue', 'orange', 'brown'];

                    $scope.$watch('selected', function(newvalue, oldvale) {
                        if($scope.finishedLoading){
                            $scope.render();
                        }
                    }, true);

                    $http.get("/api/seasons/voorjaar-2013/teams/points.json").success(function(data) {
                        var index = 1;
                        for (i in data) {
                            var newData = data[i];
                            for (j in newData) {
                                newData[j].date = parseDate(newData[j]['date']);
                            }
                            $scope.data.push(newData);
                            if (index <= maxSelected) {
                                //$scope.selected.push(parseInt(i,10));
                            }
                            index++;
                        }
                        x.domain(d3.extent($scope.data[1], function(d) {
                            return d.date;
                        }));
                        y.domain([0, 3000]);
                        $scope.finishedLoading = true;
                        $scope.render();
                        //prerender();
                        //$scope.selected.push(1);
                    });

                    var svg2 = function() {
                        return d3.select(".graphplace")
                                .append('svg')
                                .attr("width", width + margin.left + margin.right)
                                .attr("height", height + margin.top + margin.bottom)
                                .append("g")
                                .attr("transform", "translate(" + margin.left + "," + margin.top + ")");
                    }

                    var prerender = function() {
                        var svg = svg2();
                        svg.append("g").attr("class", "x axis").attr("transform", "translate(0," + height + ")").call(xAxis);
                        svg.append("g").attr("class", "y axis").call(yAxis);
                        return svg;
                    }

                    $scope.render = function() {
                        d3.select("svg").remove();
                        var svg = prerender();
                        for (i in $scope.selected) {
                            svg.append("path").attr("class", "line").attr('id', 'line_' + i)
                                    .style("stroke", linecolors[i])
                                    .attr("d", line($scope.data[i]));
                        }


                    }
                });
            }
        }
    }]);