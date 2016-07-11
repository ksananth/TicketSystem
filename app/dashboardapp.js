var app = angular.module('myApp', ['ngNotificationsBar', 'ngSanitize','ngRoute','tc.chartjs', 'toaster','ImageCropper','angularUtils.directives.dirPagination','datatables','angular-confirm']);
var dashboardRendered=false;

app.config(['notificationsConfigProvider', function(notificationsConfigProvider){
	notificationsConfigProvider.setHideDelay(3000);
	notificationsConfigProvider.setAutoHide(false);
	notificationsConfigProvider.setAcceptHTML(true);
}]);

app.filter('secondDropdown', function () {
    return function (secondSelect, firstSelect) {
        var filtered = [];
        if (firstSelect === null) {
            return filtered;
        }
        angular.forEach(secondSelect, function (s2) {
            if (s2.type_category == firstSelect) {
                filtered.push(s2);
            }
        });
        return filtered;
    };
});

app.directive('loading', function () {
      return {
        restrict: 'E',
        replace:true,
        template: '<div class="overlay"><div id="loading-img"></div></div>',
        link: function (scope, element, attr) {
              scope.$watch('loading', function (val) {
                  if (val){
					   if(window.JSInterface!=undefined){
						  window.JSInterface.showLoading();
					   }else{
						  $(element).show();
					   }
				  }
                  else
                      {
                  
					  if(window.JSInterface!=undefined){
						 window.JSInterface.dismissLoading();
					   }else{
						$(element).hide();
					   }
                      
                      }
              });
        }
      }
  });
  
  app.directive('myDateFormat', function($filter) {
    return {
      restrict: 'A',
      link: function(scope, element, attrs) {
      var res=$filter('date')(new Date(), element.text());
        element.text(res);
      
      }
    };
  });
  
app.config(['$routeProvider',
  function ($routeProvider) {
        $routeProvider.
        when('/dashboard', {
            title: 'Login',
            templateUrl: 'partials/chart.html',
            controller: 'authCtrl'
        })
            .when('/logout', {
                title: 'Logout',
                templateUrl: 'partials/login.html',
                controller: 'logoutCtrl'
            })
			.when('/showTicket', {
                title: 'Show Ticket',
                templateUrl: 'partials/listTicket.html',
                controller: 'showTicket'
            })	
			.when('/totalTicket', {
                title: 'Show Ticket',
                templateUrl: 'partials/totalTicket.html',
                controller: 'allTicket'
            })
			.when('/addTicket', {
                title: 'Add Ticket',
                templateUrl: 'partials/addTicket.html',
                controller: 'addTicket'
            })
			.when('/searchTicket', {
                title: 'Search Ticket',
                templateUrl: 'partials/searchTicket.html',
                controller: 'searchTicket'
            })
			.when('/searchResult', {
                title: 'Search Result',
                templateUrl: 'partials/listTicket.html',
                controller: 'searchResult'
            })
			.when('/myAccount', {
                title: 'My Account',
                templateUrl: 'partials/myAccount.html',
                controller: 'myAccount'
            })
			.when('/logout', {
                title: 'Logout',
                templateUrl: 'partials/login.html',
                controller: 'logoutCtrl'
            })
			.when('/viewUser', {
                title: 'viewUser',
                templateUrl: 'partials/viewUser.html',
                controller: 'viewUser'
            })
			.when('/addUser', {
                title: 'editUser',
                templateUrl: 'partials/editUser.html',
                controller: 'editUser'
            })
            .when('/', {
                redirectTo: '/dashboard'
            })
            .otherwise({
                redirectTo: '/dashboard'
            });
  }])
    .run(function ($rootScope, $location, Data) {

        $rootScope.$on("$routeChangeStart", function (event, next, current) {
            $rootScope.authenticated = false;
			$rootScope.dashboardRendered = false;
			
			
            Data.get('session').then(function (results) {
                if (results.uid) {
                    $rootScope.authenticated = true;
                    $rootScope.uid = results.uid;
                    $rootScope.name = results.name;
                    $rootScope.email = results.email;
					$rootScope.user_level = results.user_level;
					$rootScope.dashboardRendered = true;
					
					if($rootScope.user_level>0 && dashboardRendered == false){
					  dashboardRendered =true;
					  $location.path("/showTicket");
					}
                } else {
                    //var nextUrl = next.$$route.originalPath;
                    //if (nextUrl == '/signup' || nextUrl == '/login') {

                    //} else {
                    //    $location.path("/login");
                    //}
                }
            });
        });
    });