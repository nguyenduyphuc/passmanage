<div class="row">
  <div class="col s12">
    <h2>Requirements</h2>
    <p class="intro">Now we'll check whether your web hosting meets the requirements needed to install PassHub.</p>
  
    <check if="{{ @SSL_TEST===false }}">
      <div class="card-panel yellow lighten-3">
        <i class="material-icons left">error_outline</i> <strong>Warning: SSL not detected.</strong> To prevent unauthorized access to your login details through connection sniffing, it's highly recommended to access PassHub with an SSL certificate installed, using "https://" instead of "http://".
      </div>
    </check>

  </div>
</div>

<div class="row">
  <div class="col s12">
    <div class="card">
      <div class="card-content">
        <table>
          <thead>
            <tr>
              <th>Test</th>
              <th>Result</th>
            </tr>
          </thead>
          <tbody>
            <repeat group="{{ @TESTS }}" key="{{ @test }}" value="{{ @result }}">
              <tr>
                <td>{{ @test }}</td>
                <td><span class="result ok {{ (@result===true) ? 'green' : 'red' }} white-text">{{ (@result===true) ? 'OK' : 'FAIL' }}</span></td>
              </tr>
            </repeat>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col s12">
    <check if="{{ @REQUIREMENTS_MET===true }}">
      <p><strong>Your web hosting meets or exceeds the requirements!</strong></p>
      <a href="{{@BASEURL}}/install/database" class="btn btn-large btn-next"><i class="material-icons right">navigate_next</i>Next</a>
    </check>

    <check if="{{ @REQUIREMENTS_MET===false }}">
      <p>Some requirements were not met. If config.ini is not writable, change the permissions to allow writing (644 on most web hosts). If other requirements were not met, please contact your web host with these results and see if they can enable the features. If not, you may have to switch to a compatible web host.</p>
    </check>
  </div>
</div>
