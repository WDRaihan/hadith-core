jQuery(function ($) {
    "use strict";
    
    jQuery(document).ready(function () {
        $('#hadith_reports').DataTable();
    });

    jQuery('.hadith_report_status').on('change',function(){
        
        var status = jQuery(this).val();
        var report_id = jQuery(this).attr('report-id');
        
        var url = hadith_obj.api_url + 'report/';

        var xhr = new XMLHttpRequest();
        xhr.open("POST", url);

        xhr.setRequestHeader("Content-Type", "application/json");
        xhr.setRequestHeader( 'X-WP-Nonce', hadith_obj.api_nonce );

        xhr.onreadystatechange = function () {
           if (xhr.readyState === 4) {
              console.log(xhr.status);
              console.log(xhr.responseText);
              alert(JSON.parse(xhr.responseText).message);
           }};

        var data = '{"partial":"true","status":"'+status+'","report_id":"'+report_id+'"}';

        xhr.send(data);

    });
    
});