<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0">
  <title>PassHub</title>

  <!-- CSS -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link href="{{ @BASEURL }}/assets/css/materialize.css?v=1.1.0" type="text/css" rel="stylesheet">
  <link href="{{ @BASEURL }}/assets/css/style.css?v=1.1.0" type="text/css" rel="stylesheet">

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
<body class="{{ @mode }}" data-mode="{{ @mode }}">
  <!-- Dropdown Structure -->
  <ul id="dropdown1" class="dropdown-content">
    <li><a href="#modal1" title="PassHub Help" class="modal-trigger">Get Support</a></li>
    <li><a href="{{ @BASEURL }}/edit-account/">Edit Account</a></li>
    <li class="divider"></li>
    <li><a href="{{ @BASEURL }}/auth/logout">Sign Out</a></li>
  </ul>
  <!-- Modal Structure -->
  <div id="modal1" class="modal">
    <div class="modal-content">
      <h4 class="grey-text"><span class="blue-text text-lighten-1">Pass</span><span class="grey-text text-darken-1">Hub</span> Support</h4>
      <p>Questions about how to use PassHub? Want it customized for your specific requirements? Feel free to contact me using these channels.</p>
      <ul>
        <li><strong>Email:</strong> <a href="mailto:themes@loewenweb.com">themes@loewenweb.com</a></li>
        <li><strong>Contact Form:</strong> <a target="_blank" href="http://codecanyon.net/user/loewenweb#contact">CodeCanyon Profile</a>
      </ul>
      <p>Thank you,<br>
        Derek Loewen</p>
		<strong><p>U2NyaXB0IGRvd25sb2FkZWQgZnJvbSBDT0RFTElTVC5DQw==</p></strong>
    </div>
    <div class="modal-footer">
      <a href="#!" class=" modal-action modal-close waves-effect waves-green btn-flat">OK!</a>
    </div>
  </div>
  <nav class="light-blue lighten-1" style="display:none">
    <div class="nav-wrapper container"><a id="logo-container" href="{{ @BASEURL }}" class="brand-logo light-blue-text text-lighten-5"><span class="white-text">Pass</span>Hub</a>
      <ul class="right hide-on-med-and-down">
        <repeat group="{{ @pages }}" value="{{ @page }}">
          <check if="{{ @permissions['page'][@page.id]['read'] || strtolower(@page.name) == 'logins' }}">
            <li class="{{ @mode==strtolower(@page.name)?'active':'' }}"><a href="{{ @BASEURL }}/{{ @page.name !== 'Logins' ? strtolower(@page.name) : '' }}">{{ @page.name }}</a></li>
          </check>
        </repeat>
        <!-- Dropdown Trigger -->
        <li><a class="dropdown-button account-button" href="#!" data-activates="dropdown1" title="Manage your account"><i class="material-icons left">account_circle</i>{{ @SESSION.user_name }}<i class="material-icons account-dropdown-icon">arrow_drop_down</i></a></li>
      </ul>
      <a href="#" data-activates="nav-mobile" class="button-collapse text-white right"><i class="material-icons">menu</i></a>
    </div>
  </nav>

  <!-- Mobile Menu -->
  <ul id="nav-mobile" class="side-nav">
    <repeat group="{{ @pages }}" value="{{ @page }}">
      <check if="{{ @permissions['page'][@page.id]['read'] || strtolower(@page.name) == 'logins' }}">
        <li class="{{ @mode==strtolower(@page.name)?'active':'' }}"><a href="{{ @BASEURL }}/{{ @page.name !== 'Logins' ? strtolower(@page.name) : '' }}">{{ @page.name }}</a></li>
      </check>
    </repeat>
    <li class="collapsible-parent">
      <ul class="collapsible collapsible-accordion">
        <li>
          <a class="collapsible-header"><i class="material-icons left">account_circle</i>{{ @SESSION.user_name }}<i class="material-icons right">arrow_drop_down</i></a>
          <div class="collapsible-body">
            <ul>
              <li><a href="#modal1" title="PassHub Help" class="modal-trigger">Get Support</a></li>
              <li><a href="{{ @BASEURL }}/edit-account/">Edit Account</a></li>
              <li class="divider"></li>
              <li><a href="{{ @BASEURL }}/auth/logout">Sign Out</a></li>
            </ul>
          </div>
        </li>
      </ul>
    </li>
  </ul>

  <include href="{{ @content }}" />

  <!-- Templates -->
  <!-- White Loading Spinner -->
  <script type="text/html" id="loadingTemplateWhite">
    <div class="preloader-wrapper small active">
      <div class="spinner-layer spinner-white-only">
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
  <script src="{{ @BASEURL }}/assets/js/materialize.min.js?v=1.1.0"></script>
  <script src="{{ @BASEURL }}/assets/js/velocity.min.js?v=1.1.0"></script>
  <script src="{{ @BASEURL }}/assets/js/velocity.ui.min.js?v=1.1.0"></script>
  <script src="{{ @BASEURL }}/assets/js/utils.js?v=1.1.0"></script>
  <script src="{{ @BASEURL }}/assets/js/jquery.fn.sortable.min.js?v=1.1.0"></script>
  <script src="{{ @BASEURL }}/assets/js/underscore.min.js?v=1.1.0"></script>
  <script src="{{ @BASEURL }}/assets/js/functions.js?v=1.1.0"></script>
  <script src="{{ @BASEURL }}/assets/js/main.js?v=1.1.0"></script>

  </body>
</html>
