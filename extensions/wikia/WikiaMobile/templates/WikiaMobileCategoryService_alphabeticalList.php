<section class="alphaSec noWrap">
	<header><?= $wf->MsgExt( 'wikiamobile-categories-items-total', array( 'parsemag', 'content' ), $wg->ContLang->formatNum( $total ) ) ;?><button class=wkBtn id=expAll><span class=expand><?= $wf->MsgExt( 'wikiamobile-categories-expand', array( 'parsemag', 'content' )) ;?></span><span class=collapse><?= $wf->MsgExt( 'wikiamobile-categories-collapse', array( 'parsemag', 'content' )) ;?></span></button></header>
<? foreach ( $collections as $index => $collection) :?>
	<?
	$batch = ( $index == $requestedIndex ) ? $requestedBatch : 1;
	$itemsBatch = $collection->getItems( $batch );
	$nextBatch = $itemsBatch['currentBatch'] + 1;
	$prevBatch = $itemsBatch['currentBatch'] - 1;
	$id = 'catAlpha' . htmlentities( $index );
	$urlSafeIndex = urlencode( $index );
	$urlSafeId = urlencode( $id );
	?>
	<h2 class=collSec><?= strtoupper( $index ) ;?> <span class=cnt><?= $wg->ContLang->formatNum( $itemsBatch['total'] ) ;?></span><span class=chev></span></h2>
	<section id=<?= $id ;?> class=artSec data-batches=<?= $itemsBatch['batches'] ;?>>
		<a class="pagLess<?= ( $itemsBatch['currentBatch'] > 1 ? ' visible' : '' ) ;?>" data-batch=<?=$prevBatch?> href="?page=<?=$prevBatch;?>&index=<?=$urlSafeIndex;?>#<?=$urlSafeId;?>"><?= $wf->Msg( 'wikiamobile-category-items-prev' ) ;?></a>
		<?= $app->getView( 'WikiaMobileCategoryService', 'getBatch', array( 'itemsBatch' => $itemsBatch ) ) ;?>
		<a class="pagMore<?= ( $itemsBatch['next']  ? ' visible' : '' ) ;?>" data-batch=<?=$nextBatch;?> href="?page=<?=$nextBatch;?>&index=<?=$urlSafeIndex;?>#<?=$urlSafeId;?>"><?= $wf->Msg( 'wikiamobile-category-items-more' ) ;?></a>
	</section>
<? endforeach ;?>
</section>