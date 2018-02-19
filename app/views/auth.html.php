<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0">
  <title>PassHub</title>

  <!-- CSS  -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link href="{{ @BASEURL }}/assets/css/materialize.css" type="text/css" rel="stylesheet">
  <link href="{{ @BASEURL }}/assets/css/style.css" type="text/css" rel="stylesheet">
</head>
<body class="{{ @mode }}" data-mode="{{ @mode }}">
  <div class="section center-align">
    <h1 class="auth-title grey-text text-darken-1"><span class="blue-text text-lighten-1">Pass</span>Hub</h1>
    <div class="container">
      <div class="row">
        <div class="card col s10 offset-s1 z-depth-1">
          <div class="card-content">
            <check if="{{ @reset }}">
              <form action="{{ @BASEURL }}{{ 'authreset' | alias }}" method="post" class="reset-form" novalidate>
                <div class="row">
                  <span class="card-title grey-text text-darken-1">Reset Password</span>
                  <p class="desc">Lost your password? No problem. Just enter your email and you'll receive an email with password reset instructions.</p>
                  <check if="{{ @error!='' }}">
                    <div class="notification yellow lighten-3 left-align"><i class="material-icons left">error_outline</i><p>{{ @error }}</p></div>
                  </check>
                  <div class="input-field col s12">
                    <input id="email" type="email" name="email" class="validate">
                    <label for="email">Email</label>
                  </div>
                  <div class="col s12">
                    <button class="btn btn-large waves-effect waves-light blue" type="submit" name="action">Send</button>
                  </div>
                </div>
              </form>
            </check>
            <check if="{{ ! @reset }}">
              <form action="{{ @BASEURL }}{{ 'authpost' | alias }}" method="post" novalidate>
                <check if="{{ @error!='' }}">
                  <div class="notification yellow lighten-3 left-align"><i class="material-icons left">error_outline</i><p>{{ @error }}</p></div>
                </check>
                <check if="{{ @success!='' }}">
                  <div class="notification green lighten-4 left-align green-text text-darken-4"><i class="material-icons left">check</i><p>{{ @success | raw }}</p></div>
                </check>
                <div class="row">
                  <div class="input-field col s12">
                    <input id="email" type="email" name="email" class="validate">
                    <label for="email">Email</label>
                  </div>
                  <div class="input-field col s12">
                    <input id="password" type="password" name="password" class="validate">
                    <label for="password">Password</label>
                  </div>
                  <div class="col s12 right-align">
                    <a class="forgot-password teal-text" href="{{ @BASEURL }}{{ 'authreset' | alias }}">Forgot password?</a>
                  </div>
                  <div class="col s12">
                    <button class="btn btn-large waves-effect waves-light blue" type="submit" name="action">Sign in</button>
                  </div>
                </div>
              </form>
            </check>
          </div>
        </div>
      </div>
      <check if="{{ @reset }}">
        <div class="row">
          <div class="col s12">
            <a class="back-to-login teal-text" href="{{ @BASEURL }}/auth/">Back to sign in</a>
          </div>
        </div>
      </check>
    </div>
  </div>

  <!-- White Loading Spinner Template -->
  <script type="text/html" id="loadingTemplateWhite">
    <div class="preloader-wrapper small active">
      <div class="spinner-layer spinner-blue-only">
        <div class="circle-clipper left">
          <div class="circle"></div>
        </div><div class="gap-patch">
          <div class="circle"></div>
        </div><div class="circle-clipper right">
          <div class="circle"></div>
        </div>
      </div>
    </div>
  </script>

  <!-- Scripts -->
  <script>
    var baseUrl = "{{ @BASEURL }}";
    var csrf = "{{ @SESSION.csrf }}";
  </script>
  <!-- Include jQuery with local fallback -->
  <script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
  <script>window.jQuery || document.write('<script src="{{ @BASEURL }}/assets/js/jquery-2.1.4.min.js"><\/script>')</script>
  <script src="{{ @BASEURL }}/assets/js/materialize.js"></script>
  <script src="{{ @BASEURL }}/assets/js/velocity.min.js"></script>
  <script src="{{ @BASEURL }}/assets/js/velocity.ui.min.js"></script>
  <script src="{{ @BASEURL }}/assets/js/utils.js"></script>
  <script src="{{ @BASEURL }}/assets/js/functions.js"></script>
  <script src="{{ @BASEURL }}/assets/js/main.js"></script>

  </body>
</html>
