<div>
{if:noImages}
<p>There are currently no images in the database</p>
{else:}
{foreach:images,image}
<div style="float: left; margin: 1em;">
<h3>{image[label]:h}</h3>
{image[image]:h}
</div>
{end:}
{end:}
<br style="clear: both"/>
<p>{addLink:h}</p>
</div>
