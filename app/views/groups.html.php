<div class="container" id="title" style="display:none">
  <div class="row">
    <div class="col s12 m8 l6 offset-m2 offset-l3 center">
      <h2 class="grey-text text-darken-3">Groups</h2>
    </div>
  </div>
</div>

<div class="container">
  <div class="section groupsList itemsList" id="groupsList" style="display:none">
    <form action="" method="post">
      <div class="row">
        <div class="col s12 m12 l12">
          <div class="card hoverable">
            <div class="card-content">
              <!-- Content -->
              <div class="card-content-inner"></div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
  <br><br>
</div>

<include href="grouptemplates.html.php" />

<!-- Help Modals -->
  
<!-- Category ACL -->
<div id="category-acl-help" class="modal">
  <div class="modal-content">
    <h4 class="grey-text"><span class="blue-text text-lighten-1">Pass</span><span class="grey-text text-darken-1">Hub</span> Support</h4>
    <h5>Category Access Help</h5>
    <p>Category Access controls what the group can do with logins in each category. For example, if "Edit" permission is given to the "General" category, users in the group will be able to edit all logins in the "General" category. Note that "Read" access is required to see the category on the Logins page.</p>
  </div>
  <div class="modal-footer">
    <a href="#!" class=" modal-action modal-close waves-effect waves-green btn-flat">OK!</a>
  </div>
</div>

<!-- Page ACL -->
<div id="page-acl-help" class="modal">
  <div class="modal-content">
    <h4 class="grey-text"><span class="blue-text text-lighten-1">Pass</span><span class="grey-text text-darken-1">Hub</span> Support</h4>
    <h5>Page Access Help</h5>
    <p>Page Access controls what the group can do on each page. Logins are excluded here, because each user must have access to view the logins page (it&rsquo;s the whole point of this app, after all!).</p> 
    <p>Use the Category Access table to control access to logins based on what category they&rsquo;re in.</p>
  </div>
  <div class="modal-footer">
    <a href="#!" class=" modal-action modal-close waves-effect waves-green btn-flat">OK!</a>
  </div>
</div>

