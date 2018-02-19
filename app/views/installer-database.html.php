<div class="row">
  <div class="col s12">
    <h2>Database</h2>
    <p class="intro">Please enter your database credentials.</p>
  </div>
</div>

<form action="{{ @BASEURL }}{{ 'installerdatabase' | alias }}" method="post" novalidate>

<div class="row">
  <div class="col s12">
    <div class="card">
      <div class="card-content">
        <check if="{{ @error!='' }}">
          <div class="notification yellow lighten-3 left-align"><i class="material-icons left">error_outline</i><p>{{ @error }}</p></div>
        </check>
        <div class="input-field col s12">
          <input id="db_host" type="text" name="db_host" class="validate" value="localhost">
          <label for="db_host">Database Host</label>
        </div>
        <div class="input-field col s12">
          <input id="db_port" type="text" name="db_port" class="validate" value="3306">
          <label for="db_port">Database Port</label>
        </div>
        <div class="input-field col s12">
          <input id="db_name" type="text" name="db_name" class="validate" value="{{ (@POST.db_name) ? @POST.db_name : '' }}">
          <label for="db_name">Database Name</label>
        </div>
        <div class="input-field col s12">
          <input id="db_username" type="text" name="db_username" class="validate" value="{{ (@POST.db_username) ? @POST.db_username : '' }}">
          <label for="db_username">Database Username</label>
        </div>
        <div class="input-field col s12">
          <input id="db_password" type="password" name="db_password" class="validate" value="{{ (@POST.db_password) ? @POST.db_password : '' }}">
          <label for="db_password">Database Password</label>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col s12">
    <button class="btn btn-large btn-next" type="submit" name="action"><i class="material-icons right">navigate_next</i>Next</button>
  </div>
</div>

</form>