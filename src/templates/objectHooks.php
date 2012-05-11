
<?php foreach ($symbols as $symbol) : ?>
/**
 *
 */
public function pre<?php echo ucfirst($symbol) ?>(PropelPDO $con = null)
{
    return true;
}

/**
 *
 */
public function on<?php echo ucfirst($symbol) ?>(PropelPDO $con = null)
{
}

/**
 *
 */
public function post<?php echo ucfirst($symbol) ?>(PropelPDO $con = null)
{
}

<?php endforeach; ?>
