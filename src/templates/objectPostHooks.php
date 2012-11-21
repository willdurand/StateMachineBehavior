
switch ($this-><?php echo $stateColumnGetter ?>()) {
<?php foreach ($states as $stateConstant => $postHookMethodName) : ?>
    case <?php echo $objectClassName ?>::<?php echo $stateConstant ?>:
        $this-><?php echo $postHookMethodName ?>($con);

<?php endforeach; ?>
    default:
}
