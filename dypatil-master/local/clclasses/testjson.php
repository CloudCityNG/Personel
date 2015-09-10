
<script src= "//code.jquery.com/jquery-1.9.1.min.js" ></script>
<script src= "//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js" ></script>

<script>
$(document).ready(function() {
    $('#example').dataTable( {
        "ajax": 'jsoncontent.php'
    } );
} );



</script>
<?php






  echo '<table id="example" class="display" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Name</th>
                <th>Position</th>
                <th>Office</th>
               
            </tr>
        </thead>
 
    
    </table>';









?>