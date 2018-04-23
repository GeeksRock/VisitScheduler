<!DOCTYPE html>
  <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>MCM Scheduler - Mobile</title>
        <script src="libs/moment.js"></script>
        <script src="js/waiter.js"></script>
        <script src="libs/jquery-3.1.1.min.js"></script>
        <script src="libs/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
        <link rel="stylesheet" href="libs/jquery-ui-1.12.1.custom/jquery-ui.min.css">
        <script src="libs/underscore.js"></script>
        <script src="js/utils.js"></script>
        <script src="libs/vue2_dev.js"></script>
        <link rel="stylesheet" href="libs/bootstrap-4.0.0/css/bootstrap.css">
        <script src="libs/bootstrap-4.0.0/js/bootstrap.js"></script>
        <link rel="stylesheet" href="libs/font-awesome-4.7.0/css/font-awesome.css">
        <link rel="stylesheet" href="css/app.css">
		<link rel="stylesheet" href="css/mobile_app.css">
        <link rel="stylesheet" href="libs/awesome-bootstrap-checkbox-1.0.0-alpha.5/awesome-bootstrap-checkbox.css">
    </head>
    <body>
	  <script type="text/x-template" id="clientSearch">	  
		<div class="container" style="width:100%;padding:0;margin:0">
			<i class="fa fa-search" aria-hidden="true"></i>&nbsp;Search:<br/>
			<span class="nowrap">
				<input v-if="!isSearching" v-model="query" placeholder="type to search" class="form-control" style="display:inline;width:95%!important">
			<a href="#" @click.prevent="clear">Clear</a>
			</span>
			<br/>
			<label v-if="isSearching">Searching <i class="fa fa-spinner fa-spin"></i></label>
			<ul class="list-group" style="position: relative;max-height:100px;overflow-y:scroll;" v-if="model.showPickList === true &amp;&amp; results.length > 0">
				<li class="list-group-item" v-if="results.length === 0"></li>
				<li @click.prevent="getSelectedClient(result)" class="cursor list-group-item" v-for="result in results" >{{result.FirstName}} {{result.LastName}}</li>
			</ul>
			<label v-if="model.showPickList === true &amp;&amp; noSearchResults"> No matching clients found</label>
		</div>
	  </script>
	  
      <script type="text/x-template" id="pageWaiter">
          <div class="page-waiter">
              <div class="page-waiter-counter">Page {{pageData.current_page_number}} of {{pageData.number_of_pages}} | {{pageData.number_of_records}} {{pluralism}} found</div>
              <div class="page-waiter-center"><button v-bind:disabled="pageData.number_of_pages < 2 || pageData.current_page_number === 1" @click.prevent="prevPage" class="btn btn-sm btn-primary"><i class="fa fa-chevron-left" aria-hidden="true"></i>&nbsp;Previous</button>&nbsp;&nbsp;<button v-bind:disabled="pageData.number_of_pages < 2 || pageData.current_page_number === pageData.number_of_pages" @click.prevent="nextPage" class="btn btn-sm btn-primary">Next&nbsp;<i class="fa fa-chevron-right" aria-hidden="true"></i></button></div>
              <div class="page-waiter-right page-waiter-records">Records per page: <select class="records-per-page" v-model="recordsPerPage"><option value="5">5</option><option value="10">10</option><option value="15">15</option><option value="20">20</option></select></div>
          </div>
      </script>
	
      <script type="text/x-template" id="clientList">         
        <div class="container" style="width:98vw">
			<ul class="list-group" style="width:95%;position:relative">
			  <li class="list-group-item active" @click.prevent=" $parent.option = 'menu' ">
				<div>
					<i class="cursor fa fa-bars" aria-hidden="true" title="Menu"></i>
					&nbsp;Member Care Ministries 
					<font class="mobile-only">&nbsp;- Mobile</font>
				</div>
			  </li>
			  <li class="active-option-bg list-group-item">
				<div class="client-edit-opts">
					<span style="min-width:20%;display:inline">
						<i class="fa fa-user-circle" aria-hidden="true"></i>&nbsp;{{actionText}}
					</span>
					<span v-if="option !== 'client-new' &amp;&amp; !isEditing" style="float:right"> 
						<i @click.prevent=" $parent.option = 'clientNew' " class="cursor fa fa-plus" aria-hidden="true" title="Add New Client"></i>
					</span>
				</div>
			  </li>
			  <li class="list-group-item" v-if=" $parent.clientSearch === true &amp;&amp; option === 'client-search' &amp;&amp; $parent.option === 'clientSearch'">
					<client-search v-bind:model="clientSearchComponent.model">
					</client-search>
			  </li>
			  <li 
			  v-if=" !isLoadingClients &amp;&amp; (option === 'client-main' &amp;&amp; $parent.option === 'clientList') || ($parent.option === 'clientSearch' &amp;&amp; option === 'client-search' )  " @click.prevent="openClient(client)" class="list-group-item cursor" 
			  v-for="client in clients" title="Click to Edit">
					<div class="container" style="width:100%;padding:0;margin:0">
					  <div class="row no-gutters">
						<div class="col-md-4 col-lg-4 col-sm-12">
						  <label class="text-primary">Client:</label> {{client.FirstName}} {{client.LastName}}
						</div>
						<div class="col-md-4 col-lg-4 col-sm-12">
						  <label class="text-primary">Phone:</label> {{client.Phone}}
						</div>
						<div class="col-md-4 col-lg-4 col-sm-12">
						  <label class="text-primary">Email:</label> {{client.Email}}
						</div>
					  </div>
					</div>
			  </li>
			  <li class="list-group-item" v-if="(!isLoadingClients &amp;&amp; clients.length === 0 &amp;&amp; noSearchResults) &amp;&amp; 
($parent.option === 'clientList' || $parent.option === 'clientSearch')">
				<div class="">
					No records found
				</div>
			  </li>
			  <li class="list-group-item" v-if= "!isLoadingClients &amp;&amp; ($parent.option === 'clientList' &amp;&amp; option === 'client-main' || $parent.option === 'clientSearch' &amp;&amp; option === 'client-search') &amp;&amp; clients.length > 0 &amp;&amp; !isEditing " >
				<div class="container" style="width:100%;padding:0;margin:0">
					<page-waiter v-bind:model="pageWaiter.model"></page-waiter>
				</div>
			  </li>
			  <li class="list-group-item" v-if="isLoadingClients">
				<div>Loading&nbsp;<i class="fa fa-spinner fa-spin" aria-hidden="true"></i></div>
			  </li>
			  <li  v-if=" option === 'client-edit' || $parent.option === 'clientNew' " class="list-group-item" > 
				<div class="container" style="width:100%;position:relative;padding:0;margin:0">		
					<ul class="list-group" style="width:100%;position:relative">
						<li class="list-group-item">
							<div class="client-edit-opts" style="width:100%;position:relative">
								<span v-if=" !isEditing &amp;&amp; showEditControls" title="Edit" class="cursor nowrap" @click.prevent=" isEditing = true ">
									<i class="cursor edit fa fa-pencil-square-o" aria-hidden="true"></i>&nbsp;Edit
								</span>
								<span v-if=" (option === 'client-new' &amp;&amp; sub_option === 'client-edit-info') || (isEditing &amp;&amp; showEditControls) " title="Save" class="cursor nowrap" @click.prevent="save">
									<i class="cursor save fa fa-check" aria-hidden="true"></i>&nbsp;Save
								</span>
								<font v-if=" (option === 'client-new' &amp;&amp; sub_option === 'client-edit-info') || (isEditing &amp;&amp; showEditControls) ">&nbsp;|&nbsp;</font>
								<span v-if=" (option === 'client-new' &amp;&amp; sub_option === 'client-edit-info') || (isEditing  &amp;&amp; showEditControls) " title="Cancel" class="cursor nowrap" @click.prevent="cancel">
									<i class="cursor cancel fa fa-times" aria-hidden="true"></i>&nbsp;Cancel
								</span>
								
								<font v-if=" ($parent.option === 'clientSearch' &amp;&amp; $parent.clientSearch === true) &amp;&amp; (!isEditing &amp;&amp; option === 'client-edit' &amp;&amp; sub_option === 'client-edit-info') ">&nbsp;|&nbsp;</font>
								<span v-if=" ($parent.option === 'clientSearch' &amp;&amp; $parent.clientSearch === true) &amp;&amp; (!isEditing &amp;&amp; option === 'client-edit' &amp;&amp; sub_option === 'client-edit-info') " title="Return to Search" class="cursor nowrap" @click.prevent=" option = 'client-search' ">
									<i class="cursor cancel fa fa-search" aria-hidden="true"></i>&nbsp;Return to Search
								</span>
								
								<label class="edit" v-if=" option === 'client-edit' &amp;&amp; (sub_option === 'client-edit-visits' || sub_option === 'client-edit-notes') ">Client:&nbsp;</label>
								<font v-if=" option === 'client-edit' &amp;&amp; (sub_option === 'client-edit-visits' || sub_option === 'client-edit-notes') ">{{client.FirstName}}&nbsp;{{client.LastName}}</font>
								
								<font v-if=" ($parent.option === 'clientList') &amp;&amp; (!isEditing &amp;&amp; option === 'client-edit' &amp;&amp; sub_option === 'client-edit-info') ">&nbsp;|&nbsp;</font>
								<span v-if=" ($parent.option === 'clientList') &amp;&amp; (!isEditing &amp;&amp; option === 'client-edit' &amp;&amp; sub_option === 'client-edit-info') " title="Return to List" class="cursor nowrap" @click.prevent=" option = 'client-main' ">
									<i class="cursor cancel fa fa-list" aria-hidden="true"></i>&nbsp;Return to List
								</span>
								
								<span style="float:right" v-if=" option === 'client-edit' &amp;&amp; !isEditing" class="cursor nowrap">
									<i title="Client Info" @click.prevent=" sub_option = 'client-edit-info' " class="cursor fa fa-info-circle" aria-hidden="true" v-bind:class=" { 'text-primary': sub_option === 'client-edit-info' } "></i>&nbsp;
									<i title="Client Visit Info" @click.prevent=" sub_option = 'client-edit-visits' " class="cursor fa fa-calendar" aria-hidden="true" v-bind:class=" { 'text-primary': sub_option === 'client-edit-visits' } "></i>&nbsp;
									<i title="Client Notes" @click.prevent=" sub_option = 'client-edit-notes' " class="cursor fa fa-sticky-note" aria-hidden="true" v-bind:class=" { 'text-primary': sub_option === 'client-edit-notes' } "></i>
								</span>
							</div>
						</li>
						<li class="list-group-item" v-if=" sub_option === 'client-edit-info' ">
							<label class="text-primary">First name:</label>
							<br/><input v-model="client.FirstName" class="form-control" v-bind:readonly="!isEditing &amp;&amp; option !== 'client-new' ">
							<label class="text-primary top-space">Last name:</label>
							<br/><input v-model="client.LastName" class="form-control" v-bind:readonly="!isEditing &amp;&amp; option !== 'client-new' ">
							<label class="text-primary top-space">Phone:</label>
							<br/><input v-model="client.Phone" class="form-control" v-bind:readonly="!isEditing &amp;&amp; option !== 'client-new' ">
							<label class="text-primary top-space">Email:</label>
							<br/><input v-model="client.Email" class="form-control" v-bind:readonly="!isEditing &amp;&amp; option !== 'client-new' ">
							<label class="text-primary top-space">Address:</label>
							<br/><input v-model="client.Address" class="form-control" v-bind:readonly="!isEditing &amp;&amp; option !== 'client-new' "><p></p>
							<label class="text-primary top-space">City:</label>
							<br/><input v-model="client.City" class="form-control" v-bind:readonly="!isEditing &amp;&amp; option !== 'client-new' ">
							<label class="text-primary top-space">State:</label>
							<br/><input v-model="client.State" class="form-control" v-bind:readonly="!isEditing &amp;&amp; option !== 'client-new' ">
							<label class="text-primary top-space">Zip code:</label>
							<br/><input v-model="client.ZipCode" class="form-control" v-bind:readonly="!isEditing &amp;&amp; option !== 'client-new' "><br/><br/>
							<div style="width:100%">
								<div v-if=" option !== 'client-new' " class="checkbox abc-checkbox">
									<input v-model="client.Inactive" id="inactive" type="checkbox" v-bind:disabled="!isEditing &amp;&amp; option !== 'client-new' "> 
									<label class="text-primary top-space" for="inactive">Is this client inactive?</label>
									<p></p>
								</div>							
							</div>
						</li>
						<li class="list-group-item" v-if=" sub_option === 'client-edit-visits' ">
							<div class="container" style="width:100%;padding:0;margin:0">
								<label class="edit"><i class="fa fa-forward" aria-hidden="true"></i>&nbsp;Next Visit</label> <label v-if="nextVisit.isOverdue &amp;&amp; !isLoadingNextVisit" class="text-danger">&nbsp;&nbsp;
										<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
										This visit is overdue</label><p></p>
									<div v-if="nextVisit.id &amp;&amp; !isLoadingNextVisit" class="container" style="width:100%;padding:0;margin:0">
									  <div class="row no-gutters">
										<div class="col-md-4 col-lg-4 col-sm-12">
										  <label class="text-primary">Date:</label> {{nextVisit.date}}
										</div>
										<div class="col-md-4 col-lg-4 col-sm-12">
										  <label class="text-primary">Visit type:</label> {{nextVisit.type}}
										</div>
										<div class="col-md-4 col-lg-4 col-sm-12">
										  <label class="text-primary">Visitor:</label> {{nextVisit.user}}
										</div>
									  </div>
									</div>
									<div v-if="isLoadingNextVisit">Loading&nbsp;<i class="fa fa-spinner fa-spin" aria-hidden="true"></i></div>
									<h6 v-if="!nextVisit.id &amp;&amp; !isLoadingNextVisit">Client has no pending visits</h6>
							</div>
						</li>
						<li class="list-group-item" v-if=" sub_option === 'client-edit-visits' ">
							<div class="container" style="width:100%;padding:0;margin:0">
								<label class="edit"><i class="fa fa-backward" aria-hidden="true"></i>&nbsp;Last Visit</label>
								<label v-if="lastVisit.followUp === '1' &amp;&amp; !isLoadingLastVisit" class="text-info">&nbsp;&nbsp;
								<i class="fa fa-info-circle" aria-hidden="true"></i>
								A follow-up visit is required</label><p></p>
								<div  v-if="lastVisit.id &amp;&amp; !isLoadingLastVisit" class="container" style="width:100%;padding:0;margin:0">
								  <div class="row no-gutters">
									<div class="col-md-3 col-lg-4 col-sm-12">
									  <label class="text-primary">Date:</label> {{lastVisit.date}}
									</div>
									<div class="col-md-2 col-lg-4 col-sm-12">
									  <label class="text-primary">Visit type:</label> {{lastVisit.type}}
									</div>
									<div class="col-md-7 col-lg-4 col-sm-12">
									  <label class="text-primary">Visitor:</label> {{lastVisit.user}}
									</div>
								  </div>
								</div>
								<div v-if="isLoadingLastVisit">Loading&nbsp;<i class="fa fa-spinner fa-spin" aria-hidden="true"></i></div>
								<h6 v-if="!lastVisit.id &amp;&amp; !isLoadingLastVisit">Client has never been visited</h6>
							</div>
						</li>
						<li class="list-group-item" v-if=" sub_option === 'client-edit-visits' ">
							<div class="container" style="width:100%;padding:0;margin:0">
								<label class="edit"><i class="fa fa-bar-chart" aria-hidden="true"></i>&nbsp;Visit Statistics</label><p></p>
								<div class="container" style="width:100%;padding:0;margin:0" v-if="!isLoadingVisitStats">
								  <div class="row no-gutters">
									<div class="col-md-4 col-lg-4 col-sm-12">
									  <label class="text-primary">Completed:</label> {{visitStats.complete}}
									</div>
									<div class="col-md-4 col-lg-4 col-sm-12">
									  <label class="text-primary">Pending:</label> {{visitStats.pending}}
									</div>
									<div class="col-md-4 col-lg-4 col-sm-12">
									  <label class="text-primary">Overdue:</label> {{visitStats.overdue}}
									</div>
								  </div>
								</div>
								<div v-if="isLoadingVisitStats">Loading&nbsp;<i class="fa fa-spinner fa-spin" aria-hidden="true"></i></div>
							</div>
						</li>
						<li class="list-group-item" v-if=" sub_option === 'client-edit-visits' ">
							<div>
								<label class="edit"><i class="fa fa-list" aria-hidden="true"></i>&nbsp;All Visits</label><p></p>
								<div>TODO</div>
							</div>
						</li>
						<li class="list-group-item cursor" v-for="note in notes" v-if="!isLoadingNotes &amp;&amp; sub_option === 'client-edit-notes' " @click.prevent="openClientNote(note)">
							<div class="notes">
								<label v-bind:class="{'added-by-current-user': $parent.loggedInUser.id === note.UserId, 'not-added-by-current-user': $parent.loggedInUser.id !== note.UserId}">Added {{note.Date}} by {{note.User}}</label>
								<input v-if="isEditingClientNote &amp;&amp; thisNoteIsBeingEdited(note) &amp;&amp; !confirmNoteDeletion" v-model="note.Details" class="form-control">
								<p v-else>{{note.Details}}</p>
								<span v-if="!confirmNoteDeletion">
									<i v-if="isEditingClientNote &amp;&amp; thisNoteIsBeingEdited(note)" title="Save" class="cursor save fa fa-check" aria-hidden="true" @click.prevent="saveNote(note)"></i>
									<i v-if="isEditingClientNote &amp;&amp; thisNoteIsBeingEdited(note)" title="Cancel" class="cursor cancel fa fa-times" @click.prevent="cancel" aria-hidden="true"></i>
									<i v-if="isEditingClientNote &amp;&amp; thisNoteIsBeingEdited(note)" title="Delete" class="cursor delete fa fa-trash" aria-hidden="true" @click.prevent="confirmNoteDeletion = true"></i>
								</span>								
							</div>
							<div v-if="confirmNoteDeletion &amp;&amp; isEditingClientNote &amp;&amp; thisNoteIsBeingEdited(note)">
								<label>
									<i class="delete fa fa-trash" aria-hidden="true"></i>
									&nbsp;Are you sure you want to delete this note? Deleting this note will permanently remove it from the system.
								</label>
								<br/> 
								<span @click.prevent="deleteNote(note)" class="cursor">
									<i class="save fa fa-check" aria-hidden="true"></i>
									&nbsp;Yes
								</span>&nbsp; 
								<span @click.prevent="confirmNoteDeletion = false" class="cursor">
									<i class="cancel fa fa-times" aria-hidden="true"></i>
									&nbsp;No
								</span>
							</div>
						</li>
						<li class="list-group-item" v-if="!isLoadingNotes &amp;&amp; !isEditingClientNote &amp;&amp; sub_option === 'client-edit-notes' ">
							<div class="notes">
							<input v-model="note.Details" placeholder="enter a note" class="form-control">
							<span>
								<i title="Save" v-bind:class="{save: hasNoText(note.Details), disabled: !hasNoText(note.Details)}" class="cursor fa fa-check" aria-hidden="true" @click.prevent="saveNote"></i>
								<i title="Cancel" v-bind:class="{disabled: hasNoText(note.Details)}" class="cursor cancel fa fa-times" @click.prevent="cancel" aria-hidden="true"></i>
							</span>	
							</div>
						</li>
					  <li class="list-group-item" v-if="isLoadingNotes">
						<div>Loading&nbsp;<i class="fa fa-spinner fa-spin" aria-hidden="true"></i></div>
					  </li>
					</ul>	
				</div>
			  </li>
			</ul>	
			<br/>	
        </div>
      </script>
	  
	<script type="text/x-template" id="visitList">       
        <div class="container" style="width:98vw">
			<ul class="list-group" style="width:95%;position:relative">			  
			  <li class="list-group-item active" @click.prevent=" $parent.option = 'menu' ">
				<div>
					<i class="cursor fa fa-bars" aria-hidden="true" title="Menu"></i>
					&nbsp;Member Care Ministries 
					<font class="mobile-only">&nbsp;- Mobile</font>
				</div>
			  </li>  			  
			  <li class="active-option-bg list-group-item">
				<div class="client-edit-opts" style="width:100%;position:relative">
					<span>
						<i class="fa" v-bind:class="{'fa-calendar': $parent.option !== 'visitCalendar', 'fa-calendar-check-o': $parent.option === 'visitCalendar'}" aria-hidden="true"></i>&nbsp;{{actionText}}	
					</span>					
					<span style="float:right" v-if="option === 'visit-calendar' ">
						<i title="Show My Visits" class="cursor fa fa-user" aria-hidden="true" v-bind:class="{edit: sub_option === 'visit-calendar-user'}" @click.prevent="getWeeklyVisits('visit-calendar-user')"></i>
						<i title="Show All Visits" class="cursor fa fa-users" aria-hidden="true" v-bind:class="{edit: sub_option === 'visit-calendar-users'}" @click.prevent="getWeeklyVisits('visit-calendar-users')"></i>	
					</span>
					<span v-if="option === 'visit-main'" style="float:right">
						<i @click.prevent=" $parent.option = 'visitNew' " class="cursor fa fa-search" aria-hidden="true" title="Add New Visit"></i>
					</span>
					<font style="float:right">&nbsp;</font>
					<span v-if="option === 'visit-main'" style="float:right">
						<i @click.prevent=" $parent.option = 'visitNew' " class="cursor fa fa-plus" aria-hidden="true" title="Add New Visit"></i>
					</span>
				</div>
			  </li>			  
			  <li class="list-group-item" v-if=" $parent.visitSearch === true &amp;&amp; option === 'visit-search' &amp;&amp; $parent.option === 'visitSearch' &amp;&amp; option !== 'visit-edit' &amp;&amp; option !== 'visit-new' ">			
					<client-search v-bind:model="visitSearchComponent.model">
					</client-search>
			  </li>			  
			  <li title="Click to Edit" class="cursor list-group-item" v-if="!isLoadingVisits &amp;&amp; (option === 'visit-calendar' || (option === 'visit-main' &amp;&amp; $parent.option === 'visitList') || ($parent.option === 'visitSearch' &amp;&amp; option === 'visit-search') || ($parent.option === 'visitSearch' &amp;&amp; option === 'visit-main')) " @click.prevent="openVisit(visit)" v-for="visit in visits">
				<div class="container"  style="width:100%;padding:0;margin:0">
				  <div class="row no-gutters">
					  <div class="col-md-6 col-lg-6 col-sm-6 text-danger" v-if="visit.Overdue">
						<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>This visit is overdue										
					  </div>
					  <div class="col-md-6 col-lg-6 col-sm-6 text-info" v-if="visit.FollowUpRequired">
						<i class="fa fa-info-circle" aria-hidden="true"></i>A follow-up visit is required										
					  </div>
				  </div>
				  <div class="row no-gutters">
					<div class="col-md-6 col-lg-6 col-sm-6">
					  <label class="text-primary">Client:</label> {{visit.Client}}
					</div>
					<div class="col-md-6 col-lg-6 col-sm-6">
					  <label class="text-primary">Date/Time:</label> {{visit.Date}} {{visit.Time}}
					</div>
				  </div>
				  <div class="row no-gutters">
					<div class="col-md-6 col-lg-6 col-sm-6">
					  <label class="text-primary">Visitor:</label> {{visit.Visitor}}
					</div>
					<div class="col-md-6 col-lg-6 col-sm-6">
					  <label class="text-primary">Type:</label> {{visit.Type}}
					</div>
				  </div>
				</div>
			  </li>
			  <li class="list-group-item" v-if=" (!isLoadingVisits  &amp;&amp; visits.length === 0) &amp;&amp; (($parent.option === 'visitList' || $parent.option === 'visitSearch' || $parent.option === 'visitCalendar') &amp;&amp; noSearchResults) ">
				<div class="">
					No records found
				</div>
			  </li>
			  <li class="list-group-item" v-if= "!isLoadingVisits &amp;&amp; ($parent.option === 'visitList' &amp;&amp; option === 'visit-main' || $parent.option === 'visitSearch' &amp;&amp; option === 'visit-search') &amp;&amp; visits.length > 0 &amp;&amp; !isEditing " >
	            <div class="container">
					<page-waiter v-bind:model="pageWaiter.model"></page-waiter>
				</div>
			  </li>
			  <li class="list-group-item" v-if="isLoadingVisits">
				<div>Loading&nbsp;<i class="fa fa-spinner fa-spin" aria-hidden="true"></i></div>
			  </li>
			  <li  v-if=" option === 'visit-edit' || $parent.option === 'visitNew' &amp;&amp; $parent.option !== 'visitCalendar' " class="list-group-item" > 
				<div class="container"  style="width:100%;padding:0;margin:0">			 
					<ul class="list-group">
						<li class="list-group-item">
							<div class="client-edit-opts">
								<span v-if=" !isEditing &amp;&amp; showEditControls" title="Edit" class="cursor nowrap" @click.prevent=" isEditing = true ">
									<i class="cursor edit fa fa-pencil-square-o" aria-hidden="true"></i>&nbsp;Edit
								</span>
								<span v-if=" (option === 'visit-new' &amp;&amp; sub_option === 'visit-edit-info') || (isEditing &amp;&amp; showEditControls) &amp;&amp; !confirmCancelation " title="Save" class="cursor nowrap" @click.prevent="save">
									<i class="cursor save fa fa-check" aria-hidden="true"></i>&nbsp;Save
								</span>
								<font v-if=" (option === 'visit-new' &amp;&amp; sub_option === 'visit-edit-info') || (isEditing &amp;&amp; showEditControls) &amp;&amp; !confirmCancelation ">&nbsp;|&nbsp;</font>
								<span v-if=" (option === 'visit-new' &amp;&amp; sub_option === 'visit-edit-info') || (isEditing  &amp;&amp; showEditControls) &amp;&amp; !confirmCancelation " title="Cancel" class="cursor nowrap" @click.prevent="cancel">
									<i class="cursor cancel fa fa-times" aria-hidden="true"></i>&nbsp;Cancel
								</span>
								<font v-if="isChangingClients">&nbsp;|&nbsp;</font>
								<span v-if="isChangingClients" title="Return to Visit Profile" class="cursor nowrap" @click.prevent="cancel">
									<i class="cursor delete fa fa-arrow-left" aria-hidden="true"></i>&nbsp;Back
								</span>
								<font v-if=" ($parent.option === 'visitSearch' &amp;&amp; $parent.visitSearch === true || $parent.option === 'visitCalendar') &amp;&amp; (!isEditing &amp;&amp; option === 'visit-edit' &amp;&amp; sub_option === 'visit-edit-info') ">&nbsp;|&nbsp;</font>
								<span v-if=" ($parent.option === 'visitSearch' &amp;&amp; $parent.visitSearch === true) &amp;&amp; (!isEditing &amp;&amp; option === 'visit-edit' &amp;&amp; sub_option === 'visit-edit-info') " title="Return to Search" class="cursor nowrap" @click.prevent=" option = 'visit-search' ">
									<i class="cursor cancel fa fa-search" aria-hidden="true"></i>&nbsp;Return to Search
								</span>
								<span v-if=" ($parent.option === 'visitCalendar') &amp;&amp; (!isEditing &amp;&amp; option === 'visit-edit' &amp;&amp; sub_option === 'visit-edit-info') " title="Return to Calendar" class="cursor nowrap" @click.prevent=" option = 'visit-calendar' ">
									<i class="cursor cancel fa fa-calendar" aria-hidden="true"></i>&nbsp;Return to Calendar
								</span>
								<label class="edit" v-if=" option === 'visit-edit' &amp;&amp; (sub_option === 'visit-edit-visits' || sub_option === 'visit-edit-notes') ">Client:&nbsp;</label>
								<font v-if=" option === 'visit-edit' &amp;&amp; (sub_option === 'visit-edit-visits' || sub_option === 'visit-edit-notes') ">{{visit.Client}}</font>
								
								<font v-if=" ($parent.option === 'visitList') &amp;&amp; (!isEditing &amp;&amp; option === 'visit-edit' &amp;&amp; sub_option === 'visit-edit-info') ">&nbsp;|&nbsp;</font>
								<span v-if=" ($parent.option === 'visitList') &amp;&amp; (!isEditing &amp;&amp; option === 'visit-edit' &amp;&amp; sub_option === 'visit-edit-info') " title="Return to List" class="cursor nowrap" @click.prevent=" option = 'visit-main' ">
									<i class="cursor cancel fa fa-list" aria-hidden="true"></i>&nbsp;Return to List
								</span>
								<span style="float:right" v-if=" option === 'visit-edit' &amp;&amp; !confirmCancelation " class="cursor nowrap">
									<i title="Cancel Visit" v-if="isEditing &amp;&amp; sub_option === 'visit-edit-info' &amp;&amp; !isChangingClients" @click.prevent=" confirmCancelation = true " class="cursor fa fa-ban text-danger" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;
									<i title="Visit Info" @click.prevent=" sub_option = 'visit-edit-info' " class="cursor fa fa-info-circle" aria-hidden="true" v-bind:class=" { 'text-primary': sub_option === 'visit-edit-info' } "></i>&nbsp;
									<i title="Client Info" @click.prevent=" sub_option = 'visit-edit-client' " class="cursor fa fa-user-circle" aria-hidden="true" v-bind:class=" { 'text-primary': sub_option === 'visit-edit-client' } "></i>&nbsp;
									<i title="Visit Notes" @click.prevent=" sub_option = 'visit-edit-notes' " class="cursor fa fa-sticky-note" aria-hidden="true" v-bind:class=" { 'text-primary': sub_option === 'visit-edit-notes' } "></i>
								</span>
							</div>
							<div v-if=" confirmCancelation ">
								<label>
									<i class="text-danger fa fa-ban" aria-hidden="true"></i>
									&nbsp;Are you sure you want to cancel this visit? Canceling this visit will permanently remove the visit, and any notes related to the visit, from the system. Notes related to the client will not be affected. If this is not your intention, you can choose to mark the visit as Completed instead.
								</label>
								<br/> 
								<span @click.prevent="cancelVisit" class="cursor">
									<i class="save fa fa-check" aria-hidden="true"></i>
									&nbsp;Yes
								</span>&nbsp; 
								<span @click.prevent="confirmCancelation = false" class="cursor">
									<i class="cancel fa fa-times" aria-hidden="true"></i>
									&nbsp;No
								</span>
							</div>
						</li>
						<li class="list-group-item" v-if=" sub_option === 'visit-edit-info' ">
							<div class="container"  style="width:100%;padding:0;margin:0">
								<div>
									<label class="text-primary">Client:</label>
									<i v-if="visit.Client.length > 0 &amp;&amp; isEditing &amp;&amp; !isChangingClients">click to change client</i><br/>
									<client-search v-bind:model="clientSelectComponent.model" v-if="isChangingClients"></client-search>
									<input v-bind:disabled="!isEditing &amp;&amp; option !== 'visit-new' || confirmCancelation " placeholder="click to search clients" v-if="!isChangingClients" @click.prevent="isChangingClients = true" v-model="visit.Client" class="cursor form-control" readonly><p></p>
								</div>
								<div v-if="!isChangingClients" >
									<label class="text-primary">Date:</label>
									<i v-if="visit.Date.length > 0 &amp;&amp; isEditing">click to change date</i><br/>
									<input placeholder="click to set date" id="date-picker" v-model="visit.Date" class="cursor form-control" v-bind:disabled="!isEditing &amp;&amp; option !== 'visit-new' || confirmCancelation " readonly><p></p> 
								</div>
								<div v-if="!isChangingClients" class="container row">
									<label class="text-primary" style="width:100%">Time:</label>
									<div class="col-sm-4 col-md-4 col-lg-4 time-col">
										<select class="form-control date-time-picker clear-left" v-model="visit.TimeHour" v-bind:disabled="!isEditing &amp;&amp; option !== 'visit-new' || confirmCancelation ">
											<option v-bind:value="hour" v-for="hour in timeHours">{{hour}}</option>
										</select>
									</div>
									<div class="col-sm-4 col-md-4 col-lg-4 time-col">
										<select class="form-control date-time-picker" v-model="visit.TimeMinutes" v-bind:disabled="!isEditing &amp;&amp; option !== 'visit-new' || confirmCancelation ">
											<option v-bind:value="minute" v-for="minute in timeMinutes">{{minute}}</option>
										</select>
									</div>
									<div class="col-sm-4 col-md-4 col-lg-4 time-col">
										<select class="form-control date-time-picker" v-model="visit.TimeOfDay" v-bind:disabled="!isEditing &amp;&amp; option !== 'visit-new' || confirmCancelation ">
											<option value="AM">AM</option>
											<option value="PM">PM</option>
										</select>
									</div>
								</div>
								<div v-if="!isChangingClients" >
									<p></p>
									<label class="text-primary" style="width:100%" v-bind:disabled="!isEditing &amp;&amp; option !== 'visit-new' || confirmCancelation ">Type:</label>
									<label v-if="isLoadingTypes">Loading <i class="fa fa-spinner fa-spin"></i></label>
									<select v-else class="form-control" v-model="visit.Type_Id" v-bind:disabled="!isEditing &amp;&amp; option !== 'visit-new' || confirmCancelation ">
										<option v-for="type in types" v-bind:value="type.Id">{{type.Type}}</option>
									</select>
									<p></p>
								</div>
								<div v-if="!isChangingClients" >
									<label class="text-primary" style="width:100%">Visitor:</label>
									<div v-if="isLoadingVisitors">Loading <i class="fa fa-spinner fa-spin"></i></div>
									<select v-else class="form-control" v-model="visit.Visitor_Id" v-bind:disabled="!isEditing &amp;&amp; option !== 'visit-new' || confirmCancelation ">
										<option v-for="visitor in visitors" v-bind:value="visitor.Id">{{visitor.FirstName}} {{visitor.LastName}}</option>
									</select>
								</div>
								<div style="width:100%">								
									<div v-if="!isChangingClients &amp;&amp; option !== 'visit-new' " class="checkbox abc-checkbox">
										<p></p>
										<input v-model="visit.Completed" id="complete" type="checkbox" v-bind:disabled="!isEditing &amp;&amp; option !== 'visit-new' || confirmCancelation "> 
										<label class="text-primary top-space" for="complete">Is this visit complete?</label>
										<br/>
										<input v-model="visit.FollowUpRequired" id="followup" type="checkbox" v-bind:disabled="!isEditing &amp;&amp; option !== 'visit-new' || confirmCancelation "> 
										<label class="text-primary top-space" for="followup">Is a follow-up visit required?</label>
										<p></p>
									</div>
								</div>
							</div>
						</li>
						<li class="list-group-item" v-if=" sub_option === 'visit-edit-client' ">
							<div class="container">
							<div v-if="isLoadingVisitClient">Loading&nbsp;<i class="fa fa-spinner fa-spin" aria-hidden="true"></i></div>
								<table class="table table-bordered" v-if="!isLoadingVisitClient">
									<tbody>
										<tr><td colspan="3"><h3>
										<i class="fa fa-user-o" aria-hidden="true"></i>&nbsp;{{visit.Client}}</h3></td></tr>
										<tr>
											<td>
												<div><b>Email:</b>&nbsp;
													<font v-if="client.Email.length > 0">{{client.Email}}</font>
													<i v-if="client.Email.length === 0">No saved email</i>
												</div>
												<div><b>Phone:</b>&nbsp;
													<font v-if="client.Phone.length > 0">{{client.Phone}}</font>
													<i v-if="client.Phone.length === 0">No saved phone</i>
												</div>
												<div><b>Address:</b>&nbsp;
													<font v-if="client.Address.length > 0">{{client.Address}}
															 &nbsp;{{client.City}}
															 <font v-if="client.City.length > 0 &amp;&amp; client.State.length > 0">,</font>
															 &nbsp;{{client.State}}
															 &nbsp;{{client.ZipCode}}</font>
													<i v-if="client.Address.length === 0">No saved address</i>
												</div>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</li>
						<li class="list-group-item cursor" v-for="note in notes" v-if="!isLoadingNotes &amp;&amp; sub_option === 'visit-edit-notes' " @click.prevent="openVisitNote(note)">
							<div class="notes">
								<label v-bind:class="{'added-by-current-user': $parent.loggedInUser.id === note.UserId, 'not-added-by-current-user': $parent.loggedInUser.id !== note.UserId}">Added {{note.Date}} by {{note.User}}</label>
								<input v-if="isEditingVisitNote &amp;&amp; thisNoteIsBeingEdited(note) &amp;&amp; !confirmNoteDeletion" v-model="note.Details" class="form-control">
								<p v-else>{{note.Details}}</p>
								<span v-if="!confirmNoteDeletion">
									<i v-if="isEditingVisitNote &amp;&amp; thisNoteIsBeingEdited(note)" title="Save" class="cursor save fa fa-check" aria-hidden="true" @click.prevent="saveNote(note)"></i>
									<i v-if="isEditingVisitNote &amp;&amp; thisNoteIsBeingEdited(note)" title="Cancel" class="cursor cancel fa fa-times" @click.prevent="cancel" aria-hidden="true"></i>
									<i v-if="isEditingVisitNote &amp;&amp; thisNoteIsBeingEdited(note)" title="Delete" class="cursor delete fa fa-trash" aria-hidden="true" @click.prevent="confirmNoteDeletion = true"></i>
								</span>								
							</div>
							<div v-if="confirmNoteDeletion &amp;&amp; isEditingVisitNote &amp;&amp; thisNoteIsBeingEdited(note)">
								<label>
									<i class="delete fa fa-trash" aria-hidden="true"></i>
									&nbsp;Are you sure you want to delete this note? Deleting this note will permanently remove it from the system.
								</label>
								<br/> 
								<span @click.prevent="deleteNote(note)" class="cursor">
									<i class="save fa fa-check" aria-hidden="true"></i>
									&nbsp;Yes
								</span>&nbsp; 
								<span @click.prevent="confirmNoteDeletion = false" class="cursor">
									<i class="cancel fa fa-times" aria-hidden="true"></i>
									&nbsp;No
								</span>
							</div>
						</li>
						<li class="list-group-item" v-if="!isLoadingNotes &amp;&amp; !isEditingVisitNote &amp;&amp; sub_option === 'visit-edit-notes' ">
							<div class="notes">
							<input v-model="note.Details" placeholder="enter a note" class="form-control">
							<span>
								<i title="Save" class="cursor save fa fa-check" aria-hidden="true" @click.prevent="saveNote"></i>
								<i title="Cancel" class="cursor cancel fa fa-times" @click.prevent="cancel" aria-hidden="true"></i>
							</span>	
							</div>
						</li>
					  <li class="list-group-item" v-if="isLoadingNotes">
						<div>Loading&nbsp;<i class="fa fa-spinner fa-spin" aria-hidden="true"></i></div>
					  </li>
					</ul>						
				</div>
			  </li>
			</ul>	
			<br/>
        </div>
      </script>
	  
	  <script type="text/x-template" id="userList">       
        <div class="container" style="width:98vw">
			<ul class="list-group" style="width:95%;position:relative">
			  <li class="list-group-item active" @click.prevent=" $parent.option = 'menu' "><i class="cursor fa fa-bars" aria-hidden="true" title="Menu"></i>&nbsp;Member Care Ministries - Mobile</li>
			  <li class="active-option-bg list-group-item">
				<div class="client-edit-opts" style="width:95%;position:relative">
					<span style="min-width:20%;display:inline">
						<i class="fa fa-user" aria-hidden="true"></i>&nbsp;{{actionText}}
					</span>
					<span v-if="option === 'user-main' &amp;&amp; $root.loggedInUser.isAdmin === '1'" style="float:right">
						<i @click.prevent=" $parent.option = 'userNew' " class="cursor fa fa-plus" aria-hidden="true" title="Add New User"></i>
					</span>
				</div>
			  </li> 
			  <li class="list-group-item" v-if="isLoadingUsers">
				<div>Loading&nbsp;<i class="fa fa-spinner fa-spin" aria-hidden="true"></i></div>
			  </li>
			  <li 
			  v-if=" !isLoadingUsers &amp;&amp; option === 'user-main' &amp;&amp; $parent.option === 'userList' " @click.prevent="openUser(user)" class="cursor list-group-item" 
              v-for="user in users">
				<div class="container" style="width:100%;padding:0;margin:0">
					<div class="row no-gutters">
						<div class="col-md-4 col-lg-4 col-sm-12">
						  <label class="text-primary">User:</label> {{user.FirstName}} {{user.LastName}}
						</div>
						<div class="col-md-4 col-lg-4 col-sm-12">
						  <label class="text-primary">Phone:</label> {{user.Phone}}
						</div>
						<div class="col-md-4 col-lg-4 col-sm-12">
						  <label class="text-primary">Email:</label> {{user.Email}}
						</div>
					</div>
				</div>
			  </li>
			  <li  v-if=" option === 'user-edit' || $parent.option === 'userNew' || $parent.option === 'myProfile'" class="list-group-item" > 
				<div class="container"  style="width:100%;padding:0;margin:0">			 
					<ul class="list-group">
						<li class="list-group-item">
							<div class="client-edit-opts">
								<span v-if=" !isEditing &amp;&amp; option !== 'user-new' &amp;&amp; ($root.loggedInUser.isAdmin === '1' || $root.loggedInUser.id == user.Id.toString())" title="Edit" class="cursor nowrap" @click.prevent=" isEditing = true ">
									<i class="cursor edit fa fa-pencil-square-o" aria-hidden="true"></i>&nbsp;Edit
								</span>
								<span v-if=" (option === 'user-new' || isEditing) &amp;&amp; !attentionRequired " title="Save" class="cursor nowrap" @click.prevent="save">
									<i class="cursor save fa fa-check" aria-hidden="true"></i>&nbsp;Save
								</span>
								<font v-if=" (option === 'user-new' || isEditing) &amp;&amp; !attentionRequired ">&nbsp;|&nbsp;</font>
								<span v-if=" option === 'user-new' || isEditing " title="Cancel" class="cursor nowrap" @click.prevent="cancel">
									<i class="cursor cancel fa fa-times" aria-hidden="true"></i>&nbsp;Cancel
								</span>							
								<font v-if=" ($parent.option === 'userList') &amp;&amp; (!isEditing &amp;&amp; option === 'user-edit') &amp;&amp; $root.loggedInUser.isAdmin === '1'">&nbsp;|&nbsp;</font>
								<span v-if=" ($parent.option === 'userList') &amp;&amp; (!isEditing &amp;&amp; option === 'user-edit') " title="Return to List" class="cursor nowrap" @click.prevent=" option = 'user-main' ">
									<i class="cursor cancel fa fa-list" aria-hidden="true"></i>&nbsp;Return to List
								</span>
								<span style="float:right" v-if=" (option === 'user-edit' &amp;&amp; isEditing &amp;&amp; canResetPassword) &amp;&amp; !attentionRequired " class="cursor nowrap">
									<i title="Reset User Password" @click.prevent="confirmPasswordReset = true" class="cursor fa fa-key" aria-hidden="true" v-bind:class=" { 'text-primary': canResetPassword === true } "></i>
								</span>
								<span style="float:right" v-if=" $root.option === 'myProfile' &amp;&amp; !attentionRequired " class="cursor nowrap">
									<i title="Log Off" @click.prevent="confirmLogout = true" class="cursor fa fa-power-off" aria-hidden="true" v-bind:class=" { 'text-primary': canResetPassword === true } "></i>
								</span>
							</div>
						</li>
						<li class="list-group-item" v-if="confirmPasswordReset">
							<div>
							<i class="text-primary fa fa-key"></i>&nbsp;Are you sure you want to reset {{user.FirstName}} {{user.LastName}}'s password?<br/>
								<span @click.prevent="resetUserPassword" class="cursor">
									<i class="save fa fa-check" aria-hidden="true"></i>
									&nbsp;Yes
								</span>&nbsp; 
								<span @click.prevent="confirmPasswordReset = false" class="cursor">
									<i class="cancel fa fa-times" aria-hidden="true"></i>
									&nbsp;No
								</span>
							</div>
						</li>
						<li class="list-group-item" v-if="notifyOfPasswordReset">
							<div>
							<i class="text-primary fa fa-key"></i>&nbsp;Done! We've sent an email to notify {{user.FirstName}} {{user.LastName}}.
							<button type="button" class="btn btn-primary btn-sm" @click.prevent="notifyOfPasswordReset = false">OK</button
							</div>
						</li>
						<li class="list-group-item" v-if="notifyOfDuplicateEmail">
							<div>
							<i class="text-primary fa fa-exclamation"></i>&nbsp;This email address has been registered to another user: {{user.Email}}
							<button type="button" class="btn btn-primary btn-sm" @click.prevent="notifyOfDuplicateEmail = false">OK</button
							</div>
						</li>
						<li class="list-group-item" v-if="notifyOfInvalidEmailAddress">
							<div>
							<i class="text-primary fa fa-exclamation"></i>&nbsp;Enter a valid email address to continue.							
							</div>
						</li>
						<li class="list-group-item" v-if="notifyOfInvalidFirstName">
							<div>
							<i class="text-primary fa fa-exclamation"></i>&nbsp;Enter the user's first name to continue.
							</div>
						</li>
						<li class="list-group-item" v-if="notifyOfInvalidLastName">
							<div>
							<i class="text-primary fa fa-exclamation"></i>&nbsp;Enter the user's last name to continue.
							</div>
						</li>
						<li class="list-group-item" v-if="confirmLogout">
							<div>
							<i class="text-primary fa fa-power-off"></i>&nbsp;Are you sure you want to log off?<br/>
								<span @click.prevent="$root.logOut" class="cursor">
									<i class="save fa fa-check" aria-hidden="true"></i>
									&nbsp;Yes
								</span>&nbsp; 
								<span @click.prevent="confirmLogout = false" class="cursor">
									<i class="cancel fa fa-times" aria-hidden="true"></i>
									&nbsp;No
								</span>
							</div>
						</li>
						<li class="list-group-item" v-if=" option === 'user-edit' || option === 'user-new' ">
							<label class="text-primary">First Name:</label>
							<br/><input @input="notifyOfInvalidFirstName = false" v-model="user.FirstName" class="form-control" v-bind:readonly="!isEditing &amp;&amp; option !== 'user-new' "><p></p>
							<label class="text-primary">Last Name:</label>
							<br/><input @input="notifyOfInvalidLastName = false" v-model="user.LastName" class="form-control" v-bind:readonly="!isEditing &amp;&amp; option !== 'user-new' "><p></p> 
							<label class="text-primary">Phone:</label>
							<br/><input v-model="user.Phone" class="form-control" v-bind:readonly="!isEditing &amp;&amp; option !== 'user-new' "><p></p>
							<label class="text-primary">Email:</label>
							<br/><input @input="notifyOfInvalidEmailAddress = false" v-model="user.Email" class="form-control" v-bind:readonly="!isEditing &amp;&amp; option !== 'user-new' ">
							<br/><br/>
							<div style="width:100%">
								<div class="checkbox abc-checkbox">
									<input v-model="user.IsAdmin" id="isAdmin" type="checkbox" v-bind:disabled="!isEditing &amp;&amp; option !== 'user-new' "> 
									<label class="text-primary top-space" for="isAdmin">Is this user an Administrator?</label>
								</div>								
								<div v-if=" option !== 'user-new' " class="checkbox abc-checkbox">
									<input v-model="user.Active" id="Active" type="checkbox" v-bind:disabled="!isEditing &amp;&amp; option !== 'user-new' "> 
									<label class="text-primary top-space" for="Active">Is this user Active?</label>
								</div>	
							</div>
						</li>
					</ul>	
				</div>
			  </li>
			</ul>	
			<br/>
        </div>
      </script>  

		<p></p>
		<div class="container" id="app" v-cloak style="width:98vw"> 
			<div class="container" style="width:98vw" style="width:98vw">
				<div class="container" v-if="showError"  style="width:98vw">
					<div>
						<ul class="list-group">
							<li class="list-group-item active">Member Care Ministries - Mobile</li>
							<li class="list-group-item"><h3 class="center-text text-center">{{errorObj.errorTitle}}</h3></li>
							<li class="list-group-item">
								<div>
									<p>{{errorObj.errorContent}}</p>
									<button type="button" class="btn btn-primary btn-sm" @click.prevent="logOut">OK</button>
								</div>
							</li>
						</ul>
					</div>
				</div>
				<div v-else style="width:98vw">
					<client-list ref="clientSection" v-show=" option === 'clientList' || option === 'clientNew' || option === 'clientSearch' "></client-list>
					<visit-list ref="visitSection" v-show=" option === 'visitList' || option === 'visitNew' || option === 'visitSearch' || option === 'visitCalendar' "></visit-list>
					<user-list ref="userSection" v-show=" option === 'userList' || option === 'userNew' || option === 'myProfile' "></user-list>
				</div>
				<ul class="list-group" style="overflow-x:auto" v-if=" option === 'menu' ">
				  <li class="list-group-item active">Member Care Ministries - Mobile</li>
				  <li class="list-group-item active-option-bg"><i class="fa fa-user-circle" aria-hidden="true"></i>&nbsp;Clients</li>
				  <li class="cursor list-group-item" @click.prevent=" option = 'clientList' " title="Client List">&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-list" aria-hidden="true"></i>&nbsp;List</li>
				  <li class="cursor list-group-item" @click.prevent=" option = 'clientNew' " title="Add New Client">&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-plus" aria-hidden="true"></i>&nbsp;Add new client</li>
				  <li class="cursor list-group-item" @click.prevent=" clientSearch = true " title="Client Search">&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-search" aria-hidden="true"></i>&nbsp;Search</li>
				  <li class="list-group-item active-option-bg"><i class="fa fa-calendar" aria-hidden="true"></i>&nbsp;Visits</li>
				  <li class="cursor list-group-item" @click.prevent=" option = 'visitList' " title="Visit List">&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-list" aria-hidden="true"></i>&nbsp;List</li>
				  <li class="cursor list-group-item" @click.prevent=" option = 'visitNew' " title="Add New Visit">&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-plus" aria-hidden="true"></i>&nbsp;Add new visit</li>
				  <li class="cursor list-group-item" @click.prevent=" visitSearch = true "  title="Visit Search">&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-search" aria-hidden="true"></i>&nbsp;Search</li>
				  <li class="cursor list-group-item" @click.prevent=" option = 'visitCalendar' " title="Weekly Calendar">
				  &nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-calendar-check-o" aria-hidden="true"></i>&nbsp;Weekly calendar
				  </li>
				  <li class="list-group-item active-option-bg"><i class="fa fa-user-o" aria-hidden="true"></i>&nbsp;Users</li>
				  <li class="cursor list-group-item" @click.prevent=" option = 'userList' " title="User List">&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-list" aria-hidden="true"></i>&nbsp;List</li>
				  <li v-if="$root.loggedInUser.isAdmin==='1'" class="cursor list-group-item" @click.prevent=" option = 'userNew' " title="Add New User">&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-plus" aria-hidden="true"></i>&nbsp;Add new user</li>
				  <li class="cursor list-group-item" @click.prevent=" option = 'myProfile' " title="My profile">&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-star" aria-hidden="true"></i>&nbsp;My profile</li>
				</ul>
			</div>
		</div>
		<div class="container" id="warning-message">
			<ul class="list-group" v-if=" option === 'menu' ">
				<li class="list-group-item active">Member Care Ministries 
				<font class="mobile-only">&nbsp;- Mobile</font>
				</li>	
				<li class="list-group-item">This app is only available in landscape mode. Please rotate your device.</li>
			</ul>
		</div>
        <div class="pusher"><br/><br/><br/><br/></div>
        <footer class="footer">
              <div class="container">
                <span class="text-muted">Powered with <i class="fa fa-heart" aria-hidden="true"></i> by <a href="https://www.???.site/MemberCareMinistries">clac.site</a> | <a href="#">codelikeachicken.com</a></span>
              </div>
        </footer>
    </body> 
  </html>
  <script src="js/mobile_mcmApp.js"></script>
