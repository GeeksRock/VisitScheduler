    $(window).on('hashchange', function(){
        /* 	[todo]: update all areas to use hash navigation
			[devNote]:
			On each hash change, update the $root hash property.
			Child components will be listening for the change on $root.
		*/
		app.hash = window.location.hash;
    });
	
	var app = new Vue({
		/* [toDo]: 
			re-vamp this component to use hash navigation
		*/
		el: "#app",
		data: {
			clientSearch: false,
			visitSearch: false,
			option: "menu",
			hash: "",
			loggedInUser: {},
			failedSecurityCheck: false,
			showError: false,
			errorObj: {
				errorContent: null,
				errorTitle: null
			}
		},
		computed: {
			//todo: update entire app to use hash navigation!!!
			hashStats: function () {
				return {
					hash: this.hash,
					option: this.option,
					clientSearch: this.clientSearch,
					visitSearch: this.visitSearch
				};
			}
		},
		watch: {
			hash: function (newVal, oldVal) {
				//debugger
				switch (newVal) {
					case "":
						this.option = "menu";
					break;
					case "visits":
						this.option = "visitList";
					break;
					case "clients":
						this.option = "clientList";						
					break;
					case "users":
						this.option = "userList";
					break;
				}
			},
			option: function (newVal, oldVal) {
				if (newVal === "menu") {
					this.clientSearch = false;
					this.visitSearch = false;
				}
			},
			clientSearch: function (newVal, oldVal) {
				if (newVal === true) {
					this.option = 'clientSearch';
				}
			},
			visitSearch: function (newVal, oldVal) {
				if (newVal === true) {
					this.option = 'visitSearch';
				}
			}
		},
		methods: {
			getSession: function () {
				if (this.$root.failedSecurityCheck) { return; }
				var session = this;
				$.ajaxSetup({cache: false});

				utils.async('php/session.php', {allow_pass_through: 1},
					function (error) {
					  if (error.indexOf("error.php") > -1) {
						session.$root.executeAccountModifiedSecurityProcedures("session");
					  } else {
						  session.$root.executeUnexpectedErrorProcedures("verifying", "session");
					  }
					},
					function (data) {
					  if (data.indexOf("error.php") > -1) {
						session.$root.executeAccountModifiedSecurityProcedures("session");
					  } else if (utils.jsonTryParse(data)) {
							session.loggedInUser = JSON.parse(data);
					  } else {
						    document.location.href = "https://www.clac.site/ChurchScheduler/error.php";
					  }
					});
			},
			handleAccountAndSecurityErrors: function (error, phpFileName) {
			  if (error.indexOf("error.php") > -1) {
				this.executeAccountModifiedSecurityProcedures(phpFileName);
			  }
			},
			handleDataBaseResultErrors: function (results, phpFileName) {
			  if (result.indexOf("error.php") > -1) {
				this.executeUnexpectedErrorProcedures(phpFileName);
			  }
			},
			executeUnexpectedErrorProcedures: function (action, entity) {
			  if (this.$root.failedSecurityCheck) { return; }
			  var contentString = utils.stringBuilder(action, "this", true);
				  contentString = utils.stringBuilder(contentString, ".");

			  var errorObj = {
						  errorTitle: "Unexpected Error!",
						  errorContent: "An unexpected error occurred while " + contentString + " \n Try refeshing your browser window. \n If the problem persists, contact your System Administrator."
					  };

			  //this.setErrors(errorObj);
			  return;
			},
			executeAccountModifiedSecurityProcedures: function (location) {
				debugger;
			  //this function is executed when a logged-in user's credentials
			  //have been altered and they no longer have acess to the app
			  this.failedSecurityCheck = true;
			  var errorObj = utils.accountModifiedErrorObj(location);
			  this.setErrors(errorObj);
			  //this.logOut();
			},
			setErrors: function (errorObj) {
				var hr = "";
				for (var l = 0; l < errorObj.errorTitle.length; l++) {
					hr += "=";
				}
				this.errorObj = errorObj;				
				this.showError = true;
			},
			logOut: function  () {
				var session = this;
				$.ajaxSetup({cache: false});
				$.post("php/logOut.php", function (data) {
					session.loggedInUser = {};
					document.location.href = "https://www.clac.site/ChurchScheduler/login.php";
				});
			}
		},
		created: function () {
			if (this.failedSecurityCheck) { return; } //do we need this check?
			window.location.hash = '';
			this.getSession();
		}
	});	
	
	var userList = Vue.component('UserList', {
		/* [toDo]:
			re-vamp this component to use hash navigation
		*/
		template: "#userList",
		data: function () {
			return {
				confirmLogout: false,
				confirmPasswordReset: false,
				notifyOfPasswordReset: false,
				notifyOfDuplicateEmail: false,
				notifyOfInvalidEmailAddress: false,
				notifyOfInvalidFirstName: false,
				notifyOfInvalidLastName: false,
				//canResetPassword: update computed prop
				option: "user-main",
				sub_option: "",
				user: {},
				isEditing: false,
				isLoadingUsers: false,
				users: []
			}
		},
		computed: {
			attentionRequired: function () {
				if (this.notifyOfDuplicateEmail) { return true; }
				if (this.notifyOfInvalidEmailAddress) { return true; }
				if (this.notifyOfInvalidFirstName) { return true; }
				if (this.notifyOfInvalidLastName) { return true; }
				if (this.notifyOfPasswordReset) { return true; }
				if (this.confirmPasswordReset) { return true; }
				return false;
			},
			hashStats: function () {
				return {
					hash: this.$parent.hash,
					parentOption: this.$parent.option,
					option: this.option,
					subOption: this.sub_option
				};
			},
			isLoggedInUser: function () {
				var isLoggedInUser = false;
				if (parseInt(this.$root.loggedInUser.id) === this.user.Id) {
					isLoggedInUser = true;
				}
				return isLoggedInUser;
			},
			showEditControls: function () {
				return this.option === 'user-edit';
			},
			canResetPassword: function () {
				var canResetPassword = true;
				
				if (!this.$root.loggedInUser.isAdmin) { 
					canResetPassword = false;
				}
				if (this.isLoggedInUser) {
					canResetPassword = false;
				}
				
				return canResetPassword;
			},
			actionText: function () {
				if (this.$parent.option === "myProfile") { return "My Profile"; }
				if (this.option === "user-edit") { return "Edit User"; }
				if (this.$parent.option === "userList") { return "User List"; }
				if (this.$parent.option === "userNew") { return "Add New User"; }				
			}
		},
		watch: {	
			'$parent.option': function (newVal, oldVal) {
				switch(newVal) {
					case "userNew": 
						this.getNewUserTemplate();
						this.option = "user-new";
						break;
					case "userList":
						this.option = "user-main";
						this.getUsers();
						break;
					case "myProfile":
						this.option = "user-edit";
						this.getMyProfile();
						break;
					default:
						this.reset();
						break;	
				}
			}
		},
		methods: {
			reset: function (){
				this.confirmPasswordReset = false;
				this.notifyOfPasswordReset = false;
				//canResetPassword: update computed prop
				this.option = "user-main";
				this.sub_option = "";
				this.user = {};
				this.isEditing = false;
				this.isLoadingUsers = false;
				this.users = [];
				if (this.originalUser) { this.originalUser = {}; }
			},
			addUser: function (callBack) {
				if (this.$root.failedSecurityCheck) { return; }

				var phpFile = "php/addUser.php",
				  userListComponent = this, 
				  loggedInUserData = {
					  logged_in_user_id: userListComponent.$root.loggedInUser.id,
					  logged_in_user_email: userListComponent.$root.loggedInUser.email,
					  first_name: userListComponent.user.FirstName,
					  last_name: userListComponent.user.LastName,
					  email: userListComponent.user.Email,
					  phone: userListComponent.user.Phone,
					  is_admin: userListComponent.user.IsAdmin,
					  password: utils.userLogIns.generateRandomPassword(20)
				  };
				
				  utils.async(phpFile, loggedInUserData,
					  function (err) {
						  if (err.indexOf("error.php") > -1) {							  
							userListComponent.$root.executeAccountModifiedSecurityProcedures(phpFile);
							return; 
						  }
						  userListComponent.$root.executeUnexpectedErrorProcedures("loading", "data");
					  },
					  function (result) {
						  if (result.indexOf("error.php") > -1) {
							userListComponent.$root.executeAccountModifiedSecurityProcedures(phpFile);
							if (callBack && callBack(result));
							return;
						  }
						  if (result === "This email address is already in use.") {
							  if (callBack && callBack(result));
							  return;
						  } else {
							  if (utils.jsonTryParse(result)) {
								userListComponent.users = JSON.parse(result);  
							  } else {
								userListComponent.users = result;  
							  }
							  userListComponent.users.forEach(function (user) {
								user.Active = parseInt(user.Active);
								user.IsAdmin = parseInt(user.IsAdmin);
							  });
						  }
					  });	
			},
			updateUser: function () {
				if (this.$root.failedSecurityCheck) { return; }
				
				var phpFile = "php/updateUser.php",
				  userListComponent = this, 
				  loggedInUserData = {
					  logged_in_user_id: userListComponent.$root.loggedInUser.id,
					  logged_in_user_email: userListComponent.$root.loggedInUser.email,
					  first_name: userListComponent.user.FirstName,
					  last_name: userListComponent.user.LastName,
					  email: userListComponent.user.Email,
					  phone: userListComponent.user.Phone,
					  is_admin: userListComponent.user.IsAdmin,
					  id: userListComponent.user.Id
				  };
				
				  utils.async(phpFile, loggedInUserData,
					  function (err) {
						  if (err.indexOf("error.php") > -1) {
							userListComponent.$root.executeAccountModifiedSecurityProcedures(phpFile);
							userListComponent.isLoadingUsers = false;
							return;
						  }
						  userListComponent.$root.executeUnexpectedErrorProcedures("loading", "data");
					  },
					  function (result) {
						  if (result.indexOf("error.php") > -1) {
							userListComponent.$root.executeAccountModifiedSecurityProcedures(phpFile);
							return;
						  }
						  if (utils.jsonTryParse(result)) {
							userListComponent.users = JSON.parse(result);  
						  } else {
							userListComponent.users = result;  
						  }
						  userListComponent.users.forEach(function (user) {
							user.Active = parseInt(user.Active);
							user.IsAdmin = parseInt(user.IsAdmin);
						  });
					  });	
			},
			resetUserPassword: function () {
				this.notifyOfPasswordReset = true; 
				this.confirmPasswordReset = false;

				if (this.$root.failedSecurityCheck) { return; }
				
				var phpFile = "php/updateUserPassword.php",
				  userListComponent = this, 
				  loggedInUserData = {
					  logged_in_user_id: userListComponent.$root.loggedInUser.id,
					  logged_in_user_email: userListComponent.$root.loggedInUser.email,
					  first_name: user.FirstName,
					  last_name: user.LastName,
					  email: user.Email,
					  password: utils.userLogIns.generateRandomPassword(20)
				  };
				
				  utils.async(phpFile, loggedInUserData,
					  function (err) {
						  if (err.indexOf("error.php") > -1) {
							userListComponent.$root.executeAccountModifiedSecurityProcedures(phpFile);
							return;
						  }
						  userListComponent.$root.executeUnexpectedErrorProcedures("loading", "data");
					  },
					  function (result) {
						  if (result.indexOf("error.php") > -1) {
							userListComponent.$root.executeAccountModifiedSecurityProcedures(phpFile);
							return;
						  }
						  if (utils.jsonTryParse(result)) {
							userListComponent.users = JSON.parse(result);  
						  } else {
							userListComponent.users = result;  
						  }
						  userListComponent.users.forEach(function (user) {
							user.Active = parseInt(user.Active);
							user.IsAdmin = parseInt(user.IsAdmin);
						  });
					  });	
			},	
			getMyProfile: function () {
				var userProfile = this.$root.loggedInUser;
				this.user.Email = userProfile.email;
				this.user.FirstName = userProfile.firstName;
				this.user.LastName = userProfile.lastName;
				this.user.Phone = userProfile.phone;
				this.user.Id = parseInt(userProfile.id);
				this.user.IsAdmin = parseInt(userProfile.isAdmin);
				this.user.Active = 1;
			},
			getUsers: function () {
				if (this.$root.failedSecurityCheck) { return; }
				
				var phpFile = "php/getUsers.php",
				  userListComponent = this, 
				  loggedInUserData = {
					  logged_in_user_id: userListComponent.$root.loggedInUser.id,
					  logged_in_user_email: userListComponent.$root.loggedInUser.email
				  };
				  this.isLoadingUsers = true;
				
				  utils.async(phpFile, loggedInUserData,
					  function (err) {
						  if (err.indexOf("error.php") > -1) {
							userListComponent.$root.executeAccountModifiedSecurityProcedures(phpFile);
							userListComponent.isLoadingUsers = false;
							return;
						  }
						  userListComponent.$root.executeUnexpectedErrorProcedures("loading", "data");
						  userListComponent.isLoadingUsers = false;
					  },
					  function (result) {
						  if (result.indexOf("error.php") > -1) {
							userListComponent.$root.executeAccountModifiedSecurityProcedures(phpFile);
							userListComponent.isLoadingUsers = false;
							return;
						  }
						  if (utils.jsonTryParse(result)) {
							userListComponent.users = JSON.parse(result);  
						  } else {
							userListComponent.users = result;  
						  }
						  userListComponent.users.forEach(function (user) {
							user.Id = parseInt(user.Id);
							user.Active = parseInt(user.Active);
							user.IsAdmin = parseInt(user.IsAdmin);
						  });
						  
						  userListComponent.isLoadingUsers = false;
					  });	
			},	
			getNewUserTemplate: function () {
				var template = {
					FirstName: "",
					LastName: "",
					Email: "",
					Phone:"",
					IsAdmin: false
				};
				this.user = template;
			},
			openUser: function (user) {
				this.option = "user-edit";
				this.user = user;
			},
			cancel: function () {
				if (this.option === "user-edit") {
					this.isEditing = false;
				}
				if (this.$parent.option === "userNew") {
					this.$parent.option = "menu";
					this.option = "user-main";
				}
				this.notifyOfDuplicateEmail = false;
				this.notifyOfInvalidEmailAddress = false;
				this.notifyOfInvalidFirstName = false;
				this.notifyOfInvalidLastName = false;
				this.notifyOfPasswordReset = false;
				this.confirmPasswordReset = false;
			},
			save: function () {
				debugger
				var isEmailValid = utils.isValidEmailAddress(this.user.Email), 
					isLastNameValid = this.user.LastName.trim().length > 0, 
					isFirstNameValid = this.user.FirstName.trim().length > 0, 
					canSave  = isEmailValid && isLastNameValid && isFirstNameValid;

				if (!canSave) {
					if (!isEmailValid) { this.notifyOfInvalidEmailAddress = true; }
					if (!isLastNameValid) { this.notifyOfInvalidLastName = true; }
					if (!isFirstNameValid) { this.notifyOfInvalidFirstName = true; }
				} else {
					debugger;
					var self = this;
					if (this.user.hasOwnProperty("Id")) {					
						this.updateUser(function (updateUserResult) {
							if (updateUserResult === "This email address is already in use.") {
								self.notifyOfDuplicateEmail = true;
							} else if (true) {
								
							} else {
								self.cancel();
							}
						});
						this.cancel();
					} else {
						this.addUser(function (addUserResult) {
							if (addUserResult === "This email address is already in use.") {
								self.notifyOfDuplicateEmail = true;
							} else if (true) {
								
							} else {
								self.cancel();
							}
						});
					}
				}
			}
		},
		created: function () {
			//untracked properties; used for cancellations
			this.originalUser = {};
		}
	});
	
	var clientList = Vue.component('ClientList', {
		/* [toDo]:
			re-vamp this component to use hash navigation
		*/
		template: "#clientList",
		data: function () {
			var context = this;
			return {
				showNewClient: false,
				option: "client-main",
				sub_option: "client-edit-info",
				confirmNoteDeletion: false,
				notifyOfInvalidFirstName: false,
				notifyOfInvalidLastName: false,
				client: {},
				isEditing: false,
				note: {},
				nextVisit: {},
				isLoadingNextVisit: false,
				lastVisit: {},
				isLoadingLastVisit: false,
				visitStats: {},
				isLoadingVisitStats: false,
				clients: [], 
				isLoadingClients: false,
				noSearchResults: false,
				notes: [],
				isLoadingNotes: false,
				pageWaiter: {
					model: {
						dataWaiterDataSource: [],
						dataWaiterCallBack: function (data_to_display) {
							if (context.$root.failedSecurityCheck) { return; }
							context.clients = data_to_display;	
						}
					}
				}
			}
		},
		computed: {
			clientSearchComponent: function () {
				var context = this,
					clientSearchComponent = {}, 
					model = {
						resultsCallBack: context.processClientData,
						searchingCallBack: function (state) {
							context.isLoadingClients = state;
						}
					};
				clientSearchComponent["model"] = model;
				return clientSearchComponent;
			},
			hashStats: function () {
				return {
					hash: this.$parent.hash,
					parentOption: this.$parent.option,
					option: this.option,
					subOption: this.sub_option
				};
			},
			showEditControls: function () {
				return this.sub_option === 'client-edit-info' && this.option === 'client-edit';
			},
			isEditingClientNote: function () {
				return !_.isEmpty(this.note);
			},
			actionText: function () {
				if (this.option === "client-edit" && this.sub_option === "client-edit-info") { return "Edit Client - Info"; }
				if (this.option === "client-edit" && this.sub_option === "client-edit-visits") { return "Edit Client - Visits"; }
				if (this.option === "client-edit" && this.sub_option === "client-edit-notes") { return "Edit Client - Notes"; }
				if (this.option === "client-edit") { return "Edit Client"; }
				if (this.$parent.option === "clientList") { return "Client List"; }
				if (this.$parent.option === "clientNew") { return "Add New Client"; }
				if (this.$parent.option === "clientSearch") { return "Client Search"; } 
			}
		},
		watch: {	
			'hashStats.hash': function (newVal, oldVal) {		
				if (newVal.indexOf("#client-") === 0) {
					if (utils.charCount(newVal, '-') === 1) {
						this.option = newVal.substr(1);
						if (this.option === 'client-edit') {
							this.sub_option = "client-edit-info";
						} else {
							this.isEditing = false;
						}
					} else if (utils.charCount(newVal, '-') === 2){
						this.sub_option = newVal.substr(1);
						if (this.sub_option !== 'client-edit-notes') {
							this.note = {};
							this.originalNote = {};
						}
					}				
				}
			},
			option: function (newVal, oldVal) {
				if (newVal === 'client-edit' || oldVal === 'client-edit') {
					this.sub_option = 'client-edit-info';					
				}
				if (oldVal === 'client-new' && newVal === 'client-main') {
					this.fetchClientData();
				}
				this.setHash(newVal);
				this.noSearchResults = false;
			},
			sub_option: function (newVal, oldVal) {		
				switch (newVal) {
					case "client-edit-visits":
						this.getNextVisit();
						this.getLastVisit();
						this.getVisitStats();
						//is.getAllVisits();
						break;
					case "client-edit-notes":
						this.getClientNotes();
						break;
					case "client-edit-info":
						//anything?
						break;
				}
				this.setHash(newVal);
				this.noSearchResults = false;
			},
			'$parent.option': function (newVal, oldVal) { 
				switch(newVal) {
					case "clientNew": 
						this.getNewClientTemplate();
						this.option = "client-new";
						this.setHash(this.option);
						break;
					case "clientList":				
						this.option = "client-main";
						this.client = {};
						this.originalClient = {};
						this.fetchClientData();
						this.setHash(this.option);
						break;						
					case "menu": 
						//anything?
						break;	
					case "clientSearch": 
						this.option = "client-search";
						this.clients = [];
						this.setHash(this.option);
						break;
					default:
						this.reset();
						break;
				}		
				this.noSearchResults = false;				
			}
		},
		methods: {
			hasNoText: function (text) {
				debugger
				if (!text) {
					return false;
				} else {
					return text.trim().length > 0;
				}
			},
			reset: function () {
				this.showNewClient = false,
				this.option = "client-main";
				this.sub_option = "client-edit-info";
				this.confirmNoteDeletion = false;
				this.client = {};
				this.isEditing = false;
				this.note = {};
				this.nextVisit = {};
				this.isLoadingNextVisit = false;
				this.lastVisit = {};
				this.isLoadingLastVisit = false;
				this.visitStats = {};
				this.isLoadingVisitStats = false;
				this.clients = [];
				this.isLoadingClients = false;
				this.notes = [];
				this.isLoadingNotes = false;
				this.pageWaiter.model.dataWaiterDataSource = [];
				if (this.originalClient) { this.originalClient = {}; }
			},
			processClientData: function (clientData) {
				if (this.$root.failedSecurityCheck) { return; }
				this.clients = clientData;
				
				if (this.clients.length > 0) {
					this.clients.forEach(function (client) {
					  client.Inactive = (parseInt(client.Inactive) === 0) ? false : true;
					  client.Id = parseInt(client.Id);
					});
					
					this.pageWaiter.model.dataWaiterDataSource = this.clients;
					this.showNewClient = false;	
				} else {
					this.noSearchResults = true;
				}
			},
			fetchClientData: function () {
			  if (this.$root.failedSecurityCheck) { return; }
			  var phpFile = (this.showNewClient) ? 'php/showNewestClients.php' : 'php/getClients.php', 
				  clientListComponent = this, 
				  loggedInUserData = {
					  logged_in_user_id: clientListComponent.$root.loggedInUser.id,
					  logged_in_user_email: clientListComponent.$root.loggedInUser.email
				  };
				  this.isLoadingClients = true;
				  
			  utils.async(phpFile, loggedInUserData,
				  function (err) {
					  if (err.indexOf("error.php") > -1) {
						clientListComponent.$root.executeAccountModifiedSecurityProcedures('php/getClients.php');
						clientListComponent.isLoadingClients = false;
						return;
					  }
					  clientListComponent.$root.executeUnexpectedErrorProcedures("loading", "data");
					  clientListComponent.isLoadingClients = false;
				  },
				  function (result) {
					  if (result.indexOf("error.php") > -1) {
						clientListComponent.$root.executeAccountModifiedSecurityProcedures('php/getClients.php');
						clientListComponent.isLoadingClients = false;
						return;
					  }
					  if (utils.jsonTryParse(result)) {
						  clientListComponent.processClientData(JSON.parse(result));
					  } else {
						  clientListComponent.processClientData(result);
					  }
					  clientListComponent.isLoadingClients = false;
				  });
			},
			getNextVisit: function () {
				if (this.$root.failedSecurityCheck) { return; }
				var clientListComponent = this, 
				  loggedInUserAndClientData = {
					  logged_in_user_id: clientListComponent.$root.loggedInUser.id,
					  logged_in_user_email: clientListComponent.$root.loggedInUser.email,
					  client_id: clientListComponent.client.Id
				  };
				  this.isLoadingNextVisit = true;
				  
				  utils.async('php/getNextVisit.php', loggedInUserAndClientData,
				  function (err) {
					  if (err.indexOf("error.php") > -1) {
						clientListComponent.$root.executeAccountModifiedSecurityProcedures('php/getNextVisit.php');
						clientListComponent.isLoadingNextVisit = false;
						return;
					  }
					  clientListComponent.$root.executeUnexpectedErrorProcedures("loading", "data");
					  clientListComponent.isLoadingNextVisit = false;
				  },
				  function (result) {
					  if (result.indexOf("error.php") > -1) {
						clientListComponent.$root.executeAccountModifiedSecurityProcedures('php/getNextVisit.php');
						clientListComponent.isLoadingNextVisit = false;
						return;
					  }
					  if (utils.jsonTryParse(result)) {
						var nextVisitData = JSON.parse(result);
						if (nextVisitData.length === 1) {
							clientListComponent.nextVisit = {
								id: nextVisitData[0].Id,
								date: nextVisitData[0].Date + " " + nextVisitData[0].Time,
								type: nextVisitData[0].Type,
								user: nextVisitData[0].Visitor,
								isOverdue: parseInt(nextVisitData[0].Completed) !== 1 && utils.isPastDate(nextVisitData[0].Date + " " + nextVisitData[0].Time)
							};
						}						
					  }

					  clientListComponent.isLoadingNextVisit = false;
				  });			
			},
			getLastVisit: function () {
				if (this.$root.failedSecurityCheck) { return; }
				var clientListComponent = this, 
				  loggedInUserAndClientData = {
					  logged_in_user_id: clientListComponent.$root.loggedInUser.id,
					  logged_in_user_email: clientListComponent.$root.loggedInUser.email,
					  client_id: clientListComponent.client.Id
				  };
				  this.isLoadingLastVisit = true;
				  
				  utils.async('php/getLastVisit.php', loggedInUserAndClientData,
				  function (err) {
					  if (err.indexOf("error.php") > -1) {
						clientListComponent.$root.executeAccountModifiedSecurityProcedures('php/getLastVisit.php');
						clientListComponent.isLoadingLastVisit = false;
						return;
					  }
					  clientListComponent.$root.executeUnexpectedErrorProcedures("loading", "data");
					  clientListComponent.isLoadingLastVisit = false;
				  },
				  function (result) {
					  if (result.indexOf("error.php") > -1) {
						clientListComponent.$root.executeAccountModifiedSecurityProcedures('php/getLastVisit.php');
						clientListComponent.isLoadingLastVisit = false;
						return;
					  }
					  if (utils.jsonTryParse(result)) {
						var lastVisitData = JSON.parse(result);
						if (lastVisitData.length === 1) {
							clientListComponent.lastVisit = {
								id: lastVisitData[0].Id,
								date: lastVisitData[0].Date + " " + lastVisitData[0].Time,
								type: lastVisitData[0].Type,
								user: lastVisitData[0].Visitor,
								followUp: lastVisitData[0].FollowUpRequired
							};
						}
					  }
					  clientListComponent.isLoadingLastVisit = false;
				  });			
			},
			getVisitStats: function () {
				if (this.$root.failedSecurityCheck) { return; }
				var clientListComponent = this, 
				  loggedInUserAndClientData = {
					  logged_in_user_id: clientListComponent.$root.loggedInUser.id,
					  logged_in_user_email: clientListComponent.$root.loggedInUser.email,
					  client_id: clientListComponent.client.Id
				  };
				  this.isLoadingVisitStats = true;
				  
				  utils.async('php/getVisitStats.php', loggedInUserAndClientData,
				  function (err) {
					  if (err.indexOf("error.php") > -1) {
						clientListComponent.$root.executeAccountModifiedSecurityProcedures('php/getVisitStats.php');
						clientListComponent.isLoadingVisitStats = false;
						return;
					  }
					  clientListComponent.$root.executeUnexpectedErrorProcedures("loading", "data");
					  clientListComponent.isLoadingVisitStats = false;
				  },
				  function (result) {
					  if (result.indexOf("error.php") > -1) {
						clientListComponent.$root.executeAccountModifiedSecurityProcedures('php/getVisitStats.php');
						clientListComponent.isLoadingVisitStats = false;
						return;
					  }
					  if (utils.jsonTryParse(result)) {
						var visitStatsData = JSON.parse(result);
						if (visitStatsData.length > 0) {
							clientListComponent.visitStats = {
								complete: utils.sum(visitStatsData, 'completed'),
								pending: utils.sum(visitStatsData, 'pending'),
								overdue: utils.sum(visitStatsData, 'overdue')
							};
						}
					  }
					  clientListComponent.isLoadingVisitStats = false;
				  });			
			},
			getClientNotes: function () {
				if (this.$root.failedSecurityCheck) { return; }
				var clientListComponent = this, 
				  loggedInUserAndClientData = {
					  logged_in_user_id: clientListComponent.$root.loggedInUser.id,
					  logged_in_user_email: clientListComponent.$root.loggedInUser.email,
					  client_id: clientListComponent.client.Id
				  };
				  this.isLoadingNotes = true;
				  
				  utils.async('php/getClientNotes.php', loggedInUserAndClientData,
				  function (err) {
					  if (err.indexOf("error.php") > -1) {
						clientListComponent.$root.executeAccountModifiedSecurityProcedures('php/getClientNotes.php');
						clientListComponent.isLoadingNotes = false;
						return;
					  }
					  clientListComponent.$root.executeUnexpectedErrorProcedures("loading", "data");
					  clientListComponent.isLoadingNotes = false;
				  },
				  function (result) {
					  if (result.indexOf("error.php") > -1) {
						clientListComponent.$root.executeAccountModifiedSecurityProcedures('php/getClientNotes.php');
						clientListComponent.isLoadingNotes = false;
						return;
					  }
					  if (utils.jsonTryParse(result)) {
						var clientNoteData = JSON.parse(result);
						clientListComponent.notes = clientNoteData;
						clientListComponent.isLoadingNotes = false;
						clientListComponent.note = {};
						clientListComponent.originalNote = {};
					  }
				  });	
			},
			setHash: function (hash) {
				if (!hash) {
					window.location.hash = "";
				} else if (hash) {
					window.location.hash = "#" + hash;
				}
			},
			getNewClientTemplate: function () {
				var template = {
					FirstName: "",
					LastName: "",
					Phone: "",
					Email: "",
					Address: "",
					City: "",
					State: "",
					ZipCode: ""
				};
				this.client = template;
				this.originalClient = utils.cloneObject(this.client);
			},
			openClient: function (client) {
				this.option = "client-edit";
				this.sub_option = "client-edit-info";
				this.client = client;
				this.originalClient = utils.cloneObject(this.client);
			},
			openClientNote: function (note) {
				if (this.$parent.loggedInUser.id === note.UserId && !this.isEditingClientNote) {
					this.originalNote = utils.cloneObject(note);
					this.note = utils.cloneObject(note);
				}
			},
			thisNoteIsBeingEdited: function (note) {
				return this.note.Id === note.Id;
			},
			cancel: function () {
				var clientListComponent = this;
				
				if (this.option === "") {
					
				}
				
				if (this.option === "client-edit" && this.sub_option === "client-edit-notes") {
					this.getClientNotes();
					return;
				}

				if (this.option === "client-edit") {
					var keys = _.keys(this.client);
					keys.forEach(function (key) {						
						clientListComponent.client[key] = clientListComponent.originalClient[key];
					});
					//this.originalClient = {};
					//this.client = {};
					this.isEditing = false;
				}				
				
				if (this.option === "client-new" && this.$parent.option === "clientNew") {
					this.$parent.option = "menu";
				}		
			},
			save: function () {
				if (this.$root.failedSecurityCheck) { return; }

				var phpFile = (this.option === "client-new") ? "php/addClient.php" : "php/updateClient.php",
				  clientListComponent = this, 
				  loggedInUserAndClientData = {
					  logged_in_user_id: clientListComponent.$root.loggedInUser.id,
					  logged_in_user_email: clientListComponent.$root.loggedInUser.email,
					  first_name: clientListComponent.client.FirstName,
					  last_name: clientListComponent.client.LastName,
					  email: clientListComponent.client.Email,
					  phone: clientListComponent.client.Phone,
					  address: clientListComponent.client.Address,
					  city: clientListComponent.client.City,
					  state: clientListComponent.client.State,
					  zip_code: clientListComponent.client.ZipCode,
					  client_id: (clientListComponent.client.Id) ? clientListComponent.client.Id : null,
					  inactive: (clientListComponent.client.Inactive) ? clientListComponent.client.Inactive : false
				  };

				  utils.async(phpFile, loggedInUserAndClientData,
				  function (err) {
					  if (err.indexOf("error.php") > -1) {
						clientListComponent.$root.executeAccountModifiedSecurityProcedures(phpFile);
						return;
					  }
					  clientListComponent.$root.executeUnexpectedErrorProcedures("loading", "data");
				  },
				  function (result) {
					  if (result.indexOf("error.php") > -1) {
						clientListComponent.$root.executeAccountModifiedSecurityProcedures(phpFile);
						return;
					  }
				  });
				this.isEditing = false;
				if (this.option === 'client-new') {
					this.showNewClient = true;
					setTimeout(function () {
						clientListComponent.$parent.option = 'clientList';
					}, 1000);
				}
			},
			saveNote: function (note_opt) {
				if (this.$root.failedSecurityCheck) { return; }

				var phpFile = (note_opt && note_opt.Id) ? "php/updateNote.php" : "php/addNote.php",
				  clientListComponent = this, 
				  loggedInUserAndClientData = {
					  logged_in_user_id: clientListComponent.$root.loggedInUser.id,
					  logged_in_user_email: clientListComponent.$root.loggedInUser.email,
					  note_id: (note_opt && note_opt.Id) ? note_opt.Id : null,
					  note_details: clientListComponent.note.Details,
					  note_date: (note_opt && note_opt.Id) ? note_opt.Date : utils.getCurrentDateTimeStamp(),
					  user_id: clientListComponent.$root.loggedInUser.id,
					  client_id: (clientListComponent.client.Id) ? clientListComponent.client.Id : null
				  };

				  utils.async(phpFile, loggedInUserAndClientData,
				  function (err) {
					  if (err.indexOf("error.php") > -1) {
						clientListComponent.$root.executeAccountModifiedSecurityProcedures(phpFile);
						return;
					  }
					  clientListComponent.$root.executeUnexpectedErrorProcedures("loading", "data");
				  },
				  function (result) {
					  if (result.indexOf("error.php") > -1) {
						clientListComponent.$root.executeAccountModifiedSecurityProcedures(phpFile);
						return;
					  }
				  });
				this.getClientNotes();
				this.note = {};
			},
			deleteNote: function (note) {				
				this.confirmNoteDeletion = false;
				if (this.$root.failedSecurityCheck) { return; }

				var phpFile = "php/deleteNote.php",
				  clientListComponent = this, 
				  loggedInUserAndClientData = {
					  logged_in_user_id: clientListComponent.$root.loggedInUser.id,
					  logged_in_user_email: clientListComponent.$root.loggedInUser.email,
					  note_id: note.Id
				  };

				  utils.async(phpFile, loggedInUserAndClientData,
				  function (err) {
					  if (err.indexOf("error.php") > -1) {
						clientListComponent.$root.executeAccountModifiedSecurityProcedures(phpFile);
						return;
					  }
					  clientListComponent.$root.executeUnexpectedErrorProcedures("loading", "data");
				  },
				  function (result) {
					  if (result.indexOf("error.php") > -1) {
						clientListComponent.$root.executeAccountModifiedSecurityProcedures(phpFile);
						return;
					  }
				  });
				this.getClientNotes();
				this.cancel();
			}
		},
		created: function () {
			this.pageWaiter.model.dataWaiterDataSource = this.clients;
			
			//untracked properties; used for cancellations
			this.originalClient = {};
			this.originalNote = {};
		}
	});
	
	var visitList = Vue.component('VisitList', {
		/* [toDo]:
			re-vamp this component to use hash navigation
		*/
		template: "#visitList",
		data: function () {
			var context = this;
			return {
				showNewVisit: false,
				option: "visit-main",
				sub_option: "visit-edit-info",
				confirmNoteDeletion: false,
				confirmCancelation: false,
				visit: {},
				isEditing: false,
				note: {},
				notes: [],
				isLoadingNotes: false,
				visits: [],
				isLoadingVisits: false,
				noSearchResults: false,
				visitors: [],
				isLoadingVisitors: false,
				types: [],
				isLoadingTypes: false,
				client: {},
				isChangingClients: false,
				isLoadingVisitClient: false,
				pageWaiter: {
					model: {
						dataWaiterDataSource: [],
						dataWaiterCallBack: function (data_to_display) {
							if (context.$root.failedSecurityCheck) { return; }
							context.visits = data_to_display;
						}
					}
				}
			}
		},
		computed: {
			hasNoText: function () {
				if (!this.note && this.note.Details) { return true; }
				return this.note.Details.trim().length > 0;
			},
			hashStats: function () {
				return {
					hash: this.$parent.hash,
					parentOption: this.$parent.option,
					option: this.option,
					subOption: this.sub_option
				};
			},
			clientSelectComponent: function () {
				//this search component sets selected client for current visit
				var context = this,
					clientSelectComponent = {}, 
					model = {
						showPickList: true,
						selectionCallBack: function (client) {
							context.visit.Client_Id = client.Id;
							context.visit.Client = utils.fullName(client.FirstName, client.LastName);
							context.isChangingClients = false;
						}
					};
				clientSelectComponent["model"] = model;
				return clientSelectComponent;
			},
			visitSearchComponent: function () {
				//this search component shows a list of all visits for the selected client
				var context = this,
					visitSearchComponent = {}, 
					model = {
						showPickList: true,
						selectionCallBack: function (client) {
							context.client = client;
							if (context.option === 'visit-search') {
								context.option = 'visit-main';
								context.fetchVisitData();
							}
						}
					};
				visitSearchComponent["model"] = model;
				return visitSearchComponent;
			},
			timeHours: function () {
				var timeHours = [];
				for (var hour = 1; hour <= 12; hour++) {
					timeHours.push(hour);
				}
				return timeHours;
			},
			timeMinutes: function () {
				var timeMinutes = [];
				for (var m = 0; m < 60; m++) {
					var minute = m.toString();
					if (minute.length === 1) {
						minute = "0" + minute.toString();
					}
					timeMinutes.push(minute);
				}
				return timeMinutes;
			},
			showEditControls: function () {
				return this.sub_option === 'visit-edit-info' && this.option === 'visit-edit';
			},
			isEditingVisitNote: function () {
				return !_.isEmpty(this.note);
			},
			actionText: function () {
				if (this.option === "visit-edit" && this.sub_option === "visit-edit-info") { return "Edit Visit - Info"; }
				if (this.option === "visit-edit" && this.sub_option === "visit-edit-client") { return "Edit Visit - Client"; }
				if (this.option === "visit-edit" && this.sub_option === "visit-edit-notes") { return "Edit Visit - Notes"; }
				if (this.option === "visit-edit") { return "Edit Visit"; }
				if (this.$parent.option === "visitList") { return "Visit List"; }
				if (this.$parent.option === "visitNew") { return "Add New Visit"; }
				if (this.$parent.option === "visitSearch") { return "Client Visit Search"; }
				if (this.option === "visit-calendar" && this.sub_option === "visit-calendar-user") { return "Weekly Calendar - My Visits"; }
				if (this.option === "visit-calendar" && this.sub_option === "visit-calendar-users") { return "Weekly Calendar - All Visits"; }
				if (this.$parent.option === "visitCalendar") { return "Weekly Calendar"; }
			}
		},
		watch: {//todo: update entire app to use hash navigation!!!
			'hashStats.hash': function (newVal, oldVal) {
				if (newVal.indexOf("#visit-") === 0) {
					if (utils.charCount(newVal, '-') === 1) {
						this.option = newVal.substr(1);
						if (this.option === 'visit-edit') {
							this.sub_option = "visit-edit-info";
							this.fetchVisitData();
						} else {
							this.isEditing = false;
						}
						if (newVal === '#visit-calendar') {
							this.sub_option = this.lastCalendarSelection;
							this.getWeeklyVisits(this.lastCalendarSelection);
						}
					} else if (utils.charCount(newVal, '-') === 2){
						this.sub_option = newVal.substr(1);
						if (this.sub_option !== 'visit-edit-notes') {
							this.note = {};
							this.originalNote = {};
						}
					    if (this.sub_option === "visit-edit-info") {
							this.fetchVisitData();
						}
						if (newVal === '#visit-calendar-user' || newVal === '#visit-calendar-users') {
							this.option = "visit-calendar";
							this.getWeeklyVisits(this.lastCalendarSelection);
						}
					}
				}				
			},		
			isChangingClients: function (newVal, oldVal) {
				if (oldVal === true && newVal === false) {
					this.initializeDatePicker();
				}
			},		
			isEditing: function (newVal, oldVal) {
				if (newVal === true) {
				  this.initializeDatePicker();
				}
			},
			option: function (newVal, oldVal) {
				//debugger
				if (newVal === 'visit-new') {
					this.visits = [];
					this.sub_option = 'visit-edit-info';
				}
				if (newVal === 'visit-search') {
					this.visits = [];
					this.visit = {};
				}
				if (newVal === 'visit-edit' || oldVal === 'visit-edit') {
					this.sub_option = 'visit-edit-info';					
				}
				if (oldVal === 'visit-edit') {
					this.sub_option = 'visit-edit-info';
				}
				if (oldVal === 'visit-edit' && newVal === 'visit-calendar') {
					//this.fetchVisitData();
				}
				if (newVal === 'visit-main') {
					this.fetchVisitData();
				}
				this.setHash(newVal);	
				this.noSearchResults = false; 				
			},		
			sub_option: function (newVal, oldVal) {					
				switch (newVal) {
					case "visit-edit-visits":
						//anything?
						break;
					case "visit-edit-info":
						if (this.option === "visit-main") {
							this.fetchVisitData();
						}
						break;
					case "visit-edit-client":
						this.getVisitClient();
						break;
					case "visit-edit-notes":
						this.getVisitNotes();
						break;
					case "visit-calendar-user":
					case "visit-calendar-users":
						this.getWeeklyVisits(newVal);
						break;
				}
				this.setHash(newVal);
				this.noSearchResults = false; 
			},
			'$parent.option': function (newVal, oldVal) {		
				switch(newVal) {
					case "visitNew": 
						this.getNewVisitTemplate();
						this.option = "visit-new";
						this.setHash(this.option);
						this.isEditing = true;
						break;
					case "visitList":
						this.option = "visit-main";
						this.sub_option = "visit-edit-info";
						this.visit = {};
						this.originalVisit = {};
						this.fetchVisitData();
						this.setHash(this.option);
						break;	
					case "menu":
						//anything?
						break;
					case "visitSearch": 
						this.option = "visit-search";
						this.visits = [];
						this.setHash(this.option);
						break;
					case "visitCalendar":
						this.option = "visit-calendar";
						//this.visit = {};
						this.getWeeklyVisits("visit-calendar-user");
						this.setHash(this.option);
						this.isChangingClients = false;
						break;
					default:
						this.reset();
						break;	
				}
				this.isChangingClients = false;
				this.noSearchResults = false; 
			}
		},
		methods: {
			initializeDatePicker: function () {
			  var context = this;
			  setTimeout(function () {
				  $("#date-picker")
					.datepicker({onSelect: context.setDate})
					.css({"z-index":2000});
			  }, 0);
			},
			reset: function () {
				this.showNewVisit = false;
				this.option = "visit-main";
				this.sub_option = "visit-edit-info";
				this.confirmNoteDeletion = false;
				this.confirmCancelation = false;
				this.visit = {};
				this.isEditing = false;
				this.note = {};
				this.notes = [];
				this.isLoadingNotes = false;
				this.visits = [];
				this.isLoadingVisits = false;
				this.visitors = [];
				this.isLoadingVisitors = false;
				this.types = [];
				this.isLoadingTypes = false;
				this.client = {};
				this.isChangingClients = false;
				this.isLoadingVisitClient = false;	
				this.pageWaiter.model.dataWaiterDataSource = [];
				if (this.originalVisit) { this.originalVisit = {}; }
			},
			setDate: function (date) {
				this.visit.Date = date;
			},
			setHash: function (hash) {
				if (!hash) {
					window.location.hash = "";
				} else if (hash) {
					window.location.hash = "#" + hash;
				}
			},
			fetchVisitData: function () {
			  if (this.$root.failedSecurityCheck) { return; }
			  var phpFile = (this.showNewVisit) ? 'php/showNewestVisits.php' : 'php/getVisits.php',
			      visitListComponent = this, 
				  loggedInUserData = {
					  logged_in_user_id: visitListComponent.$root.loggedInUser.id,
					  logged_in_user_email: visitListComponent.$root.loggedInUser.email,
					  search_param: (visitListComponent.client.Id) ? visitListComponent.client.Id : null
				  };
				  this.isLoadingVisits = true;
				  
			  utils.async(phpFile, loggedInUserData,
				  function (err) {
					  if (err.indexOf("error.php") > -1) {
						visitListComponent.$root.executeAccountModifiedSecurityProcedures('php/getVisits.php');
						visitListComponent.isLoadingVisits = false;
						return;
					  }
					  visitListComponent.$root.executeUnexpectedErrorProcedures("loading", "data");
					  visitListComponent.isLoadingVisits = false;
				  },
				  function (result) {
					  if (result.indexOf("error.php") > -1) {
						visitListComponent.$root.executeAccountModifiedSecurityProcedures('php/getVisits.php');
						visitListComponent.isLoadingVisits = false;
						return;
					  }
					  if (utils.jsonTryParse(result)) {
						  visitListComponent.processVisitData(JSON.parse(result));
					  } else {
						  visitListComponent.processVisitData(result);
					  }
					  visitListComponent.isLoadingVisits = false;
				  });	
			},
			processVisitData: function (visitData) {
				if (this.$root.failedSecurityCheck) { return; }
				this.visits = visitData;
				if (this.visits.length > 0) {
					this.visits.forEach(function (visit) {
					  visit.Completed = (parseInt(visit.Completed) === 0) ? false : true;
					  visit.FollowUpRequired = (parseInt(visit.FollowUpRequired) === 0) ? false : true;
					  visit.Id = parseInt(visit.Id);
					  var timeObject = utils.time.getObjectFromTimeString(visit.Time);
					  visit.TimeHour = timeObject.TimeHour;
					  visit.TimeMinutes = timeObject.TimeMinutes;
					  visit.TimeOfDay = timeObject.TimeOfDay;
					  visit.Overdue = !visit.Completed && utils.isPastDate(visit.Date + " " + visit.Time)
					});
					this.pageWaiter.model.dataWaiterDataSource = this.visits;	
					this.showNewVisit = false;
				} else {
					this.noSearchResults = true;
				}
			},
			getWeeklyVisits: function (state) {
				if (this.$root.failedSecurityCheck) { return; }
				this.sub_option = state;
				this.lastCalendarSelection = state;
				this.isLoadingVisits = true;
				
				var phpFile = (this.sub_option === "visit-calendar-user") ? "php/getWeeklyVisitsForUser.php" : "php/reports/weekly_visits.php",
				  visitListComponent = this, 
				  loggedInUserAndVisitData = {
					  logged_in_user_id: visitListComponent.$root.loggedInUser.id,
					  logged_in_user_email: visitListComponent.$root.loggedInUser.email
				  };
				  utils.async(phpFile, loggedInUserAndVisitData,
				  function (err) {
					  if (err.indexOf("error.php") > -1) {
						visitListComponent.$root.executeAccountModifiedSecurityProcedures(phpFile);
						visitListComponent.isLoadingVisits = false;
						return;
					  }
					  visitListComponent.$root.executeUnexpectedErrorProcedures("loading", "data");
					  visitListComponent.isLoadingVisits = false;
				  },
				  function (result) {
					  if (result.indexOf("error.php") > -1) {
						visitListComponent.$root.executeAccountModifiedSecurityProcedures(phpFile);
						visitListComponent.isLoadingVisits = false;
						return;
					  }
					  if (utils.jsonTryParse(result)) {
						visitListComponent.processVisitData(JSON.parse(result));  
					  } else {
						visitListComponent.processVisitData(result);  
					  }
					  visitListComponent.isLoadingVisits = false;
				  });	
			},
			getVisitNotes: function () {
				if (this.$root.failedSecurityCheck) { return; }
				var visitListComponent = this, 
				  loggedInUserAndClientVisitData = {
					  logged_in_user_id: visitListComponent.$root.loggedInUser.id,
					  logged_in_user_email: visitListComponent.$root.loggedInUser.email,
					  client_id: visitListComponent.visit.Client_Id,
					  visit_id: visitListComponent.visit.Id
				  };
				  this.isLoadingNotes = true;
				  
				  utils.async('php/getVisitNotes.php', loggedInUserAndClientVisitData,
				  function (err) {
					  if (err.indexOf("error.php") > -1) {
						visitListComponent.$root.executeAccountModifiedSecurityProcedures('php/getVisitNotes.php');
						visitListComponent.isLoadingNotes = false;
						return;
					  }
					  visitListComponent.$root.executeUnexpectedErrorProcedures("loading", "data");
					  visitListComponent.isLoadingNotes = false;
				  },
				  function (result) {
					  if (result.indexOf("error.php") > -1) {
						visitListComponent.$root.executeAccountModifiedSecurityProcedures('php/getVisitNotes.php');
						visitListComponent.isLoadingNotes = false;
						return;
					  }
					  if (utils.jsonTryParse(result)) {
						var visitNoteData = JSON.parse(result);
						visitListComponent.notes = visitNoteData;
						visitListComponent.isLoadingNotes = false;
						visitListComponent.note = {};
						visitListComponent.originalNote = {};
					  }
				  });	
			},
			cancelVisit: function () {
				if (this.$root.failedSecurityCheck) { return; }

				var phpFile = "php/cancelVisit.php",
				  visitListComponent = this, 
				  loggedInUserAndClientVisitData = {
					  logged_in_user_id: visitListComponent.$root.loggedInUser.id,
					  logged_in_user_email: visitListComponent.$root.loggedInUser.email,
					  visit_id: this.visit.Id
				  };

				  utils.async(phpFile, loggedInUserAndClientVisitData,
				  function (err) {
					  if (err.indexOf("error.php") > -1) {
						visitListComponent.$root.executeAccountModifiedSecurityProcedures(phpFile);
						return;
					  }
					  visitListComponent.$root.executeUnexpectedErrorProcedures("loading", "data");
				  },
				  function (result) {
					  if (result.indexOf("error.php") > -1) {
						visitListComponent.$root.executeAccountModifiedSecurityProcedures(phpFile);
						return;
					  }
				  });
				  
				  utils.async("php/deleteAllVisitNotes.php", loggedInUserAndClientVisitData,
				  function (err) {
					  if (err.indexOf("error.php") > -1) {
						visitListComponent.$root.executeAccountModifiedSecurityProcedures("php/deleteAllVisitNotes.php");
						return;
					  }
					  visitListComponent.$root.executeUnexpectedErrorProcedures("loading", "data");
				  },
				  function (result) {
					  if (result.indexOf("error.php") > -1) {
						visitListComponent.$root.executeAccountModifiedSecurityProcedures("php/deleteAllVisitNotes.php");
						return;
					  }
				  });
				  
				  this.isEditing = false;
				  this.confirmCancelation = false;
				  setTimeout(function () {
					this.$parent.option = "visitList";
				  }, 1000);
			},
			getNewVisitTemplate: function () {
				var template = {
					Client: "",
					Client_Id: "",
					Date: "",
					Type_Id: "",
					TimeHour: "",
					TimeMinutes: "",
					TimeOfDay: "",
					Visitor_Id: ""		
				};
				this.visit = template;
				this.getVisitTypes();
				this.getVisitors();
				this.isEditing = true;
			},
			openVisit: function (visit) {
				this.getVisitTypes();
				this.getVisitors();
				this.option = "visit-edit";
				this.sub_option = "visit-edit-info";
				this.visit = visit;
				this.originalVisit = utils.cloneObject(this.visit);
			},
			openVisitNote: function (note) {
				if (this.$parent.loggedInUser.id === note.UserId && !this.isEditingVisitNote) {
					this.note = note;
					this.originalNote = utils.cloneObject(this.note);
				}
			},
			thisNoteIsBeingEdited: function (note) {
				return this.note.Id === note.Id;
			},
			cancel: function () {
				var visitListComponent = this;
				if (this.isChangingClients) {
					//make sure client isn't updated
					this.isChangingClients = false;
					return;
				}
				if (this.option === "visit-edit" && this.sub_option === "visit-edit-notes") {
					this.getVisitNotes();
					return;
				}	

				if (this.option === "visit-edit") {
					var keys = _.keys(this.visit);
					keys.forEach(function (key) {						
						visitListComponent.visit[key] = visitListComponent.originalVisit[key];
					});
					//this.originalVisit = {};
					//this.visit = {};
					this.isEditing = false;
				}	
				
				if (this.option === "visit-new" && this.$parent.option === "visitNew") {
					this.$parent.option = "menu";
				}
			},
			save: function () {
				if (this.$root.failedSecurityCheck) { return; }

				var phpFile = (this.option === "visit-new") ? "php/addVisit.php" : "php/updateVisit.php",
				  visitListComponent = this, 
				  loggedInUserAndVisitData = {
					  logged_in_user_id: visitListComponent.$root.loggedInUser.id,
					  logged_in_user_email: visitListComponent.$root.loggedInUser.email,
					  visit_id: (visitListComponent.visit.Id) ? visitListComponent.visit.Id : null,
					  date: visitListComponent.visit.Date,
					  time_hour: visitListComponent.visit.TimeHour,
					  time_minutes: visitListComponent.visit.TimeMinutes,
					  time_of_day: visitListComponent.visit.TimeOfDay,
					  client_id: visitListComponent.visit.Client_Id,
					  user_id: visitListComponent.visit.Visitor_Id,
					  type_id: visitListComponent.visit.Type_Id,
					  follow_up_required: (visitListComponent.visit.FollowUpRequired) ? visitListComponent.visit.FollowUpRequired : false,
					  completed: (visitListComponent.visit.Completed) ? visitListComponent.visit.Completed : false
				  };
				  
				  debugger;

				  utils.async(phpFile, loggedInUserAndVisitData,
				  function (err) {
					  if (err.indexOf("error.php") > -1) {
						visitListComponent.$root.executeAccountModifiedSecurityProcedures(phpFile);
						return;
					  }
					  visitListComponent.$root.executeUnexpectedErrorProcedures("loading", "data");
				  },
				  function (result) {
					  if (result.indexOf("error.php") > -1) {
						visitListComponent.$root.executeAccountModifiedSecurityProcedures(phpFile);
						return;
					  }  
				  });
				this.isEditing = false;
				if (this.option === 'visit-new') {
					this.showNewVisit = true;
					setTimeout(function () {
						visitListComponent.$parent.option = 'visitList';
					}, 1000);
				}
			},	
			saveNote: function (note_opt) {
				debugger
				if (this.$root.failedSecurityCheck) { return; }

				var phpFile = (note_opt && note_opt.Id) ? "php/updateNote.php" : "php/addNote.php",
				  visitListComponent = this, 
				  loggedInUserAndClientVisitData = {
					  logged_in_user_id: visitListComponent.$root.loggedInUser.id,
					  logged_in_user_email: visitListComponent.$root.loggedInUser.email,
					  note_id: (note_opt && note_opt.Id) ? note_opt.Id : null,
					  note_details: visitListComponent.note.Details,
					  note_date: (note_opt && note_opt.Id) ? note_opt.Date : utils.getCurrentDateTimeStamp(),
					  user_id: visitListComponent.$root.loggedInUser.id,
					  visit_id: (visitListComponent.visit.Id) ? visitListComponent.visit.Id : null, 
					  client_id: visitListComponent.visit.Client_Id 
				  };

				  utils.async(phpFile, loggedInUserAndClientVisitData,
				  function (err) {
					  if (err.indexOf("error.php") > -1) {
						visitListComponent.$root.executeAccountModifiedSecurityProcedures(phpFile);
						return;
					  }
					  visitListComponent.$root.executeUnexpectedErrorProcedures("loading", "data");
				  },
				  function (result) {
					  if (result.indexOf("error.php") > -1) {
						visitListComponent.$root.executeAccountModifiedSecurityProcedures(phpFile);
						return;
					  }
				  });
				this.getVisitNotes();
				this.note = {};
			},
			deleteNote: function (note) {				
				this.confirmNoteDeletion = false;
				if (this.$root.failedSecurityCheck) { return; }

				var phpFile = "php/deleteNote.php",
				  visitListComponent = this, 
				  loggedInUserAndClientVisitData = {
					  logged_in_user_id: visitListComponent.$root.loggedInUser.id,
					  logged_in_user_email: visitListComponent.$root.loggedInUser.email,
					  note_id: note.Id
				  };

				  utils.async(phpFile, loggedInUserAndClientVisitData,
				  function (err) {
					  if (err.indexOf("error.php") > -1) {
						visitListComponent.$root.executeAccountModifiedSecurityProcedures(phpFile);
						return;
					  }
					  visitListComponent.$root.executeUnexpectedErrorProcedures("loading", "data");
				  },
				  function (result) {
					  if (result.indexOf("error.php") > -1) {
						visitListComponent.$root.executeAccountModifiedSecurityProcedures(phpFile);
						return;
					  }
				  });
				this.getVisitNotes();
				this.cancel();
			},
			getVisitTypes: function () {
				if (this.$root.failedSecurityCheck) { return; }
				
				var phpFile = "php/getVisitTypes.php",
				  visitListComponent = this, 
				  loggedInUserAndVisitData = {
					  logged_in_user_id: visitListComponent.$root.loggedInUser.id,
					  logged_in_user_email: visitListComponent.$root.loggedInUser.email
				  };
				  this.isLoadingTypes = true;
								
				  utils.async(phpFile, loggedInUserAndVisitData,
					  function (err) {
						  if (err.indexOf("error.php") > -1) {
							visitListComponent.$root.executeAccountModifiedSecurityProcedures(phpFile);
							visitListComponent.isLoadingTypes = false;
							return;
						  }
						  visitListComponent.$root.executeUnexpectedErrorProcedures("loading", "data");
						  visitListComponent.isLoadingTypes = false;
					  },
					  function (result) {
						  if (result.indexOf("error.php") > -1) {
							visitListComponent.$root.executeAccountModifiedSecurityProcedures(phpFile);
							visitListComponent.isLoadingTypes = false;
							return;
						  }
						  if (utils.jsonTryParse(result)) {							
							visitListComponent.types = JSON.parse(result); 
						  } else {
							visitListComponent.types = result;  
						  }				  
						  visitListComponent.isLoadingTypes = false;
					  });	
			},
			getVisitors: function () {
				if (this.$root.failedSecurityCheck) { return; }
				
				var phpFile = "php/getActiveUsers.php",
				  visitListComponent = this, 
				  loggedInUserAndVisitData = {
					  logged_in_user_id: visitListComponent.$root.loggedInUser.id,
					  logged_in_user_email: visitListComponent.$root.loggedInUser.email
				  };
				  this.isLoadingVisitors = true;
				
				  utils.async(phpFile, loggedInUserAndVisitData,
					  function (err) {
						  if (err.indexOf("error.php") > -1) {
							visitListComponent.$root.executeAccountModifiedSecurityProcedures(phpFile);
							visitListComponent.isLoadingVisitors = false;
							return;
						  }
						  visitListComponent.$root.executeUnexpectedErrorProcedures("loading", "data");
						  visitListComponent.isLoadingVisitors = false;
					  },
					  function (result) {
						  if (result.indexOf("error.php") > -1) {
							visitListComponent.$root.executeAccountModifiedSecurityProcedures(phpFile);
							visitListComponent.isLoadingVisitors = false;
							return;
						  }
						  if (utils.jsonTryParse(result)) {
							visitListComponent.visitors = JSON.parse(result);  
						  } else {
							visitListComponent.visitors = result;  
						  }
						  visitListComponent.visitors.forEach(function (visitor) {
							visitor.Active = parseInt(visitor.Active);
							visitor.IsAdmin = parseInt(visitor.IsAdmin);
						  });
						  
						  visitListComponent.isLoadingVisitors = false;
					  });	
			},
			getVisitClient: function () {
				if (this.$root.failedSecurityCheck) { return; }
				
				var phpFile = "php/getClient.php",
				  visitListComponent = this, 
				  loggedInUserAndVisitData = {
					  logged_in_user_id: visitListComponent.$root.loggedInUser.id,
					  logged_in_user_email: visitListComponent.$root.loggedInUser.email,
					  client_id: visitListComponent.visit.Client_Id
				  };
				  this.isLoadingVisitClient = true;
				
				  utils.async(phpFile, loggedInUserAndVisitData,
					  function (err) {
						  if (err.indexOf("error.php") > -1) {
							visitListComponent.$root.executeAccountModifiedSecurityProcedures(phpFile);
							visitListComponent.isLoadingVisitClient = false;
							return;
						  }
						  visitListComponent.$root.executeUnexpectedErrorProcedures("loading", "data");
						  visitListComponent.isLoadingVisitClient = false;
					  },
					  function (result) {
						  if (result.indexOf("error.php") > -1) {
							visitListComponent.$root.executeAccountModifiedSecurityProcedures(phpFile);
							visitListComponent.isLoadingVisitClient = false;
							return;
						  }
						  if (utils.jsonTryParse(result)) {
							visitListComponent.client = JSON.parse(result)[0];  
						  } else {
							visitListComponent.client = result[0];  
						  }
						  
						  visitListComponent.isLoadingVisitClient = false;
					  });	
			}
		},
		created: function () {
			this.pageWaiter.model.dataWaiterDataSource = this.visits;
			
			//untracked properties; used for cancellations
			this.originalVisit = {};
			this.originalNote = {};
			this.lastCalendarSelection = "";
		}
	});
		
	var pageWaiter = Vue.component('pageWaiter', {
		template: '#pageWaiter',
		props: ['model'],
		watch: {
			'model.dataWaiterDataSource': function () {
				this.initDefaults();
			},
			recordsPerPage: function (newValue, oldValue) {
				this.initDefaults();
			}
		},
		data: function () {
			return {
				pageData: null,
				recordsPerPage: 5
			};
		},
		methods: {
			nextPage: function () {
				if (this.model.dataWaiterCallBack && this.model.dataWaiterCallBack(Waiter.serveNextPage().current_page));
				this.pageData = Waiter.servePageData();
			},
			prevPage: function () {
				if (this.model.dataWaiterCallBack && this.model.dataWaiterCallBack(Waiter.servePrevPage().current_page));
				this.pageData = Waiter.servePageData();
			},
			initDefaults: function () {
				if (!this.model.dataWaiterDataSource) {
				  this.dataSource = [];
				} else {
				  this.dataSource = this.model.dataWaiterDataSource;
				}

				Waiter.takeDataOrder(this.dataSource, this.recordsPerPage).serveSelectedPage(1);
				this.pageData = Waiter.servePageData();
				if (this.model.dataWaiterCallBack && this.model.dataWaiterCallBack(this.pageData.current_page));
			}
		},
		computed:{
			pluralism: function () {
				return (this.pageData.number_of_records === 1) ? "record" : "records";
			}
		},
		created: function () {
			this.initDefaults();
		}
	});

	var clientSearch = Vue.component('clientSearch', {
		template: "#clientSearch",
		props: ["model"],
		data: function () {
			return {
				query: "",
				results: [],
				isSearching: false,
				noSearchResults: false 
			};
		},
		watch: {
			query: function () {
				this.search();
			}
		},
		methods: {
			clear: function () {
				this.query = "";
				this.results = [];
				this.noSearchResults = false;
				if (this.model) {
					if (this.model.resultsCallBack && this.model.resultsCallBack(this.results));
				} 
			},
			getSelectedClient: function (client) {
				if (this.model && this.model.selectionCallBack && this.model.selectionCallBack(client));
			},
			search: _.debounce(function () {
			  if (this.$root.failedSecurityCheck) { return; }
			  var searchComponent = this,
				  loggedInUserDataWithSearchParam = {
				  logged_in_user_id: this.$root.loggedInUser.id,
				  logged_in_user_email: this.$root.loggedInUser.email,
				  search_param: this.query
				};
				  this.isSearching = true;
				  if (this.model.searchingCallBack && this.model.searchingCallBack(this.isSearching));

				  if (this.query === "") {
					this.isSearching = false;
					if (this.model.searchingCallBack && this.model.searchingCallBack(this.isSearching));		
					if (this.model) {
						if (!searchComponent.model.showPickList && this.model.resultsCallBack && this.model.resultsCallBack(this.results));
					} 					  
				    return;
				  }
				  
				  utils.async('php/getClients.php', loggedInUserDataWithSearchParam,
					  function (err) {
						  if (err.indexOf("error.php") > -1) {
							searchComponent.$root.executeAccountModifiedSecurityProcedures('php/getClients.php');
							searchComponent.isSearching = false;
							if (searchComponent.model.searchingCallBack && searchComponent.model.searchingCallBack(searchComponent.isSearching));
							return;
						  }
						  searchComponent.$root.executeUnexpectedErrorProcedures("loading", "data");
						  searchComponent.isSearching = false;
						  if (searchComponent.model.searchingCallBack && searchComponent.model.searchingCallBack(searchComponent.isSearching));
					  },
					  function (result) {
						  if (result.indexOf("error.php") > -1) {
							searchComponent.$root.executeAccountModifiedSecurityProcedures('php/getClients.php');
							searchComponent.isSearching = false;
							if (searchComponent.model.searchingCallBack && searchComponent.model.searchingCallBack(searchComponent.isSearching));
							return;
						  }
						  if (utils.jsonTryParse(result)) {
							if (searchComponent.model) {
								searchComponent.results = JSON.parse(result);
								if (!searchComponent.model.showPickList && searchComponent.model.resultsCallBack && searchComponent.model.resultsCallBack(searchComponent.results));
							}
						  } else {
							if (searchComponent.model) {
								searchComponent.results = result;
								if (!searchComponent.model.showPickList && searchComponent.model.resultsCallBack && searchComponent.model.resultsCallBack(searchComponent.results));
							}  
						  }
						  searchComponent.isSearching = false;
						  if (searchComponent.model.searchingCallBack && searchComponent.model.searchingCallBack(searchComponent.isSearching));
						  
						  if (searchComponent.results.length === 0) {
							  searchComponent.noSearchResults = true;
						  } else {
							  searchComponent.noSearchResults = false;
						  }
					  });
			}, 500)
		}
	});
	

	

	

