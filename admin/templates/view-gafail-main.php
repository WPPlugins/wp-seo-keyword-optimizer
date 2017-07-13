<?php
if (!defined('ABSPATH')) exit;

$err_str = $error->getMessage();
$err = json_decode($err_str);
$unknown = false;
if ($err)
{
	if (is_object($err) && isset($err->error->errors))
	{
		?><p style="font-weight:bold;">Google Error:</p>
		
		<?php
		$err_c = count($err->error->errors);
		if ($err_c == 0)
		{
			$unknown = $err_str;
		}
		else
		{
			$err = $err->error->errors[0];
			?><?=$err->message?><?php 
			if ($err_c > 1)
			{
				?><?=($err_c - 1)?> more errors...<br/><br/><?php
				foreach ($err->errors as $e)
				{
					?><?=$e->message?><br/><?php
				}
			}
		}
		
	}
	else
	{
		$unknown = $err_str;
	}
}

if ($unknown)
{
	?>
	Unknown Error with Google API. Please contact support.<br/><br/>
	
	Message: <?=$unknown?>
	<?php
}
?>