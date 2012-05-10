
/**
 * @return string|null
 */
public function getHumanizedState()
{
	switch ($this-><?php echo $stateColumnGetter ?>()) {
<?php foreach ($humanizedStates as $state => $humanizedState) : ?>
		case self::STATE_<?php echo $state ?>:
			return '<?php echo $humanizedState ?>';

<?php endforeach; ?>
		default:
	}

	return null;
}
