<h1>Error</h1>
<pre>
<?php print_r($error); ?>
</pre>
<?php
if (isset($incorrect)) {
    echo '<pre>';
    print_r($incorrect);
    echo '</pre>';
}
?>
