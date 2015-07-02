<?php echo form_open_multipart('', ['id' => 'upload']); ?>
<div id="drop">
    <?php
        if (!empty($errorMessage)) {
            echo '
            <div class="error">
                ' . $errorMessage . '
            </div>';
        }
    ?>
    <input type="file" name="userfile" />
    <br>
</div>
