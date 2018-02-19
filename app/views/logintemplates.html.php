<!-- Search Form Template -->
<script type="text/html" id="searchTemplate">
  <%
    /* 
    Pre-preocess categories to sort by "sorting" numbers.
    */
    sortedCategories = {};
    $.each( categories, function( key, category ) {
      sortedCategories[category.sorting] = category; 
    });
  %>
  <div class="row">
    <form class="col s12 m8 l6 offset-m2 offset-l3 center">
      <div class="row">
        <div class="input-field col s6 m6">
          <input id="search" type="text" placeholder="Keyword" class="validate">
          <label for="search">Search Logins</label>
        </div>
        <div class="input-field col s6 m6">
          <select id="category" name="category">
            <option value="0">All</option>
            <% $.each( sortedCategories, function( key, category ) { %>
              <option value="<%=category.id %>"><%=category.name %></option>
            <% }); /* end each */ %>
          </select>
          <label>Category</label>
        </div>
      </div>
    </form>
  </div>
</script>

<!-- Logins Cards Wrapper Template -->
<script type="text/html" id="loginsTemplate">
  <%
    /* 
    Pre-preocess categories to sort by "sorting" numbers.
    */
    sortedCategories = {};
    $.each( categories, function( key, category ) {
      sortedCategories[category.sorting] = category; 
    });
  %>
  <% $.each( logins, function( key, login ) { %>
    <div class="row loginSingle itemSingle" data-login-id="<%=login.loginId%>">
      <div class="col s12 m8 l6 offset-m2 offset-l3">
        <div class="card hoverable">
          <div class="card-content">
            <!-- Title -->
            <span class="card-title grey-text text-darken-4"><%=login.loginName%></span>
            <a class="singleContextBtn dropdown-button btn-floating waves-effect waves-dark right z-depth-0" href="#" data-activates="loginContext<%=login.loginId%>"><i class="material-icons grey-text">more_vert</i></a>
            <div class="card-content-inner">
              <!-- Snippet and Forms -->
              <%=tmpl('loginContentTemplate', {login: login, categories: sortedCategories})%>
            </div>
          </div>
        </div>
      </div>
      <ul id="loginContext<%=login.loginId%>" class="dropdown-content">
        <li><a class="btnContext" data-action="edit" href="login/edit"><i class="material-icons left">edit</i>Edit</a></li>
        <li><a class="btnContext" data-action="delete" href="login/delete"><i class="material-icons left">delete</i>Delete</a></li>
      </ul>
    </div>
  <% }); /* end each */ %>
</script>

<!-- Logins Card Content Template -->
<script type="text/html" id="loginContentTemplate">
  <div class="snippet">
    <p>
      <% 
        var i = 0; 
        $.each( login.fields, function( key, field ) { 
          var isURL = ( field.fieldValue.slice(0, 4) == 'http' ) ? true : false;
      %>
        <span class="snippet-field">
          <span class="grey-text"><%=titleize(field.fieldName)%>:</span> &nbsp;
          <% if( isURL ) { /* URLs */ %>
            <a target="_blank" href="<%=field.fieldValue%>" title="Click to jump to URL"><%=field.fieldValue%> <i class="material-icons tiny">open_in_new</i></a>
          <% } else { /* Non-URLs */ %>  
          <a class="copy-button" href="#" data-clipboard-text="<%=field.fieldValue%>" title="Click to copy me">
            <% if( field.fieldType == 'password' ) { %>
              <%=passwordMask(field.fieldValue)%>
            <% } else { %>
              <%=field.fieldValue%>
            <% } %>
          </a>
          <% } %>
          <% if( field.fieldType == 'password' ) { %>
            <button class="z-depth-0 grey-text btn" data-action="showPassword">show</button>
          <% } %>
          <% if( ! isURL && field.fieldType != 'password' ) { %>
            <button class="z-depth-0 grey-text btn" data-action="select">select</button>
          <% } %>
        </span>
        <br>
      <% i++ }); /* end each */ %>
      <% if( i < 2 ) { %>
        <br>
      <% } /* end if */ %>
    </p>
  </div>
  <div class="edit-form" style="display:none">
    <form action="login/<%=login.loginId%>" method="post">
      <div class="row">
        <div class="select-wrap col s12">
          <label>Category</label>
          <select name="category" class="browser-default">
            <% $.each( categories, function( key, category ) { %>
              <option value="<%=category.id%>" <%=category.id == login.loginCategoryId ? 'selected="selected"':''%>><%=category.name%></option>
            <% }); /* end each */ %>
          </select>
        </div>
        <div class="fields-wrapper">
          <%=tmpl('fieldTemplate', { fields: login.fields })%>
        </div>
        <div class="col s12 right-align">
          <button class="btnCancel z-depth-0 btn waves-effect waves-dark white lighten-2 grey-text text-darken-2">Close</button>
          <button class="btnSave z-depth-0 btn waves-effect waves-light">Save</button>
        </div>
      </div>
    </form>
  </div>
  <div class="delete-form" style="display:none">
    <div class="row">
      <div class="col s12">
        <p>Delete this login permanently?</p>
      </div>
      <div class="col s12 right-align">
        <button class="btnCancel z-depth-0 btn waves-effect waves-dark white lighten-2 grey-text text-darken-2">Cancel</button>
        <button class="btnDelete z-depth-0 btn red waves-effect waves-light" >Confirm</button>
      </div>
    </div>
  </div>
</script>

<!-- Field Template -->
<script type="text/html" id="fieldTemplate">
  <% $.each( fields, function( key, field ) { %>
    <div class="input-field <%=field.fieldType%> col s12" data-field-index="<%=field.fieldId%>">
      <% if ( field.fieldType == 'text' || field.fieldType == 'password' ) { %>
        <input type="<%=field.fieldType%>" name="<%=field.fieldName%>" value="<%=field.fieldValue%>" placeholder=" ">
      <% } else { %>
        <textarea class="materialize-textarea" name="<%=field.fieldName%>"><%=field.fieldValue%></textarea>
      <% } %>
      <label class="active" contentEditable="true"><%=titleize(field.fieldName)%></label>
      <div class="actions">
        <% if ( field.fieldType == 'password' ) { %>
          <button 
            class="z-depth-0 grey-text white btn tooltipped" 
            data-action="showPassword" 
            data-position="bottom" 
            data-tooltip="show password"
          ><i class="material-icons">visibility</i></button>
          <button 
            class="z-depth-0 grey-text white btn tooltipped" 
            data-action="generatePassword" 
            data-position="bottom" 
            data-tooltip="generate new password"
          ><i class="material-icons">settings</i></button>
        <% } /* end if */ %>
        <button 
          class="z-depth-0 grey-text white btn tooltipped" 
          data-action="deleteField" 
          data-position="bottom" 
          data-tooltip="delete field"
        ><i class="material-icons">remove_circle</i></button>
        <button 
          class="z-depth-0 grey-text white btn tooltipped" 
          data-action="addField" 
          data-position="bottom" 
          data-tooltip="add new field below"
        ><i class="material-icons">add_circle</i></button>
        <span 
          class="reorderHandle z-depth-0 grey-text white btn" 
          data-action="reorderField"
        ><i class="material-icons">reorder</i></span>
      </div>
      <ul class="field-type-picker">
        <li><a class="z-depth-0 btn" data-type="text">Text</a></li>
        <li><a class="z-depth-0 btn" data-type="textarea">Textarea</a></li>
        <li><a class="z-depth-0 btn" data-type="password">Password</a></li>
      </ul>
    </div>
  <% }); /* end each */ %>
</script>
