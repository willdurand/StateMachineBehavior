$isStateColumnModified = $this->isColumnModified(<?php echo $stateColumn ?>);
<?php if ($timestampable): ?>
if ($isStateColumnModified) {
    switch ($this-><?php echo $stateColumnGetter ?>()) {
<?php foreach ($timestampColumnSetters as $stateConstant => $timestampColumnSetter) : ?>
        case static::<?php echo $stateConstant ?>:
            $this-><?php echo $timestampColumnSetter ?>(time());
            break;
<?php endforeach; ?>
    }
}
<?php endif; ?>
