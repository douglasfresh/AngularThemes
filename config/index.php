<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$compile = false;

if(isset($_GET["color1"])) {
	$compile = true;
	$color1 = $_GET["color1"];
	$content = "@brand-primary:" . $color1 . ";";
	$content .= " @theme-primary:" . $color1 . ";";
}

if(isset($_GET["color2"])) {
	$color2 = $_GET["color2"];
	$content .= " @brand-success:" . $color2 . ";";
}

if(isset($_GET["font1"])) {
	$font1 = $_GET["font1"];
}

if(isset($_GET["font2"])) {
	$font2 = $_GET["font2"];
}

if(isset($_GET["theme"])) {
	$theme = $_GET["theme"];
}

// Write the contents to the file from HTTP post or retry
if(isset($request)) {

	//Create the static HTML file from HTTP post
	if(isset($request->theme)) {
		$file = 'deploy/' . $request->theme . '.html';
	}
	else {
		$file = 'static.html';
	}
	$contents = '<!DOCTYPE html><html>' . $request->html . '</html>';
	file_put_contents($file, $contents);

}
else if(isset($theme)){

	// create curl resource
    $ch = curl_init();

    // set url
    curl_setopt($ch, CURLOPT_URL, "http://sanstatic.com/theme/" . $theme . ".html");

    //return the transfer as a string
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // $output contains the output string
    $output = curl_exec($ch);

    // close curl resource to free up system resources
    curl_close($ch);   

}

// Compile the LESS from HTTP get
if($compile) {

	// Resources
	$variables = "less/" . $theme . "/variables.less";
	$input = "less/" . $theme . "/" . $theme . ".less";
	$output = "css/" . $theme . ".css";

	// Write the LESS variables
	$content .= " @gray: lighten(black, 50%); @gray-dark: lighten(black, 25%); @gray-light: #eee; @gray-lighter: lighten(#000, 93.5%); @theme-dark:#222; @white-faded: fade(white, 80%);";
	file_put_contents($variables, $content);

	// Compile the LESS to CSS
	require "lessphp/lessc.inc.php";
	$less = new lessc;

	$less->compileFile($input, $output);

}
else {

	$content = "No variables found.";

}


if($compile) header('Location: http://sanstatic.com/theme/config/deploy/' . $theme . ".html");

?>

<!-- JS -->
<script src="https://storage.googleapis.com/cdnsanstatic/js/angular.min.js"></script>
<script src="https://storage.googleapis.com/cdnsanstatic/js/contentful.min.js"></script>
<script src="http://sanstatic.com/site/js/angular-route.min.js"></script>

<!-- CSS -->
<link rel="stylesheet" href="https://storage.googleapis.com/cdnsanstatic/css/bootstrap.min.css" type="text/css">

<!-- Inline CSS -->
<style>
	form {
		width:300px;
    	margin-left:15px;
	}
	.right {
		float:right;
	}
</style>

<!-- deployApp -->
<div ng-app="deployApp" ng-controller="formCtrl">
	<div class="container">
		<div class="row">
	    	<div class="col-lg-4">
		    	<h3>Settings</h3>
			    <form novalidate action="index.php" method="get">
			    	<div class="row">
			    	<label>Brand</label>
				    	<span class="right">{{brand.name}}</span>
					</div>
					<div class="row">
					    <label>Space ID</label>
					    <span class="right">{{design.space}}</span>
					</div>
			    	<div class="row">
			    		<label>Theme</label>
				    	<select class="right" ng-model="theme" ng-options="t for t in themes"></select>
					</div>
			    	<div class="row">
					    <label>Primary Color</label>
					    <input class="right" type="text" ng-model="design.color1" name="color1"><br/>
					</div>
					<div class="row">
					    <label>Secondary Color</label>
					    <input class="right" type="text" ng-model="design.color2" name="color2"><br/>
				    </div>
					<div class="row">
					    <label>Heading Font</label>
					    <input class="right" type="text" ng-model="design.font1" name="font1"><br/>
				    </div>
					<div class="row">
				    	<label>Paragraph Font</label>
				    	<input class="right" type="text" ng-model="design.font2" name="font2"><br/>
			    	</div>
					<br/>
					<input name="theme" value="{{theme}}" type="hidden">
					<div class="row">
					    <button ng-click="reset()">RESET</button>
					    <button type="submit">DEPLOY</button>
					</div>
			   	</form>
		   </div>
		
			<div class="col-lg-6">
				<div class="row">
			    	<h3>Themes</h3>
			    	<ul>
			    		<li ng-repeat="t in themes"><a ng-href="deploy/{{t}}.html" target="_blank">{{t}}</a>
			    	</ul>
				</div>
				<!-- Debug
				    <div class="row">
		                <h3>Contentful Brand</h3>
		                <p class="text-muted">Primary Color: {{brand.primaryColor}}, Secondary Color: {{brand.secondaryColor}}, Heading Font: {{brand.primaryFont}}, Paragraph Font: {{design.font2}}, and BG: {{design.bg}}</p>
				    </div>
				    <div class="row">
		                <h3>Form Inputs</h3>
		                <p class="text-muted">Color1: {{design.color1}}, Color2: {{design.color2}}, Font1: {{design.font1}}, Font2: {{design.font2}}, and BG: {{design.bg}}</p>
				    </div>
				-->
				<div class="row">
			    	<h3>Resources</h3>
			    	<ul>
			    		<li><a href="https://www.google.com/design/spec/style/color.html" target="_blank">Google Colors</a></li>
			    		<li><a href="https://www.google.com/fonts" target="_blank">Google Fonts</a></li>
			    	</ul>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	// Angular deployApp module with formCtrl controller
	var app = angular.module('deployApp', []);

	app.controller('formCtrl', ['$scope', '$q', '$http', function($scope, $q, $http) {

	   // Contentul API Client
	   var client = contentful.createClient({
	      // ID of Space
	     space: 'bhbl6r0rag31',

	     // A valid access token within the Space 
	     accessToken: '9249ff3590642679bcf612864e139395ad456fdad79e3870b78f9221da8d4726',

	     // Enable or disable SSL. Enabled by default.
	     secure: true,

	     // Set an alternate hostname, default shown.
	     host: 'cdn.contentful.com',

	     // Resolve links to entries and assets
	     resolveLinks: true,

	   });

	   $scope.themes = ['blog','landing-page','freelancer','creative'];

	   $scope.reset = function() {
	   	   var entries = $q.when(client.entries({content_type: 'brand'}));

			entries.then(function(entries) {
		    	$scope.brand = entries[0].fields;
		    	$scope.design = new Array();
		    	$scope.design["color1"] = entries[0].fields.primaryColor;
		    	$scope.design["color2"] = entries[0].fields.secondaryColor;
		    	$scope.design["font1"] = entries[0].fields.primaryFont;
		    	$scope.design["font2"] = entries[0].fields.secondaryFont;
		    	$scope.design["space"] = entries[0].fields.space;
		    	$scope.theme = entries[0].fields.theme;
			});
	   };

		$scope.reset();

	}]);
</script>