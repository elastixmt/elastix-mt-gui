<link rel="stylesheet" media="screen" type="text/css" href="web/apps/{$module_name}/applets/News/tpl/css/styles.css" />
{foreach from=$NEWS_LIST item=NEWS_ITEM}
<div class="neo-applet-news-row">
    <span class="neo-applet-news-row-date">{$NEWS_ITEM.date_format}</span>
    <a href="https://twitter.com/share?original_referer={$WEBSITE|escape:"url"}&related=&source=tweetbutton&text={$NEWS_ITEM.title|escape:"url"}&url={$NEWS_ITEM.link|escape:"url"}&via=elastixGui"  target="_blank">
        <img src="web/apps/{$module_name}/applets/News/images/twitter-icon.png" width="16" height="16" alt="tweet" />
    </a>
    <a href="{$NEWS_ITEM.link}" target="_blank">{$NEWS_ITEM.title|escape:"html"}</a>
</div>
{foreachelse}
<div class="neo-applet-news-row">{$NO_NEWS}</div>
{/foreach}