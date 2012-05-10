
<?php foreach ($states as $state) : ?>
/**
 *
 */
public function pre<?php ucfirst($state) ?>(PropelPDO $con = null)
{
    return true;
}

/**
 *
 */
public function on<?php ucfirst($state) ?>(PropelPDO $con = null)
{
}

/**
 *
 */
public function post<?php ucfirst($state) ?>(PropelPDO $con = null)
{
}

<?php endforeach; ?>
