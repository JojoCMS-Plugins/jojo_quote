{if $error}<div class="error">{$error}</div>{/if}

{if $jojo_quote}
   <div class="quote">
        {if $jojo_quote.image}<a href="{$jojo_quote.url}" title="{$jojo_quote.title}"><img src="{$SITEURL}/images/{$jojo_quote.mainimage}/{$jojo_quote.image}" class="float-right" alt="{$jojo_quote.title}" /></a>{/if}
        <div class="quotebody">{$jojo_quote.qt_body}</div>
        <div class="quotecredit" style="text-align:left">{$jojo_quote.qt_author}{if $jojo_quote.qt_designation}<br />
        {$jojo_quote.qt_designation}{/if}{if $jojo_quote.qt_company} - {if $jojo_quote.qt_weblink}<a href ="{$jojo_quote.qt_weblink}" title="{$jojo_quote.qt_company}">{/if}{$jojo_quote.qt_company}{if $jojo_quote.qt_weblink}</a>{/if}{/if}</div>
    </div>
    <div>
        {$jojo_quote.qt_description}
    </div>

{if !$onequote}<p class="links">&lt;&lt; <a href="{$jojo_quote.pageurl}" title="back">{$jojo_quote.pagetitle}</a>&nbsp; {if $prevquote}&lt; <a href="{$prevquote.url}" title="Previous">{$prevquote.title}</a>{/if}{if $nextquote} | <a href="{$nextquote.url}" title="Next">{$nextquote.title}</a> &gt;{/if}</p>{/if}
{if $tags}
    <p class="tags"><strong>Tags: </strong>
{if $itemcloud}
        {$itemcloud}
{else}
{foreach from=$tags item=tag}
        <a href="{if $multilangstring}{$multilangstring}{/if}tags/{$tag.url}/">{$tag.cleanword}</a>
{/foreach}
    </p>
{/if}
{/if}

{elseif $jojo_quotes}
    {if $pg_body && $pagenum==1}{$pg_body}{/if}
    {foreach from=$jojo_quotes item=q}
        <h3 class="clear"><a href="{$q.url}" title="{$q.title}">{$q.title}</a></h3>
        <div class="quote">
        {if $q.image}<a href="{$q.url}" title="{$q.title}"><img src="{$SITEURL}/images/{if $q.snippet=='full'}{$q.mainimage}{else}{$q.thumbnail}{/if}/{$q.image}" class="index-thumb" alt="{$q.title}" /></a>{/if}
        {if $q.snippet=='full'}{$q.qt_body}
        {if $q.author}<p class="credit">{$q.author}{if $q.designation}, {$q.designation}, {$q.company}{/if}</p>{/if}
        {else}<p>{$q.bodyplain|truncate:$q.snippet} {if $q.author}- <span class="credit">{$q.author}{if $q.designation}, {$q.designation}, {$q.company}{/if}</span><br />{/if}
        <a href="{$q.url}" title="{$q.title}" class="more">{$q.readmore}</a></p>{/if}
       {if $q.showdate}<div class="article-date">Added: {$q.datefriendly}</div>{/if}
       {if $q.comments && $q.numcomments}<div class="numcomments"><img src="images/blog_comment_icon.gif" class="icon-image" />{$q.numcomments} Comment{if $q.numcomments>1}s{/if}</div>{/if}
        </div>
    {/foreach}
    <div class="article-pagination links">
        {$pagination}
    </div>
{/if}
