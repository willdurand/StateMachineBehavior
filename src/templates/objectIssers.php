
<?php foreach ($issers as $isser) : ?>
/**
 * @return Boolean
 */
public function <?php echo $isser['methodName'] ?>()
{
	return null !== $this-><?php echo $stateColumnGetter ?>() && static::<?php echo $isser['constantName'] ?> === $this-><?php echo $stateColumnGetter ?>();
}

<?php endforeach; ?>
