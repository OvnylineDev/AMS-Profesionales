var namefile = "";

$(document).ready(function() {
     setTimeout ('test()', 500);

     // $('#filecam').on('change', function(e) {
     // 	var files = e.target.files;
     // 	for (var i = 0; i < files.length; i++) {
     // 		var reader = new FileReader();
     //
     // 		namefile = files[i].name;
     //
     // 		reader.onload = function(e) {
     // 			forceDownload(e.target.result, namefile);
     // 		}
     //
     // 		reader.readAsDataURL(files[i]);
     // 	}
     // });
});

$(document).on("change", '#filecam', function(e) {
     var files = e.target.files;
     for (var i = 0; i < files.length; i++) {
          var reader = new FileReader();

          namefile = files[i].name;

          reader.onload = function(e) {
               forceDownload(e.target.result, namefile);
          }

          reader.readAsDataURL(files[i]);
     }
});

function forceDownload(href, name) {
     var anchor = document.createElement('a');
     anchor.href = href;
     anchor.download = name;
     document.body.appendChild(anchor);
     anchor.click();
     $("#back").trigger( "click" );
}

function goBack() {
   window.history.back();
}

function test(){
     alert("test");
     $("#filecam").trigger( "click" );
}
