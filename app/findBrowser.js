 myApp.factory('findBrowser', function() {
        return {
            isAndroid: function() {
                //if(navigator.userAgent.match(/Android/i)) 
				//if(window.JSInterface!=undefined)
				//	return true;      
				//else					
				return false;
            }
        };
    });