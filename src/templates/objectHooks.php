
<?php foreach ($symbols as $symbol) : ?>
/**
 *
 */
public function <?php echo $symbol['pre'] ?>(PropelPDO $con = null)
{
    return true;
}

/**
 *
 */
public function <?php echo $symbol['on'] ?>(PropelPDO $con = null)
{
}

/**
 *
 */
public function <?php echo $symbol['post'] ?>(PropelPDO $con = null)
{
}

<?php endforeach; ?>
