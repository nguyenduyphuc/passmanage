<div class="row">
  <div class="col s12">
    <h2>Email Setup</h2>
    <p class="intro">The email configuration below will be used to send password recovery emails.</p>
  </div>
</div>

<form action="{{ @BASEURL }}{{ 'installeremail' | alias }}" method="post" novalidate>

<div class="row">
  <div class="col s12">
    <div class="card">
      <div class="card-content">
        <check if="{{ @error!='' }}">
          <div class="notification yellow lighten-3 left-align"><i class="material-icons left">error_outline</i><p>{{ @error }}</p></div>
        </check>
        <p class="desc">If you don't have an email to send with, sign up with a transactional email service such as <a target="_blank" href="https://www.mandrill.com/">Mandrill</a> or <a target="_blank" href="https://sendgrid.com/">SendGrid</a> and use their SMTP connection information below.</p>
        <div class="input-field col s12">
          <input id="email_username" type="email" name="email_username" class="validate" value="{{ (@POST.email_username) ? @POST.email_username : '' }}">
          <label for="email_username">Email Username</label>
        </div>
        <div class="input-field col s12">
          <input id="email_password" type="password" name="email_password" class="validate" value="{{ (@POST.email_password) ? @POST.email_password : '' }}">
          <label for="email_password">Email Password</label>
        </div>
        <div class="input-field col s12">
          <input id="smtp_server" type="text" name="smtp_server" class="validate" value="{{ (@POST.smtp_server) ? @POST.smtp_server : '' }}">
          <label for="smtp_server">SMTP Server Address</label>
        </div>
        <div class="input-field col s12">
          <input id="smtp_port" type="text" name="smtp_port" class="validate" value="{{ (@POST.smtp_port) ? @POST.smtp_port : '' }}">
          <label for="smtp_port">SMTP Port</label>
        </div>
        <div class="col s12">
          <p>Use TLS Encryption</p>
          <div class="switch">
            <label>
              Off
              <input id="smtp_scheme" name="smtp_scheme" type="checkbox" value="tls" checked />
              <span class="lever"></span>
              On
            </label>
          </div>
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