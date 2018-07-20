<h1>Post Meta Index</h1>
<table>
    <thead>
        <tr>
            <td>Meta ID</td>
            <td>Meta Value</td>
        </tr>
    </thead>
    <tbody>
    <?php
        foreach ($data->all() as $meta) {
            echo '<tr><td>' . $meta->meta_id . '</td><td>' . $meta->meta_value . '</td></tr>';
        }
    ?>
    </tbody>
</table>