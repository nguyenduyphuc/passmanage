<!-- Card Wrapper Template -->
<script type="text/html" id="groupsTemplate">
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
    <div class="fields-wrapper">
      <%=tmpl('fieldTemplate', { groups: groups, pages: pages, categories: sortedCategories, permissions: permissions })%>
    </div>
  </div>
  <div class="row">
    <div class="col s12 right-align primary-actions">
      <button class="btnSave z-depth-0 btn waves-effect waves-light">Save</button>
    </div>
  </div>
</script>

<!-- Field Template -->
<script type="text/html" id="fieldTemplate">
  <% $.each( groups, function( key, group ) { %>
    <%
    /* Set the index to use for the field IDs */
    var fieldId = group.id;
    /* If it's a new group, it won't have an ID available, so generate a random value */
    if( group.id == '' ) {
      fieldId = Math.ceil(Math.random() * 1000);
    }
    %>
    <div class="input-field col s12" data-item-index="<%=group.id%>">
      <input class="name-field" type="text" name="group[]" placeholder="Name" value="<%=group.name.capitalizeFirstLetter()%>">
      <div class="actions">
        <button 
          class="z-depth-0 grey-text white btn tooltipped" 
          data-action="toggleSettings" 
          data-position="bottom" 
          data-tooltip="group settings"
          ><i class="material-icons">settings</i></button>
        <button 
          class="z-depth-0 grey-text white btn tooltipped" 
          data-action="deleteGroup" 
          data-position="bottom" 
          data-tooltip="delete group"
        ><i class="material-icons">remove_circle</i></button>
        <button 
          class="z-depth-0 grey-text white btn tooltipped" 
          data-action="addGroup" 
          data-position="bottom" 
          data-tooltip="add new group below"
        ><i class="material-icons">add_circle</i></button>
      </div>
     <div class="row settings-container well" style="display: none;">
        <div class="col s12 l6">
          <h5>Page Access <a title="Help" class="modal-trigger waves-effect waves-teal btn-flat ph-help-btn" href="#page-acl-help"><i class="material-icons">info_outline</i></a></h5>
          <table class="bordered shade-first-col acl-table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Read</th>
                <th>Create</th>
                <th>Edit</th>
                <th>Delete</th>
              </tr>
            </thead>
            <tbody>
              <% $.each( pages, function(key, page) { %>
                <% 

                /* If the page is "Logins", skip it. Login permissions are handled by category access. */
                if( page.name == 'Logins' ) {
                  return true;
                }

                /* Set each permission for the resource */
                $.each( PassHub.permissionTypes, function( key, perm ) {
                  if( 
                    typeof permissions.groups !== 'undefined' /* must have perms defined */
                    && permissions.groups[group.id].pages[page.id][perm] === true 
                  ) { 
                    page[perm] = true; 
                  } else {
                    page[perm] = false;  
                  }
                }); /* End PassHub.permissionTypes each */

                %>
                <tr data-resource-id="<%=page.id%>">
                  <td><%=page.name%></td>
                  <!-- Read -->
                  <td>
                    <input type="checkbox" class="solo filled-in" data-resource="page" data-type="read" id="pr_<%=page.id%>_<%=fieldId%>" <% if(page.read === true) { %>checked="checked"<% } %> />
                    <label for="pr_<%=page.id%>_<%=fieldId%>"></label>
                  </td>
                  <!-- Create -->
                  <td>
                    <input type="checkbox" class="solo filled-in" data-resource="page" data-type="create" id="pc_<%=page.id%>_<%=fieldId%>" <% if(page.create === true) { %>checked="checked"<% } %> />
                    <label for="pc_<%=page.id%>_<%=fieldId%>"></label>
                  </td>
                  <!-- Edit -->
                  <td>
                    <input type="checkbox" class="solo filled-in" data-resource="page" data-type="edit" id="pe_<%=page.id%>_<%=fieldId%>" <% if(page.edit === true) { %>checked="checked"<% } %> />
                    <label for="pe_<%=page.id%>_<%=fieldId%>"></label>
                  </td>
                  <!-- Delete -->
                  <td>
                    <input type="checkbox" class="solo filled-in" data-resource="page" data-type="delete" id="pd_<%=page.id%>_<%=fieldId%>" <% if(page.delete === true) { %>checked="checked"<% } %> />
                    <label for="pd_<%=page.id%>_<%=fieldId%>"></label>
                  </td>
                </tr>
              <% }); /* end pages each */ %>
            </tbody>
          </table>
          <p class="select-buttons">
            <span class="grey-text">Select:</span>&nbsp;
            <a href="#" class="select-btn" data-type="all">All</a> <span class="grey-text">&nbsp;|&nbsp;</span> 
            <a href="#" class="select-btn" data-type="none">None</a>
          </p>
        </div>
        <div class="col s12 l6">
          <h5>Category Access <a title="Help" class="modal-trigger waves-effect waves-teal btn-flat ph-help-btn" href="#category-acl-help"><i class="material-icons">info_outline</i></a></h5>
          <table class="bordered shade-first-col acl-table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Read</th>
                <th>Create</th>
                <th>Edit</th>
                <th>Delete</th>
              </tr>
            </thead>
            <tbody>
              <% $.each( categories, function( key, category ) { %>
                <%                 
                /* Set each permission for the resource */
                $.each( PassHub.permissionTypes, function( key, perm ) {
                  if( 
                    typeof permissions.groups !== 'undefined' /* must have perms defined */
                    && permissions.groups[group.id].categories[category.id][perm] === true 
                  ) { 
                    category[perm] = true; 
                  } else {
                    category[perm] = false;  
                  }
                }); /* End PassHub.permissionTypes each */
                %>
                <tr data-resource-id="<%=category.id%>">
                  <td><%=category.name%></td>
                  <!-- Read -->
                  <td>
                    <input type="checkbox" class="solo filled-in" data-resource="category" data-type="read" id="cr_<%=category.id%>_<%=fieldId%>" <% if(category.read === true) { %>checked="checked"<% } %> />
                    <label for="cr_<%=category.id%>_<%=fieldId%>"></label>
                  </td>
                  <!-- Create -->
                  <td>
                    <input type="checkbox" class="solo filled-in" data-resource="category" data-type="create" id="cc_<%=category.id%>_<%=fieldId%>" <% if(category.create === true) { %>checked="checked"<% } %> />
                    <label for="cc_<%=category.id%>_<%=fieldId%>"></label>
                  </td>
                  <!-- Edit -->
                  <td>
                    <input type="checkbox" class="solo filled-in" data-resource="category" data-type="edit" id="ce_<%=category.id%>_<%=fieldId%>" <% if(category.edit === true) { %>checked="checked"<% } %> />
                    <label for="ce_<%=category.id%>_<%=fieldId%>"></label>
                  </td>
                  <!-- Delete -->
                  <td>
                    <input type="checkbox" class="solo filled-in" data-resource="category" data-type="delete" id="cd_<%=category.id%>_<%=fieldId%>" <% if(category.delete === true) { %>checked="checked"<% } %> />
                    <label for="cd_<%=category.id%>_<%=fieldId%>"></label>
                  </td>
                </tr>
              <% }); /* end categories each */ %>
            </tbody>
          </table>
          <p class="select-buttons">
            <span class="grey-text">Select:</span>&nbsp;
            <a href="#" class="select-btn" data-type="all">All</a> <span class="grey-text">&nbsp;|&nbsp;</span> 
            <a href="#" class="select-btn" data-type="none">None</a>
          </p>
        </div>
      </div><!-- end settings wrapper -->
    </div>
  <% }); /* end groups each */ %>
</script>