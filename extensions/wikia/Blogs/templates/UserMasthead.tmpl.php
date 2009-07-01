<?php
global $wgStyleVersion, $wgExtensionsPath, $wgTitle, $wgUser;

if ($wgTitle->getNamespace() == NS_USER || $wgTitle->getNamespace() == NS_USER_TALK) {
	global $wgTitle;
	$userMastheadName = $wgTitle;
}
if ($wgTitle == 'Special:Watchlist') {
	$userMastheadName = $wgUser->getName();
}

?>

<div id="user_masthead" class="reset clearfix">
	<?php echo $avatar->display( 50, 50, false, "avatar", false, ( ( $userspace == $wgUser->getName() ) || ( $wgUser->isAllowed( 'removeavatar' ) && ( !$avatar-> isDefault() ) ) ), 'usermasthead/user' ) ?>
<? if ( ( $userspace == $wgUser->getName() ) || ( $wgUser->isAllowed( 'removeavatar' ) ) ) { ?>
	<span class="avatarOverlay color1" style="visibility: hidden;" id="wk-avatar-change" onmouseover="this.style.visibility='visible';" onmouseout="this.style.visibility='hidden';">
<? if ( $userspace == $wgUser->getName() ) { ?>	
		<span onclick="WET.byStr('usermasthead/editavatar');javascript:location='<?php echo Title::newFromText("Preferences", NS_SPECIAL)->getLocalUrl(); ?>'"><?=wfMsg('blog-avatar-edit')?></span>
<? } ?>
<? if ( ( $wgUser->isAllowed( 'removeavatar' ) ) && ( !$avatar-> isDefault() ) ) { ?>	
		<span onclick="WET.byStr('usermasthead/removeavatar');javascript:location='<?php echo Title::newFromText("RemoveAvatar", NS_SPECIAL)->getLocalUrl("action=search_user&av_user={$avatar->getUserName()}"); ?>'"><?=wfMsg('blog-avatar-delete')?></span>
<? } ?>		
	</span>
<? } ?>
	<h2><?=$data['userspace']?></h2>
	<?
	if(!empty($nav_urls['blockip'])) {
		echo '<a href="'. $nav_urls['blockip']['href'] .'" onclick="WET.byStr(\'usermasthead/blockip\')">'. wfMsg('blockip') .'</a>';
	}
	?>
	<ul id="user_masthead_links">
		<?
		foreach( $data['nav_links'] as $navLink ) {
			echo "<li ". ( ( $current  == $navLink[ "dbkey" ]) ? 'class="selected">' : ">" ) . '<a href="'. $navLink['href'] .'" onclick="WET.byStr(\'usermasthead/' . $navLink['tracker'] . '\')">'. $navLink['text'] .'</a></li>';
		}
		?>
	</ul>
</div>
