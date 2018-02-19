<!-- Users Cards Wrapper Template -->
<script type="text/html" id="usersTemplate">
<% $.each( users, function( key, user ) { %>
  <div class="row userSingle itemSingle" data-user-id="<%=user.userId%>">
    <div class="col s12 m8 l6 offset-m2 offset-l3">
      <div class="card hoverable">
        <div class="card-content">
          <!-- Title -->
          <span class="card-title grey-text text-darken-4"><%=user.userName%></span>
          <a class="singleContextBtn dropdown-button btn-floating waves-effect waves-dark right z-depth-0" href="#" data-activates="userContext<%=user.userId%>"><i class="material-icons grey-text">more_vert</i></a>
          <div class="card-content-inner">
            <!-- Snippet and Forms -->
            <%=tmpl('userContentTemplate', { user: user, groups: groups })%>
          </div>
        </div>
      </div>
    </div>
    <ul id="userContext<%=user.userId%>" class="dropdown-content">
      <li><a class="btnContext" data-action="edit" href="user/edit"><i class="material-icons left">edit</i>Edit</a></li>
      <li><a class="btnContext" data-action="delete" href="user/delete"><i class="material-icons left">delete</i>Delete</a></li>
    </ul>

  </div>
<% }); /* end each */ %>
</script>

<!-- Users Card Content Template -->
<script type="text/html" id="userContentTemplate">
  <% 
  /* Pre-processing for groups */
  var groupOptionsHtml = '';
  var currentGroupName = '';
  $.each( groups, function( key, group ) {
    var selected = '';
    if(group.groupId == user.userGroupId) {
      selected ='selected="selected"';
      currentGroupName = titleize(group.groupName);
    } 
    groupOptionsHtml += '<option value="' + group.groupId + '" ' + selected + '>' + titleize(group.groupName) + '</option>';
  }); /* end each */ 
  %>
  <div class="snippet">
    <p>
      <span class="grey-text">Group:</span>
      <span class="grey-text text-darken-2"><%=currentGroupName%></span>
      <br>
      <span class="grey-text">Email:</span>
      <span class="grey-text text-darken-2"><%=user.userEmail%></span>
    </p>
  </div>
  <div class="edit-form" style="display:none">
    <form action="user/<%=user.userId%>" method="post">
      <div class="row">
        <div class="input-field col s12">
          <select id="group<%=user.userId%>" name="groupId">
            <%=groupOptionsHtml%>
          </select>
          <label for="group<%=user.userId%>">Group</label>
        </div>        
        <div class="input-field col s12">
          <input type="email" id="userEmail<%=user.userId%>" name="email" value="<%=user.userEmail%>">
          <label class="active" for="userEmail<%=user.userId%>">Email</label>         
        </div>
        <div class="input-field col s12">
          <input type="text" id="userPassword<%=user.userId%>" name="password" value="<%=user.userPassword%>">
          <label class="active" for="userPassword<%=user.userId%>">Update Password</label>  
          <div class="actions">
            <button 
              class="z-depth-0 grey-text white btn tooltipped" 
              data-action="generatePassword" 
              data-position="bottom" 
              data-tooltip="generate new password"
            ><i class="material-icons">settings</i></button>
          </div>       
        </div>
        <div class="col s12 right-align primary-actions">
          <button class="btnCancel z-depth-0 btn waves-effect waves-dark white lighten-2 grey-text text-darken-2">Close</button>
          <button class="btnSave z-depth-0 btn waves-effect waves-light">Save</button>
        </div>
      </div>
    </form>
  </div>
  <div class="delete-form" style="display:none">
    <div class="row">
      <div class="col s12">
        <p>Delete this user permanently?</p>
      </div>
      <div class="col s12 right-align">
        <button class="btnCancel z-depth-0 btn waves-effect waves-dark white lighten-2 grey-text text-darken-2">Cancel</button>
        <button class="btnDelete z-depth-0 btn red waves-effect waves-light">Confirm</button>
      </div>
    </div>
  </div>
</script>
