<?php



$apiFormBox = '
<form method="post" id="apiForm">
  <!--div class="field">
    <label class="label">MapBox API URL</label>
    <div class="control">
      <input class="input" name="api_url" type="url" placeholder="" value="%s">
    </div>
  </div-->

  <!--div class="field">
    <label class="label">MapBox API Login</label>
    <div class="control">
      <input class="input" type="text" name="api_login" placeholder="" value="%s"/>
  </div-->

  <div class="field">
    <label class="label">MapBox API Token</label>
    <div class="control">
      <textarea class="input" type="text" name="api_token" placeholder="token">%s</textarea>
    </div>
  </div>

  <div class="field is-grouped">
    <div class="control">
      <button id="submitApiCredentials" class="button is-link">Save</button>
    </div>
    <!--div class="control">
      <button class="button is-link is-light">Cancel</button>
    </div-->
    </div>
  </form>
';

$activateCitiesForm = '
<form method="post" id="citiesForm">
  <div class="field">
    <label class="label">Cities list</label>
    <div class="control">
      <textarea class="input" type="text" placeholder="London,Bristol,Liverpool…">%s</textarea>
    </div>
  </div>
  <div class="field is-grouped">
    <div class="control">
      <button id="submitCities" class="button is-link">Save</button>
    </div>
    <!--div class="control">
        <button class="button is-link is-light">Cancel</button>
    </div-->
  </div>
</form>
';


$mapModal = '
<div class="modal">
  <div class="modal-background"></div>
  <div class="modal-content">
    <!-- Any other Bulma elements you want -->
  </div>
  <button class="modal-close is-large" aria-label="close"></button>
</div>
';

$mapBox = '<div id="map" style="width: 500px; height: 420px;"></div>';


$mapPanel = '<div class="panel %s %s">
  <header class="panel-heading">
    <p class="panel-header-title is-size-5">%s</p>
    <!--a href="#" class="panel-header-icon" aria-label="more options">
      <span class="icon">
      <i class="fas fa-angle-down" aria-hidden="true"></i>
    </span>
    </a-->
  </header>
  <div class="panel-block has-background-white">
    <div class="content %s">%s</div>
  </div>
  <!--footer class="card-footer">
    <a href="#" class="card-footer-item">Save</a>
    <a href="#" class="card-footer-item"></a>
    <a href="#" class="card-footer-item"></a-->
  </footer-->
</div>';
?>