<?php
  /*
  Plugin Name: WP flash img show
  Plugin URI: http://xwjie.com/post/wp-flash-img-show.html
  Version: 1.3
  Author: Tojary
  Author URI: http://xwjie.com
  Description: wp-flash-img-show is a FLASH Image Slide plugin for WordPress. You can show your articles , photo,goods,product and other ad. or introduction.Just enjoy it.  [Chinese ver.]: 这是一个flash图片幻灯片轮换wordpress插件，你可以利用它展示热门日志、艺术图片、商品、产品。通过改变用户设置，还可以用来做图片广告、宣传标语等等。请发挥创意。

  */
?>
<?php
  /*
	V1.3 Build 2010-11-24
  */
?>
<?php
		if ( strlen($_POST['config']) == 0 )
		{
			$config_name = "default";	
		}
		else
		{
			$config_name = $_POST['config'];
		}



// Language 
	$dr_locale = get_locale();
	$dr_mofile = dirname(__FILE__) . "/languages/wp-flash-img-show-$dr_locale.mo";
	load_textdomain('wp-flash-img-show', $dr_mofile);

	
//转换旧数据	
if ( strlen(get_option("wp_flash_img_show_pic_number")) != 0 )
	{
	translate_old_ver() ;
	}

//第一次使用 The First Time
if(!get_option("wp_flash_img_show")){	//如果是第一次使用，把沙发排名数据写入数据库
 new_config_initialize("default");  //DEBUG  

}


// 恢复默认设置
if($_POST['set_wp_flash_img_show_default_option']){ 
	
 $config_name = $_POST['config']; //获得配置名
 new_config_initialize($config_name);//DEBUG  

}

	
//设置页面主函数
function wp_flash_img_show_options()
{
	
	  
	
	
	//获得配置名
	if ( strlen($_POST['config']) == 0 )
		{
			$config_name = "default";	
		}
		else
		{
			$config_name = $_POST['config'];
		}

	if($_POST['del_config'])  //删除Config
	{ 
		$message='Delete config : '.$config_name;
		if ( $config_name !=  "default" ) {
		$dele_array = get_option("wp_flash_img_show") ; 
		unset($dele_array[$config_name]); // 删除config元素
		update_option("wp_flash_img_show",$dele_array);
		
			// file path
			$wp_flash_img_filename = "wp-flash-img-show" ;
			if((defined('WP_ALLOW_MULTISITE') && WP_ALLOW_MULTISITE) || (function_exists('is_multisite') && is_multisite()))  
			{
			global $blog_id;
			$wp_flash_img_filename =   "wp-flash-img-show-ms".$blog_id ;
			}
			if ($config_name == "default" ) 
			{ $config_name = ".xml"; }
			else
			{ $config_name = "-".$config_name.".xml" ; }
			$wp_flash_img_xml_path = dirname(__FILE__) ."/". $wp_flash_img_filename . $config_name ;
 
		if( is_file( $wp_flash_img_xml_path ) )
		{
		unlink($wp_flash_img_xml_path) ;
		}
		
		$config_name = "default";
		}
		else
		{$message="Can not Delete config : ".$config_name;}
	}

	if($_POST['change_config'])  //编辑 Config
	{ 
		$message='Edit config : '.$config_name ;
	}

	if($_POST['create_config'])  //新建 Config
	{ 
		$message='Create a New Config : '.$config_name ;
	}
 
	if($_POST['update_wp_flash_img_show_option'])  //开始保存数据到数据库
	{ 
	
	$message='Settings saved . Enjoy it !';

	// 全局设置数组
	$wp_flash_img_show_array = get_option("wp_flash_img_show") ;
 
	// Save Munber
	$wp_flash_img_show_array[$config_name]["pic_number"] =  $_POST['wp_flash_img_show_pic_number_option']; //save number	
	$wp_flash_img_show_array[$config_name]["autogetimg"] = $_POST['wp_flash_img_show_autogetimg_option']; //save how to get img 

// if not auto get img
if ($wp_flash_img_show_array[$config_name]["autogetimg"] != "frompost" )
{	
	// Save IMG   
	$store_pic_array = array();
	for ($i=1; $i<= $wp_flash_img_show_array[$config_name]["pic_number"] ; $i++) {				
 
		$url_option = "wp_flash_img_show_".$i."_url_option"; 
		$link_option = "wp_flash_img_show_".$i."_link_option"; 
		$description_option = "wp_flash_img_show_".$i."_description_option"; 
		
		$each_pic_array=array();
		$each_pic_array["url"]=$_POST[$url_option];
		$each_pic_array["link"]=$_POST[$link_option];
		$each_pic_array["description"]=$_POST[$description_option];

		$store_pic_array[$i]= $each_pic_array;

	}
		$wp_flash_img_show_array[$config_name]["pic"] =  $store_pic_array ; 
}  


	//save option 			
	$store_option_array = array();
	$option_names = array("width","height","roundcorner","autoplaytime","isheightquality","windowopen","btnsetmargin","btndistance","titlebgcolor","titlebgalpha","titletextcolor","titlefont","titlemoveduration","btnalpha","btntextcolor","btndefaultcolor","btnhovercolor","btnfocuscolor","changimagemode","isshowbtn","isshowtitle","scalemode","transform","isshowabout");
	$option_number = count($option_names) - 1;
	for ($i=0; $i<= $option_number ; $i++) 
		{	
		$itemnames = "wp_flash_img_show_".$option_names[$i] ;
		$itemnames_option = "wp_flash_img_show_".$option_names[$i]."_option";
		$$itemnames = $_POST[$itemnames_option];
	$store_option_array[$itemnames] = $$itemnames ;
		}
		
	$wp_flash_img_show_array[$config_name]["option"] =  $store_option_array ;
	  // $wp_flash_img_show_array = array(); //debug 清空所有设置
	 update_option("wp_flash_img_show",$wp_flash_img_show_array);
 

	wp_flash_img_show_save_to_xml($config_name);	//更新 XML
	
	
if ($wp_flash_img_show_array[$config_name]["autogetimg"] == "frompost" )
{
// Auto Get IMG
$message = get_img_from_post($config_name) ." , ". $message  ;
}		

	} //保存完毕

 
		if ( $message ==  'Save failed' ){
		echo '<div class="error"><strong><p>'.$message.'</p></strong></div>';
		}
		elseif ( strlen($message) >  0 ) {
		echo '<div class="updated"><strong><p>'.$message.'</p></strong></div>';
		};


		
// ===================================================	
$thisurl = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
  echo  '<link rel="stylesheet" media="screen" type="text/css" href="'.$thisurl.'css/admin-layout.css" /> ';
  echo  ' <script type="text/javascript" src="'.$thisurl.'js/layout.js?ver=1.0.2"></script>';
?>
<div class=wrap>
 <?php global $blog_id; echo "<!-- DEBUG \n config_name: \n".$config_name ."\n _POST['config']: \n". $_POST['config'] ."\n blogid: \n".$blog_id." \n  -->" ; ?>
		<h2>WP flash img show Setting</h2>
		<fieldset name="wp_basic_options"  class="options">
 <?php
 	$thisurl = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
	echo '<script src="'. $thisurl  .'swfobject.js" type="text/javascript"></script>' ;
	echo "\n" ;
 ?>
<h3 id="Preview"><?php _e('Preview','wp-flash-img-show'); ?>		</h3>
<?php if (function_exists('wp_flash_img_show')) {wp_flash_img_show($config_name);} ?> 		
<br />

<span id="getcodebutt" onclick="showcode()" style="cursor:pointer;color:#21759B;text-decoration:underline;"><?php _e('Get code','wp-flash-img-show'); ?> </span>&nbsp;|&nbsp;
<a target="_blank" href="http://www.google.com/support/accounts/bin/answer.py?answer=32050">
<?php _e("If there's no change,clear your Browser's Cache and try again.",'wp-flash-img-show'); ?>
</a>
	<div id="this_config_code" style="display: none;background:#FFFFaa;width:650px">
 <script type="text/javascript">
function showcode()
{
var sty = document.getElementById('this_config_code').style.display;
if  (sty=='none') document.getElementById('this_config_code').style.display='block';
if  (sty=='block') document.getElementById('this_config_code').style.display='none';
}
 
function showcolor(e)
{ }
</script> 
		<b style="color:#ff0000">HTML</b> (<?php _e('Config Name','wp-flash-img-show'); ?>:<font color="#FF0000"> <?php echo $config_name ; ?></font> ) :<br />
		<span id="htmlcode"><?php highlight_string('<div id="wp_flash_img_show_here_'.$config_name.'">wp_flash_img_show will display  here</div>') ?></span> 
		<br />
		<b style="color:#ff0000">PHP</b> (<?php _e('Config Name','wp-flash-img-show'); ?>:<font color="#FF0000"> <?php echo $config_name ; ?></font> ) :<br />
		<span id="phpcode"><?php highlight_string("<?php if (function_exists('wp_flash_img_show')) {wp_flash_img_show('".$config_name."');} ?>") ?></span> 
	</div>
<?php
$wp_flash_img_show_array = get_option("wp_flash_img_show");
 
// 对于新Config的处理
 if ( strlen( $wp_flash_img_show_array[$config_name]["pic_number"] ) == 0 )  
{
  new_config_initialize($config_name) ; //DEBUG   
  $wp_flash_img_show_array = get_option("wp_flash_img_show");
} 
 
$pic_number = $wp_flash_img_show_array[$config_name]["pic_number"];
 
?>

 

<h3><?php _e('Manage Config','wp-flash-img-show'); ?></h3>
<table>
<tr>
<td width="150" >

<form method="post" action="" name="f1" id="f1" >
<?php _e('Choose a Config','wp-flash-img-show'); ?>
</td><td>
<select  name="config" id="select_config" >
<?php
foreach ($wp_flash_img_show_array as $key => $theconfig )
{
?>
<option value="<?php echo $key ; ?>" <?php if ( $config_name == $key  ) { echo 'selected="true"' ; } ?> ><?php echo $key ; ?> </option>
<?php
}
?>
</select>
 
<input  type="submit"  name="change_config" value="<?php _e('Edit this Config','wp-flash-img-show'); ?>"   />
 <input  type="submit"  name="del_config" value="<?php _e('Delete Config','wp-flash-img-show'); ?>"   onclick="return  checkupthis()"  />
		<script language="javascript">
			function checkupthis()
			{ 
			var obj=document.getElementById("select_config").value;
			if (
			window.confirm("<?php _e('Delete This Config','wp-flash-img-show'); ?> : [ "+obj+" ]")
			)
				{ return true; }
				else
				{ return false; }
			}
		</script>
</form>
</td>
</tr>

<tr>
<td>
<form method="post" action="" name="f2" id="f2" >
<?php _e('New Config Name','wp-flash-img-show'); ?>
</td>
<td>
<input  type="text" name="config" value="" size="19"  />
 
<input  type="submit"  name="create_config" value="<?php _e('Create a New Config','wp-flash-img-show'); ?>"   />
</form>

</td>
</tr>
<tr><td colspan="3" >
<?php _e('Config Name Only consist of letters,numbers','wp-flash-img-show'); ?>
</td> </tr>
</table>

 
<h3><?php _e('Basic Settings','wp-flash-img-show'); ?> (<?php _e('Config Name','wp-flash-img-show'); ?>:<font color="#FF0000"> <?php echo $config_name ; ?></font> )</h3>

<form method="post" action="">
<table>
			<tr>
                <td valign="top" align="right"><?php _e('Img item number','wp-flash-img-show'); ?>:</td>
				<td>
				<input size="3" type="text" name="wp_flash_img_show_pic_number_option" value="<?php echo $pic_number ;  ?>" /> 
				&nbsp; <input type="checkbox" name="wp_flash_img_show_autogetimg_option"  value="frompost"  <?php if ($wp_flash_img_show_array[$config_name]["autogetimg"]=="frompost") echo 'checked="checked"'; ?> ><?php _e('Get images from Recent post automatic.','wp-flash-img-show'); ?>
				</td>
		</tr>
</table>	
<table>
<tr>
<td align="center">No.</td>
<td align="center"><?php _e('Image URL','wp-flash-img-show'); ?>
 ( <a href='media-new.php' target='_blank' tabindex="1"><?php _e('Upload Inages','wp-flash-img-show'); ?></a> )
</td>
<td align="center"><?php _e('Link','wp-flash-img-show'); ?></td>
<td align="center"><?php _e('description','wp-flash-img-show'); ?></td>
</tr>
<?php

$pic_array =  $wp_flash_img_show_array[$config_name]["pic"];
for ($i=1; $i<= $pic_number; $i++) {

?>
<tr><td><?php echo $i ?></td>
<td><input size="50" type="text" name="wp_flash_img_show_<?php echo $i ?>_url_option" value="<?php echo $pic_array[$i][url];  ?>" /> </td>
<td><input type="text" name="wp_flash_img_show_<?php echo $i ?>_link_option" value="<?php echo $pic_array[$i][link];  ?>" /> </td>
<td><input type="text" name="wp_flash_img_show_<?php echo $i ?>_description_option" value="<?php echo $pic_array[$i][description];  ?>" /> </td>
</tr>
<?php
}
?>	
</table>

 
 
<?php _e('Image URL is not allow cross-domain , it must begin with:','wp-flash-img-show'); 
echo " http://".getdomain(home_url('/'));
?>
<br />
<span style="color:#f00">
<?php _e('Do Not upload your image file to "wp-content/plugins/wp_flash_img_show/images/" ,or you may lose any custom image files when update this plugin.','wp-flash-img-show'); ?>
</span>
<br /> 


<h3><?php _e('Display option','wp-flash-img-show'); ?> (<?php _e('Config Name','wp-flash-img-show'); ?>:<font color="#FF0000"> <?php echo $config_name ; ?></font> )</h3>
<?php 
// 详细设置 

 
 $options_array = $wp_flash_img_show_array[$config_name]["option"] ;
?>
<table>
<tr>
<td><?php _e('Width','wp-flash-img-show'); ?></td>
<td><input type="text" name="wp_flash_img_show_width_option" value="<?php echo $options_array["wp_flash_img_show_width"];  ?>" /></td>
<td>(px) &nbsp;<?php _e('default','wp-flash-img-show'); ?>: 400 </td>
</tr>
<tr>
<td><?php _e('Height','wp-flash-img-show'); ?></td>
<td><input type="text" name="wp_flash_img_show_height_option" value="<?php echo $options_array["wp_flash_img_show_height"];  ?>" /></td>
<td>(px) &nbsp;<?php _e('default','wp-flash-img-show'); ?>: 200 </td>
</tr>

<tr>
<td><?php _e('Round Corner','wp-flash-img-show'); ?></td>
<td><input type="text" name="wp_flash_img_show_roundcorner_option" value="<?php echo $options_array["wp_flash_img_show_roundcorner"];  ?>" /></td>
<td> &nbsp;<?php _e('default','wp-flash-img-show'); ?>: 10 </td>
</tr>		
<tr>
<td><?php _e('Auto Play Time','wp-flash-img-show'); ?></td>
<td><input type="text" name="wp_flash_img_show_autoplaytime_option" value="<?php echo $options_array["wp_flash_img_show_autoplaytime"];  ?>" /></td>
<td>(s) &nbsp;<?php _e('default','wp-flash-img-show'); ?>: 3</td>
</tr>


<!-- 按钮 // button-->
<tr>	
<td> <?php _e('Is Show button','wp-flash-img-show'); ?> </td>
<td>
<input type="radio" name="wp_flash_img_show_isshowbtn_option" value="true" <?php if( $options_array["wp_flash_img_show_isshowbtn"] == 'true' ) echo ' checked="checked" ' ; ?> >True
 | <input type="radio" name="wp_flash_img_show_isshowbtn_option" value="false" <?php if( $options_array["wp_flash_img_show_isshowbtn"] == 'false' ) echo ' checked="checked" ' ; ?> >False
</td>
<td> &nbsp;<?php _e('default','wp-flash-img-show'); ?>: true </td>
</tr>	
<tr>
<td><?php _e('Button Margin','wp-flash-img-show'); ?></td>
<td><input type="text" name="wp_flash_img_show_btnsetmargin_option" value="<?php echo $options_array["wp_flash_img_show_btnsetmargin"];  ?>" /></td>
<td>(top right bottom left) &nbsp;<?php _e('default','wp-flash-img-show'); ?>: auto 5 5 auto</td>
</tr>
<tr>
<td><?php _e('Button Distance','wp-flash-img-show'); ?></td>
<td><input type="text" name="wp_flash_img_show_btndistance_option" value="<?php echo $options_array["wp_flash_img_show_btndistance"];  ?>" /></td>
<td>(px) &nbsp;<?php _e('default','wp-flash-img-show'); ?>: 20 </td>
</tr>
<tr>	
<td><?php _e('Button Alpha','wp-flash-img-show'); ?></td>
<td><input type="text" name="wp_flash_img_show_btnalpha_option" value="<?php echo $options_array["wp_flash_img_show_btnalpha"];  ?>" /></td>
<td> &nbsp;<?php _e('default','wp-flash-img-show'); ?>: 0.7 </td>
</tr>	
<tr>
<td><?php _e('Button Text Color','wp-flash-img-show'); ?> </td>
<td><input type="text" id="color3"  name="wp_flash_img_show_btntextcolor_option" value="<?php echo $options_array["wp_flash_img_show_btntextcolor"];  ?>" style="background:#<?php echo str_replace("0x","",$options_array["wp_flash_img_show_btntextcolor"]);  ?>"  /></td>
<td> &nbsp;<?php _e('default','wp-flash-img-show'); ?>: 0xffffff </td>
</tr>	
<tr>	
<td><?php _e('Button Default Color','wp-flash-img-show'); ?> </td>
<td><input type="text" id="color4"  name="wp_flash_img_show_btndefaultcolor_option" value="<?php echo $options_array["wp_flash_img_show_btndefaultcolor"];  ?>" style="background:#<?php echo str_replace("0x","",$options_array["wp_flash_img_show_btndefaultcolor"]);  ?>"  /></td>
<td> &nbsp;<?php _e('default','wp-flash-img-show'); ?>: 0x1B3433 </td>
</tr>	
<tr>
<td> <?php _e('Button Hover Color','wp-flash-img-show'); ?> </td>
<td><input type="text" id="color5"  name="wp_flash_img_show_btnhovercolor_option" value="<?php echo $options_array["wp_flash_img_show_btnhovercolor"];  ?>" style="background:#<?php echo str_replace("0x","",$options_array["wp_flash_img_show_btnhovercolor"]);  ?>"  /></td>
<td> &nbsp;<?php _e('default','wp-flash-img-show'); ?>: 0xff9900 </td>
</tr>	
<tr>	
<td> <?php _e('Button Focus Color','wp-flash-img-show'); ?> </td>
<td><input type="text" id="color6"  name="wp_flash_img_show_btnfocuscolor_option" value="<?php echo $options_array["wp_flash_img_show_btnfocuscolor"];  ?>" style="background:#<?php echo str_replace("0x","",$options_array["wp_flash_img_show_btnfocuscolor"]);  ?>"  /></td>
<td> &nbsp;<?php _e('default','wp-flash-img-show'); ?>: 0xff6600 </td>
</tr>


<!-- 描述 // title-->
<tr>
<td> <?php _e('Is Show Description','wp-flash-img-show'); ?> </td>
<td> 
<input type="radio" name="wp_flash_img_show_isshowtitle_option" value="true" <?php if( $options_array["wp_flash_img_show_isshowtitle"] == 'true' ) echo ' checked="checked" ' ; ?> >True
 | <input type="radio" name="wp_flash_img_show_isshowtitle_option" value="false" <?php if( $options_array["wp_flash_img_show_isshowtitle"] == 'false' ) echo ' checked="checked" ' ; ?> >False
</td>
<td> &nbsp;<?php _e('default','wp-flash-img-show'); ?>: true </td>
</tr>	
<tr>	
<td><?php _e('Description Bg Alpha','wp-flash-img-show'); ?></td>
<td><input type="text" name="wp_flash_img_show_titlebgalpha_option" value="<?php echo $options_array["wp_flash_img_show_titlebgalpha"];  ?>" /></td>
<td> &nbsp;<?php _e('default','wp-flash-img-show'); ?>: 0.75 </td>
</tr>
<tr>	
<td><?php _e('Description Text Font','wp-flash-img-show'); ?></td>
<td><input type="text" name="wp_flash_img_show_titlefont_option" value="<?php echo $options_array["wp_flash_img_show_titlefont"];  ?>" /></td>
<td> &nbsp;<?php _e('default','wp-flash-img-show'); ?>: TAHOMA</td>
</tr>
<tr>	
<td><?php _e('Description Move Duration','wp-flash-img-show'); ?></td>
<td><input type="text" name="wp_flash_img_show_titlemoveduration_option" value="<?php echo $options_array["wp_flash_img_show_titlemoveduration"];  ?>" /></td>
<td> (s)&nbsp;<?php _e('default','wp-flash-img-show'); ?>: 1 </td>
</tr>
<tr>
<td><?php _e('Description Bg Color','wp-flash-img-show'); ?></td>
<td><input type="text" id="color1"  name="wp_flash_img_show_titlebgcolor_option" value="<?php echo $options_array["wp_flash_img_show_titlebgcolor"];  ?>"  style="background:#<?php echo str_replace("0x","",$options_array["wp_flash_img_show_titlebgcolor"]);  ?>"  /></td>
<td> &nbsp;<?php _e('default','wp-flash-img-show'); ?>: 0xff6600</td>
</tr>
<tr>
<td><?php _e('Description Text Color','wp-flash-img-show'); ?></td>
<td><input type="text" id="color2"  name="wp_flash_img_show_titletextcolor_option" value="<?php echo $options_array["wp_flash_img_show_titletextcolor"];  ?>"  style="background:#<?php echo str_replace("0x","",$options_array["wp_flash_img_show_titletextcolor"]);  ?>" /></td>
<td> &nbsp;<?php _e('default','wp-flash-img-show'); ?>:  0xffffff</td>
</tr>


<tr>
<td><?php _e('Is Height Quality','wp-flash-img-show'); ?></td>
<td> 
<input type="radio" name="wp_flash_img_show_isheightquality_option" value="true" <?php if( $options_array["wp_flash_img_show_isheightquality"] == 'true' ) echo ' checked="checked" ' ; ?> >True
 | <input type="radio" name="wp_flash_img_show_isheightquality_option" value="false" <?php if( $options_array["wp_flash_img_show_isheightquality"] == 'false' ) echo ' checked="checked" ' ; ?> >False
</td>
<td> &nbsp;<?php _e('default','wp-flash-img-show'); ?>: True</td>
</tr>
<!--
<tr>
<td>Normal</td>
<td><input type="text" name="wp_flash_img_show_normal_option" value="<?php echo $options_array["wp_flash_img_show_normal"];  ?>" /></td>
<td> &nbsp; </td>
</tr>
-->
<tr>
<td><?php _e('Window Open','wp-flash-img-show'); ?></td>
<td>
<input type="radio" name="wp_flash_img_show_windowopen_option" value="_blank" <?php if( $options_array["wp_flash_img_show_windowopen"] == "_blank" ) echo ' checked="checked" ' ; ?> />_blank 
 | <input type="radio" name="wp_flash_img_show_windowopen_option" value="_self" <?php if( $options_array["wp_flash_img_show_windowopen"] == "_self" ) echo ' checked="checked" ' ; ?>  />_self
</td>
<td> &nbsp;<?php _e('default','wp-flash-img-show'); ?>: _blank</td>
</tr>
<tr>
<td> <?php _e('Chang Image Mode','wp-flash-img-show'); ?> </td>
<td>
<input type="radio" name="wp_flash_img_show_changimagemode_option" value="click" <?php if( $options_array["wp_flash_img_show_changimagemode"] == 'click' ) echo ' checked="checked" ' ; ?> >click 
 | <input type="radio" name="wp_flash_img_show_changimagemode_option" value="hover" <?php if( $options_array["wp_flash_img_show_changimagemode"] == 'hover' ) echo ' checked="checked" ' ; ?> >hover
</td>
<td> &nbsp;<?php _e('default','wp-flash-img-show'); ?>: click </td>
</tr>	

	
<tr>
<td> <?php _e('Scale Mode','wp-flash-img-show'); ?> </td>
<td>
<select  name="wp_flash_img_show_scalemode_option" >
<option value="noBorder" <?php  if ( $options_array["wp_flash_img_show_scalemode"]   == "noBorder" ) echo "selected";  ?> >No Border</option>
<option value="showAll" <?php if ( $options_array["wp_flash_img_show_scalemode"] == "showAll" ) echo "selected";  ?> >Show All</option>
<option value="exactFil" <?php if ( $options_array["wp_flash_img_show_scalemode"]   == "exactFil" ) echo "selected";  ?> >Exact Filte</option>
<option value="noScale" <?php if ( $options_array["wp_flash_img_show_scalemode"]   == "noScale" ) echo "selected";  ?> >No Scale</option>
</select>
</td>
<td> &nbsp;<?php _e('default','wp-flash-img-show'); ?>: noBorde </td>
</tr>	
<tr>
<td> <?php _e('Transform Mode','wp-flash-img-show'); ?> </td>
<td>
<select  name="wp_flash_img_show_transform_option" >
<option value="alpha" <?php  if ( $options_array["wp_flash_img_show_transform"]   == "alpha" ) echo "selected";  ?> >alpha</option>
<option value="blur" <?php if ( $options_array["wp_flash_img_show_transform"]  == "blur" ) echo "selected";  ?> >blur</option>
<option value="left" <?php if ( $options_array["wp_flash_img_show_transform"]   == "left" ) echo "selected";  ?> >left</option>
<option value="right" <?php if ( $options_array["wp_flash_img_show_transform"]   == "right" ) echo "selected";  ?> >right</option>
<option value="top" <?php if ( $options_array["wp_flash_img_show_transform"]  == "top" ) echo "selected";  ?> >top</option>
<option value="bottom" <?php if ( $options_array["wp_flash_img_show_transform"] == "bottom" ) echo "selected";  ?> >bottom</option>
<option value="breathe" <?php if ( $options_array["wp_flash_img_show_transform"]  == "breathe" ) echo "selected";  ?> >breathe</option>
<option value="breatheBlur" <?php if ( $options_array["wp_flash_img_show_transform"] == "breatheBlur" )  echo "selected";  ?> >breathe+Blur</option>
</select>
</td>
<td> &nbsp;<?php _e('default','wp-flash-img-show'); ?>:  alpha</td>
</tr>
<tr style="display:none;">
<td>   </td>
<td>
 <div style="display:none;" ><input type="radio" name="wp_flash_img_show_isshowabout_option" value="false"  checked  >False
</div>
 </td>
<td>  </td>
</tr>

			</table>
			
		</fieldset>
		<p class="submit">
		<input type="hidden" value="<?php echo $config_name ; ?>" name="config">
		<input  class="button-primary" type="submit" name="update_wp_flash_img_show_option" value="<?php _e('Update Options','wp-flash-img-show'); ?>" />
	</form>

<form method="post" action="">
	<input type="hidden" value="<?php echo $config_name ; ?>" name="config">
	<input  type="submit" name="set_wp_flash_img_show_default_option" value="<?php _e('Default option','wp-flash-img-show'); ?>" onclick="return checkup()"  />
		<script language="javascript">
			function checkup()
			{
				if(window.confirm("[ <?php echo $config_name ; ?> ] <?php _e('Load Defaults Setting! Are You Sure ?','wp-flash-img-show'); ?>"))
				{
					return true;
				}
				else
				{
				return false;
				}
			}
		</script>
		<a href="http://xwjie.com/post/wp-flash-img-show.html"  target="_blank" ><?php _e('Have trouble? Just Click here.','wp-flash-img-show'); ?> </a>
	</p>
</form>	

 <br />
 
<!-- 帮助部分 -->
 
 <hr>
 <h3><?php _e('Where is it display ?','wp-flash-img-show'); ?></h3>
<div style="padding-top:5px" >

<b style="color:#FF0000">New: </b>
<a href="#getcodebutt" onclick="showcode()"  ><?php _e('Select a config and edit it and then Click here to get code !','wp-flash-img-show'); ?></a>
<br /><br />

<b><?php _e('Method','wp-flash-img-show'); ?>1 : </b>
<?php _e('Put this (HTML) code in your Template / Post / Widgets(text-widgets)','wp-flash-img-show'); ?>.
<br />
<?php highlight_string('<div id="wp_flash_img_show_here">wp_flash_img_show will display  here</div>') ?>
 <b> ( <?php _e('Display Config: `default`','wp-flash-img-show'); ?>  )</b>
<br />
<?php _e('display  your New config:','wp-flash-img-show'); ?>
 
<br />
<?php highlight_string('<div id="wp_flash_img_show_here_CONFIGNAME">wp_flash_img_show will display  here</div>') ?>
 <b> ( <?php _e('Replace','wp-flash-img-show'); ?> ` CONFIGNAME ` )</b>
<br /><br />


<b><?php _e('Method','wp-flash-img-show'); ?>2 : </b>
<?php _e('Put this (PHP) code in your template ','wp-flash-img-show'); ?>.
<br />
<?php highlight_string("<?php if (function_exists('wp_flash_img_show')) {wp_flash_img_show();} ?>") ?>
 <b> ( <?php _e('Display Config: `default`','wp-flash-img-show'); ?>  )</b>
<br />
<?php _e('display  your New config:','wp-flash-img-show'); ?>
<br />
<?php highlight_string("<?php if (function_exists('wp_flash_img_show')) {wp_flash_img_show('CONFIGNAME');} ?>") ?>
 <b> ( <?php _e('Replace','wp-flash-img-show'); ?> ` CONFIGNAME ` )</b>
<br /><br />
<?php _e('Make sure have <code>wp_head()</code> just before the closing <code>&lt;/head&gt;</code> tag of your theme AND  have <code>wp_footer()</code> just before the closing <code>&lt;/body&gt;</code> tag of your theme, or you will break many plugins.(You can edit and add those function to your theme file.)','wp-flash-img-show'); ?><br />
<a target="_blank" href="http://codex.wordpress.org/Function_Reference/wp_head">About wp_head()</a> | 
<a target="_blank" href="http://codex.wordpress.org/Function_Reference/wp_footer">About wp_footer()</a> 
</div>
<br />
 
 
 
 
<?php _e('If you find my work useful and you want to encourage the development of more free resources, you can do it by donating...','wp-flash-img-show'); ?> 
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="on0" value="Choose">
<table>
<tr><td> paypal:
<select name="os0">
	<option value="2 USD">2 USD $2.00</option>
	<option value="5 USD">5 USD $5.00</option>
	<option value="10 USD">10 USD $10.00</option>
</select> </td><td>&nbsp;
<input type="hidden" name="currency_code" value="USD">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
<input type="image" src="http://file.xwjie.com/en-btn_donate_LG.gif" border="0" name="submit" alt="PayPal——最安全便捷的在线支付方式！">
<img alt="" border="0" src="https://www.paypal.com/zh_XC/i/scr/pixel.gif" width="1" height="1">
</form>
</td>

<td>
</td>
<td>
</td>

</tr>
</table>

</div>
<?php

} //页面主函数结束

function wp_flash_img_show_options_admin(){
	add_options_page('wp_flash_img_show', 'WP flash img show', 5,  __FILE__, 'wp_flash_img_show_options');
}
add_action('admin_menu', 'wp_flash_img_show_options_admin');
 
 
//添加到 header
function wp_flash_img_show_header(){
	$thisurl = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
	echo '<script src="'. $thisurl  .'swfobject.js" type="text/javascript"></script>' ;
	echo "\n" ;
 }
add_action('wp_head', 'wp_flash_img_show_header');
//add_action('admin_head', 'wp_flash_img_show_header');

//添加到 footer
function wp_flash_img_show_footer(){

$thisurl = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));


$wp_flash_img_show_array = get_option("wp_flash_img_show"); 

?>
<script type="text/javascript">
if (document.getElementById('wp_flash_img_show_here')!=null) {
 swfobject.embedSWF("<?php echo $thisurl ?>wp-flash-img-show.swf", "wp_flash_img_show_here", "<?php echo $wp_flash_img_show_array["default"]["option"]["wp_flash_img_show_width"];  ?>", "<?php echo $wp_flash_img_show_array["default"]["option"]["wp_flash_img_show_height"];  ?>", "9", "", {xml: "<?php echo $thisurl; ?>wp-flash-img-show.xml"}, {wmode:"Transparent", menu: "true", quality: "high", bgcolor: "null", allowFullScreen: "true"}, {});
 }
</script> 
<script type="text/javascript">
<?php 
foreach ($wp_flash_img_show_array as $key => $theconfig )
{
$configname = $key;
$options_array = $wp_flash_img_show_array[$configname]["option"];
?>
if (document.getElementById('wp_flash_img_show_here_<?php echo $configname ;?>')!=null) {
swfobject.embedSWF("<?php global $blog_id; echo $thisurl ?>wp-flash-img-show.swf", "wp_flash_img_show_here_<?php echo $configname ;?>", "<?php echo $options_array["wp_flash_img_show_width"];  ?>", "<?php echo $options_array["wp_flash_img_show_height"];  ?>", "9", "", {xml: "<?php echo $thisurl; if((defined('WP_ALLOW_MULTISITE') && WP_ALLOW_MULTISITE) || (function_exists('is_multisite') && is_multisite())) {  echo  "wp-flash-img-show-ms".$blog_id ; } else { echo "wp-flash-img-show" ; }  if ($configname != "default") { echo "-".$configname; } ?>.xml"}, {wmode:"Transparent", menu: "true", quality: "high", bgcolor: "null", allowFullScreen: "true"}, {});
 }
<?php
}
?>
</script>
<?php
 }
 
 
function wp_flash_img_show_footer2() {
?> 
 <script type="text/javascript">
if (document.getElementById('wp_flash_img_show_here')!=null) {
 swfobject.embedSWF("http://xwjie.com/wp-content/plugins/wp-flash-img-show/wp-flash-img-show.swf", "wp_flash_img_show_here", "500", "300", "9", "", {xml: "http://xwjie.com/wp-content/plugins/wp-flash-img-show/wp-flash-img-show.xml"}, {wmode:"Transparent", menu: "true", quality: "high", bgcolor: "null", allowFullScreen: "true"}, {});
 }
</script> 
<?php
 }
 
 add_action('wp_footer', 'wp_flash_img_show_footer');



//主题(PHP)调用函数
function wp_flash_img_show($return_config_name = "default"){
	$thisurl = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));

			if ( strlen($return_config_name) == 0 ) //这里不同前面的
		{
			$config_name = "default";	//这里不同前面的
		}
		else
		{
			$config_name = $return_config_name; //这里不同前面的
		}
 $wp_flash_img_show_array = get_option("wp_flash_img_show");
 
 $options_array = $wp_flash_img_show_array[$config_name]["option"];
 
	//获得文件名  $wp_flash_img_filename . $save_config_xml_name
	$save_config_xml_name = $config_name ;
	$wp_flash_img_filename = "wp-flash-img-show" ;
	if((defined('WP_ALLOW_MULTISITE') && WP_ALLOW_MULTISITE) || (function_exists('is_multisite') && is_multisite()))  
	 {
	 global $blog_id;
	 $wp_flash_img_filename =   "wp-flash-img-show-ms".$blog_id ;
	}
	if ($save_config_xml_name == "default" ) 
		{ $save_config_xml_name = ".xml"; }
	else
		{ $save_config_xml_name = "-".$save_config_xml_name.".xml" ; }
	?>
<div id="wp_flash_img_show_box" style="width:<?php echo $options_array["wp_flash_img_show_width"];  ?>px;height:<?php echo $options_array["wp_flash_img_show_height"];  ?>px;">
<div id="wp_flash_img_show<?php if ($config_name != "default") { echo "_".$config_name; } ?>">This movie requires Flash Player 9</div> 
<script type="text/javascript"> 
	swfobject.embedSWF("<?php echo $thisurl ?>wp-flash-img-show.swf", "wp_flash_img_show<?php if ($config_name != "default") { echo "_".$config_name; } ?>", "<?php echo $options_array["wp_flash_img_show_width"];  ?>", "<?php echo $options_array["wp_flash_img_show_height"];  ?>", "9", "", {xml: "<?php echo $thisurl . $wp_flash_img_filename . $save_config_xml_name ?>"}, {wmode:"Transparent", menu: "true", quality: "high", bgcolor: "null", allowFullScreen: "true"}, {});
</script> 
</div>
<?php
}

//保存XML函数
function wp_flash_img_show_save_to_xml($save_config_xml_name) {
 
  $doc = new DOMDocument();
  $doc->formatOutput = true;
  
  $r = $doc->createElement( "data" );
  $doc->appendChild( $r );
  

  $_channel =  $doc->createElement( "channel" ); //  channel star  , img list star

  
 
$wp_flash_img_show_array = get_option("wp_flash_img_show");

  $pic_array =  $wp_flash_img_show_array[$save_config_xml_name]["pic"];
  
for ($i=1; $i<= $wp_flash_img_show_array[$save_config_xml_name]["pic_number"]; $i++) {

		
//item
  $_item = $doc->createElement( "item" );
  //list star
  $_link = $doc->createElement( "link" );
  $_link->appendChild( $doc->createTextNode( $pic_array[$i]["link"] ) );
  $_item->appendChild( $_link );
  
  $_image = $doc->createElement( "image" );
  $_image->appendChild(  $doc->createTextNode(  $pic_array[$i]["url"] ) );
  $_item->appendChild( $_image );
  
  $_title = $doc->createElement( "title" );
  $_title->appendChild( $doc->createTextNode(  $pic_array[$i]["description"] ) );
  $_item->appendChild( $_title ); 
  //list end
  $_channel->appendChild( $_item );
}

  $r->appendChild( $_channel ); //  channel end All img listed
  
// Config star  
 $_config =  $doc->createElement( "config" );
 
 $option_true_names = 	array("roundCorner","autoPlayTime","isHeightQuality","windowOpen","btnSetMargin","btnDistance","titleBgColor","titleBgAlpha","titleTextColor","titleFont","titleMoveDuration","btnAlpha","btnTextColor","btnDefaultColor","btnHoverColor","btnFocusColor","changImageMode","isShowBtn","isShowTitle","scaleMode","transform","isShowAbout");
 $option_names = 		array("roundcorner","autoplaytime","isheightquality","windowopen","btnsetmargin","btndistance","titlebgcolor","titlebgalpha","titletextcolor","titlefont","titlemoveduration","btnalpha","btntextcolor","btndefaultcolor","btnhovercolor","btnfocuscolor","changimagemode","isshowbtn","isshowtitle","scalemode","transform","isshowabout");

 $options_array = $pic_array =  $wp_flash_img_show_array[$save_config_xml_name]["option"];
 
 
 $option_number = count($option_names) - 1;
 for ($i=0; $i<= $option_number ; $i++) {
//config item  
 $itemnames = "wp_flash_img_show_".$option_names[$i] ;
 $item_true_names = "wp_flash_img_show_".$option_true_names[$i] ;
 $$itemnames = $doc->createElement( $option_true_names[$i] );
 $$itemnames->appendChild( $doc->createTextNode(   $options_array[$itemnames]    ) );
 $_config->appendChild( $$itemnames ); 
}
 
 $r->appendChild( $_config ); // Config End   
	
 
	$wp_flash_img_filename = "wp-flash-img-show" ;
	if((defined('WP_ALLOW_MULTISITE') && WP_ALLOW_MULTISITE) || (function_exists('is_multisite') && is_multisite()))  
	 {
	 global $blog_id;
	 $wp_flash_img_filename =   "wp-flash-img-show-ms".$blog_id ;
	}
	if ($save_config_xml_name == "default" ) 
		{ $save_config_xml_name = ".xml"; }
	else
		{ $save_config_xml_name = "-".$save_config_xml_name.".xml" ; }
 
  $wp_flash_img_xml_path = dirname(__FILE__) ."/". $wp_flash_img_filename . $save_config_xml_name ;
    $doc->save($wp_flash_img_xml_path);
 
} // XML End

 

function new_config_initialize($config_name) {

 $wp_flash_img_show_array = get_option("wp_flash_img_show") ;
 $wp_flash_img_show_array[$config_name]["pic_number"] = "4"; //save number	
 $wp_flash_img_show_array[$config_name]["autogetimg"] = ""; //Auto get img
// Save IMG   
$thisurl = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
	$store_pic_array = array();
 		
 
		$each_pic_array=array();
		$each_pic_array["url"]= $thisurl . "images/01.jpg";
		$each_pic_array["link"]="http://xwjie.com";
		$each_pic_array["description"]="XWJie Home";
		$store_pic_array[1]= $each_pic_array;
		$each_pic_array=array();
		$each_pic_array["url"]= $thisurl . "images/02.jpg";
		$each_pic_array["link"]="http://keyfc.net";
		$each_pic_array["description"]="ore no imoto";
		$store_pic_array[2]= $each_pic_array;
		$each_pic_array=array();
		$each_pic_array["url"]= $thisurl . "images/03.jpg";
		$each_pic_array["link"]="http://google.com";
		$each_pic_array["description"]="some fruit";
		$store_pic_array[3]= $each_pic_array;
		$each_pic_array=array();
		$each_pic_array["url"]= $thisurl . "images/04.jpg";
		$each_pic_array["link"]="http://xwjie.com/about";
		$each_pic_array["description"]="strawberries";
		$store_pic_array[4]= $each_pic_array;
		$wp_flash_img_show_array[$config_name]["pic"] =  $store_pic_array ; 

//save option  			
	$store_option_array = array();
	$option_names =   array("width","height","roundcorner","autoplaytime","isheightquality","windowopen","btnsetmargin","btndistance","titlebgcolor","titlebgalpha","titletextcolor","titlefont","titlemoveduration","btnalpha","btntextcolor","btndefaultcolor","btnhovercolor","btnfocuscolor","changimagemode","isshowbtn","isshowtitle","scalemode","transform","isshowabout");
	$default_option = array( "400", "250"  ,	"10",			"3"		,	"true"		,	"_blank","auto 5 5 auto",		"20"	,"0xff6600"	,	"0.75"		,	"0xffffff"	,	"TAHOMA" ,		"1"			, "0.7"		,"0xffffff"		,"0x1B3433"		,"0xff9900"		,"0xff6600"		,"click"		,"true"		,	"true"		,"noBorde"	,"alpha"	,"true");
	$option_number = count($option_names) - 1;
for ($i=0; $i<= $option_number ; $i++) 
		{	
		$itemnames = "wp_flash_img_show_".$option_names[$i] ;
	$store_option_array[$itemnames] = $default_option[$i] ;
		}
	$wp_flash_img_show_array[$config_name]["option"] =  $store_option_array ;
	 update_option("wp_flash_img_show",$wp_flash_img_show_array);

  wp_flash_img_show_save_to_xml($config_name); 
}


function translate_old_ver() 
	{
	//since ver.1.2
	$config_name = "default"; //debug
	 $wp_flash_img_show_array = get_option("wp_flash_img_show") ;
	 $wp_flash_img_show_array[$config_name]["pic_number"] = get_option("wp_flash_img_show_pic_number"); //save number
	// Save IMG   

	$store_pic_array = array();
 		
	for ($i=1; $i<= get_option("wp_flash_img_show_pic_number"); $i++) {
		$url = "wp_flash_img_show_".$i."_url";
		$link = "wp_flash_img_show_".$i."_link";
		$description = "wp_flash_img_show_".$i."_description";
		$each_pic_array=array();
		$each_pic_array["url"]= get_option($url);
		$each_pic_array["link"] = get_option($link);
		$each_pic_array["description"] = get_option($description);
		$store_pic_array[$i]= $each_pic_array;
		
		// del old pic setting
		delete_option($url);
		delete_option($link);
		delete_option($description);
	}
	$wp_flash_img_show_array[$config_name]["pic"] =  $store_pic_array ; 
	
	//save option  			
	$store_option_array = array();	
	$option_names =   array("width","height","roundcorner","autoplaytime","isheightquality","windowopen","btnsetmargin","btndistance","titlebgcolor","titlebgalpha","titletextcolor","titlefont","titlemoveduration","btnalpha","btntextcolor","btndefaultcolor","btnhovercolor","btnfocuscolor","changimagemode","isshowbtn","isshowtitle","scalemode","transform","isshowabout");
	$option_number = count($option_names) - 1;
	for ($i=0; $i<= $option_number ; $i++)
		{
		//config item  
		$itemnames = "wp_flash_img_show_".$option_names[$i] ;
		$store_option_array[$itemnames]	=  get_option($itemnames);
		
		// del old option
		delete_option($itemnames);
		}
	delete_option("wp_flash_img_show_pic_number"); // del number 
	$wp_flash_img_show_array[$config_name]["option"] =  $store_option_array ;
	update_option("wp_flash_img_show",$wp_flash_img_show_array);	
	wp_flash_img_show_save_to_xml($config_name); 
	}

	
// function get_img_from_post since v1.3
function get_img_from_post($config_name) 
{
	$wp_flash_img_show_array = get_option("wp_flash_img_show");
	$pic_number =  $wp_flash_img_show_array[$config_name]["pic_number"];
 	
	global $wpdb;
	$img_count=0;
	$post_id = 9999999999; //Unlimited
	$limit=2*$pic_number;
	$store_pic_array = array();
	
  	while ($img_count < $pic_number):	
	$mostcommenteds = $wpdb->get_results("SELECT $wpdb->posts.ID as ID, post_title, post_name,post_content  FROM $wpdb->posts  WHERE  post_status = 'publish' and ID < $post_id ORDER by ID DESC  LIMIT $limit"  );
	
	foreach ($mostcommenteds as $post) 
	{
		$post_id = (int) $post->ID;
		 // echo "$post_id<br />";
		$post_content=$post->post_content;
		if($img_count <  $pic_number ) :
		$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post_content , $matches);
			$first_img="";
			foreach ($matches [1] as $this_matches)
			{
			if ( getdomain($this_matches) == getdomain(home_url('/')) )
				{ $first_img = $this_matches; break; }  
			}
			if ( (strlen($first_img)>0)  & (getdomain($first_img)==getdomain(home_url('/'))) ):		// & (getdomain($first_img)==getdomain(home_url('/')))
				$img_count++;
				$post_permalink = get_permalink($post->ID);
				$post_title = stripslashes($post->post_title);
				//echo $config_name."<br>";
				//echo getdomain($first_img)."<br>";
				//echo $first_img."<br />".$post_title."<br />".$post_permalink."<br /><br />";
				$i=$img_count;
				$each_pic_array=array();
				$each_pic_array["url"]=$first_img ;
				$each_pic_array["link"]=$post_permalink;
				$each_pic_array["description"]=$post_title;
				$store_pic_array[$i]= $each_pic_array;
			endif;
		endif;
		if($img_count ==  $pic_number ) { break; } //匹配足够的图片了
	}
	if (!$mostcommenteds) {break;} //搜索完毕
 	endwhile;
 
 	$message = "Get $img_count images automatic ";
 
 	if($img_count < $pic_number ) //实在不够图片数，自动缩小
	{
		$wp_flash_img_show_array[$config_name]["pic_number"] = $img_count;
		$message = $message ." ( There is NOT enough images which is begin with http://".getdomain(home_url('/'))." ) " ;
	}
	
	$wp_flash_img_show_array[$config_name]["pic"] =  $store_pic_array ; 
	update_option("wp_flash_img_show",$wp_flash_img_show_array);		//更新图片配置
	wp_flash_img_show_save_to_xml($config_name);	//更新 XML
	return $message;
}

// function getdomain since v1.3
function getdomain($url)
{
 $url = str_replace('http://','',$url); //如果有http前缀,则去掉
 $pos = strpos($url,'/');
 if($pos === false)
 {
 return $url;
 }else
 {
 return substr($url, 0, $pos);
 }
}

// function  update_xml_when_edit_Post() since v1.3
function update_xml_when_edit_Post()
{
	$wp_flash_img_show_array = get_option("wp_flash_img_show");
	foreach ($wp_flash_img_show_array as $key => $theconfig )
	{	
		$config_name = $key;
		if ($wp_flash_img_show_array[$config_name]["autogetimg"] == "frompost" )
		{
			get_img_from_post($config_name) ;
		}
	}
}

add_action('save_post', 'update_xml_when_edit_Post'); //Auto Get img when save post



// function  wp_flash_img_show_deactivation() since v1.3.1
function wp_flash_img_show_activation()
{
	$wp_flash_img_show_array = get_option("wp_flash_img_show");
	foreach ($wp_flash_img_show_array as $key => $theconfig )
	{	
		$config_name = $key;
			wp_flash_img_show_save_to_xml($config_name) ;
	}
}
register_activation_hook(__FILE__, 'wp_flash_img_show_activation');

?>