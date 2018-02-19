<!-- Category Card Wrapper Template -->
<script type="text/html" id="categoriesTemplate">
  <div class="row">
    <div class="fields-wrapper">
      <%=tmpl('fieldTemplate', { categories: categories })%>
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
  <%
    /* 
    Pre-preocess categories to sort by "sorting" numbers.
    */
    sortedCategories = {};
    $.each( categories, function( key, category ) {
      sortedCategories[category.sorting] = category; 
    });
  %>
  <% $.each( sortedCategories, function( key, category ) { %>
    <div class="input-field col s12" data-category-index="<%=category.id%>">
      <input type="text" name="category[]" placeholder="Name" value="<%=category.name%>">
      <div class="actions">
        <% if(category.name != 'General') { %>
        <button 
          class="z-depth-0 grey-text white btn tooltipped" 
          data-action="deleteCategory" 
          data-position="bottom" 
          data-tooltip="delete category"
        ><i class="material-icons">remove_circle</i></button>
        <% } %>
        <button 
          class="z-depth-0 grey-text white btn tooltipped" 
          data-action="addCategory" 
          data-position="bottom" 
          data-tooltip="add new category below"
        ><i class="material-icons">add_circle</i></button>
        <span 
          class="reorderHandle z-depth-0 grey-text white btn" 
          data-action="reorderCategory"
        ><i class="material-icons">reorder</i></span>
      </div>
    </div>
  <% }); /* end each */ %>
</script>