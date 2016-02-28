// AngularJS Application
var app = angular.module( "app", ['ngRoute'] );

// Contentul API Client
var client = contentful.createClient({
  
  // ID of Space
  space: '4sukcz8ms49e',

  // A valid access token within the Space
  accessToken: '807e586395e27121d7604f1be565c98a1bb444a36711ddf89fc1e7c74d812c8e',

  // Enable or disable SSL. Enabled by default.
  secure: true,

  // Set an alternate hostname, default shown.
  host: 'cdn.contentful.com',

  // Resolve links to entries and assets
  resolveLinks: true,

});

// Contenful Controller
app.controller('ContentfulCtrl', ['$scope', '$q', '$http', '$routeParams', '$location', function($scope, $q, $http, $routeParams, $location) {

  // View Model
  var vm = this;

  // Contentful Entries
  var entries = $q.when(client.entries({include:2}));

  vm.sections = Array();
  vm.menu = Array();

  entries.then(function(entries) {

    entries.forEach(function(entry) {

      switch(entry.sys.contentType.sys.id) {
        case "aside":
          vm.aside = entry.fields;
          break;

        case "brand":
          vm.brand = entry.fields;
          break;

        case "button":
          vm.menu.push(entry.fields);
          break;

        case "design":
          vm.design = entry.fields;
          break;

        case "footer":
          vm.footer = entry.fields;
          break;

        case "header":
          vm.header = entry.fields;
          break;

        case "navigation":
          vm.nav = entry.fields;
          break;

        case "section":
          vm.sections.push(entry.fields);
          break;

        default:
          break;
      } 

    });

    angular.element(document).ready(function () {

      // Remove Angular includes
      var body = document.getElementsByTagName('body')[0];
      var includes = document.getElementById("includes");
      console.log(includes.innerHTML);
      body.removeChild(includes);

      // Get template name
      var theme = $location.absUrl().split(".")[1];
      theme = theme.split("/")[2];

      // Post to themes/config/index.php
      var data = {
          'html' : document.getElementsByTagName('html')[0].innerHTML,
          'theme' : theme,
      };

      $http.post("http://sanstatic.com/themes/config/index.php", data).success(function(data, status) {
        console.log('HTTP Post Completed');
      });
      
    });

  });

}]);

app.filter("getFont", function() {

  //convert + to space
  return function(input){
    if(input) return input.replace(/\s+/g," "); 
  }

});