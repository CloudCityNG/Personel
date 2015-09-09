<html>
    <script src="media/js/jquery-1.9.1.js" type="text/javascript"></script>
    <script src="media/js/jquery-migrate-1.2.1.js" type="text/javascript"></script>
    <script src="dtjs/jquery.dataTables.min.js" type="text/javascript"></script>
    <script src="dtjs/jquery.jeditable.js" type="text/javascript"></script>
    <script src="dtjs/jquery-ui.js" type="text/javascript"></script>
    <script src="dtjs/jquery.validate.js" type="text/javascript"></script>
    <script src="dtjs/jquery.dataTables.editable.js" type="text/javascript"></script>
    <script language="javascript" type="text/javascript">
        $(document).ready(function () {
            $('#myDataTable').dataTable().makeEditable({
                sDeleteURL: "deletedata.php"
            });
        });
    </script>
    <div class="add_delete_toolbar" />

    <!-- Custom form for adding new records -->
    <form id="formAddNewRow" action="#" title="Add new record">
        <label for="engine">Rendering engine</label><br />
        <input type="text" name="engine" id="name" class="required" rel="0" />
        <br />
        <label for="browser">Browser</label><br />
        <input type="text" name="browser" id="browser" rel="1" />
        <br />
        <label for="platforms">Platform(s)</label><br />
        <textarea name="platforms" id="platforms" rel="2"></textarea>
        <br />
        <label for="version">Engine version</label><br />
        <select name="version" id="version" rel="3">
            <option>1.5</option>
            <option>1.7</option>
            <option>1.8</option>
        </select>
        <br />
        <label for="grade">CSS grade</label><br />
        <input type="radio" name="grade" value="A" rel="4"> First<br>
        <input type="radio" name="grade" value="B" rel="4"> Second<br>
        <input type="radio" name="grade" value="C" checked rel="4"> Third
        <br />
    </form>

    <button id="btnDeleteRow">Delete</button>
    <table id="myDataTable">
        <thead>
            <tr>
                <th>Company name</th>
                <th>Address</th>
                <th>Town</th>
            </tr>
        </thead>
        <tbody>
            <tr id="17">
                <td>Emkay Entertainments</td>
                <td>Nobel House, Regent Centre</td>
                <td>Lothian</td>
            </tr>
            <tr id="18">
                <td>The Empire</td>
                <td>Milton Keynes Leisure Plaza</td>
                <td>Buckinghamshire</td>
            </tr>
            <tr id="19">
                <td>Asadul Ltd</td>
                <td>Hophouse</td>
                <td>Essex</td>
            </tr>
            <tr id="21">
                <td>Ashley Mark Publishing Company</td>
                <td>1-2 Vance Court</td>
                <td>Tyne &amp; Wear</td>
            </tr>
        </tbody>
    </table>

</html>