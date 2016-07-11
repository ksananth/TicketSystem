function getRandomColor() {
    var letters = '0123456789ABCDEF'.split('');
    var color = '#';
    for (var i = 0; i < 6; i++ ) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}



/***************************************************************
				DASHBOARD
****************************************************************/

app.controller('AddOrderController', function($scope, $rootScope, $routeParams, $location, $http, Data,$window,findBrowser) {     
$scope.message = 'This is Add new order screen'; 		

   $rootScope.$on('addTicket', function (event, args) {
		$scope.message = args.message;
		console.log($scope.message);
		$scope.loading = true;
									  Data.post('dashboard').then(function (results) {
											Data.toast(results);
											console.log(results);
											if(results.status=='Success'){
											  $scope.loading = false;
											  $scope.dashboard=results;
											}else{
											  $scope.loading = false;
											}											
									  });
 });
    	
	
$scope.logout = function () {
        Data.get('logout').then(function (results) {
            Data.toast(results);
            //$location.path('login');
			//$window.location="index.php";
			if(findBrowser.isAndroid()) 
					document.location.href='index.html'     
			else
		      $window.location.href = 'index.html';
		  
		  	  //Auto sign in
	if(findBrowser.isAndroid()) {
					window.JSInterface.delPassword();
					window.JSInterface.delUsername();
	}     
	else{
				
					//Saving in browser database
					if(typeof(Storage) !== "undefined") {
							localStorage.setItem("login","");
							localStorage.setItem("password","");
					} else {
					// Sorry! No Web Storage support..
					}
	}
	
        });
    }

   
     $scope.loading = true;
	 Data.post('dashboard').then(function (results) {
            Data.toast(results);
            console.log(results);
			if(results.status=='Success'){
				$scope.loading = false;
				//if(results.dashboard.user_level!='0'){
				//$location.path('showTicket');
					//}
				
				$scope.category=results.category;
				var defaultCategory = $.grep($scope.category, function(e){ return e.defaultType == 1; });
				$scope.categoryselected=defaultCategory[0].type_id;
				
				//Onchange of Department
				$scope.updateCatyegory=function(e){
					  console.log(e);
					  $scope.loading = true;
					   Data.post('changeDept',{search: {selectedDept:e}}).then(function (results) {
							Data.toast(results);
							console.log(results);
							if(results.status=='Success'){
								$scope.loading = false;
								$scope.departmentTicket(results.deptTicket);
								//$scope.$apply();
							}else{
								$scope.loading = false;
							}
					});
				}
				
				//Pie chart- total Open 
				var openTicket = results.openTicket;
				var	pieChartOpenTicket=[];			
				for (var i = 0; i < openTicket.length; i++) {
					console.log(openTicket[i].type_name);
					var pieChartObj={};
					pieChartObj.color=getRandomColor();
					pieChartObj.highlight='#FF5A5E';
					pieChartObj.value=parseInt(openTicket[i].count);
					pieChartObj.label=(openTicket[i].type_name);
					pieChartOpenTicket.push(pieChartObj);				
				}
				
				//Pie chart- total Open by staff
				var openTicketByStaff = results.openTicketbyStaff;
				var	pieChartOpenTicketByStaff=[];			
				for (var i = 0; i < openTicketByStaff.length; i++) {
					console.log(openTicketByStaff[i].user_name);
					var pieChartObj={};
					pieChartObj.color=getRandomColor();
					pieChartObj.highlight='#FF5A5E';
					pieChartObj.value=parseInt(openTicketByStaff[i].count);
					pieChartObj.label=(openTicketByStaff[i].user_name);
					pieChartOpenTicketByStaff.push(pieChartObj);				
				}
				
				
				
				
				//$scope.departmentTicket=function(deptTicke){
						var deptTicket = results.allDeptStatus;
						var categoySel=[];	
						var openTick=[];	
						var fixedTick=[];	
						var pendingTick=[];	
						var closedTick=[];							
						
						for (var i = 0; i < deptTicket.length; i++) {
							categoySel.push(deptTicket[i].type_name);
							openTick.push(parseInt(deptTicket[i].open));
							fixedTick.push(parseInt(deptTicket[i].fixed));
							pendingTick.push(parseInt(deptTicket[i].pending));
							closedTick.push(parseInt(deptTicket[i].closed));
						}
						
						  $scope.barChartData = {
      labels: categoySel,
      datasets: [
        {
          label: 'Closed',
          fillColor: '#993333',
          strokeColor: 'rgba(220,220,220,0.8)',
          highlightFill: 'rgba(220,220,220,0.75)',
          highlightStroke: 'rgba(220,220,220,1)',
          data: closedTick
        },
        {
          label: 'Open',
          fillColor: ' #ff9900',
          strokeColor: 'rgba(151,187,205,0.8)',
          highlightFill: 'rgba(151,187,205,0.75)',
          highlightStroke: 'rgba(151,187,205,1)',
          data: openTick
        },
        {
          label: 'Fixed',
          fillColor: '#009933',
          strokeColor: 'rgba(151,187,205,0.8)',
          highlightFill: 'rgba(151,187,205,0.75)',
          highlightStroke: 'rgba(151,187,205,1)',
          data: fixedTick
        },
        {
          label: 'Pending',
          fillColor: '#ff6666',
          strokeColor: 'rgba(151,187,205,0.8)',
          highlightFill: 'rgba(151,187,205,0.75)',
          highlightStroke: 'rgba(151,187,205,1)',
          data: pendingTick
        }
      ]
    };
				
				
				
				//$scope.departmentTicket(results.deptTicket);
				
				
				//PIEChart Starts
				// Chart.js Data
    $scope.data = pieChartOpenTicket;

    // Chart.js Options
    $scope.options =  {

      // Sets the chart to be responsive
      responsive: true,

      //Boolean - Whether we should show a stroke on each segment
      segmentShowStroke : true,

      //String - The colour of each segment stroke
      segmentStrokeColor : '#fff',

      //Number - The width of each segment stroke
      segmentStrokeWidth : 2,

      //Number - The percentage of the chart that we cut out of the middle
      percentageInnerCutout : 0, // This is 0 for Pie charts

      //Number - Amount of animation steps
      animationSteps : 100,

      //String - Animation easing effect
      animationEasing : 'easeOutBounce',

      //Boolean - Whether we animate the rotation of the Doughnut
      animateRotate : true,

      //Boolean - Whether we animate scaling the Doughnut from the centre
      animateScale : false,

      //String - A legend template
      legendTemplate : '<ul class="tc-chart-js-legend"><% for (var i=0; i<segments.length; i++){%><li><span style="background-color:<%=segments[i].fillColor%>"></span><span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></span></ul>'

    };
	//PIECHART Encds
	
	
	
	//PIEChart -Starts
	// Pie chart - open ticket by staff
    $scope.dataOpenTicketByStaff = pieChartOpenTicketByStaff;

    // Chart.js Options
    $scope.optionsOpenTicketByStaff =  {

      // Sets the chart to be responsive
      responsive: true,

      //Boolean - Whether we should show a stroke on each segment
      segmentShowStroke : true,

      //String - The colour of each segment stroke
      segmentStrokeColor : '#fff',

      //Number - The width of each segment stroke
      segmentStrokeWidth : 2,

      //Number - The percentage of the chart that we cut out of the middle
      percentageInnerCutout : 0, // This is 0 for Pie charts

      //Number - Amount of animation steps
      animationSteps : 100,

      //String - Animation easing effect
      animationEasing : 'easeOutBounce',

      //Boolean - Whether we animate the rotation of the Doughnut
      animateRotate : true,

      //Boolean - Whether we animate scaling the Doughnut from the centre
      animateScale : false,

      //String - A legend template
      legendTemplate : '<ul class="tc-chart-js-legend"><% for (var i=0; i<segments.length; i++){%><li><span style="background-color:<%=segments[i].fillColor%>"></span><span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></span></ul>'

    };
	//PIECHART Encds
	
	

	
	
	

    // Chart.js Options
    $scope.BarOptions =  {

      // Sets the chart to be responsive
      responsive: true,

      //Boolean - Whether the scale should start at zero, or an order of magnitude down from the lowest value
      scaleBeginAtZero : true,

      //Boolean - Whether grid lines are shown across the chart
      scaleShowGridLines : true,

      //String - Colour of the grid lines
      scaleGridLineColor : "rgba(0,0,0,.05)",

      //Number - Width of the grid lines
      scaleGridLineWidth : 1,

      //Boolean - If there is a stroke on each bar
      barShowStroke : true,

      //Number - Pixel width of the bar stroke
      barStrokeWidth : 2,

      //Number - Spacing between each of the X value sets
      barValueSpacing : 5,

      //Number - Spacing between data sets within X values
      barDatasetSpacing : 1,

      //String - A legend template
      legendTemplate : '<ul class="tc-chart-js-legend"><% for (var i=0; i<datasets.length; i++){%><li><span style="background-color:<%=datasets[i].fillColor%>"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>'
    };
	//BAR chart ends
	
	
		
			}else{
				
				//Errir case
				
			}
			
			$scope.dashboard=results;
        });
		
		
	
		
		
	
});

/***************************************************************
				aUTH CONTREOL(DASHBOARD)
****************************************************************/

app.controller('loginCtrl', function ($scope, $rootScope, $routeParams, $location, $http, Data,$window,$document,findBrowser) {
	
					
 Data.post('getDepartment', {            
        }).then(function (results) {
            Data.toast(results);
            if (results.status == "success") {               
				$scope.department=results.department;
				$scope.course=results.course;
            }else{
				$scope.department=[];
				$scope.course=[];
			}
        });
		
	 //initially set those objects to null to avoid undefined error
    $scope.login = {};
    $scope.signup = {};
	//$('body').addClass('sidebar-collapse');
	$('body').removeClass('sidebar-open');
	
	$('.box .img').on('click', function() {
			$(this).toggleClass('clicked');
			if($(this).hasClass('clicked')){
			  $(".form").fadeIn(1000);
			}else{	
			  $(".form").fadeOut(100);
			}
			
		});
	
    $scope.doLogin = function (customer) {
		$rootScope.flag=true;
        Data.post('login', {
            customer: customer
        }).then(function (results) {
            Data.toast(results);
            if (results.status == "success") {               
				
				if(findBrowser.isAndroid()) {
					window.JSInterface.registerGCM(results.email,results.name,results.uid,'Login Successfull!');
					document.location='dashboard.html';
					if(results.user_level==0 || results.user_level==0){
						document.location='dashboard.html';
					}else{
						document.location='dashboard.html#/showTicket';
					}
					window.JSInterface.saveUsername(results.user_login,results.password);
				}     
				else{
					if(results.user_level==0 || results.user_level==0){
						$window.location.href = 'dashboard.html';
					}else{
						$window.location.href = 'dashboard.html#/showTicket';
					}
					
					//Saving in browser database
					if(typeof(Storage) !== "undefined") {
					// Code for localStorage/sessionStorage.
						localStorage.setItem("login", results.user_login);
						localStorage.setItem("password", results.password);
					} else {
					// Sorry! No Web Storage support..
					}
					
				}
					
            }else{
				return false;
			}
        });
    };
	
	
		if($rootScope.flag==undefined){
	//Auto sign in
	if(findBrowser.isAndroid()) {
					if(window.JSInterface.getUsername()!=""){
						var customer={};
							customer.email=window.JSInterface.getUsername();
							customer.password=window.JSInterface.getPassword();
							$scope.doLogin(customer);
					}
	}     
	else{
				
					//Saving in browser database
					if(typeof(Storage) !== "undefined") {
					// Code for localStorage/sessionStorage.
						if(localStorage.getItem("login")!=""){
							var customer={};
							customer.email=localStorage.getItem("login");
							customer.password=localStorage.getItem("password");
							$scope.doLogin(customer);
						}
						
					} else {
					// Sorry! No Web Storage support..
					}
	}
	}
	
	
	
	 $scope.$watch('result',function(){
			//console.log('changed');
			$rootScope.user_profile_pic=$scope.result;
		  });
		  
    $scope.signup = {user_name:'',user_login:'',user_email:'',user_password:'',user_phone:'',user_category:'',user_registrationId:'',user_department:'',user_course:''};
    $scope.signUp = function (customer) {
	
	if($rootScope.user_profile_pic === undefined || $rootScope.user_profile_pic ===''){
	 $rootScope.user_profile_pic="profilepic/avatar5.png";
	}
	customer.user_profile_pic=$rootScope.user_profile_pic;
	
        Data.post('signUp', {
            customer: customer
        }).then(function (results) {
            Data.toast(results);
            if (results.status == "success") {
                
				if(findBrowser.isAndroid()){
					window.JSInterface.registerGCM($scope.signup.user_email,$scope.signup.user_login,results.uid,'Registration Successfull!');
					$location.path('login');
				}else{
					$location.path('login');
				}
				 
            }
        });
    };
    $scope.logout = function () {
        Data.get('logout').then(function (results) {
            Data.toast(results);
            if(findBrowser.isAndroid()) 
					document.location.href='index.html'     
			else
		      $window.location.href = 'index.html';
        });
    }
	
	
	});
	


/***************************************************************
				aUTH CONTREOL(DASHBOARD)
****************************************************************/

app.controller('authCtrl', function ($scope, $rootScope, $routeParams, $location, $http, Data,$window,$document,findBrowser) {
	

				
 Data.post('getDepartment', {            
        }).then(function (results) {
            Data.toast(results);
            if (results.status == "success") {               
				$scope.department=results.department;
				$scope.course=results.course;
            }else{
				$scope.department=[];
				$scope.course=[];
			}
        });
			
   
	$scope.fileChanged = function(e) {			
		
			var files = e.target.files;
					
     		var fileReader = new FileReader();
			fileReader.readAsDataURL(files[0]);		
			
			fileReader.onload = function(e) {
				$scope.imgSrc = this.result;
				$rootScope.user_profile_pic=this.result;
				$scope.$apply();
			};
			
		}		
	   
		$scope.clear = function() {
			 $scope.imageCropStep = 1;
			 $rootScope.user_profile_pic='';
			 delete $scope.imgSrc;
			 delete $scope.result;
			 delete $scope.resultBlob;
		};
		
		
	
		
});




/***************************************************************
				SHOW TICKET
****************************************************************/
app.controller('showTicket',['$scope','$rootScope','Data','$location','$confirm','notifications','$filter',function($scope,$rootScope, Data,$location,$confirm,notifications,$filter){
	$scope.users = []; //declare an empty array
	//$('body').addClass('sidebar-collapse');
	$('body').removeClass('sidebar-open');
	
	
		
	$scope.showAlertNotify=function(arr){
			$scope.fixedTickets = $filter('filter')(arr.showTicket, {call_status: '4'});
			console.log($scope.fixedTickets);
			resultSize=$scope.fixedTickets.length;
			if(resultSize>0 && arr.userLevel=='2'){				
						$scope.shownotification($scope.fixedTickets[resultSize-1].call_id);						
					      
			}else{
				$(".notifications-container").remove();
			}
	}

	$scope.callSearch=function(){
		$scope.loading = true;
		Data.post('searchTicket',{search: {status:$location.search().status}}).then(function (results) {
             
			 if(results.status=='Success'){
				  $scope.users = results.showTicket;
				  Data.toast(results);
				  $scope.loading = false;
				  angular.element(document).ready(function () {
        		  tab=$('#example').DataTable();	
					
				  //resultSize=results.showTicket.length;
				  $scope.flag=0;
				  $scope.showAlertNotify(results);
				  
					});
			 }else{
				 
			 }
			
        });
	}
	
	$scope.updateFixedDefect=function(call_id,status){
		$scope.loading = true;
			Data.post('updateFixedTicket',{search: {call_id:call_id,status:status}}).then(function (results) {
             
			 if(results.status=='success'){
				  Data.toast(results);
				  $scope.loading = false;
				  $(".error").remove();
				  $scope.callSearch();
					
			 }else{
				 
			 }
			
        });
	}
	
	$scope.shownotification=function(call_id){
		notifications.closeAll();
		notifications.showError("<span>Ticket # "+call_id+" has been Fixed.</span><br><span>Would you like to close the Ticket?</span><span class='like glyphicon glyphicon-thumbs-up thumbsUP' style='cursor:pointer;'>&nbspYES</span><span class='dislike glyphicon glyphicon-thumbs-down thumbsDOWN' click='closeAll()'>&nbspNO</span>");
		setTimeout(function() { 
			$(".thumbsUP").bind("click", function(){
				   //$(".error").slideUp(1000, function() {
					   $scope.updateFixedDefect(call_id,'OK');
						//$(".notifications-container").remove();
					//});
			});
			
			$(".thumbsDOWN").bind("click", function(){
				   //$(".error").slideUp(1000, function() {
						//$(".notifications-container").remove();
						$scope.updateFixedDefect(call_id,'NOK');
					//});
			});
		}, 2000);
	}
	
	
	
	
		
	
	


	//alert($location.search().status);
	$scope.loading = true;
	var tab;
	var resultSize=0;
	$scope.callSearch();
	
	
		
		$scope.closeAll=function(){
			console.log(notifications);
		}
		
		
	 $scope['deleteConfirmWithSettings'] = function(settings,idx,ticketId) {
      $confirm(angular.extend({text: 'Are you sure you want to delete?'}, settings || {}))
        .then(function() {
          
		  console.log(idx);
		  $scope.loading = true;
		  Data.post('deleteTicket',{search: {ticket:ticketId}}).then(function (results) {
            if(results.status=="success"){
				Data.toast(results);
				$scope.loading = false;
				$scope.deletedConfirm = 'Deleted';
				$('.ticket_row_'+idx).remove();
				tab.draw();
			}else{
				$scope.loading = false;
			}
			
        });
        
        });
    };
  
	
	$scope.sort = function(keyname){
		$scope.sortKey = keyname;   //set the sortKey to the param passed
		$scope.reverse = !$scope.reverse; //if true make it false and vice versa
	}
}]);

/***************************************************************
				Forgot password
****************************************************************/
app.controller('forgotPasswordController',function($scope, Data,$location){
	$scope.loginName=false;
	$scope.showGenerateBtn=true;
	$scope.showForgotPassBtn=false;
	$scope.showSecretCode=false;
	$scope.generateCode = function(login){
		Data.post('getSecretCode',{search: {login:login}}).then(function (results) {
			if(results.status=='success'){
				Data.toast(results);
				$scope.loginName=true;
				$scope.showSecretCode=true;
				$scope.showGenerateBtn=false;
				$scope.showForgotPassBtn=true;
			}
             
        });
	}
	
	$scope.sendPassword = function(login,pass,code){
		Data.post('sendPassword',{search: {login:login,pass:pass,code:code}}).then(function (results) {
			Data.toast(results);
			if(results.status=='success'){
				$location.path('login');
			}
             
        });
	}
	
	
});




/***************************************************************
				SHOW ALL TICKET
****************************************************************/
app.controller('allTicket',function($scope, Data,$location){
	$scope.users = []; //declare an empty array
	//$('body').addClass('sidebar-collapse');
	$('body').removeClass('sidebar-open');
	
	//alert($location.search().color);
	Data.post('searchTicket',{search: {status:""}}).then(function (results) {
		Data.toast(results);
             $scope.users = results.showTicket;
        });
	
	$scope.sort = function(keyname){
		$scope.sortKey = keyname;   //set the sortKey to the param passed
		$scope.reverse = !$scope.reverse; //if true make it false and vice versa
	}
});



/***************************************************************
				ADD TICKET
****************************************************************/
app.controller('addTicket',function($scope, Data,$location,$rootScope){
	//$('body').addClass('sidebar-collapse');
	$('body').removeClass('sidebar-open');
	
	$scope.loading = true;
	Data.post('editTicket',{search: {ticket:$location.search().ticket}}).then(function (results) {
			Data.toast(results);
            if(results.status=="Success"){
				$scope.loading = false;
				$scope.results=results;
				//if staff
				if($scope.results.userPersonalDetail.user_level==1){
				  $scope.results.status=[{status:'Open',id:'0'},{status:'Fixed',id:'4'}];
				}
				//else if admin/super admin
				else if($scope.results.userPersonalDetail.user_level==0 || $scope.results.userPersonalDetail.user_level==4){
				 $scope.results.status=[{status:'Open',id:'0'},{status:'Closed',id:'1'},{status:'Fixed',id:'4'},{status:'Pending',id:'3'}];
				}
				
				if($scope.results.userDetails.call_date == ''){	 				  					
				  $scope.results.userDetails.call_date=Date.now();
				}
				
				if($location.search().ticket==''){
				  	$scope.results.userDetails.call_phone=$scope.results.userPersonalDetail.user_phone;
				    $scope.results.userDetails.call_email=$scope.results.userPersonalDetail.user_email;
				    $scope.results.userDetails.call_first_name=$scope.results.userPersonalDetail.user_name;
				}
			 	
			  	console.log('results',results);
			}
			
        });
		
		
		
		//Comments add
		$scope.comment=[];
		
		if($location.search().ticket!=''){
			$scope.oldTicket=true;
		}else{
			$scope.oldTicket=false;
		}
		
		$scope.btn_add = function(txtcoment) {
                    if(txtcoment !=''){
						console.log('comment-',txtcoment);
                    
					$scope.loading = true;
						Data.post('addComment',{search: {name:$scope.results.userDetails.call_first_name,ticket:$location.search().ticket,comment:txtcoment}}).then(function (results) {
								Data.toast(results);
								if(results.status=="success"){
									$scope.loading = false;
									var txtcomentRow=[];
									txtcomentRow.note_id=results.commentId;
									txtcomentRow.note_body=txtcoment;
									txtcomentRow.note_relation_name=$scope.results.userPersonalDetail.user_login;
									txtcomentRow.note_post_date=new Date();
									$scope.results.notes.push(txtcomentRow);
									$scope.txtcoment = "";
									
								
									
								}else{
									$scope.loading = false;
								}
								
							});
                    }
                }
 
                $scope.remItem = function($index,note_id) {
                    
					 $scope.loading = true;
					  Data.post('deleteComment',{search: {ticket:$location.search().ticket}}).then(function (results) {
						Data.toast(results);
						if(results.status=="success"){
							$scope.loading = false;
							$scope.results.notes.splice($index, 1);
						}else{
							$scope.loading = false;
						}
						
						});
                }
		
		
		
		
	 $scope.submitForm = function(isValid) {
	 if(isValid){
	 
	 $scope.loading = true;
	      Data.post('addTicket', {
            customer: $scope.results.userDetails
        }).then(function (results) {
            Data.toast(results);
            if (results.status == "success") {
				$scope.loading = false;
				
				$rootScope.$emit('addTicket', { message: 'add' });									  
									  
				if($rootScope.user_level==0){
				 $location.path('dashboard');
				}else{
				 $location.path('showTicket');
				}
                
				//Data.toast(results.message);
            }
        });
		
	 }else{
	   alert("please enter all mandatory fields");
	 }
        
        //return false;j
      };   
});


/***************************************************************
				SEARCH TICKET
****************************************************************/
app.controller('searchTicket',function($scope, Data,$location,$rootScope){
	//$('body').addClass('sidebar-collapse');
	$('body').removeClass('sidebar-open');
	$("#fromDate").datepicker({
         dateFormat: 'yy-mm-dd'
    });
	$("#toDate").datepicker({
         dateFormat: 'yy-mm-dd'
    });
	$scope.loading = true;
	Data.post('editTicket',{search: {ticket:0}}).then(function (results) {
            Data.toast(results);
			if(results.status=="Success"){
				$scope.loading = false;
				$scope.results=results;
				$scope.results.status=[{status:'Active',id:0},{status:'Closed',id:1},{status:'Deleted',id:2}];			 	
			  	console.log('results',results);
			}
			else{
			}
			
        });
		
	$scope.searchTicket=function(){
	  console.log($scope.results);
	  $rootScope.searchResults=$scope.results
	  $scope.loading = true;
	  Data.post('searchTicket',{search: $scope.results.userDetails}).then(function (results) {
	 		 Data.toast(results);
			 if(results.status=="Success"){
				 $scope.loading = false;
             	$scope.users = results.showTicket;
				$rootScope.users = results.showTicket;
				$location.path('searchResult');
			 }else{
			  console.log("error");
			 }
       });
	}	
});


/***************************************************************
				SEARCH RESULT
****************************************************************/
app.controller('searchResult',function($scope, Data,$location,$rootScope){
	$scope.users = []; //declare an empty array
	//$('body').addClass('sidebar-collapse');
	$('body').removeClass('sidebar-open');
	
	//alert($location.search().color);
	//Data.post('searchTicket',{search: {status:""}}).then(function (results) {
             $scope.users = $rootScope.users;
    //    });
	
	
});


/***************************************************************
				Add USER
****************************************************************/
app.controller('editUser',function($scope, Data,$location,$rootScope){
	$('body').removeClass('sidebar-open');
	
	$scope.loading = true;
	  Data.post('editUsers',{search: {userId:$location.search().userId}}).then(function (results) {
	 		 Data.toast(results);
			 if(results.status=="success"){
				$scope.loading = false;
             	$scope.userDetail = results.userDetail[0];	
					
				
				if($scope.userDetail==undefined){
					$scope.userDetail={};
					$scope.userDetail.user_address = '';
					$scope.userDetail.user_category = '';
					$scope.userDetail.user_city = '';
					$scope.userDetail.user_country = '';
					$scope.userDetail.user_course = '';
					$scope.userDetail.user_department = '';
					$scope.userDetail.user_email = '';
					$scope.userDetail.user_level = '';
					$scope.userDetail.user_msg_send = '';
					$scope.userDetail.user_name = '';
					$scope.userDetail.user_login='';
					$scope.userDetail.user_password = '';
					$scope.userDetail.user_pending = '';
					$scope.userDetail.user_phone = '';
					$scope.userDetail.user_protect_edit = '';
					$scope.userDetail.user_registrationId = '';
					$scope.userDetail.user_state = '';
					$scope.userDetail.user_zip = '';
				} 
				$scope.userDetail.user_password = '';
				$scope.userDetail.userId=$location.search().userId;
				
				//if super admin
				if(results.user_level=='4'){
					$scope.userDetail.level=[{status:'Site Admin',id:0},{status:'Staff',id:1},{status:'User',id:2}];	
				}else if(results.user_level=='0'){
					$scope.userDetail.level=[{status:'Staff',id:1},{status:'User',id:2}];	
				}
				
				$scope.userDetail.category=['Staff','Student','Parent'];
				
				if($scope.userDetail.user_protect_edit=='1' || $scope.userDetail.user_protect_edit==1){
					$scope.userDetail.user_protect_edit=true;
				}else{
					$scope.userDetail.user_protect_edit=false;
				}
				
				if($scope.userDetail.user_msg_send=='1' || $scope.userDetail.user_msg_send==1){
					$scope.userDetail.user_msg_send=true;
				}else{
					$scope.userDetail.user_msg_send=false;
				}
				
				if($scope.userDetail.user_pending=='1' || $scope.userDetail.user_pending==1){
					$scope.userDetail.user_pending=true;
				}else{
					$scope.userDetail.user_pending=false;
				}
				
				
				$scope.userDetail.user_level=parseInt($scope.userDetail.user_level);
				
					
			 }else{
			  console.log("error");
			 }
       });
	   
	   
	    $scope.updateUser = function(isValid) {
			 if(isValid){
			 
				 $scope.loading = true;
				 if($scope.userDetail.user_protect_edit==true){
					$scope.userDetail.user_protect_edit=1;
				}else{
					$scope.userDetail.user_protect_edit=0;
				}
				
				if($scope.userDetail.user_msg_send==true){
					$scope.userDetail.user_msg_send=1;
				}else{
					$scope.userDetail.user_msg_send=0;
				}
				
				if($scope.userDetail.user_pending==true){
					$scope.userDetail.user_pending=1;
				}else{
					$scope.userDetail.user_pending=0;
				}
					 
					 Data.post('saveUsers', {
						customer: $scope.userDetail
					}).then(function (results) {
					    Data.toast(results);
						if (results.status == "success") {
							$scope.loading = false;
							$location.path('viewUser');
							Data.toast(results.message);
						}else{
							
						}
					});
				
			 }else{
			   alert("please enter all mandatory fields");
			 }
      
      };  

});


/***************************************************************
				View USER
****************************************************************/
app.controller('viewUser',function($scope, Data,$location,$rootScope){
	$('body').removeClass('sidebar-open');
	
	$scope.loading = true;
	  Data.post('getUsers',{search: 0}).then(function (results) {
		  Data.toast(results);
	 		 if(results.status=="Success"){
				 $scope.loading = false;
             	$scope.users = results.userList;			
			 }else{
			  console.log("error");
			 }
       });

});



/***************************************************************
				MY ACCOUNT
****************************************************************/
app.controller('myAccount',function($scope, Data,$location,$rootScope){
	//$('body').addClass('sidebar-collapse');
	$('body').removeClass('sidebar-open');
		
	//Get Account details
	$scope.loading = true;	
	Data.post('getAccountDetail').then(function (results) {
		Data.toast(results);
            if(results.status=="Success"){
				$scope.loading = false;
				$scope.results=results;
				 	console.log('results',results);
					$scope.accountDetail=results.accountDetail[0];
					$scope.accountDetail['password']='';
					if($scope.accountDetail.user_msg_send==0){
						$scope.accountDetail['user_alert']=false;
					}else{
						$scope.accountDetail['user_alert']=true;
					}
			}
			
        });	
	
	 $scope.$watch('result',function(){
			//console.log('changed');
			$rootScope.cropped_user_profile_pic=$scope.result;
			if($rootScope.cropped_user_profile_pic === undefined || $rootScope.cropped_user_profile_pic ===''){
				//$rootScope.cropped_user_profile_pic=$scope.accountDetail.user_profile_pic;
			}
	});
	
	$scope.updateUserDetail=function(){
		
		//if crooped then select it
		if($rootScope.cropped_user_profile_pic==undefined || $rootScope.cropped_user_profile_pic==''){
			$scope.accountDetail.user_profile_pic=$rootScope.user_profile_pic;
		}else{
			$scope.accountDetail.user_profile_pic=$scope.accountDetail.cropped_user_profile_pic;
		}
		
		$scope.loading = true;
		 Data.post('editAccount', {
            customer: $scope.accountDetail
        }).then(function (results) {
            Data.toast(results);
            if (results.status == "success") {
				$scope.loading = false;
                $location.path('dashboard');
				Data.toast(results.message);
            }
        });
	}
		  
     
	
	$scope.fileChanged = function(e) {			
		
			var files = e.target.files;
					
     		var fileReader = new FileReader();
			fileReader.readAsDataURL(files[0]);		
			
			fileReader.onload = function(e) {
				$scope.imgSrc = this.result;
				$rootScope.user_profile_pic=this.result;
				$scope.$apply();
			};
			
		}		
	   
		$scope.clear = function() {
			 $scope.imageCropStep = 1;
			 $rootScope.user_profile_pic='';
			 delete $scope.imgSrc;
			 delete $scope.result;
			 delete $scope.resultBlob;
		};
		
});


