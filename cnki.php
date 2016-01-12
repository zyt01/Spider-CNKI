
<?php
/*****************************************************/

include("simple_html_dom.php");
include("conn/conn.php");

/*****************************************************/

$times = 5;

/*****************************************************/

$url = Array(
		"start" => 'http://www.cnki.net/KCMS/detail/detail.aspx?dbcode=CMFD&dbname=CMFD201301&filename=1013121221.nh',
		"uid" => "",
		"domain" => "http://www.cnki.net",
		"endomain" => "http://lks.cnki.net/index.html",
		"kcms" => "kcms",
		"detail" => "detail",
		"frame" => "frame",
		"detailfile" => "detail.aspx",
		"listfile" => "detaillist.aspx"
	);

/*****************************************************/

$reftype = Array(
		"reference" => 1,
		"similarity" => 7,
		"focus" => 8
	);

/*****************************************************/

$htmldom = Array(
		"titleid" => "#chTitle",
		"entitleid" => "#entitle",
		"authorclass" => ".KnowledgeNetLink",
		"abstractid" => "#ChDivSummary",
		"keywordid" => "#ChDivKeyWord",
		"listv" => "#listv",
		"zwjdown" => ".zwjdown",
		"zwjRightRow" => ".zwjRightRow",
		"entable" => "table[bgcolor=#f1f1f1]",
		"strContext" => ".strContext",
		"filenameid" => "#filename",
		"tablenameid" => "#tablename"
	);

/*****************************************************/

$htmlcode = new simple_html_dom();

$curlmulti = curl_multi_init();

/*****************************************************/

function merge_spaces ($string)
{
    return preg_replace("/\s(?=\s)/", "\\1", $string);
}
function merge_ques ($string)
{
    return str_replace('&amp;nbsp;', '', $string);
}
function html_encode ($string)
{
    return str_replace('"', '\'', htmlspecialchars($string));
}

/*****************************************************/

function CreateHtml ($urllist){
	$curlobj = curl_init();
	curl_setopt($curlobj, CURLOPT_URL, $urllist);
	curl_setopt($curlobj, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curlobj, CURLOPT_HEADER, 0);
	curl_setopt($curlobj, CURLOPT_FOLLOWLOCATION, 1);

	//curl_multi_add_handle ($GLOBALS['curlmulti'], $curlobj);

	// $active = null;
	// do {   
	// 	$mrc = curl_multi_exec($GLOBALS['curlmulti'], $active);
	// } while ($active);
	//} while ($mrc == CURLM_CALL_MULTI_PERFORM);
	//$mrc = curl_multi_exec($GLOBALS['curlmulti'], $active);

	//$outputhtml = curl_multi_getcontent($curlobj);
	// while ($active && $mrc == curlm_ok) {
	//     if (curl_multi_select($globals['curlmulti']) != -1) {
	//         do {
	//         	$outputhtml = curl_multi_getcontent($curlobj);
	//             $mrc = curl_multi_exec($globals['curlmulti'], $active);
	//         } while ($mrc == curlm_call_multi_perform);
	//     }
	// }

	$outputhtml=curl_exec($curlobj);

	//curl_multi_remove_handle($GLOBALS['curlmulti'],$curlobj); 
	curl_close($curlobj);

	//$htmlcode = new simple_html_dom();
	//$htmlcode->load_file($urllist);
	$GLOBALS['htmlcode'] = str_get_html($outputhtml);
	unset($curlobj, $outputhtml);
	return $GLOBALS['htmlcode'];
}

function GetArticleElement ($htmlcode, $key){
	if(null !== ($articleelement = $htmlcode->find($key, 0))) return $articleelement;
	else {
		unset($articleelement);
		return null;
	}
}
function GetArticleElements ($htmlcode, $key){
	if(null !== ($articleelements = $htmlcode->find($key))) return $articleelements;
	else {
		unset($articleelements);
		return null;
	}
}
function GetArticleElementsNum ($htmlcode, $key){
	if(null !== ($articleelements = $htmlcode->find($key))) return count($articleelements);
	else {
		unset($articleelements);
		return 0;
	}
}
function GetArticleText ($htmlcode, $key){
	if(null !== ($articleelement = $htmlcode->find($key, 0))) {
		if(isset($articleelement->plaintext)) return $articleelement->plaintext;
		else {
			unset($articleelement);
			return null;
		}
	}
	else {
		unset($articleelement);
		return null;
	}
}
function GetArticleIndexText ($htmlcode, $key, $index){
	if(null !== ($articleelement = $htmlcode->find($key, $index))) {
		if(isset($articleelement->plaintext)) return $articleelement->plaintext;
		else {
			unset($articleelement);
			return null;
		}
	}
	else {
		unset($articleelement);
		return null;
	}
}
function GetArticleValue ($htmlcode, $key){
	if(null !== ($articleelement = $htmlcode->find($key, 0))) {
		if(isset($articleelement->value)) return $articleelement->value;
		else return null;
	}
	else return null;
}
function GetArticleHref ($htmlcode, $key){
	if(null !== ($articleelement = $htmlcode->find($key, 0))) {
		if(isset($articleelement->href)) return $articleelement->href;
		else return null;
	}
	else return null;
}
function GetListUrl ($url_domain, $url_kcms, $url_detail, $url_frame, $url_listfile, $url_filename, $dbcode, $reftype, $listv){
	return $url_domain."/".$url_kcms."/".$url_detail."/".$url_frame."/".$url_listfile."?filename=".$url_filename."&dbcode=".$dbcode."&reftype=".$reftype."&vl=".$listv;
}
// function GetArticleUrls ($url_domain, $htmlcode, $key){
// 	if($articleurl = $htmlcode->find($key, 0)->find("a")) return $articleurl;
// 	else return null;
// }


function RunListApp ($urllist, $from, $times){
	global $htmldom;
	$htmlcode = CreateHtml($urllist);
	if(GetArticleElement($htmlcode, $htmldom["zwjRightRow"]) == null){
		unset($htmlcode);
		return 0;
	}
	else {
		$templi = GetArticleElements(GetArticleElement($htmlcode, $htmldom["zwjRightRow"]), 'li');
		if($templi !== null){
			foreach ($templi as $key => $li) {
		 		$alink = GetArticleElement($li, 'a');
		 		if ($alink !== null) {
		 			global $url;
	 				if (isset($alink->href)) {
	 					$articlehref = $alink->href;
	 					if (preg_match('/filename=([^&]+)/', $articlehref, $match)) {
							$articlename = count($match) > 1 ? $match[1] : '';
						}
						else if (preg_match('/FILENAME=([^&]+)/', $articlehref, $match)) {
							$articlename = count($match) > 1 ? $match[1] : '';
						}
						else $articlename = '';
	 					$slink_information = Array(
							'fromname'=>$articlename,
							'toname'=>$from
						);
						if(mysql_query('insert into `slink` (`fromname`, `toname`) values ("'.$slink_information['fromname'].'", "'.$slink_information['toname'].'");'))
			    			echo "<a>".$articlename." ===> ".$from."</a>".$times."\n";
						else echo(mysql_error());
	 					$temparticleurl = htmlspecialchars_decode($url["domain"].$articlehref);
	 					//echo $temparticleurl."<hr>";
	 					unset($templi, $alink, $articlehref, $key, $li, $articlename, $match,$slink_information);
	 					RunArticleApp($temparticleurl, $from, $times);
	 					unset($temparticleurl);
	 				}
	 				else if (isset($alink->onclick)) {
	 					$articleclick = htmlspecialchars_decode($alink->onclick);
	 					if (preg_match('/title=([^&]+)&sid=([^&]+)&aufirst=([^&]+)/', $articleclick, $match)) {
							$articletitle = count($match) > 3 ? $match[1] : '';
							$articlesid = count($match) > 3 ? $match[2] : '';
							$articleauthor = count($match) > 3 ? $match[3] : '';
						}
						else if (preg_match('/title=([^&]+)&sid=([^&]+)/', $articleclick, $match)) {
							$articletitle = count($match) > 2 ? $match[1] : '';
							$articlesid = count($match) > 2 ? $match[2] : '';
							$articleauthor = '';
						}
						else if (preg_match('/title=([^&]+)/', $articleclick, $match)) {
							$articletitle = count($match) > 1 ? $match[1] : '';
							$articlesid = '';
							$articleauthor = '';
						}
						else {
							$articletitle = '';
							$articlesid = '';
							$articleauthor = '';
						}
						$slink_information = Array(
							'fromname'=>$articletitle,
							'toname'=>$from
						);
						if(mysql_query('insert ignore into `slink` (`fromname`, `toname`) values ("'.$slink_information['fromname'].'", "'.$slink_information['toname'].'");'))
			    			echo "<a>".$articletitle." ===> ".$from."</a>".$times."\n";
						else echo(mysql_error());
	 					$temparticleurl = $url["endomain"]."?title=".$articletitle."&sid=".$articlesid."&aufirst=".$articleauthor;
	 					unset($templi, $alink, $match1, $match2, $articleclick, $articlesid, $articletitle, $key, $li, $slink_information);
	 					RunArticleApp($temparticleurl, $from, $times);
	 					unset($temparticleurl);
	 				}
	 				else {
		 				unset($templi, $alink, $key, $li);
					}
		 		}
		 		else {
		 			unset($templi, $alink, $key, $li);
				}
			}
		}
		else {
			unset($templi);
		}
		$htmlcode->clear();
		unset($htmlcode);
		return 0;
	}
}


function RunArticleApp ($urllist, $from, $times){
	$htmlcode = CreateHtml($urllist);
	//echo $htmlcode;
	global $htmldom;
	$article_information = Array(
		'title'=>'',
		'author'=>'',
		'abstract'=>'',
		'filename'=>'',
		'dbcode'=>'',
		'keywords'=>'',
		'sid'=>''
	);
	if(($article_information['title'] = GetArticleText($htmlcode, $htmldom["titleid"])) !== null && $times >= 0){
		$article_information['author'] = GetArticleText($htmlcode, $htmldom["authorclass"]);
		$article_information['abstract'] = html_encode(GetArticleText($htmlcode, $htmldom["abstractid"]));
		$article_information['keywords'] = merge_spaces(html_encode(GetArticleText($htmlcode, $htmldom["keywordid"])));
		
		$listrullocation = GetArticleElement($htmlcode, $htmldom["zwjdown"]);
		if ($listrullocation !== null) {
			$temprul = htmlspecialchars_decode(GetArticleHref($listrullocation, 'a'));
			if ($temprul !== null && $times >= 0) {
				preg_match('/filename=([^&]+)&dbcode=([^&]+)&/', $temprul, $match);
				$article_information['filename'] = count($match) > 2 ? $match[1] : '';
				$article_information['dbcode'] = count($match) > 2 ? $match[2] : '';
				if(mysql_query('insert ignore into `articles` (`title`, `author`, `abstract`, `keywords`, `filename`, `dbcode`, `type`, `href`, `toname`) values ("'.$article_information['title'].'", "'.$article_information['author'].'", "'.$article_information['abstract'].'", "'.$article_information['keywords'].'", "'.$article_information['filename'].'", "'.$article_information['dbcode'].'", "'.'1'.'", "'.html_encode($urllist).'", "'.$from.'");'))
		    		echo "<a href=".$urllist.">".$article_information['title']."</a> <strong>".$times."</strong> \n";
				else echo(mysql_error());
				if ($article_information['filename'] != '' && $times > 0) {
					global $url, $reftype;
					$listv = GetArticleValue($htmlcode, $htmldom["listv"]);
					$list_url = GetListUrl ($url["domain"], $url["kcms"], $url["detail"], $url["frame"], $url["listfile"], $article_information['filename'], $article_information['dbcode'], $reftype["reference"], $listv);
					unset($listv, $temprul, $listrullocation, $match);
					RunListApp($list_url, $article_information['filename'], $times-1);
					unset($list_url, $listv);
				}
				else {
					unset($listrullocation, $temprul, $match);
				}
			}
			else {
				unset($listrullocation, $temprul);
			}
		}
		else {
			unset($listrullocation);
		}
	}
	else if(($article_information['title'] = GetArticleText($htmlcode, $htmldom["entitleid"])) !== null){
		$article_information['filename'] = GetArticleValue($htmlcode, $htmldom["filenameid"]);
		$article_information['dbcode'] = GetArticleValue($htmlcode, $htmldom["tablenameid"]);
		$article_information['author'] = merge_spaces(merge_ques(html_encode(GetArticleIndexText($htmlcode, $htmldom["strContext"], 0))));
		if(GetArticleElementsNum($htmlcode, $htmldom["strContext"]) == 7) {
			$article_information['abstract'] = merge_spaces(merge_ques(html_encode(GetArticleIndexText($htmlcode, $htmldom["strContext"], 6))));
			$article_information['keywords'] = merge_spaces(merge_ques(html_encode(GetArticleIndexText($htmlcode, $htmldom["strContext"], 5))));
		}
		else if(GetArticleElementsNum($htmlcode, $htmldom["strContext"]) == 6){
			$article_information['abstract'] = merge_spaces(merge_ques(html_encode(GetArticleIndexText($htmlcode, $htmldom["strContext"], 5))));
		}
		
		if(mysql_query('insert ignore into `articles` (`title`, `author`, `abstract`, `keywords`, `filename`, `dbcode`, `type`, `href`, `toname`) values ("'.$article_information['title'].'", "'.$article_information['author'].'", "'.$article_information['abstract'].'", "'.$article_information['keywords'].'", "'.$article_information['filename'].'", "'.$article_information['dbcode'].'", "'.'2'.'", "'.html_encode($urllist).'", "'.$from.'");'))
			echo "<a href=".$urllist.">".$article_information['title']."</a> <strong>".$times."</strong> \n";
		else die(mysql_error());
	}
	else if (preg_match('/title=([^&]+)&sid=([^&]+)&aufirst=([^&]+)/', $urllist, $match)) {
		$article_information['title'] = count($match) > 3 ? html_encode($match[1]) : '';
		$article_information['sid'] = count($match) > 3 ? html_encode($match[2]) : '';
		$article_information['author'] = count($match) > 3 ? html_encode($match[3]) : '';
		if(mysql_query('insert ignore into `articles` (`title`, `author`, `sid`, `type`, `href`, `toname`) values ("'.$article_information['title'].'", "'.$article_information['author'].'", "'.$article_information['sid'].'", "'.'3'.'", "'.html_encode($urllist).'", "'.$from.'");'))
			echo "<a href=".$urllist.">".$article_information['title']."</a> <strong>".$times."</strong> \n";
		else die(mysql_error());
		unset($match);
	}
	else {
		unset($match);
	}
	// else if(($table = GetArticleElement($htmlcode, $htmldom["entable"])) !== null && $times >= 0) {
	// 	$trs = GetArticleElements($table, 'tr');
	// 	if($trs !== null) {
	// 		if(null !== ($article_information['author'] = $trs[0]->last_child()->plaintext));
	// 		//echo $article_information['author']."<hr>";
	// 		if(null !== ($article_information['title'] = $trs[1]->last_child()->plaintext));
	// 		if(mysql_query('insert into articles (title) values ("'.$article_information['title'].'")'))
	// 			echo "<a href=".$urllist.">".$article_information['title']."</a> <strong>".$times."</strong> \n";
	// 	}
	// 	else {
	// 		unset($table, $trs);
	// 	}
	// }
	// else {
	// 	unset($table);
	// }
	$htmlcode->clear();
	unset($htmlcode, $article_information);
	return 0;
}


/*****************************************************/


$str_file_url = file_get_contents("in.txt");
$arr_url = preg_split("/\n/", $str_file_url);


/*****************************************************/


for ($i=0; $i < count($arr_url); $i++) { 
	RunArticleApp($arr_url[$i], 0, $times);
}

curl_multi_close($curlmulti);

unset($htmlcode, $curlmulti, $htmldom, $reftype, $url, $times);



//echo '<script>var id1 = document.getElementById("id1");id1.click();</script>';




?>