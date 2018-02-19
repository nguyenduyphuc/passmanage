<div class="container" id="title" style="display:none">
  <div class="row">
    <div class="col s12 m8 l6 offset-m2 offset-l3 center">
      <h2 class="grey-text text-darken-3">Tools</h2>
    </div>
  </div>
</div>

<div class="container">
  <div class="section" id="" style="/*display:none*/">
    <form action="" method="post">
      <div class="row">
        <div class="col s12 m8 l6 offset-m2 offset-l3">
          <div class="card hoverable">
            <div class="card-content">
              <span class="card-title grey-text text-darken-4">Download Logins as CSV</span>
              <p>The CSV will contain a list of all logins in plain text, including their categories and fields. Fields will be titled generically, since each column can only have one title in the CSV file.</p>
              <div class="notification blue lighten-5 left-align"><i class="material-icons left">info_outline</i><p>Since the generated file will contain your unencrypted login information, you should store it in a secure location appropriate for the level of the data's sensitivity.</p></div>
              <div class="card-action">
                <a href="{{ @BASEURL }}/tools/download-logins-csv" id="downloadLoginsCsv" class="teal-text"><i class="material-icons left">system_update_alt</i>Download .CSV File</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
  <br><br>
</div>