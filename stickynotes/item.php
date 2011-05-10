<?php
$output = '
<li id="stk-note-'.$data['id'].'">
	<div class="stk-header">
		<div class="stk-title" onclick="Panels.expand(this)">
			'.$data['title'].'
';

	if($data['isOrder'] == 1)
		$output .= '<span>('.$data['user'].')</span>';
	
	if($data['panel'] == 'todo' && $data['isNew'] == 1)
		$output .= '<img src="../assets/templates/manager/stickynotes/img/new.png" alt="new" />';

$output .= '
	</div>
';

	if($data['finished'] == 0 && $data['isOrder'] == 0)
		$output .= '<span class="stk-icons" title="Finished" onclick="Sticky.move.finish('.$data['id'].')"></span>';
	if($data['isOrder'] != 1)
		$output .= '<span class="stk-icons" title="Edit" onclick="Sticky.form.show('.$data['id'].')"></span>';

$output .= '
			<span class="stk-progress" title="' .$data['progress']. '%" style="width:' .$data['progress']. '%"></span>
		<div class="clr"></div>
	</div>
	<div class="stk-content">
		<div class="stk-extra">
			<div class="stk-comment">' .$data['comment']. '</div>
			<div class="clr"></div>
			<div class="stk-footnote"><span class="stk-icons" title="Created On"></span>' .$data['createdon']. '</div>
';

	if($data['finished'] == 1)
		$output .= '<div class="stk-footnote"><span class="stk-icons" title="Finished On"></span>' .$data['finishedon']. '</div>';
	elseif($data['must_be_finishedon'] != 0)
		$output .= '<div class="stk-footnote"><span class="stk-icons" title="Should Be Done"></span>' .$data['must_be_finishedon']. '</div>';

$output .= '
			<div class="stk-footnote" style="float:right">
';

	if($data['archived'] == 0 && $data['isOrder'] == 0)
		$output .= '<span class="stk-icons" title="Archive it" onclick="Sticky.move.archive('.$data['id'].')"></span>';
	if($data['canDelete'] == 1)
		$output .= '<span class="stk-icons" title="Delete" onclick="Sticky.move.delete('.$data['id'].')"></span>';

$output .= '
			</div>
			<div class="clr"></div>
		</div>
	</div>
	<div class="clr"></div>
</li>
';

return $output;
?>