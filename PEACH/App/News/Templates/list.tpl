<h1>news</h1>
{foreach:items,item}
<div class="newsItem">
<p>{item.getposton():h}</p>
<h2>{item.getsubject():h}</h2>
<div style="float: left; margin-right: 1em;">{item.getThumb():h}</div>
<p>{item.getsummary():h}</p>

<br style="clear: both"/>
<p>{item.getViewLink():h}|{item.getEditLink():h}|{item.getDeleteLink():h}</p>
</div>
{end:}
<p>{addLink:h}</p>
