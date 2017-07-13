<?php
if (!defined('ABSPATH')) exit;

libxml_use_internal_errors(true);

$preg_find = array('/\[.*?\]/', '#<script(.*?)>(.*?)</script>#is', '#<h1(.*?)>(.*?)</h1>#is');
$preg_rep = array('', '', '');
$tracked_kw = get_post_meta($post->ID, 'wsko_keywords', true);
$tracked_kw_groups = array();

$p_eff = 100;
$post_notes = array();
$kw_eff = 0;
$content = $post->post_content;
if (has_post_thumbnail($post->ID))
	$content .= "<div>" . get_the_post_thumbnail($post->ID) . "</div>";

$content = preg_replace($preg_find, $preg_rep, $content);

$con = wp_remote_get(get_permalink($post->ID));
$dom_w = new DOMDocument;
$dom_w->recover = true;
$dom_w->loadXML('<div>' . wp_remote_retrieve_body($con) . '</div>');

$h1s = $dom_w->getElementsByTagName('h1');
foreach ($h1s as $h1)
{
	$content .= '<h1>' . $h1->nodeValue . '</h1>';
}

$titles = $dom_w->getElementsByTagName('title');

$content_plain = strip_tags(html_entity_decode($content));
$meta_tags = get_meta_tags(get_permalink($post->ID));
$meta_title = isset($meta_tags['title']) ? $meta_tags['title'] : (isset($meta_tags['og:title']) ? $meta_tags['og:title'] : (isset($meta_tags['twitter:title']) ? $meta_tags['twitter:title'] : ($titles && $titles->length > 0 ? $titles->item(0)->nodeValue : '')));
$meta_desc = isset($meta_tags['description']) ? $meta_tags['description'] : (isset($meta_tags['og:description']) ? $meta_tags['og:description'] : (isset($meta_tags['twitter:description']) ? $meta_tags['twitter:description'] : ''));
if (!empty($tracked_kw))
{
	$kwid = 0;
	foreach ($tracked_kw as $kw => $kw_data)
	{
		$ga_row = false;
		foreach ($rows as $row)
		{
			if ($row->keys[0] == $kw)
			{
				$ga_row = $row;
				break;
			}
		}
		$data = wsko_check_keyword($post->ID, $kw, $kw_data['focus'], $content, $content_plain, $meta_title, $meta_desc);
		$tracked_kw[$kw] = array('focus' => $kw_data['focus'], 'data' => $data, 'ga_row' => $ga_row);
		if (isset($kw_data['group']))
		{
			if (!isset($tracked_kw_groups[$kw_data['group']]))
				$tracked_kw_groups[$kw_data['group']] = array('clicks' => 0, 'position' => 0, 'impressions' => 0, 'ctr' => 0, 'keywords' => array($kw => array('focus' => $kw_data['focus'], 'ga_row' => $ga_row, 'data' => $data)), 'keywords_raw' => array());
			else
				$tracked_kw_groups[$kw_data['group']]['keywords'][$kw] = $tracked_kw[$kw];
			
			if (isset($kw_data['group_main']) && $kw_data['group_main'])
			{
				$tracked_kw_groups[$kw_data['group']]['keywords'][$kw]['group_main'] = true;
			}
			
			array_push($tracked_kw_groups[$kw_data['group']]['keywords_raw'], $kw);
			
			unset($tracked_kw[$kw]);
			continue;
		}
		$kw_eff += $data->efficiency;
	}
	foreach ($tracked_kw_groups as $group => $kw_group)
	{
		$kw_foc = false;
		$kw_gr_eff = 0;
		$tracked_kw_groups[$group]['clicks'] = 0;
		$tracked_kw_groups[$group]['position'] = 0;
		$tracked_kw_groups[$group]['impressions'] = 0;
		$tracked_kw_groups[$group]['ctr'] = 0;
		$kws = $kw_group['keywords'];
		$title = '*Unranking Keyword Group';
		$max_clicks = -1;
		$hasMain = false;
		foreach ($kws as $key => $kw)
		{
			if ($kw['ga_row'])
			{
				$tracked_kw_groups[$group]['clicks'] += $kw['ga_row']->clicks;
				$tracked_kw_groups[$group]['position'] += $kw['ga_row']->position;
				$tracked_kw_groups[$group]['impressions'] += $kw['ga_row']->impressions;
				$tracked_kw_groups[$group]['ctr'] += $kw['ga_row']->ctr;
				if (!$hasMain)
				{
					if (isset($kw['group_main']) && $kw['group_main'])
					{
						$hasMain = true;
						$title = $key;
						$kw_foc = $kw['focus'];
					}
					else if ($kw['ga_row']->clicks > $max_clicks)
					{
						$max_clicks = $kw['ga_row']->clicks;
						$title = $key;
						$kw_foc = $kw['focus'];
					}
				}
			}
		}
		if (!$hasMain)
		{
			reset($kws);
			$title = key($kws);
			$kw_foc = $kws[$title]['focus'];
		}
		
		$kws_c = count($kws);
		$data = wsko_check_keyword($post->ID, $kw_group['keywords_raw'], $kw_foc, $content, $content_plain, $meta_title, $meta_desc);
		$tracked_kw_groups[$group]['position'] = $tracked_kw_groups[$group]['position'] / $kws_c;
		$tracked_kw_groups[$group]['ctr'] = $tracked_kw_groups[$group]['ctr'] / $kws_c;
		$tracked_kw_groups[$group]['title'] = $title;
		$tracked_kw_groups[$group]['data'] = $data;
		$tracked_kw_groups[$group]['focus'] = $kw_foc;
		
		$kw_eff += $data->efficiency;
	}
	$kw_eff = $kw_eff / (count($tracked_kw) + count($tracked_kw_groups));
}
else
{
	$kw_eff = 100;
}
$dom = new DOMDocument;
$dom->recover = true;
$isValid = $dom->loadXML('<div>' . $content . '</div>');

if (!$isValid)
{
	array_push($post_notes, array('type' => '0', 'msg' => 'Your Post contains errors!'));
}

$word_count = str_word_count($content_plain, 0, '1234567890');
$words_str = '-';
$words_class = 'danger';

if ($word_count < 300)
{
	$p_eff -= (80/100) * 100;
	$words_str = $word_count . " - Too Low! (Under 300)";
	$words_class = 'danger';
}
else if ($word_count < 500)
{
	$p_eff -= (40/100) * 100;
	$words_str = $word_count . " - Okay (Under 500)";
	$words_class = 'warning';
}
else if ($word_count < 1000)
{
	$p_eff -= (10/100) * 100;
	$words_str = $word_count . " - Good (Under 1000)";
	$words_class = 'success';
}
else if ($word_count < 1500)
{
	$words_str = $word_count . " - Perfect! (Under 1500)";
	$words_class = 'success';
}
else
{
	$words_str = "Perfect! (Over 1500)";
	$words_class = 'success';
}

if ($meta_title == '')//(!isset($meta_tags['title']) && !isset($meta_tags['og:title']) && !isset($meta_tags['twitter:title']))
{
	array_push($post_notes, array('type' => '0', 'msg' => 'Meta-Title not used.'));
}


$countImg = $dom->getElementsByTagName('img')->length;
if ($countImg == 0)
{
	if (!has_post_thumbnail($post->ID))
	{
		array_push($post_notes, array('type' => '0', 'msg' => 'No Images.'));
	}
}
else
{
	$xpath = new DOMXPath($dom);
	$imgAltMiss = $xpath->query("//img[not(@alt) or @alt='']")->length;
	if ($imgAltMiss > 0)
	{
		array_push($post_notes, array('type' => '1', 'msg' => $imgAltMiss . ' Img-Tag(s) missing Alt-Tags!'));
	}
}

$countH1 = $dom->getElementsByTagName('h1')->length;
if ($countH1 > 1)
{
	array_push($post_notes, array('type' => '1', 'msg' => 'Too Many H1 (' . $countH1 . ').'));
}
else if ($countH1 == 0)
{
	array_push($post_notes, array('type' => '0', 'msg' => 'No H1.'));
}

$countH2 = $dom->getElementsByTagName('h2')->length;
if ($countH2 == 0)
{
	array_push($post_notes, array('type' => '0', 'msg' => 'No H2.'));
}

$countEm = $dom->getElementsByTagName('i')->length +  $dom->getElementsByTagName('em')->length + $dom->getElementsByTagName('b')->length + $dom->getElementsByTagName('strong')->length;
if ($countEm == 0)
{
	array_push($post_notes, array('type' => '0', 'msg' => 'No Bold- or Italic-Tags!'));
}

$base = wsko_get_host_base();
$post_uri = str_replace($base, '', get_permalink($post->ID));
$urlSeg = explode('/', $post_uri);
$urlWor = 0;
foreach ($urlSeg as $seg)
{
	$words = count(preg_split( "/(-|_)/", $seg));
	if ($words > $urlWor)
		$urlWor = $words;
}

/*if (count($urlSeg) > 5)
{
	$p_eff -= (1/20) * 100;
	array_push($post_notes, array('type' => '0', 'msg' => 'More than 5 URL Segments!'));
}*/

if ($urlWor > 5)
{
	$p_eff -= (20/100) * 100;
	array_push($post_notes, array('type' => '0', 'msg' => 'More than 5 Words in an URL-Part!'));
}

$eff = empty($tracked_kw) && empty($tracked_kw_groups) ? $p_eff : ($kw_eff * 0.8 + $p_eff * 0.2);

if ($eff >= 80)
{
	$eff_class = 'wsko_green';
}
else if ($eff >= 50)
{
	$eff_class = 'wsko_yellow';
}
else
{
	$eff_class = 'wsko_red';
}
$kw_count = count($rows);
?>
<div>
	<ul class="nav nav-tabs wsko_main_nav wsko_nav">
		<li class="active"><a data-toggle="tab" href="#wsko_keywords_view">Keywords</a></li>
		<li><a data-toggle="tab" href="#wsko_suggest_view">Suggest</a></li>
	</ul>
	
	<div class="tab-content">
		<div id="wsko_keywords_view" class="tab-pane fade in active">
		
			<div class="wsko_single_area_wrapper wsko_onpage_status" style="border:0px;">
				<label class="wsko_label" style="margin: 0px;">Onpage Score</label>
				<p style="padding:8px;" class="wsko_post_status"><span class="<?=$eff_class?>"><?=round($eff, 2)?> %</span></p>
				
				<?php /* Ranking Keywords: <?=count($rows)?>
				Sum Clicks: <?=$sum_clicks?>
				Sum Impressions: <?=$sum_imp?> */ ?>
				
				<hr class="wsko_hr" style="margin: 5px 0px !important;">
				<div class="row" style="padding:0px 10px">
						<div class="col-md-4" style="padding:5px">
								<i class="fa fa-key wsko_icon" data-toggle="tooltip" title="Ranking Keywords" aria-hidden="true"></i> <?=$kw_count == WSKO_KEYWORD_LIMIT_POST ? '>' : ''?><?=$kw_count?> <?=$kw_count == WSKO_KEYWORD_LIMIT_POST ? '<a target="_blank" href="' . admin_url('admin.php?page=wsko_settings_view') . '" title="Limit set in settings is reached. Click to increase your keyword limit (Content Optimizer)." data-toggle="tooltip"><i class="fa fa-caret-square-o-up wsko-fa-settings-link"></i></a>': ''?>
						</div>
						<div class="col-md-4" style="padding:5px">
								<i class="fa fa-mouse-pointer wsko_icon" data-toggle="tooltip" title="<?=$sum_clicks_kw?> Clicks based on <?=$kw_count?> Keywords" aria-hidden="true"></i> <?=$sum_clicks?>
						</div>
						<div class="col-md-4" style="padding:5px">
								<i class="fa fa-eye wsko_icon" data-toggle="tooltip" title="<?=$sum_imp_kw?> Impressions based on <?=$kw_count?> Keywords" aria-hidden="true"></i> <?=$sum_imp?>
						</div>
				</div>
				<span class="font-unimportant">From: <?=date('d/m/Y', $time)?> to: <?=date('d/m/Y', $time2)?></span>
				<?php /*
				<div class="wsko_progress_wrapper">
				<div class="progress wsko_progress">
				  <div class="progress-bar wsko_green" role="progressbar" aria-valuenow="<?=$eff?>"
				  aria-valuemin="0" aria-valuemax="100" style="width:<?=$eff?>%; background-image:none;">
					<span class="sr-only"><?=$eff?></span>
				  </div>
				</div>
				</div>
				*/ ?>
			</div>
			
			<div class="wsko_single_area_wrapper">
				<label class="wsko_label">General</label>
				<ul>
					<li class="bs-callout bs-callout-<?=$words_class?>">Page Length: <?=$words_str?></li>
					<?php
					foreach ($post_notes as $note)
					{
						?><li class="bs-callout bs-callout-<?=$note['type'] == '1' ? 'warning' : 'danger'?>"><?=$note['msg']?></li><?php
					}
					?>
				</ul>
			</div>
		
		
			<div class="wsko_single_area_wrapper" style="border-bottom:none;">
				<div style="margin-bottom:10px;">	
					<label class="wsko_label" style="vertical-align: sub; margin: 5px 0px;">Keywords</label>
					<a id="wsko_select_keywords_btn" class="btn preview button" href="#">Select Keywords</a>
				</div>
				
				<ul id="wsko_post_keywords_details_list">
					<?php
					if (!empty($tracked_kw) || !empty($tracked_kw_groups))
					{
						$kwid = 0;
						foreach ($tracked_kw_groups as $gr => $kw_obj)
						{
							$kw = $kw_obj['title'];
							
							$group = true;
							include($wsko_path. '/templates/template-post-keywords.php');
							$kwid++;
						}
						
						foreach ($tracked_kw as $kw => $kw_obj)
						{
							$group = false;
							include($wsko_path. '/templates/template-post-keywords.php');
							$kwid++;
						}
					}
					else
					{
						?><li>No Keywords tracked.</li><?php
					}
					?>
				</ul>
			</div>
		</div>

		<div id="wsko_suggest_view" class="tab-pane fade in">
			<div class="wsko_suggest_wrapper" style="padding: 10px 0px;">
			<label class="wsko_label">Google Suggest Keyword Tool</label>
			<div class="wsko_single_area_wrapper" style="border-bottom:0px;padding:5px 5px 15px 5px; height:50px; display:flex;">
				<input id="wsko_suggest_kw" style="float:left;" class="form-control" placeholder="Insert Keyword" list="wsko_suggest_kw_data" autocomplete="off"/>
				<div id="wsko_suggest_loading" style="display:none;">
					<i style="padding:10px" class="fa fa-spinner fa-pulse"></i>
				</div>
				<datalist id="wsko_suggest_kw_data"></datalist>
			</div>
			</div>
		</div>
	</div>
</div>