<?php
session_start(); 
if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])!='xmlhttprequest') {sleep(2);exit;} // ajax request
if(!isset($_POST['unox']) || $_POST['unox']!=$_SESSION['unox']) {sleep(2);exit;} // appel depuis uno.php
?>
<?php
include('../../config.php');
include('lang/lang.php');
$q = file_get_contents('../../data/busy.json'); $a = json_decode($q,true); $Ubusy = $a['nom'];
// ********************* actions *************************************************************************
if (isset($_POST['action']))
	{
	switch ($_POST['action'])
		{
		// ********************************************************************************************
		case 'plugin': 
			if(is_dir('../../template/tem2uno/')) f_rmdirR('../../template/tem2uno/'); ?>
		<div class="blocForm">
			<h2><?php echo _("Theme to Uno");?></h2>
			<p><?php echo _("This plugin is used to transform a theme from a CMS for a use in CMSUno.");?></p>
			<p><?php echo _("Simply download the ZIP file of the theme and double click on it. Click Analyze and choose the specific template. The theme will be available in config tab.");?></p>
			<p>
				<a href="http://get-simple.info/extend/all_themes.php" target="_blank">GetSimple</a>&nbsp;-&nbsp;
				<a href="http://skins.b2evolution.net/" target="_blank">b2evolution</a>
			</p>
			<p><?php echo _("Some adjustments to the html file may be necessary to stick perfectly to your needs.");?></p>
			<h3><?php echo _("Create Template :");?></h3>
			<table class="hForm">
				<tr>
					<td><label><?php echo _("Origin");?></label></td>
					<td>
						<select id="tem2unoCms" name="tem2unoCms">
							<option value="gs">GetSimple</option>
							<option value="b2">b2evolution</option>
						</select>
					</td>
					<td><em><?php echo _("Select the origin of the ZIP file from CMS proposed list.");?></em></td>
				</tr>
				<tr>
					<td><label><?php echo _("Template (.zip)");?></label></td>
					<td>
						<input type="text" class="input" name="tem2unoZip" id="tem2unoZip" value="" />
						<div class="bouton" style="margin-left:30px;" id="bFTem2uno" onClick="f_finder_select('tem2unoZip')" title="<?php echo _("File manager");?>"><?php echo _("File Manager");?></div>
					</td>
					<td><div class="bouton fr" onClick="f_analyze_tem2uno();" title="<?php echo _("Analyze Template");?>"><?php echo _("Analyze");?></div></td>
				</tr>
			</table>
			<div id="anaTem2uno" style="display:none;">
				<h3><?php echo _("Select a template :");?></h3>
				<ul id="listTem2uno"></ul>
			</div>
			<hr />
			<h3><?php echo _("Remove Template :");?></h3>
			<table id="outTem2uno">
				<?php f_theme_tem2uno($Ubusy); ?>
			</table>

			<div class="clear"></div>
		</div>
		<?php break;
		// ********************************************************************************************
		case 'supp':
		if(isset($_POST['s']))
			{
			if(f_rmdirR('../../template/'.$_POST['s'])) echo _('Deletion made');
			else echo '!'._('Impossible deletion');
			}
		else echo '!'._('Error');
		break;
		// ********************************************************************************************
		case 'analyze':
		if(isset($_POST['z']) && isset($_POST['o']))
			{
			$d = '../../../files'; $b = 0; $n = ''; $out = ''; $o = $_POST['o'];
			$e = explode("/",$_POST['z']);
			foreach($e as $r)
				{
				if($b) $d .= '/' . $r;
				if($r=='files') $b = 1;
				$n = $r;
				}
			$n = substr($n,0,-4);
			$zip = new ZipArchive;
			$f = $zip->open($d);
			if ($f===true)
				{
				// 1. Extract ZIP => tem2uno
				$d = '../../template/tem2uno/';
				if(!is_dir($d)) mkdir($d);
				$zip->extractTo($d);
				$zip->close();
				// 2. Template folder : $d
				if($o=='gs')
					{
					if(!file_exists($d.'/template.php'))
						{
						$h=opendir($d);
						while(($f=readdir($h))!==false)
							{
							if(is_dir($d.$f) && $f!='.' && $f!='..' && file_exists($d.$f.'/template.php'))
								{
								$d .= $f;
								break;
								}
							}
						closedir($h);
						}
					}
				if($o=='b2')
					{
					if(!file_exists($d.'/index.main.php'))
						{
						$h=opendir($d);
						while(($f=readdir($h))!==false)
							{
							if(is_dir($d.$f) && $f!='.' && $f!='..' && file_exists($d.$f.'/index.main.php'))
								{
								$d .= $f;
								break;
								}
							}
						closedir($h);
						}
					}
				// 3. List php files
				$h = opendir($d);
				while(($f=readdir($h))!==false)
					{
					$ext=explode('.',$f);
					$ext=$ext[count($ext)-1];
					if($ext=='php' && $f!='.' && $f!='..') $out .= '<li><a href="javascript:void(0);" onClick="f_create_tem2uno(\''.$f.'\',\''.$n.'\',\''.$d.'\',\''.$o.'\')">'.$f.'</a></li>';
					}
				closedir($h);
				echo $out;
				}
			else echo '!'._('Not a ZIP file');
			}
		else echo '!'._('Error');
		break;
		// ********************************************************************************************
		case 'create':
		if(isset($_POST['f']) && isset($_POST['n']) && isset($_POST['d']) && isset($_POST['o']))
			{
			$out = ''; $f = $_POST['f']; $n = $_POST['n']; $d = $_POST['d']; $o = $_POST['o'];
			$p = file_get_contents($d.'/'.$f);
			if($o=='gs') $out .= f_createGS_tem2uno($p,$d);
			if($o=='b2') $out .= f_createB2_tem2uno($p,$d);
			// 3. Copy and create file
			if(file_put_contents($d.'/template.html', $out))
				{
				f_copyR($d,'../../template/'.$n);
				echo $n;
				if(is_dir('../../template/tem2uno/')) f_rmdirR('../../template/tem2uno/');
				}
			else echo '!'._('Not a ZIP file');
			}
		else echo '!'._('Error');
		break;
		// ********************************************************************************************
		}
	clearstatcache();
	exit;
	}
//
function f_theme_tem2uno($Ubusy)
	{
	// liste des themes
	$q = file_get_contents('../../data/'.$Ubusy.'/site.json'); $a = json_decode($q,true); $tem = $a['tem'];
	$t = "../../template/";
	$d = opendir($t);
	if($d) while(($f = readdir($d))!==false)
		{
		if(is_dir($t.$f) && file_exists($t.$f.'/template.html') && $f!="." && $f!="..")
			{
			if($f==$tem) echo '<tr id="'.$f.'"><td>'.$f.'</td><td style="padding-left:10px;">'._('Selected').'</td></tr>';
			else if(stristr($f,'uno')!==false) echo '<tr id="'.$f.'"><td>'.$f.'</td><td></td></tr>';
			else echo '<tr id="'.$f.'"><td>'.$f.'</td><td style="background-image:url(uno/includes/img/close.png);background-position:center center;background-repeat:no-repeat;cursor:pointer;width:30px;padding-left:10px;" onClick="f_supp_tem2uno(\''.$f.'\')">&nbsp;</td></tr>';
			}
		}
	closedir($d);
	}
function f_rmdirR($dir)
	{
	$files = array_diff(scandir($dir), array('.','..'));
	foreach ($files as $file)
		{
		(is_dir("$dir/$file")) ? f_rmdirR("$dir/$file") : unlink("$dir/$file");
		}
	return rmdir($dir);
	}
function f_copyR($src,$dst)
	{ 
	$dir = opendir($src); 
	@mkdir($dst); 
	while(false!==($file=readdir($dir)))
		{ 
		if($file!='.'&&$file!='..')
			{ 
			if(is_dir($src.'/'.$file)) f_copyR($src . '/' . $file, $dst . '/' . $file); 
			else copy($src.'/'.$file, $dst.'/'.$file); 
			} 
		} 
	closedir($dir); 
	} 
function f_createGS_tem2uno($p,$d)
	{
	// $p : content from php file
	// $d : php file path
	$out = '';
	$q = explode('<?php',$p);
	foreach($q as $k=>$r)
		{
		$r1 = explode('?>',$r);
		if(isset($r1[1]))
			{
			if(strpos($r1[0],'get_theme_url(')!==false)
				{
				$out .= '[[template]]';
				if(substr($r1[1],0,1)=='/') $r1[1] = substr($r1[1],1);
				}
			if(strpos($r1[0],'get_navigation(')!==false)
				{
				$c = strripos($out,"<ul");
				$out = substr($out,0,$c) . '[[menu]]';
				$c = stripos($r1[1],"</ul>");
				if($c!==false) $r1[1] = substr($r1[1],$c+5);
				}
			if(strpos($r1[0],'get_header(')!==false) $out .= '<meta name="description" content="[[description]]" />'."\r\n".'[[head]]';
			if(strpos($r1[0],'get_site_url(')!==false) $out .= '[[url]]/[[name]].html';
			if(strpos($r1[0],'get_site_name(')!==false) $out .= '[[title]]';
			if(strpos($r1[0],'get_page_clean_title(')!==false) $out .= '[[title]]';
			if(strpos($r1[0],'get_page_slug(')!==false) $out .= '[[name]]';
			if(strpos($r1[0],'get_page_content(')!==false) $out .= '[[content]]';
			if(strpos($r1[0],'get_footer(')!==false) $out .= '[[foot]]';
			if(strpos($r1[0],'include')!==false)
				{
				$r2 = explode('include',$r1[0]); $a = ''; $c = 0;
				if(isset($r2[1])) for($v=0;$v<strlen($r2[1]);++$v)
					{
					if($c!==0 && substr($r2[1],$v,1)!='"' && substr($r2[1],$v,1)!="'") $a .= (substr($r2[1],$v,1));
					else if($c===0 && (substr($r2[1],$v,1)=='"' || substr($r2[1],$v,1)=="'")) $c = "";
					else if(substr($r2[1],$v,1)=='"' || substr($r2[1],$v,1)=="'") break;
					}
				if(file_exists($d.'/'.$a))
					{
					$p = file_get_contents($d.'/'.$a);
					$out .= f_createGS_tem2uno($p,$d);
					}
				}
			$out .= $r1[1];
			}
		else $out .= $r;
		}
	return $out;
	}
function f_createB2_tem2uno($p,$d)
	{
	// $p : content from php file
	// $d : php file path
	$out = '';
	$q = explode('<?php',$p);
	foreach($q as $k=>$r)
		{
		$r .= '?>';
		$r1 = explode('?>',$r);
		if(isset($r1[1]))
			{
			if(strpos($r1[0],'get_theme_url(')!==false)
				{
				$out .= '[[template]]';
				if(substr($r1[1],0,1)=='/') $r1[1] = substr($r1[1],1);
				}
			if(strpos($r1[0],'Menu')!==false)
				{
				$c = strripos($out,"<ul");
				$out = substr($out,0,$c) . '[[menu]]';
				$c = stripos($r1[1],"</ul>");
				if($c!==false) $r1[1] = substr($r1[1],$c+5);
				}
			if(strpos($r1[0],'require_css(')!==false)
				{
				$r2 = explode('require_css(',$r1[0]); $a = ''; $c = 0;
				if(isset($r2[1])) for($v=1;$v<count($r2);++$v)
					{
					if(strpos($r2[$v],'relative')!==false && strpos($r2[$v],'relative')<100)
						{
						$b = 0; $c = '';
						for($w=0;$w<strpos($r2[$v],'relative');++$w)
							{
							if($b==0 && (substr($r2[$v],$w,1)=='"' || substr($r2[$v],$w,1)=="'")) $b = 1;
							else if($b==1 && (substr($r2[$v],$w,1)=='"' || substr($r2[$v],$w,1)=="'")) break;
							else if($b==1) $c .= substr($r2[$v],$w,1);
							}
						$out .= '<link rel="stylesheet" href="[[template]]'.$c.'" />'."\r\n";
						}
					}
				}
			if(strpos($r1[0],'permanent_url(')!==false) $out .= '[[url]]/[[name]].html';
			if(strpos($r1[1],'</body>')!==false) $out .= '[[foot]]'."\r\n".'</body>';
			if(strpos($r1[0],'content_teaser(')!==false || strpos($r1[0],'content_teaser (')!==false) $out .= '[[content]]';
			if(strpos($r1[0],'skin_include(')!==false)
				{
				$r2 = explode('skin_include(',$r1[0]); $a = ''; $c = 0;
				if(isset($r2[1])) for($v=0;$v<strlen($r2[1]);++$v)
					{
					if($c!==0 && substr($r2[1],$v,1)!='"' && substr($r2[1],$v,1)!="'") $a .= (substr($r2[1],$v,1));
					else if($c===0 && (substr($r2[1],$v,1)=='"' || substr($r2[1],$v,1)=="'")) $c = "";
					else if(substr($r2[1],$v,1)=='"' || substr($r2[1],$v,1)=="'") break;
					}
				if(file_exists($d.'/'.$a))
					{
					$p = file_get_contents($d.'/'.$a);
					$out .= f_createB2_tem2uno($p,$d);
					}
				else if($a=='_body_header.inc.php' || $a=='_html_header.inc.php')
					{
					$out = '</head>'."\r\n".$out;
					if(file_exists($d.'/_skin.class.php'))
						{
						$p = file_get_contents($d.'/_skin.class.php');
						$out = f_createB2_tem2uno($p,$d).$out;
						}
					if(file_exists($d.'/style.css') && strpos($out,'[[template]]style.css')===false) $out = '<link rel="stylesheet" href="[[template]]style.css" />'."\r\n".$out;
					$out = '<!DOCTYPE html>'."\r\n".'<html>'."\r\n".'<head>'."\r\n".'<title>[[title]]</title>'."\r\n".'<meta name="description" content="[[description]]" />'."\r\n".'[[head]]'."\r\n".$out;
					}
				else if($a=='_item_content.inc.php' && strpos($out,'[[content]]')===false) $out .= '<div class="content_full">[[content]]</div>';
				else if($a=='$disp$' && strpos($out,'[[content]]')===false) $out .= '<div class="content_full">[[content]]</div>';
				else if($a=='_body_footer.inc.php' || $a=='_html_footer.inc.php') $out .= '</div>'."\r\n".'[[foot]]'."\r\n".'</body>'."\r\n".'</html>';
				}
			$out .= $r1[1];
			}
		else if(strpos($r,'<?php')!==false) $out .= $r;
		}
	// Specific
	$out = str_replace('href="style.css"', 'href="[[template]]style.css"', $out);
	// Return
	return $out;
	}
?>
