<div class="row">
  <div class="col s12">
    <h2>Admin User Setup</h2>
    <p class="intro">Remember to store your login information in a safe place!</p>
  </div>
</div>

<form action="{{ @BASEURL }}{{ 'installeradmin' | alias }}" method="post" class="reset-form">

<div class="row">
  <div class="col s12">
    <div class="card">
      <div class="card-content">
        <check if="{{ @error!='' }}">
          <div class="notification yellow lighten-3 left-align"><i class="material-icons left">error_outline</i><p>{{ @error }}</p></div>
        </check>
        <div class="input-field col s12">
          <input id="email" type="email" name="email" class="validate">
          <label for="email">Email</label>
        </div>
        <div class="input-field col s12">
          <input id="password" type="password" name="password" class="validate">
          <label for="password">Password</label>
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