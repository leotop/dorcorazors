<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if(!CModule::IncludeModule("iblock")||!CModule::IncludeModule("catalog")) break;
if(!function_exists('declOfNum')){
	function declOfNum($number, $titles){
		$cases = array (2, 0, 1, 1, 1, 2); 
		return sprintf($titles[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)]], $number);
		}
	}

$count = $delayCount =  $summ = 0;
foreach($arResult["ITEMS"] as $arItem){ 
	if($arItem["CAN_BUY"] == 'Y'){ 
		if($arItem["DELAY"] == 'N'){
			++$count; 
			$summ += $arItem["PRICE"]*$arItem["QUANTITY"]; 
		}
		else{
			++$delayCount;
		}
	} 
}

$cur = CCurrencyLang::GetCurrencyFormat(CCurrency::GetBaseCurrency());
$summ_formated = FormatCurrency($summ, $cur["CURRENCY"]);
$symb = substr($summ_formated, strrpos($summ_formated, ' '));
usort($arResult["ITEMS"], 'CKShop::cmpByID');
?>
<?$frame = $this->createFrame()->begin('');?>
<div class="basket_normal">
	<!--noindex-->
		<div class="cart<?=(!$count ? ' empty_cart' : '')?>">
			<span class="icon"><i></i></span>
			<span class="icon small"><a href="<?=$arParams["PATH_TO_BASKET"]?>"><i></i></a></span>
			<div class="cart_wrapp"<?if ($delayCount):?>style="display: none;"<?endif;?>>
				<a class="cart-call small"  href="<?=$arParams["PATH_TO_BASKET"]?>" rel="nofollow">
					<span class="pseudo"><?=GetMessage("IN_BASKET");?> <span class="total_count"><?=$count;?></span></span>
				</a>
				<a <?if($count):?>class="cart-call"<?else:?>href="<?=$arParams["PATH_TO_BASKET"]?>"<?endif;?> rel="nofollow">
					<span class="pseudo"><?=GetMessage("IN_BASKET");?> <span class="total_count"><?=$count;?></span></span>
				</a><br />
				<span class="summ"><?=GetMessage("NA");?> <span class="total_summ"><?=$summ_formated?></span></span>
			</div>
			<div class="cart_wrapp with_delay"<?if(!$delayCount):?> style="display: none;"<?endif;?>>
				<a class="cart-call small" href="<?=$arParams["PATH_TO_BASKET"]?>" rel="nofollow">
					<span class="pseudo"><?=GetMessage("BASKET");?> +<span class="total_count"><?=$count;?></span></span>
				</a>
				<a <?if($count):?>class="cart-call"<?else:?>href="<?=$arParams["PATH_TO_BASKET"]?>" class="cart-call-empty"<?endif;?> rel="nofollow">
					<span class="pseudo"><?=GetMessage("BASKET");?> +<span class="total_count"><?=$count;?></span></span>
				</a><br />
				<a class="delay_link" href="<?=$arParams["PATH_TO_BASKET"]?>?section=delay"><span class="icon"></span><?=GetMessage("DELAY");?> <span class="delay_count"><?=$delayCount?></span></a>
			</div>
		</div>
		<input type="hidden" name="path_to_basket" value="<?=$arParams["PATH_TO_BASKET"]?>" />
	<!--/noindex-->
	<div class="card_popup_frame popup">
		<div class="popup-intro"><div class="pop-up-title"><?=GetMessage("ADDED_TO_BASKET");?></div></div>
		<div class="popup-intro grey"><div class="pop-up-title"><?=GetMessage("BASKET_EMPTY_TITLE");?></div></div>
		<a class="close jqmClose"><i></i></a>
		<div class="basket_popup_wrapp">
			<table class="cart_shell" width="100%" border="0">
				<tbody>
					<?
					if($arParams["CACHE_TYPE"] != "N"){
						$cache = new CPHPCache();
						$cache_time = 100000;
						$cache_path = SITE_DIR.'kshop_basket/';
					}
					$i = 0;
					foreach($arResult["ITEMS"] as $arItem){	
						if(($arItem["CAN_BUY"] == "Y") && ($arItem["DELAY"] == "N")){
							++$i;
							if($i > 3) break;
							$cache_id = 'aspro_basket_'.$arItem["PRODUCT_ID"];
							if($arParams["CACHE_TYPE"] != "N" && $cache_time > 0 && $cache->InitCache($cache_time, $cache_id, $cache_path)){ 
								$res = $cache->GetVars(); 
								$item = $res["item"]; 
							}
							else{
								if($objRes = CIBlockElement::GetList(array(), array("ID" => $arItem["PRODUCT_ID"]))->GetNextElement()){
									$item = $objRes->GetFields();
									$item["PROPERTIES"] = $objRes->GetProperties();
									$arSelect = array("PREVIEW_PICTURE", "DETAIL_PICTURE", "ID", "DETAIL_PAGE_URL");
									if($item["PROPERTIES"]["CML2_LINK"]["VALUE"]){ 
										if($itemLink = CIBlockElement::GetList(array(), array("ID" => $item["PROPERTIES"]["CML2_LINK"]["VALUE"]), false, false, $arSelect)->GetNext()){
											$item["ID"] = $itemLink["ID"];
											$item["DETAIL_PAGE_URL"] = $itemLink["DETAIL_PAGE_URL"];
											if(!$item["PREVIEW_PICTURE"] && $itemLink["PREVIEW_PICTURE"]){
												$item["PREVIEW_PICTURE"] = $itemLink["PREVIEW_PICTURE"];
											}
											if(!$item["DETAIL_PICTURE"] && $itemLink["DETAIL_PICTURE"]){
												$item["DETAIL_PICTURE"] = $itemLink["DETAIL_PICTURE"];
											}
										}
									}
									
									if($item["PREVIEW_PICTURE"]){
										$item["PREVIEW_PICTURE"] = CFile::ResizeImageGet($item["PREVIEW_PICTURE"], array('width' => 70, 'height' => 70), BX_RESIZE_IMAGE_PROPORTIONAL, true); 
									}
									elseif($item["DETAIL_PICTURE"]){
										$item["DETAIL_PICTURE"] = CFile::ResizeImageGet($item["DETAIL_PICTURE"], array('width' => 70, 'height' => 70), BX_RESIZE_IMAGE_PROPORTIONAL, true);
									}

									if($arParams["CACHE_TYPE"] != "N" && $cache_time > 0){ 
										$cache->StartDataCache($cache_time, $cache_id, $cache_path); 
										$cache->EndDataCache(array("item" => $item)); 
									}
								}
							}
							?>
							<tr class="catalog_item" product-id="<?=$arItem["ID"]?>" catalog-product-id="<?=$item["ID"]?>">
								<td class="thumb-cell">									
									<a href="<?=$arItem["DETAIL_PAGE_URL"]?>">		
										<?if($item["PREVIEW_PICTURE"]):?>
											<img src="<?=$item["PREVIEW_PICTURE"]["src"]?>" alt="<?=($item["PREVIEW_PICTURE"]["alt"] ? $item["PREVIEW_PICTURE"]["alt"] : $arItem["NAME"]);?>" title="<?=($item["PREVIEW_PICTURE"]["title"] ? $item["PREVIEW_PICTURE"]["title"] : $arItem["NAME"]);?>" />
										<?elseif($item["DETAIL_PICTURE"]):?>
											<img src="<?=$item["DETAIL_PICTURE"]["src"]?>" alt="<?=($item["PREVIEW_PICTURE"]["alt"] ? $item["PREVIEW_PICTURE"]["alt"] : $arItem["NAME"]);?>" title="<?=($item["PREVIEW_PICTURE"]["title"] ? $item["PREVIEW_PICTURE"]["title"] : $arItem["NAME"]);?>" />	
										<?else:?>
											<img border="0" src="<?=SITE_TEMPLATE_PATH?>/images/no_photo_medium.png" alt="<?=($item["PREVIEW_PICTURE"]["alt"] ? $item["PREVIEW_PICTURE"]["alt"] : $arItem["NAME"]);?>" title="<?=($item["PREVIEW_PICTURE"]["title"] ? $item["PREVIEW_PICTURE"]["title"] : $arItem["NAME"]);?>" />
										<?endif;?>
									</a>
								</td>
								<td class="item-title"><a href="<?=$arItem["DETAIL_PAGE_URL"]?>"><span><?=$arItem["NAME"]?></span></a></td>					
								<td class="cost-cell">
									<input type="hidden" name="item_price_<?=$arItem["ID"]?>" value="<?=($arItem["PRICE"] * $arItem["QUANTITY"])?>">
									<input type="hidden" name="item_price_discount_<?=$arItem["ID"]?>" value="<?=$arItem["DISCOUNT_PRICE"]?>">
									<span class="price"><?=FormatCurrency($arItem["PRICE"] * $arItem["QUANTITY"], $arItem["CURRENCY"]);?></span>
								</td>
								<td class="remove-cell"><a class="remove" href="<?=SITE_DIR?>basket/?action=delete&id=<?=$arItem["ID"]?>" title="<?=GetMessage("SALE_DELETE_PRD")?>"><i></i></a></td>
							</tr>
						<?}?>
					<?}?>
				</tbody>
			</table>
			<div class="basket_empty clearfix">
				<table cellspacing="0" cellpadding="0" border="0" width="100%">
					<tr>
						<td class="image"><div></div></td>
						<td class="description"><div class="basket_empty_subtitle"><?=GetMessage("BASKET_EMPTY_SUBTITLE")?></div><div class="basket_empty_description"><?=GetMessage("BASKET_EMPTY_DESCRIPTION")?></div></td>
					</tr>
				</table>	
			</div>
			<div class="total_wrapp clearfix">
				<?if($count > 3):?>
					<a href="<?=$arParams["PATH_TO_BASKET"]?>" class="more_row">
						<span class="text"><?=GetMessage("STILL")?> <span class="count"><?=($count - 3)?></span> <span class="count_message"><?=declOfNum(($count - 3), array(GetMessage("PRODUCTS_ONE"), GetMessage("PRODUCTS_TWO"), GetMessage("PRODUCTS_FIVE")))?> <?=GetMessage("IN_BASKET_SMALL")?></span></span>
						<span class="icon"><i></i></span>
					</a>		
				<?endif;?>
				<div class="total"><?=GetMessage("TOTAL_SUMM_TITLE")?>:<span class="price"><?=$summ_formated?></span></div>
				<hr />		
				<input type="hidden" name="total_price" value="<?=$summ?>" />
				<input type="hidden" name="total_count" value="<?=$count;?>" />
				<input type="hidden" name="delay_count" value="<?=$delayCount;?>" />						
				<div class="but_row">
					<a class="button30 close_btn"><span><?=GetMessage("CLOSE");?></span></a>
					<a href="<?=$arParams["PATH_TO_BASKET"]?>" class="to_basket"><span class="icon"><i></i></span><span class="text"><?=GetMessage("GO_TO_BASKET");?></span></a>
					<?/*<a href="<?=$arParams["PATH_TO_BASKET"]?>?section=delay" class="to_delay"><span class="icon"><i></i></span><span class="text"><?=GetMessage("GO_TO_DELAY");?></span></a>
					<a href="<?=$arParams["PATH_TO_ORDER"]?>" class="button30 checkout"><span><?=GetMessage("CHECKOUT");?></span></a>*/?>
				</div>
			</div>
		</div>	
	</div>
	<div class="basket_hidden">
		<table><tbody>
			<? 
			if($arParams["CACHE_TYPE"] != "N"){
				$cache = new CPHPCache();
				$cache_time = 100000;
				$cache_path = SITE_DIR.'kshop_basket/';
			}
			$i = 0;
			foreach($arResult["ITEMS"] as $arItem){
				if(($arItem["CAN_BUY"] == "Y") && ($arItem["DELAY"] == "N")){
					++$i;
					if($i < 4){
						continue;
					}
					elseif($i >= 6){
						break;
					}
					$cache_id = 'aspro_basket_'.$arItem["PRODUCT_ID"];
					if($arParams["CACHE_TYPE"] != "N" && $cache_time > 0 && $cache->InitCache($cache_time, $cache_id, $cache_path)){ 
						$res = $cache->GetVars(); 
						$item = $res["item"]; 
					}
					else{
						if($objRes = CIBlockElement::GetList(array(), array("ID" => $arItem["PRODUCT_ID"]))->GetNextElement()){
							$item = $objRes->GetFields();
							$item["PROPERTIES"] = $objRes->GetProperties();
							$arSelect = array("PREVIEW_PICTURE", "DETAIL_PICTURE", "ID", "DETAIL_PAGE_URL");
							if($item["PROPERTIES"]["CML2_LINK"]["VALUE"]){ 
								if($itemLink = CIBlockElement::GetList(array(), array("ID" => $item["PROPERTIES"]["CML2_LINK"]["VALUE"]), false, false, $arSelect)->GetNext()){
									$item["ID"] = $itemLink["ID"];
									$item["DETAIL_PAGE_URL"] = $itemLink["DETAIL_PAGE_URL"];
									if(!$item["PREVIEW_PICTURE"] && $itemLink["PREVIEW_PICTURE"]){
										$item["PREVIEW_PICTURE"] = $itemLink["PREVIEW_PICTURE"];
									}
									if(!$item["DETAIL_PICTURE"] && $itemLink["DETAIL_PICTURE"]){
										$item["DETAIL_PICTURE"] = $itemLink["DETAIL_PICTURE"];
									}
								}
							}
							
							if($item["PREVIEW_PICTURE"]){
								$item["PREVIEW_PICTURE"] = CFile::ResizeImageGet($item["PREVIEW_PICTURE"], array('width' => 70, 'height' => 70), BX_RESIZE_IMAGE_PROPORTIONAL, true); 
							}
							elseif($item["DETAIL_PICTURE"]){
								$item["DETAIL_PICTURE"] = CFile::ResizeImageGet($item["DETAIL_PICTURE"], array('width' => 70, 'height' => 70), BX_RESIZE_IMAGE_PROPORTIONAL, true);
							}

							if($arParams["CACHE_TYPE"] != "N" && $cache_time > 0){ 
								$cache->StartDataCache($cache_time, $cache_id, $cache_path); 
								$cache->EndDataCache(array("item" => $item)); 
							}
						}
					}
					?>
					<tr class="catalog_item" product-id="<?=$arItem["ID"]?>" catalog-product-id="<?=$item["ID"]?>">
						<td class="thumb-cell">									
							<a href="<?=$arItem["DETAIL_PAGE_URL"]?>">										
								<?if($item["PREVIEW_PICTURE"]):?>
									<img src="<?=$item["PREVIEW_PICTURE"]["src"]?>" alt="<?=($item["PREVIEW_PICTURE"]["alt"]?$item["PREVIEW_PICTURE"]["alt"]:$arItem["NAME"]);?>" title="<?=($item["PREVIEW_PICTURE"]["title"]?$item["PREVIEW_PICTURE"]["title"]:$arItem["NAME"]);?>" />
								<?elseif($item["DETAIL_PICTURE"]):?>
									<img src="<?=$item["DETAIL_PICTURE"]["src"]?>" alt="<?=($item["PREVIEW_PICTURE"]["alt"]?$item["PREVIEW_PICTURE"]["alt"]:$arItem["NAME"]);?>" title="<?=($item["PREVIEW_PICTURE"]["title"]?$item["PREVIEW_PICTURE"]["title"]:$arItem["NAME"]);?>" />	
								<?else:?>
									<img border="0" src="<?=SITE_TEMPLATE_PATH?>/images/no_photo_medium.png" alt="<?=($item["PREVIEW_PICTURE"]["alt"]?$item["PREVIEW_PICTURE"]["alt"]:$arItem["NAME"]);?>" title="<?=($item["PREVIEW_PICTURE"]["title"]?$item["PREVIEW_PICTURE"]["title"]:$arItem["NAME"]);?>" />
								<?endif;?>
							</a>
						</td>
						<td class="item-title"><a href="<?=$arItem["DETAIL_PAGE_URL"]?>"><span><?=$arItem["NAME"]?></span></a></td>					
						<td class="cost-cell">
							<input type="hidden" name="item_price_<?=$arItem["ID"]?>" value="<?=($arItem["PRICE"] * $arItem["QUANTITY"])?>">
							<span class="price"><?=FormatCurrency($arItem["PRICE"] * $arItem["QUANTITY"], $arItem["CURRENCY"]);?></span>
						</td>
						<td class="remove-cell"><a class="remove" href="<?=SITE_DIR?>basket/?action=delete&id=<?=$arItem["ID"]?>" title="<?=GetMessage("SALE_DELETE_PRD")?>"><i></i></a></td>
					</tr>
					<?
				}
			}?>
		</tbody></table>
	</div>
	<script type="text/javascript">
	$('.card_popup_frame').ready(function(){
		$('.card_popup_frame').jqm({	
			trigger: '.cart-call:not(.small)', 
			toTop: 'false', 
			onLoad: function() {}, 
			onShow: function(hash){ 
				$('.card_popup_frame').jqmAddClose('a.jqmClose');  
				$('.card_popup_frame').jqmAddClose('a.button30.close_btn');  
				preAnimateBasketPopup(hash, $('.card_popup_frame'), 0, 200);
			},
			onHide: function(hash) { replaceBasketPopup(hash);}, 
		});
		
		$(document).on('click', '.card_popup_frame a.remove', function(e){
			e.preventDefault();
			deleteFromBasketPopup($('.card_popup_frame'), 0 , 200, $(this));
		});
	});
	</script>
</div>
<?$frame->end();?>