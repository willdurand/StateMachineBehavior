
switch ($this-><?php echo $stateColumnGetter ?>()) {
<?php foreach ($states as $stateConstant => $postHookMethodName) : ?>
    case static::<?php echo $stateConstant ?>:
        $this-><?php echo $postHookMethodName ?>($con);

<?php endforeach; ?>
    default:
}
