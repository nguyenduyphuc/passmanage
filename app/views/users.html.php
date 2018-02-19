<div class="container" id="title" style="display:none">
  <div class="row">
    <div class="col s12 m8 l6 offset-m2 offset-l3 center">
      <h2 class="grey-text text-darken-3">{{ @mode == 'users' ? 'Users':'Edit Account' }}</h2>
    </div>
  </div>
</div>

<div class="container">
  <!-- user Cards -->
  <div class="section usersList itemsList" id="usersList">
  </div>
  <br><br>
</div>

<check if="{{@mode == 'users'}}">
  <div class="fixed-action-btn" style="display: none;">
    <a id="btnAddUser" class="waves-effect waves-light btn-floating btn-large green darken-1">
      <i class="large material-icons">add</i>
    </a>
  </div>
</check>

<include href="usertemplates.html.php" />