<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0">
  <title>PassHub Installer</title>

  <!-- CSS  -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link href="{{ @BASEURL }}/assets/css/materialize.css" type="text/css" rel="stylesheet">
  <link href="{{ @BASEURL }}/assets/css/style.css" type="text/css" rel="stylesheet">

  <!-- Favicon -->
  <link rel="apple-touch-icon" href="{{ @BASEURL }}/assets/images/favicon/apple-touch-icon.png">
  <link rel="icon" type="image/png" href="{{ @BASEURL }}/assets/images/favicon/android-chrome-192x192.png">
  <link rel="manifest" href="{{ @BASEURL }}/assets/images/favicon/manifest.json">
  <link rel="mask-icon" href="{{ @BASEURL }}/assets/images/favicon/safari-pinned-tab.svg" color="#5bbad5">
  <link rel="shortcut icon" href="{{ @BASEURL }}/assets/images/favicon/favicon.ico">
  <meta name="msapplication-TileColor" content="#da532c">
  <meta name="msapplication-TileImage" content="{{ @BASEURL }}/assets/images/favicon/mstile-144x144.png">
  <meta name="msapplication-config" content="{{ @BASEURL }}/assets/images/favicon/browserconfig.xml">
  <meta name="theme-color" content="#ffffff">
</head>
<body class="installer">

  <div class="section installer-header light-blue lighten-1">
    <div class="container">
      <h1 class="header installer-title light-blue-text text-lighten-5">
        <span class="branding"><span class="white-text">Pass</span>Hub</span>
        Installer
      </h1>
    </div>

    <div class="installer-progress center">
      <div class="container">
        <ul>
          <li class="{{ (@step=='welcome') ? 'active' : '' }}">Welcome</li>
          <li class="{{ (@step=='requirements') ? 'active' : '' }}">Requirements</li>
          <li class="{{ (@step=='database') ? 'active' : '' }}">Database</li>
          <li class="{{ (@step=='email') ? 'active' : '' }}">Email</li>
          <li class="{{ (@step=='admin') ? 'active' : '' }}">Admin User</li>
          <li class="{{ (@step=='complete') ? 'active' : '' }}">Complete</li>
        </ul>
      </div>
    </div>
  </div>

  <div class="section">
    <div class="container">
      <div class="row">
        <include href="{{ @content }}" />
      </div>
      <div class="row">
        <hr style="background: none; border: none; border-bottom: solid 1px #ccc;">
        <p class="grey-text">Version {{ @PASSHUB_VERSION }}</p>
      </div>
    </div>
  </div>

  <!--  Scripts-->
  <script>
    var baseUrl = "{{ @BASEURL }}";
  </script>
  <!-- Include jQuery with local fallback -->
  <script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
  <script>window.jQuery || document.write('<script src="{{ @BASEURL }}/assets/js/jquery-2.1.4.min.js"><\/script>')</script>
  <script src="{{ @BASEURL }}/assets/js/materialize.min.js"></script>
  <script src="{{ @BASEURL }}/assets/js/velocity.min.js"></script>
  <script src="{{ @BASEURL }}/assets/js/velocity.ui.js"></script>
  <script src="{{ @BASEURL }}/assets/js/utils.js"></script>
  <script src="{{ @BASEURL }}/assets/js/jen.js"></script>
  <script src="{{ @BASEURL }}/assets/js/installer.js"></script>

  </body>
</html>
