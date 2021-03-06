<?php
	require_once("discuss_func.inc.php");
	require_once("oj-header.php");
	$sql="SELECT `title`, `cid`, `pid`, `status`, `top_level` FROM `topic` WHERE `tid` = '".mysql_real_escape_string($_REQUEST['tid'])."' AND `status` <= 1";
	$result=mysql_query($sql) or die("Error! ".mysql_error());
	$rows_cnt = mysql_num_rows($result) or die("Error! ".mysql_error());
	$row= mysql_fetch_object($result);
	$thread_status = $row->status;
	$isadmin = isset($_SESSION['administrator']);
?>
<div> 
<a class="button green" href="#">New Thread</a>
<?php if ($isadmin) { ?>
	<div style="float:right"> Set Sticky/Top level to<?php $adminurl = "threadadmin.php?target=thread&tid={$_REQUEST['tid']}&action=";
	if ($row->top_level == 0) 
		echo "<a class='button green' href=\"{$adminurl}sticky&level=3\">Top</a><a class='button green' href=\"{$adminurl}sticky&level=2\">Top-</a><a class='button green' href=\"{$adminurl}sticky&level=1\">Top--</a>"; 
	else 
		echo "<a class='button green' href=\"{$adminurl}sticky&level=0\">Standard</a>";
?> | <?php 
	if ($row->status != 1) 
		echo ("<a class='button orange' href=\"{$adminurl}lock\">Lock</a>"); 
	else 
		echo("<a class='button orange' href=\"{$adminurl}resume\">Unlock</a>");

	echo ("<a class='button red' href=\"{$adminurl}delete\">Delete</a>");
	?></div>
<?php } ?>
<br/>
<br/>
<table class="table">
<thead>
	<td width="4%">
		<a href="discuss.php
		<?php 
			if ($row->pid!=0 && $row->cid!=null) 
				echo "?pid=".$row->pid."&cid=".$row->cid;
			else if ($row->pid!=0) 
				echo"?pid=".$row->pid; 
			else if ($row->cid!=null) 
				echo"?cid=".$row->cid;
			echo "\">";
			if ($row->pid!=0) 
				echo "Prob.".$row->pid;
			else echo "MainBoard";
		?>
		</a>
	</td>
	<td width="96%"><big><?php echo nl2br(htmlspecialchars($row->title));?></big></td>
</thead>
<?php
	$sql="SELECT `rid`, `author_id`, `time`, `content`, `status` FROM `reply` WHERE `topic_id` = '".mysql_real_escape_string($_REQUEST['tid'])."' AND `status` <=1 ORDER BY `rid` LIMIT 30";
	$result=mysql_query($sql) or die("Error! ".mysql_error());
	$rows_cnt = mysql_num_rows($result);
	$cnt=0;

	for ($i=0;$i<$rows_cnt;$i++){
		mysql_data_seek($result,$i);
		$row=mysql_fetch_object($result);
		$url = "threadadmin.php?target=reply&rid={$row->rid}&tid={$_REQUEST['tid']}&action=";
		if (isset($_SESSION['user_id']))
			$isuser = strtolower($row->author_id)==strtolower($_SESSION['user_id']);
		else
			$isuser = false;
?>
<tr align=center class='<?php echo ($cnt=!$cnt)?'even':'odd';?>row'>
	<td>
		<img src="<?php echo GetGravatarUrl($row->author_id);?>"/>
	</td>
	<td>
		<a name=post<?php echo $row->rid;?>></a>
     <div style="display:inline;text-align:left; float:left; margin:0 10px"><a href="../userinfo.php?user=<?php echo $row->author_id?>"><?php echo $row->author_id; ?> </a> @ <?php echo $row->time; ?></div>
		<div class="mon" style="display:inline;text-align:right; float:right">
			<?php if (isset($_SESSION['administrator'])) {?>  
			<a class="button orange small" href="
				<?php 
				if ($row->status==0) echo $url."disable\">Lock";
				else echo $url."resume\">Unlock";
				?> 
			</a>
			<?php } ?>
			
			<?php if ($isuser || $isadmin) { ?>
				<span>[ <a href="#">Edit</a> ]</span>
				<a class='button red small' href="<?php echo $url;?>delete">Delete</a>
			<?php } ?>
			
			<span style="width:5em;text-align:right;display:inline-block;font-weight:bold;margin:0 10px">
			<?php echo $i+1;?>#</span>
		</div>
		<div class=content style="text-align:left; clear:both; margin:10px 30px">
			<?php	if ($row->status == 0) echo nl2br($row->content);//htmlspecialchars removed
					else {
						if (!$isuser || $isadmin)echo "<div class='alert error'><strong>提示信息：</strong>本内容以被管理员锁定，如果您是回帖人，请修改内容后联系管理员恢复或自行删除本回复。</div>";
						if ($isuser || $isadmin) echo nl2br($row->content);//htmlspecialchars removed
					}
			?>
		</div>
		<div style="text-align:left; clear:both; margin:10px 30px; font-weight:bold; color:red"></div>
	</td>
</tr>
<?php
	}
?>
</table>
<div style="font-size:100%; width:100%; text-align:center">[<a href="#">Top</a>]  [<a href="#">Previous Page</a>]  [<a href="#">Next Page</a>] </div>
<?php if ((isset($_SESSION['user_id'])) && ($thread_status != 1)){?>
<div style="margin:0 10px">New Reply:</div>
<form action="post.php?action=reply" method=post>
	<input type=hidden name=tid value=<?php echo $_REQUEST['tid'];?>>
	<textarea name=content style="width:700px; height:200px;"></textarea>
	<br/><input class="button blue" value="Reply" type="submit"/>
	<br/>
</form>
<?php } 
	if ($thread_status == 1) {
?>
<div class="alert">
	<strong> 【提示信息】 </strong>本帖已被锁定，您不能发表回复。
</div>
<?php } 
	if (!isset($_SESSION['user_id'])) { 
?>
<div class="alert">
	<strong> 【提示信息】 </strong>您必须登陆以发表回复。
</div>
<?php } ?>
</div>
<script type="text/javascript">
	window.onload=prettyPrint();
</script>
<?php require_once("discuss-footer.php"); ?>