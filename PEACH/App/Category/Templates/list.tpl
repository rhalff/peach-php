<h1>Category</h1>
{foreach:items,item}
<div class="Category">
<h2>{item.gettitle():h}</h2>
<p>{item.getdescription():h}</p>
{item.displayImage():h}
<p>{item.getViewLink():h}|{item.getEditLink():h}|{item.getDeleteLink():h}</p>
</div>
{end:}
<p>{addLink:h}</p>
