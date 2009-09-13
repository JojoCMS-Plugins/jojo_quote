{if $error}<div class="error">{$error}</div>{/if}

{if $jojo_quote}
   <div class="quote">
        {if $jojo_quote.qt_image}<img src="images/v60000/quotes/{$jojo_quote.qt_image}" alt="{$jojo_quote.qt_title}" class="right-image"/>{/if}
        <div class="quotebody">{$jojo_quote.qt_body}</div>
        <div class="quotecredit" style="text-align:left">{$jojo_quote.qt_author}{if $jojo_quote.qt_designation}<br />
        {$jojo_quote.qt_designation}{/if}{if $jojo_quote.qt_company} - {if $jojo_quote.qt_weblink}<a href ="{$jojo_quote.qt_weblink}" title="{$jojo_quote.qt_company}">{/if}{$jojo_quote.qt_company}{if $jojo_quote.qt_weblink}</a>{/if}{/if}</div>
	</div>
    <div>
        {$jojo_quote.qt_description}
    </div>

{if !$onequote}<p class="links">&lt;&lt; <a href="{$indexurl}" title="back">{$pg_title} index</a>&nbsp; {if $prevquote}&lt; <a href="{$prevquote.url}" title="Previous">{$prevquote.title}</a>{/if}{if $nextquote} | <a href="{$nextquote.url}" title="Next">{$nextquote.title}</a> &gt;{/if}</p>{/if}
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
	{section name=a loop=$jojo_quotes}

		<h3><a href="{$jojo_quotes[a].url}" title="{$jojo_quotes[a].qt_title}">{$jojo_quotes[a].qt_title}</a></h3>
		<div>
			<p>{if $jojo_quotes[a].qt_image}<img src="images/v12000/quotes/{$jojo_quotes[a].qt_image}" alt="{$jojo_quotes[a].qt_title}" class="right-image" />{/if}
			{$jojo_quotes[a].body|truncate:300} - <em>{$jojo_quotes[a].qt_author}{if $jojo_quotes[a].qt_designation}, {$jojo_quotes[a].qt_designation}, {$jojo_quotes[a].qt_company}{/if}</em></p>
			<p>{$jojo_quotes[a].description|truncate:300} <a href="{$jojo_quotes[a].url}" class="links" title="View full quote" rel="nofollow">&gt; Read more</a></p>
		</div>

	{/section}

	<div class="article-pagination links">
		{$pagination}
	</div>

{/if}
