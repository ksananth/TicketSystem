app.factory("Data", ['$http', 'toaster','findBrowser',
    function ($http, toaster,findBrowser) { // This service connects to our REST API

        var serviceBase = '';
		//if(findBrowser.isAndroid()){
			//serviceBase = 'http://mangaisolutions.com/dev/TicketSystem/api/v1/';
		//}else{
			serviceBase = 'api/v1/';
		//}

        var obj = {};
        obj.toast = function (data) {
            toaster.pop(data.status, "", data.message, 2000, 'trustedHtml');
        }
        obj.get = function (q) {
            return $http.get(serviceBase + q).then(function (results) {
                return results.data;
            });
        };
        obj.post = function (q, object) {
			console.log("browser--",findBrowser.isAndroid());
            return $http.post(serviceBase + q, object).then(function (results) {
                return results.data;
            });
        };
        obj.put = function (q, object) {
            return $http.put(serviceBase + q, object).then(function (results) {
                return results.data;
            });
        };
        obj.delete = function (q) {
            return $http.delete(serviceBase + q).then(function (results) {
                return results.data;
            });
        };

        return obj;
}]);


 app.factory('findBrowser', function() {
        return {
            isAndroid: function() {
                //if(navigator.userAgent.match(/Android/i)) 
				if(window.JSInterface!=undefined)
					return true;      
				else					
				return false;
            }
        };
    });