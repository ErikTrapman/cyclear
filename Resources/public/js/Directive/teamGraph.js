
angular.module('Cyclear', ['d3']).directive('teamGraph', ['d3Service', function(d3Service) {
        return {
            restrict: 'E',
            scope: true,
            templateUrl: '/bundles/cycleargame/views/team-graph/base.html',
            replace: true,
            link: function($scope, iElement, iAttr) {
                $scope.seizoen = iAttr.seizoen;
                $scope.getTeams();
                
            },
            controller: function($scope, $http) {
                $scope.teams = [];
                $scope.data = [];
                $scope.selected = [];
                $scope.finishedLoading = false;
                
                $scope.getTeams = function(){
                    // TODO set up FOS JsRouting !!! Really !
                    $http.get("/api/seasons/"+$scope.seizoen+"/teams.json").success(function(data) {
                        for (i in data) {
                            $scope.teams[i] = data[i];
                        }
                    });
                }
                
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
                    
                    // we get dates in a 201301 -format
                    var parseDate = d3.time.format("%Y%m").parse;

                    $scope.color = d3.scale.category20();

                    var xAxis = d3.svg.axis()
                            .scale(x)
                            .orient("bottom").tickFormat(d3.time.format("%b"));

                    var yAxis = d3.svg.axis()
                            .scale(y)
                            .orient("left");

                    var line = d3.svg.line().interpolate("basis")
                            .x(function(d) {
                        return x(d.date);
                    })
                            .y(function(d) {
                        return y(d.points);
                    }).defined(function(d) {
                        return typeof d.points !== 'undefined';
                    });
                    
                    var svg2 = function() {
                        return d3.select(".graph")
                                .append('svg')
                                .attr("width", width + margin.left + margin.right)
                                .attr("height", height + margin.top + margin.bottom)
                                .append("g")
                                .attr("transform", "translate(" + margin.left + "," + margin.top + ")");
                    }

                    var prerender = function() {
                        var svg = svg2();
                        svg.append("g").attr("class", "x axis").attr("transform", "translate(0," + height + ")").text("AA").call(xAxis);
                        svg.append("g").attr("class", "y axis").call(yAxis);
                        return svg;
                    }

                    $scope.$watch('teams',function(newValue, oldValue){
                        if(newValue){
                            $scope.getPoints();
                        }
                    });

                    $scope.getPoints = function(){
                        $http.get("/api/seasons/"+$scope.seizoen+"/teams/points.json").success(function(data) {
                            for (i in data) {
                                var teamData = [];
                                for (j in data[i]) {
                                    teamData.push({'date': parseDate(data[i][j].date), 'points': data[i][j].points});
                                }
                                $scope.data[i] = teamData;
                            }
                            // teams are ordered by points already
                            for (i in $scope.teams) {
                                $scope.selected.push($scope.teams[i].id);
                                if (i > 9) {
                                    break;
                                }
                            }
                            for (i in $scope.data) {
                                x.domain(d3.extent($scope.data[i], function(d) {
                                    return d.date;
                                }));
                                break;
                            }

                            y.domain([0, d3.max($scope.teams, function(team) {
                                    return parseInt(team.punten, 10);
                                }) + 50]);
                            $scope.finishedLoading = true;
                            $scope.render();
                        });
                    }
                    
                    $scope.$watch('selected', function(newvalue, oldvale) {
                        if ($scope.finishedLoading) {
                            $scope.render();
                        }
                    }, true);

                    $scope.render = function() {
                        d3.select("svg").remove();
                        var svg = prerender();
                        for (i in $scope.selected) {
                            svg
                                    .append("path").attr("class", "line").attr('id', 'line_' + i)
                                    .style("stroke", $scope.color(i))
                                    .attr("d", line($scope.data[$scope.selected[i]]))
                                    ;
                        }


                    }
                });
            }
        }
    }]);