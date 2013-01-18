<?php
function displayAjaxFunctionTiming($function, $tempo=2000, $param = array())
{
    $id=uniqid();
    $info = array('actionajax' => 'displayAjaxFunction', 'phpfunction' =>  $function );
    $data = array_merge($info,$param);
    ?>
    <div id="<?php echo $id ?>">
    
    </div>
    <script language="javascript">
        var data<?php echo $id ?>= <?php echo json_encode($data); ?>;
        var el<?php echo $id ?> = document.getElementById('<?php echo $id?>');
        displayAjaxFunction(el<?php echo $id ?>,data<?php echo $id ?>);
        setInterval(function() {displayAjaxFunction(el<?php echo $id ?>,data<?php echo $id ?>);}, <?php echo $tempo ?>)
    </script>
    <?php
}